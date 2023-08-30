<?php

return [

    'api_key' => env('MAILCHIMP_API_KEY'),

    /*
     * Set to `true` to add new user registrations to a Mailchimp audience.
     */
    'add_new_users' => false,

    'users' => [
        /*
        * A Mailchimp Audience ID.
        *
        * @see https://mailchimp.com/help/find-audience-id/.
        */
        'audience_id' => null,

        /*
        * Set to `true` to require consent before subscribing someone
        * Default: `true`
        */
        'check_consent' => true,

        /*
        * Field name used to check for consent.
        * Default: 'consent'
        */
        'consent_field' => 'consent',

        /*
        * Disable Double Opt In. Not typically a best practice.
        * Default: `false`
        *
        * @see https://mailchimp.com/help/single-opt-in-vs-double-opt-in/
        */

        'disable_opt_in' => false,

        /*
        * Field name used to collect ids of group "interests".
        * Default: 'interests'
        *
        * @see https://mailchimp.com/help/how-to-use-groups-to-add-or-update-subscriber-preferences/
        */
        'interests_field' => 'interests',

        /*
        * Field name used to indicate marketing permissions.
        * Default: `gdpr`
        */
        'marketing_permissions_field' => 'gdpr',

        /*
        * Fields used to store marketing permission ids.
        * Run `php please mailchimp:permissions <form-handle>` to get the ids.
        */
        'marketing_permissions_field_ids' => [
            // [
            //     'field_name' => '',
            //     'id' => '',
            // ],
        ],

        /*
        * Store information about your contacts with marge fields.
        *
        * @see https://mailchimp.com/help/manage-audience-signup-form-fields/
        */
        'merge_fields' => [
            // [
            //     /*
            //     * The Mailchimp tag
            //     */
            //     'tag'=> null,

            //     /*
            //     * Blueprint field name to use for the merge field
            //     */
            //     'field_name' => null,
            // ],
        ],

        /*
        * Field that contains the primary email address
        * Default: 'email'
        */
        'primary_email_field' => 'email',

        /*
        * Mailchimp Tag to assign to the contact.
        * NOTE: `tag_field` takes precedence over `tag`
        *
        * @see https://mailchimp.com/help/getting-started-tags/
        */
        'tag' => null,

        /*
        * Field to indicate which Mailchimp Tag to use
        *
        * @see https://mailchimp.com/help/getting-started-tags/
        */
        'tag_field' => null,
        'id' => 'xLw04QS5',
    ],

    /*
     * The form submissions to add to your Mailchimp Audiences
     */
    'forms' => [
        [
            /*
            * Handle of the form to listen for
            */
            'form' => null,

            /*
            * MailChimp audience id to subscribe users to
            *
            * @see https://mailchimp.com/help/find-audience-id/.
            */
            'audience_id' => null,

            /*
            * Field name used to check for consent.
            * Default: 'consent'
            */
            'check_consent' => true,

            /*
            * if you're checking for consent, which field is it? Defaults to `'consent'`
            */
            'consent_field' => 'consent',

            /*
            * Disable Double Opt In. Not typically a best practice.
            * Default: `false`
            *
            * See: https://mailchimp.com/help/single-opt-in-vs-double-opt-in/ for details
            */

            'disable_opt_in' => false,

            /*
            * if you'd like to add "interests" in a group, which field is collecting those ids? Defaults to 'interests'
            */
            'interests_field' => 'interests',

            /*
            * Field name used to indicate marketing permissions.
            * Default: `gdpr`
            */
            'marketing_permissions_field' => 'gdpr',

            /*
            * Fields used to store marketing permission ids.
            * Run `php please mailchimp:permissions <form-handle>` to get the ids.
            */
            'marketing_permissions_field_ids' => [
                [
                    'field_name' => '',
                    'id' => '',
                ],
            ],

            /*
           * Store information about your contacts with marge fields.
           *
           * @see https://mailchimp.com/help/manage-audience-signup-form-fields/
           */
            'merge_fields' => [
                [
                    /*
                    * The Mailchimp tag
                    */
                    'tag' => null,

                    /*
                    * Blueprint field name to use for the merge field
                    */
                    'field_name' => null,
                ],
            ],

            /*
           * Field that contains the primary email address
           * Default: 'email'
           */
            'primary_email_field' => 'email',

            /*
            * Mailchimp Tag to assign to the contact.
            * NOTE: `tag_field` takes precedence over `tag`
            *
            * @see https://mailchimp.com/help/getting-started-tags/
            */
            'tag' => null,

            /*
            * Field to indicate which Mailchimp Tag to use
            *
            * @see https://mailchimp.com/help/getting-started-tags/
            */
            'tag_field' => null,
        ],
    ],

    /*
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'subscribers',
];
