<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (!$request->user()->hasRole('superadmin')) {
                return response()->json(["message" => "Unauthorized"], 401);
            }
            $settings = GeneralSetting::pluck('value', 'key');
            return response()->json($settings, 200);
        } catch (\Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!$request->user()->hasRole('superadmin')) {
                return response()->json(["message" => "Unauthorized"], 401);
            }

            $data = $request->all();

            foreach ($data as $key => $value) {
                GeneralSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
            return response()->json(["message" => "Settings updated successfully"], 200);
        } catch (\Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
