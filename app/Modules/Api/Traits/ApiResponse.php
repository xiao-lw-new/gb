<?php

namespace App\Modules\Api\Traits;

use App\Modules\Api\Constants\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success($data = null, string $msg = ''): JsonResponse
    {
        return response()->json([
            'code' => ResponseCode::SUCCESS,
            'msg'  => $msg,
            'data' => $this->formatData($data)
        ]);
    }

    protected function error(string $msg = 'Error', int $code = ResponseCode::BUSINESS_ERROR, $data = null): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $this->formatData($data)
        ]);
    }

    private function formatData($data)
    {
        if (is_null($data)) {
            return new \stdClass();
        }

        if ($data instanceof LengthAwarePaginator) {
            return [
                'list' => $data->items(),
                'pagination' => [
                    'total'        => $data->total(),
                    'per_page'     => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                ]
            ];
        }

        if (is_array($data) || $data instanceof \Illuminate\Support\Collection) {
            if (is_array($data) && count(array_filter(array_keys($data), 'is_string')) > 0) {
                return $data;
            }
            return ['list' => $data];
        }

        return $data;
    }
}

