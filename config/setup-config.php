<?php

return [
    "roles_permission" => [

        [
            "role" => "superadmin",
            "permissions" => [
                "user_create",
                "user_update",
                "user_view",
                "user_delete",

                "payment_type_create",
                "payment_type_update",
                "payment_type_view",
                "payment_type_delete",

                "document_type_create",
                "document_type_update",
                "document_type_view",
                "document_type_delete",

                "repair_category_create",
                "repair_category_update",
                "repair_category_view",
                "repair_category_delete",


                "expense_category_create",
                "expense_category_update",
                "expense_category_view",
                "expense_category_delete",

                "bill_item_create",
                "bill_item_update",
                "bill_item_view",
                "bill_item_delete",

                "template_update",
                "template_view"
            ],
        ],
        [
            "role" => "user",
            "permissions" => [
                "payment_type_view",
                "repair_category_view",
                "expense_category_view",

                "bill_item_view",


                "document_type_create",
                "document_type_update",
                "document_type_view",
                "document_type_delete",

                "reminder_create",
                "reminder_update",
                "reminder_view",
                "reminder_delete",



            ],
        ],

    ],
    "roles" => [
        "superadmin",
        "user"
    ],
    "permissions" => [
        "user_create",
        "user_update",
        "user_view",
        "user_delete",

        "payment_type_create",
        "payment_type_update",
        "payment_type_view",
        "payment_type_delete",

        "document_type_create",
        "document_type_update",
        "document_type_view",
        "document_type_delete",


        "reminder_create",
        "reminder_update",
        "reminder_view",
        "reminder_delete",

        "repair_category_create",
        "repair_category_update",
        "repair_category_view",
        "repair_category_delete",

        "expense_category_create",
        "expense_category_update",
        "expense_category_view",
        "expense_category_delete",

        "bill_item_create",
        "bill_item_update",
        "bill_item_view",
        "bill_item_delete",
        "template_update",
        "template_view"

    ],

    "business_image_location" => "business_image",
    "user_image_location" => "user_image",
    "landlord_image" => "landlord_image",
    "tenant_image" => "tenant_image",
    "client_image" => "client_image",
    "property_image" => "property_image",
    "repair_image" => "repair_image",
    "repair_receipt_file" => "repair_receipt_file",
    "expense_receipt_file" => "expense_receipt_file",
    "rent_payable_file" => "rent_payable_file",
    "invoice_image" => "invoice_image",
    "repair_category_image" => "repair_category_image",
    "expense_category_image" => "expense_category_image",

    "temporary_files_location" => "temporary_files",


    "default_expense_categories" => [
        ['name' => 'Bathroom and Toilet', 'icon' => 'fa fa-bathtub'],
        ['name' => 'Kitchen', 'icon' => 'fa fa-beer'],
        ['name' => 'Heating and boiler', 'icon' => 'fa fa-thermometer-4'],
        ['name' => 'Water and leaks', 'icon' => 'fa fa-shower'],
        ['name' => 'Doors and Locks', 'icon' => 'fa fa-lock'],
        ['name' => 'Walls and ceilings', 'icon' => 'fa fa-credit-card-alt'],
        ['name' => 'Laundry', 'icon' => 'fa fa-recycle'],
        ['name' => 'Exterior and Garden', 'icon' => 'fa fa-area-chart'],
        ['name' => 'Furniture', 'icon' => 'fa fa-cube'],
        ['name' => 'Window', 'icon' => 'fa fa-th-large'],
        ['name' => 'Electricity', 'icon' => 'fa fa-bolt'],
        ['name' => 'Lightins', 'icon' => 'fa fa-circle'],
        ['name' => 'Hot Water', 'icon' => 'fa fa-fire'],
        ['name' => 'Alarms', 'icon' => 'fa fa-bell'],
        ['name' => 'Pests/Vermin', 'icon' => 'fa fa-trademark'],
        ['name' => 'Property Service', 'icon' => 'fa fa-building'],
        ['name' => 'Roof', 'icon' => 'fa fa-fax'],
        ['name' => 'Shared Facilities', 'icon' => 'fa fa-hotel'],
        ['name' => 'Utility Meters', 'icon' => 'fa fa-tachometer'],
        ['name' => 'Internet', 'icon' => 'fa fa-wifi'],
        ['name' => 'Stairs', 'icon' => 'fa fa-sort-numeric-asc'],
        ['name' => 'Air Condition', 'icon' => 'fa fa-recycle'],
        ['name' => 'Smell oil?', 'icon' => 'fa fa-map-marker'],
        ['name' => 'Fire', 'icon' => 'fa fa-fire'],
        ['name' => 'Other', 'icon' => 'fa fa-exclamation'],
    ]








];
