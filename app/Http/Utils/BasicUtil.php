<?php

namespace App\Http\Utils;

use App\Models\Expense;
use App\Models\LandlordRentPayable;
use App\Models\Rent;
use App\Models\Repair;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait BasicUtil
{

    public function processArrears($agreement, $rents, $updateRecords = false)
    {
        $startDate = Carbon::parse($agreement->date_of_moving);

        if (request()->filled("year") && request()->filled("month") && !$updateRecords) {
            $endDate = Carbon::createFromDate(request()->input("year"), request()->input("month"), 1)
                ->endOfMonth()->endOfDay();
        } else {
            $endDate = Carbon::parse($agreement->tenant_contact_expired_date);
        }

        $rentAmount = $agreement->agreed_rent;
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


                $total_arrear += $rentAmount;

                foreach ($this_month_rents as &$rent) {
                    $paidAmount = $rent->paid_amount ?? 0;

                    // Update arrear balance
                    $total_arrear -=  $paidAmount;

                    $rent->arrear = $total_arrear;

                    if ($total_arrear > 0) {
                        $rent->payment_status = 'partially_paid'; // Outstanding balance remains
                    } elseif ($total_arrear == 0) {
                        $rent->payment_status = 'fully_paid'; // Exact payment made, no arrears
                    } else {
                        $rent->payment_status  = 'overpaid'; // Payment exceeds due amount
                    }

                    if ($updateRecords) {
                        $rent->save();
                    }
                }
            }

            $currentYear++;
        }

        return $total_arrear;
    }

    public function adjust_rent_and_expense_on_rent_payable_delete($landlord_rent_payable_id)  {
        Expense::whereHas("rent_adjustments", function ($q) use ($landlord_rent_payable_id) {
                $q->where("landlord_rent_payable_id", $landlord_rent_payable_id);
            })
            ->update([
                "paid_by" => "agent"
            ]);

        Repair::whereHas("rent_adjustments", function ($q) use ($landlord_rent_payable_id) {
                $q->where("landlord_rent_payable_id", $landlord_rent_payable_id);
            })
            ->update([
                "paid_by" => "agent"
            ]);
    }
       public function adjust_rent_and_expense_on_rent_delete($rent_id)  {
       $landlord_rent_payable = LandlordRentPayable::whereHas("payable_rents", function ($q) use ($rent_id) {
                $q->where("rent_id", $rent_id);
       })
       ->first();

       if(empty($landlord_rent_payable)) {
           return false;
       }

       $this->adjust_rent_and_expense_on_rent_payable_delete($landlord_rent_payable->id);

       $landlord_rent_payable->delete();

    }
    public function calculatePayments($agreement, $compareDate)
    {
        $start_date = Carbon::parse($agreement->date_of_moving)->startOfDay();
        $end_date = Carbon::parse($agreement->tenant_contact_expired_date)->endOfDay();
        $due_day = (int)$agreement->rent_due_day;

        $compareDate = Carbon::parse($compareDate);

        $due_dates = [];
        $current_date = $start_date->copy()->startOfMonth();

        // ✅ Step 1: Generate all due dates
        while ($current_date <= $end_date) {
            $due_date = $current_date->copy()->day(min($due_day, $current_date->daysInMonth));

            if ($due_date >= $start_date && $due_date <= $end_date) {
                $due_dates[] = $due_date->copy();
            }

            $current_date->addMonth();
        }


        $passed_due_count = collect($due_dates)->filter(function ($date) use ($compareDate) {
            return Carbon::parse($date)->lt($compareDate);
        })->count();


        $total_rent   = $agreement->agreed_rent * $passed_due_count;
        $total_paid = $agreement->rents()
            ->sum('paid_amount');
        $total_arrears = $total_rent - $total_paid;

        $last_passed_due_date = collect($due_dates)
            ->filter(function ($date) use ($compareDate) {
                return Carbon::parse($date)->lt($compareDate);
            })
            ->last();
        return [
            'total_rent' => $total_rent,
            'total_paid' => $total_paid,
            'total_arrears' => $total_arrears,
            "last_passed_due_date" => $last_passed_due_date,

        ];
    }

    public function storeUploadedFiles($filePaths, $fileKey, $targetLocation, $isNestedFiles = false, $propertyId = null)
    {


        // Step 2: Handle nested arrays of file paths recursively
        if ($isNestedFiles) {
            return collect($filePaths)->map(function ($nestedFilePath) use ($fileKey, $targetLocation, $propertyId) {
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
        return collect($filePaths)->map(function ($filePathItem) use ($temporaryFilesDirectory, $fileKey, $targetLocation, $propertyId) {

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

        if (request()->filled("id") && empty($data)) {
            throw new Exception("No data found", 404);
        }
        return $data;
    }
}
