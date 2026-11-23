<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\CaptureFileUpload;
use App\Jobs\MergeCardData;
use App\Jobs\ProcessFileUpload;
use App\Jobs\VerifyFileUpload;
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

        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xml,json|max:10240',
            ]);

            $uploadedFile = $validated['file'];
            $fileExtension = Str::lower($uploadedFile->getClientOriginalExtension());
            $fileType = $uploadedFile->getClientMimeType();
            $fileSize = $uploadedFile->getSize();

            $originalBaseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBaseName = Str::slug($originalBaseName, '_');
            if ($safeBaseName === '') {
                $safeBaseName = 'upload';
            }

            $fileName = sprintf(
                '%s_%s_%s.%s',
                Str::limit($safeBaseName, 80, ''),
                now()->format('YmdHis'),
                Str::random(8),
                $fileExtension
            );

            $filePath = $uploadedFile->storeAs('public/uploads', $fileName);

            $fileLoad = [
                'filePath' => $filePath,
                'fileName' => $fileName,
                'fileType' => $fileType,
                'fileSize' => $this->filesize_format($fileSize),
                'createdBy' => $this->getApiInvoker(),
            ];
        } catch (\ErrorException $errorException) {
            return $this->error($errorException->getMessage(), 404);
        }

        // Dispatch the job to capture the file uploaded.
        CaptureFileUpload::dispatch($fileLoad);

        // Dispatch the job to process the file uploaded.
        ProcessFileUpload::dispatch($fileLoad);

        // Dispatch the job to merge the staging and actual tables after the file was processed correctly.
        //MergeCardData::dispatch();

        return $this->ok('Success', $fileLoad);
    }

    /**
     * Download file.
     */
    public function download(string $fileName)
    {
        try {
            if (!preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]*\z/', $fileName)) {
                return $this->error('Invalid file name format.', 400);
            }

            $safeFileName = basename($fileName);
            if ($safeFileName !== $fileName) {
                return $this->error('Invalid file name format.', 400);
            }

            $path = storage_path("app/public/downloads/$safeFileName");
            $headers = [
                'Content-Type' => 'text/csv',
            ];

            if (!file_exists($path)) {
                return $this->error("File $safeFileName does not exist.", 404);
            }

            return response()->download($path, $safeFileName, $headers);

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
