<?php

// config/simtal_api.php
// Konfigurasi client yang boleh akses API SiMental.
// Nama env & key config sengaja tetap "simtal_api" — mengubahnya memutus
// integrasi yang sudah berjalan dan membatalkan token yang sudah terbit.

return [
    'client_id'     => env('SIMTAL_API_CLIENT_ID', 'b2dc87a3885894a1198edcbc1c1dbdaab9d3f5a01208f652ec5bf2c3995bc5fa'),
    'client_secret' => env('SIMTAL_API_CLIENT_SECRET', ''),
];