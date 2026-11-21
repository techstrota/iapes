<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk that will be used by Filament to store files
    | by default, including FileUpload form components.
    |
    */
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
];
