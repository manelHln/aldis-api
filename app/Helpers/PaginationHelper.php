<?php

namespace App\Helpers;

use Illuminate\Pagination\CursorPaginator;

class PaginationHelper
{
    public static function cursorPaginated(CursorPaginator $result, ?string $resourceClass = null): array
    {
        $items = $resourceClass && class_exists($resourceClass)
            ? $resourceClass::collection($result->items())
            : $result->items();

        return [
            'items' => $items,
            'path' => $result->path(),
            'per_page' => $result->perPage(),
            'next_cursor' => $result->nextCursor()?->encode(),
            'next_page_url' => $result->nextPageUrl(),
            'prev_cursor' => $result->previousCursor()?->encode(),
            'prev_page_url' => $result->previousPageUrl(),
        ];
    }
}
