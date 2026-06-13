<?php

// config/simtal_api.php
// Konfigurasi client yang boleh akses API SIMTAL

return [
    'client_id'     => env('SIMTAL_API_CLIENT_ID', 'web2_app'),
    'client_secret' => env('SIMTAL_API_CLIENT_SECRET', ''),
];