<?php

namespace App\Http\Utils;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait BasicUtil
{

    public function processArrears($agreement, $rents, $updateRecords=false) {
        $startDate = Carbon::parse($agreement->date_of_moving);

        if(request()->filled("year") && request()->filled("month") && !$updateRecords) {
            $endDate = Carbon::createFromDate(request()->input("year"), request()->input("month"), 1)->endOfMonth()->endOfDay();
        } else {
            $endDate = Carbon::parse($agreement->tenant_contact_expired_date);
        }

        $rentAmount = $agreement->rent_amount;
        $total_arrear = 0; // Initial arrear balance

        $currentYear = $startDate->year;

        while ($currentYear <= $endDate->year) {
            $startMonth = ($currentYear === $startDate->year) ? $startDate->month : 1;
            $endMonth = ($currentYear === $endDate->year) ? $endDate->month : 12;

            for ($month = $startMonth; $month <= $endMonth; $month++) {
        // Get rent entry for this year & month
                $this_month_rents = $rents->filter(function ($rent) use ($currentYear, $month) {
                    return $rent->year == $currentYear && $rent->month == $month;
                });

                if ($this_month_rents->isEmpty()) {
                    // No rent record for this month, carry arrear forward
                    $total_arrear += $rentAmount;
                    continue;
                }

                foreach ($this_month_rents as &$rent) {
                    $paidAmount = $rent->paid_amount ?? 0;

                    // Update arrear balance
                    $total_arrear += $rentAmount - $paidAmount;

                    $rent->arrear = $total_arrear;

                    if ($total_arrear > 0) {
                        $rent->payment_status = 'arrears'; // Outstanding balance remains
                    } elseif ($total_arrear == 0) {
                        $rent->payment_status = 'paid'; // Exact payment made, no arrears
                    } else {
                        $rent->payment_status  = 'overpaid'; // Payment exceeds due amount
                    }

                    if($updateRecords) {
                       $rent->save();
                    }

                }
            }

            $currentYear++;
        }

        return $total_arrear;

    }



    public function storeUploadedFiles($filePaths, $fileKey, $targetLocation, $isNestedFiles = false, $propertyId = null)
{


    // Step 2: Handle nested arrays of file paths recursively
    if ($isNestedFiles) {
        return collect($filePaths)->map(function ($nestedFilePath) use ($fileKey, $targetLocation,$propertyId) {
            $nestedFilePath[$fileKey] = $this->storeUploadedFiles(
                $nestedFilePath[$fileKey],
                "",
                $targetLocation,
                false,
                $propertyId
            );
            return $nestedFilePath;
        });
    }

    // Step 3: Get the temporary files location from the configuration
    $temporaryFilesDirectory = config("setup-config.temporary_files_location");

    // Process each file path in the input array
    return collect($filePaths)->map(function ($filePathItem) use ($temporaryFilesDirectory, $fileKey, $targetLocation,$propertyId) {

         // Step 1: Retrieve the authenticated user's business
    $business = auth()->user()->my_business;

    // Construct the target location by adding the business name and (optionally) the property ID

    $targetLocation = str_replace(' ', '_', $business->name) . "/" .
                      (!empty($propertyId) ? ("/" . base64_encode($propertyId) . "/") : "") .
                      $targetLocation;


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
            // throw new Exception("File does not exist at {$temporaryFilePath}", 500);
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


public function renameOrCreateFolder($currentFolderPath, $newFolderName)
{
    // Get the full path of the current folder
    $fullCurrentFolderPath = public_path($currentFolderPath);

    // Define the new folder path
    $newFolderPath = dirname($fullCurrentFolderPath) . '/' . $newFolderName;

    // Check if the current folder exists
    if (File::exists($fullCurrentFolderPath)) {
        try {
            // Rename the folder
            File::move($fullCurrentFolderPath, $newFolderPath);
            Log::info("Folder renamed successfully from {$fullCurrentFolderPath} to {$newFolderPath}");
            return $newFolderPath;
        } catch (\Exception $e) {
            Log::error("Failed to rename folder: " . $e->getMessage());
            throw new Exception("Failed to rename folder: " . $e->getMessage());
        }
    } else {
        // If the folder doesn't exist, create it
        $fullNewFolderPath = public_path($newFolderName);
        if (!File::exists($fullNewFolderPath)) {
            try {
                File::makeDirectory($newFolderPath, 0755, true); // Create the new folder
                Log::info("Folder created successfully at {$newFolderPath}");
                return $newFolderPath;
            } catch (\Exception $e) {
                Log::error("Failed to create folder: " . $e->getMessage());
                throw new Exception("Failed to create folder: " . $e->getMessage());
            }
        }

    }
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
