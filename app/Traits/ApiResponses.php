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
        // dd(is_array($data));
        if (is_array($data)) {
            return response()->json([
                'message' => $message,
                'status' => 'success',
                'data' => $data,
                'count' => count($data),
            ], $statusCode);
        } else {
            return response()->json([
                'message' => $message,
                'status' => 'success',
                'data' => $data,
            ], $statusCode);
        }
    }

    protected function error($message, $statusCode)
    {
        return response()->json([
            'message' => $message,
            'status' => 'error',
        ], $statusCode);
    }

    // Add convenience methods for consistency
    protected function successResponse($message, $data = [], $statusCode = 200)
    {
        return $this->success($message, $data, $statusCode);
    }

    protected function errorResponse($message, $data = [], $statusCode = 400)
    {
        return response()->json([
            'message' => $message,
            'status' => 'error',
            'data' => $data,
        ], $statusCode);
    }
}