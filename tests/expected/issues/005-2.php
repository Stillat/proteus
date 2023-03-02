<?php

return [
    'api_key' => env('MAILCHIMP_APIKEY'),
    'add_new_users' => false,

    'forms' => [
        [
            'name_field' => 'name3',
            'first_name_field' => 'first_name',
            'last_name_field' => 'last_name',
            'email_field' => 'email3',
            'content_field' => 'message',
            'handle' => 'contact_you',
            'consent_field' => null,
        ],
    ],
];
