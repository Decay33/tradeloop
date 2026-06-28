<?php

return [
    'demo_mode' => (bool) env('DEMO_MODE', false),
    'sms_driver' => env('SMS_DRIVER', 'log'),
    'path_prefix' => rtrim((string) env('APP_PATH', ''), '/'),
];
