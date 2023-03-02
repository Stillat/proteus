<?php

return [
    'forms' => [
        [
            'id' => 'new1',
            'form' => 'contact',
            'check_consent' => true,
            'consent_field' => 'newsletter',
            'disable_opt_in' => false,
            'marketing_permissions_field_ids' => [
                [
                ],
            ],
            'merge_fields' => [
                [
                    'id' => 'new2',
                    'field_name' => 'first_name',
                    'tag' => 'FNAME',
                ],
                [
                    'id' => 'new3',
                    'field_name' => 'last_name',
                    'tag' => 'LNAME',
                ],
                [
                    'id' => 'new4',
                    'field_name' => 'company',
                    'tag' => 'COMPANY',
                ],
            ],
            'primary_email_field' => 'email',
            'audience_id' => 'new5',
        ],
    ],
];
