<?php

namespace App\Jobs;

use App\Models\IdmsCardData;
use App\Models\IdmsCardDataStaging;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeIdmsCardDataFromStaging implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Large merge runs can be expensive in initial sync windows.
     *
     * @var int
     */
    public $timeout = 14400;

    /**
     * Only these fields trigger updates for existing rows.
     */
    private const TRACKED_UPDATE_FIELDS = [
        'ucr_id',
        'iso',
        'library_card_number',
        'status1',
        'class',
        'year_in_school',
        'issue_date',
        'edit_date',
        'photo_date',
    ];

    public function __construct(
        private readonly ?int $chunkSize = null,
        private readonly bool $fullSync = false,
    ) {
    }

    public function handle(): void
    {
        $chunkSize = $this->normalizeInt($this->chunkSize, (int) config('idms_sync.merge.chunk_size', 2500), 250, 10000);

        $inserted = 0;
        $updated = 0;
        $unchanged = 0;
        $skippedNoMatchKey = 0;

        Log::info('Starting IDMS merge from staging to actual table', [
            'chunk_size' => $chunkSize,
            'full_sync' => $this->fullSync,
        ]);

        if ($this->fullSync || IdmsCardData::count() === 0) {
            $result = $this->runFullSyncFromStaging($chunkSize);

            Log::info('Completed IDMS full-sync merge from staging', $result);

            return;
        }

        IdmsCardDataStaging::orderBy('id')->chunkById($chunkSize, function ($stagingRows) use (&$inserted, &$updated, &$unchanged, &$skippedNoMatchKey) {
            $rowsByMatchKey = [];

            foreach ($stagingRows as $stagingRow) {
                if (empty($stagingRow->match_key)) {
                    $skippedNoMatchKey++;
                    continue;
                }

                $rowsByMatchKey[$stagingRow->match_key] = $stagingRow;
            }

            if (empty($rowsByMatchKey)) {
                return;
            }

            $existingRows = IdmsCardData::whereIn('match_key', array_keys($rowsByMatchKey))
                ->get()
                ->keyBy('match_key');

            $upsertRows = [];
            $now = now();

            foreach ($rowsByMatchKey as $matchKey => $stagingRow) {
                $candidate = $this->mapStagingToActualRow($stagingRow, $now);
                $existingRow = $existingRows->get($matchKey);

                if ($existingRow === null) {
                    $candidate['load_status'] = 'created';
                    $upsertRows[] = $candidate;
                    $inserted++;
                    continue;
                }

                if ($this->requiresUpdate($existingRow, $candidate)) {
                    $candidate['load_status'] = 'updated';
                    $candidate['created_at'] = $existingRow->created_at;
                    $upsertRows[] = $candidate;
                    $updated++;
                } else {
                    $unchanged++;
                }
            }

            if (!empty($upsertRows)) {
                DB::table('idms_card_data')->upsert(
                    $upsertRows,
                    ['match_key'],
                    [
                        'ucr_id',
                        'iso',
                        'library_card_number',
                        'status1',
                        'class',
                        'year_in_school',
                        'issue_date',
                        'edit_date',
                        'photo_date',
                        'load_status',
                        'updated_at',
                    ]
                );
            }

            Log::info('Processed merge chunk', [
                'chunk_rows' => count($rowsByMatchKey),
                'inserted' => $inserted,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'skipped_no_match_key' => $skippedNoMatchKey,
            ]);

            gc_collect_cycles();
        });

        Log::info('Completed IDMS merge from staging', [
            'inserted' => $inserted,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'skipped_no_match_key' => $skippedNoMatchKey,
        ]);
    }

    private function runFullSyncFromStaging(int $chunkSize): array
    {
        $stagingCount = IdmsCardDataStaging::count();
        $inserted = 0;

        Log::info('Running full-sync mirror from staging to actual table', [
            'staging_count' => $stagingCount,
            'chunk_size' => $chunkSize,
        ]);

        IdmsCardData::truncate();

        IdmsCardDataStaging::orderBy('id')->chunkById($chunkSize, function ($stagingRows) use (&$inserted) {
            $insertRows = [];
            $now = now();

            foreach ($stagingRows as $stagingRow) {
                $insertRows[] = $this->mapStagingToActualRow($stagingRow, $now);
            }

            if (!empty($insertRows)) {
                DB::table('idms_card_data')->insert($insertRows);
                $inserted += count($insertRows);
            }

            gc_collect_cycles();
        });

        return [
            'staging_count' => $stagingCount,
            'inserted' => $inserted,
            'actual_count' => IdmsCardData::count(),
        ];
    }

    private function mapStagingToActualRow(IdmsCardDataStaging $row, $timestamp): array
    {
        return [
            'match_key' => $row->match_key,
            'net_id' => $row->net_id ?? '',
            'ucr_id' => $row->ucr_id ?? '',
            'student_id' => $row->student_id ?? '',
            'iso' => $row->iso ?? '',
            'library_card_number' => $row->library_card_number ?? '',
            'status1' => $row->status1 ?? '',
            'class' => $row->class ?? '',
            'year_in_school' => $row->year_in_school ?? '',
            'student_fac' => $row->student_fac ?? '',
            'prox_int' => $row->prox_int ?? '',
            'issue_date' => $row->issue_date,
            'edit_date' => $row->edit_date,
            'photo_date' => $row->photo_date,
            'imported_date' => $row->imported_date,
            'load_status' => 'updated',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function requiresUpdate(IdmsCardData $existingRow, array $candidate): bool
    {
        foreach (self::TRACKED_UPDATE_FIELDS as $field) {
            $existingValue = $this->normalizeComparableValue($existingRow->{$field});
            $candidateValue = $this->normalizeComparableValue($candidate[$field] ?? null);

            if ($existingValue !== $candidateValue) {
                return true;
            }
        }

        return false;
    }

    private function normalizeComparableValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeInt(?int $value, int $default, int $min, int $max): int
    {
        $candidate = $value ?? $default;

        if ($candidate < $min) {
            return $min;
        }

        if ($candidate > $max) {
            return $max;
        }

        return $candidate;
    }
}
