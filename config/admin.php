<?php

return [
    'db_edit_enabled' => env('ADMIN_DB_EDIT_ENABLED', false),
    'db_page_size' => env('ADMIN_DB_PAGE_SIZE', 25),
    'super_admin_email' => env('SUPERADMIN_EMAIL', env('SUPER_ADMIN_EMAIL')),
    'super_admin_password' => env('SUPERADMIN_PASSWORD', env('SUPER_ADMIN_PASSWORD')),
    'super_admin_name' => env('SUPER_ADMIN_NAME', 'Fikri Rachmat'),
];
