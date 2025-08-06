<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\CaptureFileUpload;
use App\Jobs\ProcessFileUpload;
use App\Models\FileLoad;
use App\Models\UcrCardData;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Response;
use Route;
use Session;
use Str;

class FileLoadController extends Controller
{
    use ApiResponses;

    /**
     * Upload file.
     */
    public function upload(Request $request)
    {
        /**
         * NOTE: At this time - ONLY single file is allowed to be uploaded at a time.
         */

        // Check if has a file
        if (!$request->hasFile('file')) {
            # code...
            // return response()->json(['upload_file_not_found'], 400);
            return $this->error('Upload File Not Found', 400);
        }

        try {
            //code...
            $allowedFileExtension = ['csv', 'xslx', 'xml', 'json'];
            // $fileUploaded = [];

            // validate extension
            $fileExtension = $request->file('file')->getClientOriginalExtension();

            $check = in_array(Str::lower($fileExtension), haystack: $allowedFileExtension);

            if ($check) {
                # code...
                // return response()->json(['file_extension_is_valid'], 200);
                // $fileName = date('Ymdhis') . '_' . $request->file('file')->getClientOriginalName();
                $fileName = $request->file('file')->getClientOriginalName();
                $filePath = $request->file('file')->storePubliclyAs('public/uploads', $fileName);

                $fileExtension = $request->file('file')->getClientOriginalExtension();
                $fileType = $request->file('file')->getClientMimeType();
                $fileSize = $request->file('file')->getSize();

                // dd($this->getApiInvoker());

                $fileLoad = [
                    'filePath' => $filePath,
                    'fileName' => $fileName,
                    'fileType' => $fileType,
                    'fileSize' => $this->filesize_format($fileSize),
                    'createdBy' => $this->getApiInvoker(),
                ];


            } else {
                return $this->error('Invalid File Format', statusCode: 422);
                // return response()->json(['invalid_file_format'], 422);
            }
        } catch (\ErrorException $errorException) {
            return $this->error($errorException->getMessage(), 404);
        }

        // Dispatch the job to capture the file uploaded.
        CaptureFileUpload::dispatch($fileLoad);
        // Dispatch the job to process the file uploaded.
        ProcessFileUpload::dispatch($fileLoad);
        return $this->ok('Success', $fileLoad);
    }

    /**
     * Download file.
     */
    public function download(string $fileName)
    {
        try {
            // dd(public_path());
            $path = storage_path("app/public/downloads/$fileName");
            $headers = [
                'Content-Type' => 'text/csv',
            ];

            if (!file_exists($path)) {
                return $this->error("File $fileName does not exist.", 404);
            }

            return response()->download($path);

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 404);
        }
    }


    /**
     * Utilities
     */
    private function filesize_format($size)
    {
        // Using IEC standard based on Units Policy described at https://wiki.ubuntu.com/UnitsPolicy
        $precision = 2;
        $units = ['B', 'KB', 'MB', 'GB'];

        $size = max($size, 0);
        $pow = floor(($size ? log($size) : 0) / log(1000));
        $pow = min($pow, count($units) - 1);
        $size /= pow(1024, $pow);

        return round($size, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get the user that invoked the api call.
     */
    private function getApiInvoker()
    {
        $requestInvoker = Request::create('/api/v1/user', 'GET');
        $requestInvoker->headers->set('Accept', 'appliction/json');
        $response = app()->handle($requestInvoker);
        $invoker = json_decode($response->getContent());
        // dd($invoker->name);
        return $invoker->name;
    }


}
