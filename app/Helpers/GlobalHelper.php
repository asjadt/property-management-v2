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
}
