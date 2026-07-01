<?php

namespace App\Util;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * @param array<mixed>|LengthAwarePaginator<int, mixed> $data
     * @param class-string<JsonResource>|null $resource
     * @param array<mixed> $errors
     */
    public static function send(
        int $code = Response::HTTP_OK,
        string $message = 'Success response',
        array|LengthAwarePaginator $data = [],
        ?string $resource = null,
        array $errors = [],
        bool $skipPrepare = false,
    ): JsonResponse {
        $prepared = $skipPrepare
            ? ['items' => $data, 'meta' => null]
            : self::prepareData($data, $resource);

        $response = [
            'status' => $code,
            'message' => $message,
            'meta' => $prepared['meta'] ?? null,
            'data' => $prepared['items'],
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * @param array<mixed>|LengthAwarePaginator<int, mixed> $data
     * @param class-string<JsonResource>|null $resource
     */
    public static function success(
        string $message = 'Success response',
        array|LengthAwarePaginator $data = [],
        ?string $resource = null,
    ): JsonResponse {
        return self::send(Response::HTTP_OK, $message, $data, $resource);
    }

    /**
     * @param array<mixed>|LengthAwarePaginator<int, mixed> $data
     * @param class-string<JsonResource>|null $resource
     */
    public static function successPaginated(
        string $message = 'Success response',
        array|LengthAwarePaginator $data = [],
        ?string $resource = null,
    ): JsonResponse {
        return self::send(Response::HTTP_OK, $message, $data, $resource, [], true);
    }

    /**
     * @param array<mixed>|LengthAwarePaginator<int, mixed> $data
     * @param class-string<JsonResource>|null $resource
     */
    public static function created(
        string $message = 'Resource created successfully',
        array|LengthAwarePaginator $data = [],
        ?string $resource = null,
    ): JsonResponse {
        return self::send(Response::HTTP_CREATED, $message, $data, $resource);
    }

    /**
     * @param array<mixed> $errors
     */
    public static function badRequest(string $message, array $errors = []): JsonResponse
    {
        return self::send(code: Response::HTTP_BAD_REQUEST, message: $message, errors: $errors);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::send(Response::HTTP_UNAUTHORIZED, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::send(Response::HTTP_FORBIDDEN, $message);
    }

    public static function notFound(string $message = 'Not found'): JsonResponse
    {
        return self::send(Response::HTTP_NOT_FOUND, $message);
    }

    public static function invalidArgument(string $message): JsonResponse
    {
        return self::send(Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * @param array<mixed> $errors
     * @param class-string<JsonResource>|null $resource
     */
    public static function validationError(string $message, array $errors = [], ?string $resource = null): JsonResponse
    {
        return self::send(code: Response::HTTP_UNPROCESSABLE_ENTITY, message: $message, errors: $errors, resource: $resource);
    }

    /**
     * @param array<mixed> $errors
     */
    public static function unProcessableEntity(string $message, array $errors = []): JsonResponse
    {
        return self::send(code: Response::HTTP_UNPROCESSABLE_ENTITY, message: $message, errors: $errors);
    }

    public static function error(string $message = 'Server error'): JsonResponse
    {
        return self::send(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
    }

    public static function getCode(int|string $code): int
    {
        if ($code === '0' || $code === 0) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return is_int($code) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * @param LengthAwarePaginator<int, mixed> $paginator
     * @return array{current_page: int, per_page: int, total: int, last_page: int}
     */
    protected static function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    /**
     * @param array<mixed>|LengthAwarePaginator<int, mixed> $data
     * @param class-string<JsonResource>|null $resource
     * @return array{meta: array{current_page: int, per_page: int, total: int, last_page: int}|null, items: mixed}
     */
    protected static function prepareData(array|LengthAwarePaginator $data, ?string $resource): array
    {
        if ($data instanceof LengthAwarePaginator && $resource !== null) {
            return [
                'meta' => self::getPaginationMeta($data),
                'items' => $resource::collection($data->items()),
            ];
        }

        return ['meta' => null, 'items' => $data];
    }
}
