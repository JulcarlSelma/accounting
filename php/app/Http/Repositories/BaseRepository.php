<?php

namespace App\Http\Repositories;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseRepository
{
    protected $model;

    public $badRequest = Response::HTTP_BAD_REQUEST;

    public $unauthorized = Response::HTTP_UNAUTHORIZED;

    public $forbidden = Response::HTTP_FORBIDDEN;

    public $notFound = Response::HTTP_NOT_FOUND;

    public $internalServerError = Response::HTTP_INTERNAL_SERVER_ERROR;

    // For Helper
    protected $helper = null;

    // For multiple helpers
    protected $helpers = [];

    // For multiple models
    protected $models = [];

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function success($data, $message = '', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'count' => is_countable($data) ? count($data) : count($data->toArray()),
            'message' => $message,
            'errors' => null,
        ], $statusCode);
    }

    public function error($error = '', $errors = [], $statusCode = 500): JsonResponse
    {
        return response()->json([
            'data' => null,
            'message' => $error,
            'errors' => $errors,
        ], $statusCode);
    }
}
