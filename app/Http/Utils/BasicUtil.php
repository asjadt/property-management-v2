<?php

namespace App\Http\Utils;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait BasicUtil
{


    public function storeUploadedFiles($filePaths, $fileKey, $targetLocation, $isNestedFiles = false, $propertyId = null)
{
    // Step 1: Retrieve the authenticated user's business
    $business = auth()->user()->business;

    // Construct the target location by adding the business name and (optionally) the student ID
    $targetLocation = str_replace(' ', '_', $business->name) . "/" .
                      (!empty($propertyId) ? ("/" . base64_encode($propertyId) . "/") : "") .
                      $targetLocation;

    // Step 2: Handle nested arrays of file paths recursively
    if ($isNestedFiles) {
        return collect($filePaths)->map(function ($nestedFilePath) use ($fileKey, $targetLocation) {
            $nestedFilePath[$fileKey] = $this->storeUploadedFiles(
                $nestedFilePath[$fileKey],
                "",
                $targetLocation
            );
            return $nestedFilePath;
        });
    }

    // Step 3: Get the temporary files location from the configuration
    $temporaryFilesDirectory = config("setup-config.temporary_files_location");

    // Process each file path in the input array
    return collect($filePaths)->map(function ($filePathItem) use ($temporaryFilesDirectory, $fileKey, $targetLocation) {
        $filePath = !empty($fileKey) ? $filePathItem[$fileKey] : $filePathItem;

        // Construct the full paths for the temporary and target locations
        $temporaryFilePath = public_path($filePath);
        $targetFilePath = str_replace($temporaryFilesDirectory, $targetLocation, $filePath);
        $absoluteTargetPath = public_path($targetFilePath);

        // Check if the file exists at the temporary location
        if (File::exists($temporaryFilePath)) {
            try {
                // Ensure the target directory exists
                $targetDirectory = dirname($absoluteTargetPath);
                if (!File::exists($targetDirectory)) {
                    File::makeDirectory($targetDirectory, 0755, true);
                }

                // Move the file from the temporary location to the target location
                File::move($temporaryFilePath, $absoluteTargetPath);
                Log::info("File moved successfully from {$temporaryFilePath} to {$absoluteTargetPath}");
            } catch (Exception $exception) {
                throw new Exception(
                    "Failed to move file from {$temporaryFilePath} to {$absoluteTargetPath}: " . $exception->getMessage(),
                    500
                );
            }
        } else {
            // Log and throw an error if the file does not exist
            Log::error("File does not exist: {$temporaryFilePath}");
            throw new Exception("File does not exist at {$temporaryFilePath}", 500);
        }

        // Update the file path in the item
        if (!empty($fileKey)) {
            $filePathItem[$fileKey] = basename($targetFilePath);
        } else {
            $filePathItem = basename($targetFilePath);
        }

        return $filePathItem;
    })->toArray();
}



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
