<?php

namespace App\Traits;

trait ApiResponses
{
    protected function ok($message, $data = [])
    {
        return $this->success($message, $data, 200);
    }

    protected function success($message, $data = [], $statusCode = 200)
    {
        return response()->json($this->buildPayload($message, $data, $statusCode), $statusCode);
    }

    protected function error($message, $statusCode)
    {
        return response()->json($this->buildPayload($message, [], $statusCode), $statusCode);
    }

    // Add convenience methods for consistency
    protected function successResponse($message, $data = [], $statusCode = 200)
    {
        return $this->success($message, $data, $statusCode);
    }

    protected function errorResponse($message, $data = [], $statusCode = 400)
    {
        return response()->json($this->buildPayload($message, $data, $statusCode), $statusCode);
    }

    private function buildPayload($message, $data, $statusCode)
    {
        $status = $statusCode >= 400 ? 'error' : 'success';

        $payload = [
            'message' => $message,
            'status' => $status,
            'status_code' => $statusCode,
            'data' => $data,
        ];

        if (is_array($data)) {
            $payload['count'] = count($data);
        }

        return $payload;
    }
}
