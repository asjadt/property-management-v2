<?php

    private function seedBusinessDefaults($user)
    {
        $defaultSaleItems = [
            ["name" => "Parking Space", "description" => "Reserved parking space available for sale to property residents.", "price" => 15000],
            ["name" => "Storage Unit", "description" => "Secure storage unit for keeping household or personal belongings.", "price" => 25000],
            ["name" => "Security Deposit", "description" => "Refundable security deposit required for property rental or occupancy.", "price" => 50000],
            ["name" => "Maintenance Package", "description" => "Annual property maintenance package covering routine inspections and minor repairs.", "price" => 12000],
            ["name" => "Gym Membership", "description" => "Monthly access to the residential building's fitness center.", "price" => 1500],
            ["name" => "Swimming Pool Access", "description" => "Monthly access to the property's swimming pool and related facilities.", "price" => 1000],
            ["name" => "Clubhouse Membership", "description" => "Monthly membership providing access to the residential clubhouse facilities.", "price" => 2000],
            ["name" => "Cleaning Service", "description" => "Professional residential cleaning service for apartments or common spaces.", "price" => 3000],
            ["name" => "Move-In Service", "description" => "Assistance with moving furniture and belongings into the property.", "price" => 5000],
            ["name" => "Key Replacement", "description" => "Replacement charge for lost or damaged property access keys.", "price" => 500],
        ];

        $defaultBillItems = [
            ["name" => "Monthly Rent", "description" => "Monthly rental charge for the property unit.", "price" => 25000],
            ["name" => "Utility Bill", "description" => "Monthly charge for electricity, gas, water, or other utilities.", "price" => 3500],
            ["name" => "Maintenance Fee", "description" => "Monthly fee for property maintenance and common area upkeep.", "price" => 3000],
            ["name" => "Water Bill", "description" => "Monthly water consumption and supply charge.", "price" => 1000],
            ["name" => "Electricity Bill", "description" => "Monthly electricity consumption charge for the property unit.", "price" => 2500],
            ["name" => "Gas Bill", "description" => "Monthly gas supply and consumption charge.", "price" => 800],
            ["name" => "Parking Fee", "description" => "Monthly fee for using an assigned parking space.", "price" => 2000],
            ["name" => "Security Fee", "description" => "Monthly charge for property security and surveillance services.", "price" => 1500],
            ["name" => "Cleaning Fee", "description" => "Charge for professional cleaning of the property or common areas.", "price" => 1200],
            ["name" => "Late Payment Fee", "description" => "Additional charge applied when a bill is paid after the due date.", "price" => 500],
        ];

        $defaultRepairCategories = [
            ["icon" => "fa-wrench", "name" => "Plumbing"],
            ["icon" => "fa-bolt", "name" => "Electrical"],
            ["icon" => "fa-home", "name" => "Roof & Ceiling"],
            ["icon" => "fa-shower", "name" => "Bathroom"],
            ["icon" => "fa-key", "name" => "Doors & Locks"],
            ["icon" => "fa-window-maximize", "name" => "Windows"],
            ["icon" => "fa-paint-brush", "name" => "Painting"],
            ["icon" => "fa-fire", "name" => "Heating & Cooling"],
            ["icon" => "fa-bug", "name" => "Pest Control"],
            ["icon" => "fa-cogs", "name" => "Appliances"],
        ];

        $defaultComplianceHubs = [
            ["name" => "EICR", "description" => "Electric Safety Certificate", "is_active" => true],
            ["name" => "Gas Safety", "description" => "Gas Certificate", "is_active" => true],
            ["name" => "EPC", "description" => "Energy Performance Certificate", "is_active" => true],
            ["name" => "Selective Licence", "description" => "Selective Property Licence", "is_active" => true],
            ["name" => "HMO Licence", "description" => "House in multiple occupation licence", "is_active" => true],
            ["name" => "PAT Testing", "description" => "Portable appliance testing (PAT)", "is_active" => true],
            ["name" => "Fire Risk", "description" => "Fire Risk Assessment", "is_active" => true],
            ["name" => "Emergency Lighting", "description" => "Emergency Lighting Completion Certificate", "is_active" => true],
            ["name" => "Fire Detection Certificate", "description" => "Fire Detection & Alarms Certificate", "is_active" => true],
            ["name" => "Tenant’s Right to Rent", "description" => "Use a share code to check a tenant’s right to rent your residential property in England - the end of your tenant’s permission to stay in the UK - 12 months after your previous check", "is_active" => true],
        ];

        foreach ($defaultSaleItems as $item) {
            $sale_item = \App\Models\SaleItem::create([
                'name' => $item["name"],
                'description' => $item["description"],
                'price' => $item["price"],
                'created_by' => $user->id
            ]);
            $sale_item->generated_id = \Illuminate\Support\Str::random(4) . $sale_item->id . \Illuminate\Support\Str::random(4);
            $sale_item->save();

            \App\Models\BusinessDefault::create([
                'entity_type' => "sale_item",
                'entity_id' => $sale_item->id,
                'business_owner_id' => $user->id
            ]);
        }

        foreach ($defaultBillItems as $item) {
            $bill_item = \App\Models\BillItem::create([
                'name' => $item["name"],
                'description' => $item["description"],
                'price' => $item["price"],
                'created_by' => $user->id
            ]);
            $bill_item->generated_id = \Illuminate\Support\Str::random(4) . $bill_item->id . \Illuminate\Support\Str::random(4);
            $bill_item->save();

            \App\Models\BusinessDefault::create([
                'entity_type' => "bill_item",
                'entity_id' => $bill_item->id,
                'business_owner_id' => $user->id
            ]);
        }

        foreach ($defaultRepairCategories as $item) {
            $repair_category = \App\Models\RepairCategory::create([
                'name' => $item["name"],
                'icon' => $item["icon"],
                'created_by' => $user->id
            ]);
            $repair_category->generated_id = \Illuminate\Support\Str::random(4) . $repair_category->id . \Illuminate\Support\Str::random(4);
            $repair_category->save();
        }

        foreach ($defaultComplianceHubs as $item) {
            \App\Models\DocumentType::create([
                'name' => $item["name"],
                'description' => $item["description"],
                'is_active' => $item["is_active"],
                'created_by' => $user->id
            ]);
        }
    }
