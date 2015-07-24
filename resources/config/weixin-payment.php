<?php

return [
    'app_id' => env('WEIXIN_PAYMENT_APP_ID', 'secure'),
    'mch_id' => env('WEIXIN_PAYMENT_MCH_ID', 'secure'),
    'hash_secret' => env('WEIXIN_PAYMENT_HASH_SECRET', 'secure'),
    'public_key_path' => env('WEIXIN_PAYMENT_PUBLIC_KEY_PATH'),
    'private_key_path' => env('WEIXIN_PAYMENT_PRIVATE_KEY_PATH'),
];
