<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\UcrCardDataActual;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
            $query = UcrCardDataActual::query();

            // Check for search parameters
            if ($request->has('net_id')) {
                $netId = $request->input('net_id');
                $query->where('net_id', $netId);
                $records = $query->get();

                if ($records->isEmpty()) {
                    return $this->errorResponse('No records found for the provided net_id', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by net_id', $records);
            }

            if ($request->has('ssn')) {
                $ssn = $request->input('ssn');
                $record = $query->where('ssn', $ssn)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided SSN', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by SSN', $record);
            }

            if ($request->has('student_id')) {
                $studentId = $request->input('student_id');
                $record = $query->where('student_id', $studentId)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided student_id', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by student_id', $record);
            }

            if ($request->has('iso')) {
                $iso = $request->input('iso');
                $record = $query->where('iso', $iso)->first();

                if (!$record) {
                    return $this->errorResponse('No record found for the provided ISO', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by ISO', $record);
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

                $records = $query->whereBetween('issued', [$startDate, $endDate])->get();

                if ($records->isEmpty()) {
                    return $this->errorResponse('No records found for the provided date range', [], 404);
                }

                return $this->successResponse('UCR card data retrieved successfully by date range', $records);
            }

            // Default: return all records with pagination
            $perPage = $request->input('per_page', 50); // Default 50 records per page
            $records = $query->paginate($perPage);

            return $this->successResponse('All UCR card data retrieved successfully', $records);

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
            $records = UcrCardDataActual::where('net_id', $netId)->get();

            if ($records->isEmpty()) {
                return $this->errorResponse('No records found for net_id: ' . $netId, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $records);

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
            $record = UcrCardDataActual::where('ssn', $ssn)->first();

            if (!$record) {
                return $this->errorResponse('No record found for SSN: ' . $ssn, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $record);

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
            $record = UcrCardDataActual::where('student_id', $studentId)->first();

            if (!$record) {
                return $this->errorResponse('No record found for student_id: ' . $studentId, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $record);

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
            $record = UcrCardDataActual::where('iso', $iso)->first();

            if (!$record) {
                return $this->errorResponse('No record found for ISO: ' . $iso, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $record);

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
            $record = UcrCardDataActual::where('lib_num', $libraryNumber)->first();

            if (!$record) {
                return $this->errorResponse('No record found for Library Number: ' . $libraryNumber, [], 404);
            }

            return $this->successResponse('UCR card data retrieved successfully', $record);

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

            $records = UcrCardDataActual::whereBetween('issued', [$startDate, $endDate])->get();

            if ($records->isEmpty()) {
                return $this->errorResponse('No records found for the date range: ' . $startDate . ' to ' . $endDate, [], 404);
            }
            $groupedResults = [
                'total_found' => $records->count(),
                'records' => $records,
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

            $query = UcrCardDataActual::query();
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
                            $q->whereIn('ssn', $ssns);
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
                'records' => $records,
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
}
