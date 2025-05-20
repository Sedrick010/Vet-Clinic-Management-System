<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the credentials and configuration for Firebase Auth.
    | You will need to replace the placeholder values with your actual Firebase
    | project details from the Firebase console.
    |
    */

    'api_key' => env('FIREBASE_API_KEY', 'AIzaSyCf4422i9ajlUCnkj0SItUKjP9Uuym9X_8'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'vetclinic-admin.firebaseapp.com'),
    'project_id' => env('FIREBASE_PROJECT_ID', 'vetclinic-admin'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'vetclinic-admin.firebasestorage.app'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '108009409409'),
    'app_id' => env('FIREBASE_APP_ID', '1:108009409409:web:99c183cdee83ea29ae12b0'),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID', 'G-DMC5ZDRMH1'),
    
    // Admin settings
    'admin_emails' => [
        // Add admin email addresses that are allowed to use Firebase login
        'admin@vetclinic.com',
        'superadmin@yourvetclinic.com',
        '2201103327@student.buksu.edu.ph',
        '2201102976@student.buksu.edu.ph',
        '2201105150@student.buksu.edu.ph',
        '2201103184@student.buksu.edu.ph',
        '2201103590@student.buksu.edu.ph',
    ],
]; 