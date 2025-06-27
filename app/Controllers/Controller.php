<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

abstract class Controller
{
    public function respond(array $data, int $status = ResponseAlias::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    public function success(string $message, array $data = [], int $status = ResponseAlias::HTTP_OK): JsonResponse
    {
        $data = [
            'message' => $message,
            'data' => $data,
            'status' => $status,
            'success' => true
        ];

        return $this->respond(array_filter($data), $status);
    }

    public function fail(string $error, array $errors = [], int $status = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        $errors = [
            'error' => $error,
            'errors' => $errors,
            'status' => $status,
            'success' => false
        ];

        return $this->respond(array_filter($errors), $status);
    }
}