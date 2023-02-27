<?php

return [
    'forms' => [
        [
            'id' => 'c-new1',
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
                    'id' => 'c-new2',
                    'field_name' => 'first_name',
                    'tag' => 'FNAME',
                ],
                [
                    'id' => 'c-new3',
                    'field_name' => 'last_name',
                    'tag' => 'LNAME',
                ],
                [
                    'id' => 'c-new4',
                    'field_name' => 'company',
                    'tag' => 'COMPANY',
                ],
            ],
            'primary_email_field' => 'email',
            'audience_id' => 'c-new5',
        ],
        [
            'id' => 'd-new1',
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
                    'id' => 'd-new2',
                    'field_name' => 'first_name',
                    'tag' => 'FNAME',
                ],
                [
                    'id' => 'd-new3',
                    'field_name' => 'last_name',
                    'tag' => 'LNAME',
                ],
                [
                    'id' => 'd-new4',
                    'field_name' => 'company',
                    'tag' => 'COMPANY',
                ],
            ],
            'primary_email_field' => 'email',
            'audience_id' => 'd-new5',
        ],
    ],
];
