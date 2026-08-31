<?php

namespace App\Jobs;

use App\Services\IdmsCardData\DataTransferObjects\IdmsCardData;
use App\Services\IdmsCardData\IdmsCardDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoadIdmsCardDataStaging implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const STAGING_TABLE = 'idms_card_data_staging';
    private const STAGING_SHADOW_TABLE = 'idms_card_data_staging_shadow';
    private const STAGING_BACKUP_TABLE = 'idms_card_data_staging_backup';

    /**
     * Large dataset sync can run for hours during initial loads.
     *
     * @var int
     */
    public $timeout = 14400;

    public function __construct(
        private readonly ?int $pageSize = null,
        private readonly ?int $insertChunkSize = null,
    ) {
    }

    public function handle(IdmsCardDataService $service): array
    {
        $pageSize = $this->normalizeInt($this->pageSize, (int) config('idms_sync.staging.page_size', 5000), 1, 5000);
        $insertChunkSize = $this->normalizeInt($this->insertChunkSize, (int) config('idms_sync.staging.insert_chunk_size', 1000), 100, 5000);

        Log::info('Starting IDMS staging refresh', [
            'page_size' => $pageSize,
            'insert_chunk_size' => $insertChunkSize,
        ]);

        $offset = 0;
        $totalExpected = null;
        $totalFetched = 0;
        $totalInserted = 0;
        $totalInvalidRows = 0;
        $shadowTable = self::STAGING_SHADOW_TABLE;

        try {
            $this->prepareShadowTable($shadowTable);

            Log::info("{$shadowTable} prepared. Starting to fetch data from IDMS API...");
            while (true) {
                $result = $service->fetchAllIdmsCardDataPage($offset, $pageSize, false);

                if ($result['success'] !== true) {
                    throw new \RuntimeException('Failed loading IDMS staging page at offset ' . $offset . ': ' . json_encode($result['error']));
                }

                $totalExpected = $totalExpected ?? $result['totalCount'];
                $records = $result['records'] ?? [];

                if (empty($records)) {
                    break;
                }

                $mappedRows = [];
                foreach ($records as $record) {
                    if (!is_array($record)) {
                        $totalInvalidRows++;
                        continue;
                    }

                    $dto = IdmsCardData::fromArray($record);
                    $mappedRows[] = $this->mapDtoToStagingRow($dto);
                }

                $this->insertRows($shadowTable, $mappedRows, $insertChunkSize);
                $totalInserted += count($mappedRows);
                $totalFetched += count($records);

                Log::info('Processed IDMS staging page', [
                    'offset' => $offset,
                    'fetched_this_page' => count($records),
                    'total_fetched' => $totalFetched,
                    'total_inserted' => $totalInserted,
                    'total_expected' => $totalExpected,
                ]);

                if (($result['totalReturned'] ?? 0) < $pageSize) {
                    break;
                }

                if ($totalExpected !== null && $totalFetched >= (int) $totalExpected) {
                    break;
                }

                $offset += $pageSize;
                gc_collect_cycles();
            }

            $this->swapShadowIntoStaging($shadowTable);
        } catch (\Throwable $throwable) {
            $this->dropTableIfExists($shadowTable);
            throw $throwable;
        }

        Log::info('Completed IDMS staging refresh', [
            'total_expected' => $totalExpected,
            'total_fetched' => $totalFetched,
            'total_inserted' => $totalInserted,
            'invalid_rows' => $totalInvalidRows,
        ]);

        return [
            'total_expected' => $totalExpected,
            'total_fetched' => $totalFetched,
            'total_inserted' => $totalInserted,
            'invalid_rows' => $totalInvalidRows,
        ];
    }

    private function insertRows(string $tableName, array $rows, int $insertChunkSize): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, $insertChunkSize) as $batch) {
            DB::table($tableName)->insert($batch);
        }
    }

    private function prepareShadowTable(string $shadowTable): void
    {
        Log::info('Preparing shadow table for staging swap.', [
            'shadow_table' => $shadowTable,
        ]);

        $this->dropTableIfExists($shadowTable);

        DB::statement(sprintf(
            'CREATE TABLE `%s` LIKE `%s`',
            $shadowTable,
            self::STAGING_TABLE
        ));
    }

    private function swapShadowIntoStaging(string $shadowTable): void
    {
        Log::info('Swapping shadow table into staging table.', [
            'staging_table' => self::STAGING_TABLE,
            'shadow_table' => $shadowTable,
            'backup_table' => self::STAGING_BACKUP_TABLE,
        ]);

        $this->dropTableIfExists(self::STAGING_BACKUP_TABLE);

        DB::statement(sprintf(
            'RENAME TABLE `%s` TO `%s`, `%s` TO `%s`',
            self::STAGING_TABLE,
            self::STAGING_BACKUP_TABLE,
            $shadowTable,
            self::STAGING_TABLE
        ));

        $this->dropTableIfExists(self::STAGING_BACKUP_TABLE);
    }

    private function dropTableIfExists(string $tableName): void
    {
        DB::statement(sprintf('DROP TABLE IF EXISTS `%s`', $tableName));
    }

    private function mapDtoToStagingRow(IdmsCardData $dto): array
    {
        $now = now();

        $netId = $this->normalizeKeyPart($dto->netId);
        $ucrId = $this->normalizeKeyPart($dto->ucrId);
        $studentId = $this->normalizeKeyPart($dto->studentId);

        return [
            'net_id' => $this->normalizeStoredText($dto->netId),
            'ucr_id' => $this->normalizeStoredText($dto->ucrId),
            'student_id' => $this->normalizeStoredText($dto->studentId),
            'iso' => $this->normalizeStoredText($dto->iso),
            'library_card_number' => $this->normalizeStoredText($dto->libraryCardNumber),
            'status1' => $this->normalizeStoredText($dto->status1),
            'class' => $this->normalizeStoredText($dto->class),
            'year_in_school' => $this->normalizeStoredText($dto->yearInSchool),
            'student_fac' => $this->normalizeStoredText($dto->studentFac),
            'prox_int' => $this->normalizeStoredText($dto->proxInt),
            'match_key' => $this->buildMatchKey($netId, $ucrId, $studentId),
            'issue_date' => $this->normalizeDate($dto->issuedDate),
            'edit_date' => $this->normalizeDate($dto->editDate),
            'photo_date' => $this->normalizeDate($dto->photoDate),
            'imported_date' => $this->normalizeDate($dto->importedDate),
            'load_status' => 'created',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function buildMatchKey(?string $netId, ?string $ucrId, ?string $studentId): ?string
    {
        if ($netId !== null && $ucrId !== null && $studentId !== null) {
            return 'triple|' . $netId . '|' . $ucrId . '|' . $studentId;
        }

        if ($netId !== null && $ucrId !== null) {
            return 'net-ucr|' . $netId . '|' . $ucrId;
        }

        if ($netId !== null && $studentId !== null) {
            return 'net-student|' . $netId . '|' . $studentId;
        }

        if ($ucrId !== null && $studentId !== null) {
            return 'ucr-student|' . $ucrId . '|' . $studentId;
        }

        return null;
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeStoredText(?string $value): string
    {
        return $this->normalizeText($value) ?? '';
    }

    private function normalizeKeyPart(?string $value): ?string
    {
        return $this->normalizeText($value);
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
