<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 'allowed_origins' => ['http://localhost:4600','http://localhost:4601', 'http://localhost:4201', 'http://localhost:4200','https://4jwr.l.time4vps.cloud:8051','https://4jwr.l.time4vps.cloud:8052','https://4jwr.l.time4vps.cloud:8050','https://4jwr.l.time4vps.cloud:8081','https://4jwr.l.time4vps.cloud:8082','https://4jwr.l.time4vps.cloud:8083','https://4jwr.l.time4vps.cloud:8084','https://4jwr.l.time4vps.cloud:8085',
    // 'https://examinerdrivingsigpcb.anatt.bj','https://backofficesigpcb.anatt.bj','https://registersigpcb.anatt.bj','https://inspectorcodesigpcb.anatt.bj','https://codereviewsigpcb.anatt.bj','https://drivingmonitorsigpcb.anatt.bj',
    // 'https://formationsigpcb.anatt.bj:8080','https://formationsigpcb.anatt.bj:8081','https://formationsigpcb.anatt.bj:8083','https://formationsigpcb.anatt.bj:8084','https://formationsigpcb.anatt.bj:8085','https://formationsigpcb.anatt.bj:8086'
    // ],
    'allowed_origins' => ['http://localhost:4600','https://sandbox-checkout.fedapay.com','https://sandbox.fedapay.com/api','http://localhost:4601', 'http://localhost:4200','http://localhost:4201',
    'https://examinerdrivingsigpcb.anatt.bj','https://backofficesigpcb.anatt.bj','https://registersigpcb.anatt.bj','https://inspectorcodesigpcb.anatt.bj','https://codereviewsigpcb.anatt.bj','https://drivingmonitorsigpcb.anatt.bj',
    'https://formationsigpcb.anatt.bj:8080','https://formationsigpcb.anatt.bj:8081','https://formationsigpcb.anatt.bj:8083','https://formationsigpcb.anatt.bj:8084','https://formationsigpcb.anatt.bj:8085','https://formationsigpcb.anatt.bj:8086','https://formationsigpcb.anatt.bj:8082','https://evaluationsigpcb.anatt.bj',
    'https://adminsigpcb.anatt.bj','https://monpermisdeconduire.anatt.bj','https://autoecolesigpcb.anatt.bj','https://enrolementsigpcb.anatt.bj','https://examinateursigpcb.anatt.bj','https://superviseursigpcb.anatt.bj','https://codesigpcb.anatt.bj','https://supportsigpcb.anatt.bj',
    'https://supportsigpcb.anatt.bj:8080','https://supportsigpcb.anatt.bj:8081','https://supportsigpcb.anatt.bj:8083','https://supportsigpcb.anatt.bj:8084','https://supportsigpcb.anatt.bj:8085','https://supportsigpcb.anatt.bj:8086','https://supportsigpcb.anatt.bj:8082',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
