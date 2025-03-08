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
    "invoice_image" => "invoice_image",
    "repair_category_image" => "repair_category_image",
    "temporary_files_location" => "temporary_files",






];
