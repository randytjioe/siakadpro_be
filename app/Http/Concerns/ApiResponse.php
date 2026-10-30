<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/** Bentuk respons API seragam (rules/15): {data}, {data, meta}, {message, errors?}. */
trait ApiResponse
{
    protected function ok(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve();
        }
        $body = ['data' => $data];
        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status);
    }

    protected function created(mixed $data = null): JsonResponse
    {
        return $this->ok($data, [], 201);
    }

    /**
     * @param  class-string<JsonResource>  $resource
     */
    protected function paginated(LengthAwarePaginator $page, string $resource): JsonResponse
    {
        return response()->json([
            'data' => $resource::collection(collect($page->items()))->resolve(),
            'meta' => [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    protected function fail(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        $body = ['message' => $message];
        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /** per_page dibatasi 1..100 (rules/15). */
    protected function perPage(?int $requested, int $default = 25): int
    {
        return max(1, min(100, $requested ?? $default));
    }
}
