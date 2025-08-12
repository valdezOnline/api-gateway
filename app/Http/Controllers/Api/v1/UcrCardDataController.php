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

            return $this->successResponse('UCR card data retrieved successfully for date range', $records);

        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while searching by date range', ['error' => $e->getMessage()], 500);
        }
    }
}
