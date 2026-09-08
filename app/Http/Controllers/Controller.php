<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class Controller
{
    /**
     * Return a standardized success JSON response.
     * Automatically handles normal data, API resources, and paginated datasets.
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = array_merge([
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ], $meta);
        } elseif ($data instanceof ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            $response['data'] = $data;
            $paginator = $data->resource;
            $response['meta'] = array_merge([
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ], $meta);
        } else {
            $response['data'] = $data;
            if (! empty($meta)) {
                $response['meta'] = $meta;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Return a standardized failed/error JSON response.
     */
    protected function failed(string $message = 'Failed', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
