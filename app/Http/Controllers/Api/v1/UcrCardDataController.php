<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\IdmsCardData;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UcrCardDataController extends Controller
{
    use ApiResponses;

    /**
     * Get all UCR card data records or search by parameters
     */
    public function list(Request $request)
    {
        try {
            $query = IdmsCardData::query();

            // Check for search parameters
            if ($request->has('net_id')) {
                $netId = $request->input('net_id');
                $query->where('net_id', $netId);
                $records = $query->get();

                if ($records->isEmpty()) {
                    return $this->errorResponse('No records found for the provided net_id', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by net_id', $this->transformCollection($records));
            }

            if ($request->has('ssn')) {
                $ssn = $request->input('ssn');
                $record = $query->where('ucr_id', $ssn)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided SSN', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by SSN', $this->transformRecord($record));
            }

            if ($request->has('student_id')) {
                $studentId = $request->input('student_id');
                $record = $query->where('student_id', $studentId)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided student_id', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by student_id', $this->transformRecord($record));
            }

            if ($request->has('iso')) {
                $iso = $request->input('iso');
                $record = $query->where('iso', $iso)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided ISO', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by ISO', $this->transformRecord($record));
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                // Validate date format
                try {
                    $startDate = Carbon::parse($startDate)->format('Y-m-d');
                    $endDate = Carbon::parse($endDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    return $this->errorResponse('Invalid date format. Please use YYYY-MM-DD format', [], 400);
                }

                $records = $query->whereBetween('issue_date', [$startDate, $endDate])->get();

                if ($records->isEmpty()) {
                    return $this->errorResponse('No records found for the provided date range', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by date range', $this->transformCollection($records));
            }

            // Default: return all records with pagination
            $perPage = $request->input('per_page', 50); // Default 50 records per page
            $records = $query->paginate($perPage);
            $transformedPaginator = $this->transformPaginator($records);

            return $this->successResponse('All UCR card data retrieved successfully', $transformedPaginator);

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while retrieving UCR card data', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by net_id specifically (alternative endpoint)
     */
    public function searchByNetId(Request $request, $netId)
    {
        try {
            $records = IdmsCardData::where('net_id', $netId)->get();

            if ($records->isEmpty()) {
                return $this->errorResponse('No records found for net_id: ' . $netId, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $this->transformCollection($records));

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by net_id', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by SSN specifically (alternative endpoint)
     */
    public function searchBySsn(Request $request, $ssn)
    {
        try {
            $record = IdmsCardData::where('ucr_id', $ssn)->first();

            if (!$record) {
                return $this->errorResponse('No record found for SSN: ' . $ssn, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $this->transformRecord($record));

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by SSN', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by student_id specifically (alternative endpoint)
     */
    public function searchByStudentId(Request $request, $studentId)
    {
        try {
            $record = IdmsCardData::where('student_id', $studentId)->first();

            if (!$record) {
                return $this->errorResponse('No record found for student_id: ' . $studentId, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $this->transformRecord($record));

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by student_id', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by ISO specifically (alternative endpoint)
     */
    public function searchByIso(Request $request, $iso)
    {
        try {
            $record = IdmsCardData::where('iso', $iso)->first();

            if (!$record) {
                return $this->errorResponse('No record found for ISO: ' . $iso, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $this->transformRecord($record));

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by ISO', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by Library Number specifically (alternative endpoint)
     */
    public function searchByLibraryNumber(Request $request, $libraryNumber)
    {
        try {
            $record = IdmsCardData::where('library_card_number', $libraryNumber)->first();

            if (!$record) {
                return $this->errorResponse('No record found for Library Number: ' . $libraryNumber, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $this->transformRecord($record));

        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by Library Number', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by date range specifically (alternative endpoint)
     */
    public function searchByDateRange(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $records = IdmsCardData::whereBetween('issue_date', [$startDate, $endDate])->get();

            if ($records->isEmpty()) {
                return $this->errorResponse('No records found for the date range: ' . $startDate . ' to ' . $endDate, [], 404);
            }
            $groupedResults = [
                'total_found' => $records->count(),
                'records' => $this->transformCollection($records),
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ];

            return $this->successResponse('UCR card data retrieved successfully for date range', $groupedResults);

        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by date range', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search by multiple parameters (bulk search endpoint)
     */
    public function searchMultiple(Request $request)
    {
        try {
            $request->validate([
                'net_ids' => 'array',
                'net_ids.*' => 'string',
                'ssns' => 'array',
                'ssns.*' => 'string',
                'student_ids' => 'array',
                'student_ids.*' => 'string',
                'isos' => 'array',
                'isos.*' => 'string',
            ]);

            $query = IdmsCardData::query();
            $hasSearchCriteria = false;

            // Build the query with OR conditions for different parameter types
            $query->where(function ($subQuery) use ($request, &$hasSearchCriteria) {
                $conditions = [];

                if ($request->has('net_ids') && is_array($request->input('net_ids'))) {
                    $netIds = array_filter($request->input('net_ids')); // Remove empty values
                    if (!empty($netIds)) {
                        $conditions[] = function ($q) use ($netIds) {
                            $q->whereIn('net_id', $netIds);
                        };
                        $hasSearchCriteria = true;
                    }
                }

                if ($request->has('ssns') && is_array($request->input('ssns'))) {
                    $ssns = array_filter($request->input('ssns')); // Remove empty values
                    if (!empty($ssns)) {
                        $conditions[] = function ($q) use ($ssns) {
                            $q->whereIn('ucr_id', $ssns);
                        };
                        $hasSearchCriteria = true;
                    }
                }

                if ($request->has('student_ids') && is_array($request->input('student_ids'))) {
                    $studentIds = array_filter($request->input('student_ids')); // Remove empty values
                    if (!empty($studentIds)) {
                        $conditions[] = function ($q) use ($studentIds) {
                            $q->whereIn('student_id', $studentIds);
                        };
                        $hasSearchCriteria = true;
                    }
                }

                if ($request->has('isos') && is_array($request->input('isos'))) {
                    $isos = array_filter($request->input('isos')); // Remove empty values
                    if (!empty($isos)) {
                        $conditions[] = function ($q) use ($isos) {
                            $q->whereIn('iso', $isos);
                        };
                        $hasSearchCriteria = true;
                    }
                }

                // Apply all conditions with OR logic
                foreach ($conditions as $index => $condition) {
                    if ($index === 0) {
                        $condition($subQuery);
                    } else {
                        $subQuery->orWhere($condition);
                    }
                }
            });

            if (!$hasSearchCriteria) {
                return $this->errorResponse('At least one search parameter must be provided', [], 400);
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                return $this->errorResponse('No records found for the provided search parameters', [], 404);
            }

            // Group results by search parameter type for easier consumption
            $groupedResults = [
                'total_found' => $records->count(),
                'records' => $this->transformCollection($records),
                'search_summary' => [
                    'net_ids_searched' => $request->input('net_ids', []),
                    'ssns_searched' => $request->input('ssns', []),
                    'student_ids_searched' => $request->input('student_ids', []),
                    'isos_searched' => $request->input('isos', []),
                ]
            ];

            return $this->successResponse('UCR card data retrieved successfully for multiple search parameters', $groupedResults);

        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while performing multiple parameter search', ['error' => $e->getMessage()], 500);
        }
    }

    protected function transformPaginator(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $transformedItems = $this->transformCollection($paginator->getCollection());

        return new LengthAwarePaginator(
            $transformedItems,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => $paginator->path(),
                'pageName' => $paginator->getPageName(),
            ]
        );
    }

    protected function transformCollection($collection): array
    {
        return collect($collection)
            ->map(fn($record) => $this->transformRecord($record))
            ->values()
            ->all();
    }

    protected function transformRecord($record): array
    {
        if ($record instanceof \Illuminate\Database\Eloquent\Model) {
            $data = $record->toArray();
        } elseif (is_array($record)) {
            $data = $record;
        } else {
            $data = [];
        }

        return [
            'id' => $data['id'] ?? null,
            'net_id' => $data['net_id'] ?? null,
            'ssn' => $data['ucr_id'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'iso' => $data['iso'] ?? null,
            'lib_num' => $data['library_card_number'] ?? null,
            'status1' => $data['status1'] ?? null,
            'class' => $data['class'] ?? null,
            'yr_in_school' => $data['year_in_school'] ?? null,
            'stud_fac' => $data['student_fac'] ?? null,
            'prox_int' => $data['prox_int'] ?? null,
            'prox_ext' => null,
            'prox_status' => null,
            'issued' => $data['issue_date'] ?? null,
            'edit_date' => $data['edit_date'] ?? null,
            'photo_date' => $data['photo_date'] ?? null,
            'imported' => $data['imported_date'] ?? null,
            'load_status' => $data['load_status'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }
}
