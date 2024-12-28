<?php

namespace App\Http\Utils;

use Exception;
use Illuminate\Http\Request;

trait BasicUtil
{
    public function retrieveData($query, $orderByField, $tableName)
    {

        $data =  $query->when(!empty(request()->order_by) && in_array(strtoupper(request()->order_by), ['ASC', 'DESC']), function ($query) use ($orderByField, $tableName) {
            return $query->orderBy($tableName . "." . $orderByField, request()->order_by);
        }, function ($query) use ($orderByField, $tableName) {
            return $query->orderBy($tableName . "." . $orderByField, "DESC");
        })
            ->when(request()->filled("id"), function ($query) use ($tableName) {
                return $query->where($tableName . "." . 'id', request()->input("id"))->first();
            }, function ($query) {
                return $query->when(!empty(request()->per_page), function ($query) {
                    return $query->paginate(request()->per_page);
                }, function ($query) {
                    return $query->get();
                });
            });

            if(request()->filled("id") && empty($data)) {
                throw new Exception("No data found",404);
            }
        return $data;

    }

}
