<?php



if (!function_exists('debugHalt')) {
    function debugHalt($message)
    {
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // Replace "-" with "_" (if needed)
        $message = str_replace('-', '_', $message);

        // Throw readable error
        throw new Exception($message, 400);
    }
}



if (!function_exists('transform_mixed')) {
    function transform_mixed($data, Closure $callback)
    {
        if ($data instanceof \Illuminate\Pagination\Paginator || $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return $data->through($callback);
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map($callback);
        }

        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            return $callback($data);
        }

        throw new \InvalidArgumentException("Unsupported data type.");
    }

    if (!function_exists('retrieve_data')) {
        function retrieve_data($query, $orderBy = 'created_at', $tableName = '')
        {
            // Get order column and sort order
            if (request()->filled('order_by')) {
                $orderBy = request()->input('order_by');
            };
            $orderBy = request()->input('order_by', $orderBy);
            $sortOrder = strtoupper(request()->input('sort_order', 'DESC'));

            // Ensure sort_order is valid
            if (!in_array($sortOrder, ['ASC', 'DESC'])) {
                $sortOrder = 'DESC';
            }

            // Add table prefix if not included
            // if (strpos($orderBy, '.') === false) {
            //     $orderBy = $tableName . '.' . $orderBy;
            // }

            // Apply ordering
            $query = $query->orderBy($orderBy, $sortOrder);

            // Pagination setup
            $perPage = request()->input('per_page');
            $currentPage = request()->input('page', 1);
            $skip = 0;
            $total = 0;
            $totalPages = 1;

            if ($perPage) {
                $paginated = $query->paginate($perPage, ['*'], 'page', $currentPage);

                $data = $paginated->items();
                $skip = ($currentPage - 1) * $perPage;
                $total = $paginated->total();
                $perPage = $paginated->perPage();
                $currentPage = $paginated->currentPage();
                $totalPages = $paginated->lastPage();
            } else {
                $data = $query->get();
                $total = $data->count();
            }

            // Meta info
            $meta = [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'skip' => $skip,
                'total_pages' => $totalPages,
            ];

            // Return data with meta
            return [
                'data' => $data,
                'meta' => $meta,
            ];
        }
    }
}
