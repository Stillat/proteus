<?php

return [
    'forms' => [
        [
            'id' => 'id1',
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
                    'id' => 'id2',
                    'field_name' => 'first_name',
                    'tag' => 'FNAME',
                ],
                [
                    'id' => 'id3',
                    'field_name' => 'last_name',
                    'tag' => 'LNAME',
                ],
                [
                    'id' => 'id',
                    'field_name' => 'company',
                    'tag' => 'COMPANY',
                ],
            ],
            'primary_email_field' => 'email',
            'audience_id' => 'aud1',
        ],
    ],
];
