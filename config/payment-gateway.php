<?php

// Same variables as the PITEX app, so one .env layout works for both.
return [

    'default' => env('PAYMENT_GATEWAY', 'razorpay'),

    // true uses the RAZORPAY_TEST_* keys, false the live RAZORPAY_KEY / RAZORPAY_SECRET.
    'test_mode' => (bool) env('PAYMENT_GATEWAY_TEST_MODE', true),

    'drivers' => [

        'razorpay' => [
            'currency' => env('RAZORPAY_CURRENCY', 'INR'),
            'live' => [
                'key' => env('RAZORPAY_KEY'),
                'secret' => env('RAZORPAY_SECRET'),
            ],
            'test' => [
                'key' => env('RAZORPAY_TEST_KEY'),
                'secret' => env('RAZORPAY_TEST_SECRET'),
            ],
        ],

    ],

];
