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
            ],
        ],

    ],
    "roles" => [
        "superadmin",
    ],
    "permissions" => [
       "user_create",
       "user_update",
       "user_view",
       "user_delete",

    ],

    "user_image_location" => "user_image",
    "landlord_image" => "landlord_image",
    "tenant_image" => "tenant_image",
    "property_image" => "property_image",
    "repair_image" => "repair_image",
    "repair_receipt_file" => "repair_receipt_file",
    "invoice_image" => "invoice_image"

];
