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
            ],
        ],
        [
            "role" => "property_dealer",
            "permissions" => [
                "payment_type_view",
            ],
        ],

    ],
    "roles" => [
        "superadmin",
        "property_dealer"
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

    ],

    "business_image_location" => "business_image",
    "user_image_location" => "user_image",
    "landlord_image" => "landlord_image",
    "tenant_image" => "tenant_image",
    "property_image" => "property_image",
    "repair_image" => "repair_image",
    "repair_receipt_file" => "repair_receipt_file",
    "invoice_image" => "invoice_image",
    "repair_category_image" => "repair_category_image"

];
