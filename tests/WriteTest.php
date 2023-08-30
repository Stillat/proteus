<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class WriteTest extends ProteusTestCase
{
    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFiles()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    [
                        'id' => 'a new thing',
                        'frequency' => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                    [
                        'id' => 'a new thing!!!',
                        'frequency' => 'dailyasdfasdf',
                        'email_addresses' => 'hellasdfasdfasdfasdfoworld@example.org',
                    ],
                ],
            ],
        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/014a.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFilesTwo()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/018.php');

        $updater->update([
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
        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018d.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFilesThree()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014a.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    [
                        'id' => 'a new thing',
                        'frequency' => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                    [
                        'id' => 'a new thing!!!',
                        'frequency' => 'dailyasdfasdf',
                        'email_addresses' => 'hellasdfasdfasdfasdfoworld@example.org',
                    ],
                ],
            ],
        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/014b.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testUpdatingMultipleNestedKeys()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/merge.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    ['one', 'two'],
                    ['three', 'four'],
                ],
                'test' => [
                    1, 2, 3, 4,
                ],
            ],
        ], false);

        $expected = <<<'EXP'
<?php

return [
    'trackable_videos' => env('SIMPLE_TRACKABLE_VIDEOS', false),

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        // Reporting
        'reports' => [
            [
                'one',
                'two',
            ],
            [
                'three',
                'four',
            ],
        ],
        'leave' => 'this should be ignored.',
        'test' => [
            1,
            2,
            3,
            4,
        ],
    ],

];

EXP;

        $this->assertSame($expected, $updater->getDocument());
    }

    public function testAutomaticInsertionInWriteMode()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/018.php');

        $updater->update([
            'tasks' => [
                'Task 1',
                'Task 2',
            ],
        ], false);

        $expected = <<<'EOT'
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
    ], 'tasks' => [
        'Task 1',
        'Task 2',
    ],
];

EOT;

        $this->assertSame($expected, $updater->getDocument());
    }

    public function testInsertionOfSimpleRootKeys()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->update([
            'hello' => 'world',
        ], false);

        $expected = <<<'EOT'
<?php

return [
    'stripe' => [
        'api_base' => env('STRIPE_API_BASE'),
        'dg_price_1' => env('STRIPE_PRICE_ONE'),
        'dg_product_1' => env('STRIPE_PRODUCT_ONE'),
        'dg_product_2' => env('STRIPE_PRODUCT_TWO'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        'reports' => [
            [
                'id' => 'eq48Qzwt',
                'frequency' => 'daily',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
            [
                'id' => 'firstid',
                'frequency' => 'monthly',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
        ],

        // Misc
        'query_params' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
        ],
    ], 'hello' => 'world',
];

EOT;

        $this->assertSame($expected, $updater->getDocument());
    }

    public function testInsertingANewEntryIntoAnExistingConfigurationArrayDoesntDestroyIt()
    {
        // Ideally, people shouldn't do this, and rely on
        // updating __existing__ configuration keys.
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->update([
            'stripe' => [
                'hello' => 'world',
            ],
        ], false);

        // This will now insert the 'hello' => 'world' value by default.
        $expected = <<<'EOT'
<?php

return [
    'stripe' => [
        'api_base' => env('STRIPE_API_BASE'),
        'dg_price_1' => env('STRIPE_PRICE_ONE'),
        'dg_product_1' => env('STRIPE_PRODUCT_ONE'),
        'dg_product_2' => env('STRIPE_PRODUCT_TWO'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        'reports' => [
            [
                'id' => 'eq48Qzwt',
                'frequency' => 'daily',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
            [
                'id' => 'firstid',
                'frequency' => 'monthly',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
        ],

        // Misc
        'query_params' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
        ],
        'hello' => 'world',
    ],
];

EOT;
        $this->assertSame($expected, $updater->getDocument());

        // We can have it just overwrite everything entirely, though, if you really want to.
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->allowRootRemoval()->update([
            'stripe' => [
                'hello' => 'world',
            ],
        ], false);

        $expected = <<<'EOT'
<?php

return [
    'stripe' => [
        'hello' => 'world',
    ],
];

EOT;

        $this->assertSame($expected, $updater->getDocument());
    }

    public function testRootNonAssociativeArraysAreNotFiltered()
    {
        $updater = new ConfigUpdater();

        $updater->open(__DIR__.'/configs/issues/023.php');

        $updater->update([
            'forms' => [
                [
                    'id' => 'OLYoYHBb',
                    'check_consent' => true,
                    'consent_field' => 'consent',
                    'disable_opt_in' => false,
                    'interests_field' => 'interests',
                    'marking_permissions_field' => 'gdpr',
                    'marketing_permissions_field_ids' => [
                        [
                            'id' => 'asfd',
                            'field_name' => 'asdfasdf',
                        ],
                    ],
                    'merge_fields' => [
                        [
                            'id' => 'DRoHyZ6k',
                        ],
                    ],
                    'primary_email_field' => 'email',
                ],
            ],
            'add_new_users' => true,
            'users' => [
                'id' => 'xLw04QS5',
                'check_consent' => true,
                'consent_field' => 'consent-update-test',
                'disable_opt_in' => false,
                'interests_field' => 'interests',
                'marketing_permissions_field' => 'gdpr',
                'primary_email_field' => 'email',
            ],
            'api_key' => null,
        ], false);

        $expected = <<<'EXP'
<?php

return [

    'api_key' => env('MAILCHIMP_API_KEY'),

    /*
     * Set to `true` to add new user registrations to a Mailchimp audience.
     */
    'add_new_users' => true,

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
        'consent_field' => 'consent-update-test',

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
            'id' => 'OLYoYHBb',
            'check_consent' => true,
            'consent_field' => 'consent',
            'disable_opt_in' => false,
            'interests_field' => 'interests',
            'marking_permissions_field' => 'gdpr',
            'marketing_permissions_field_ids' => [
                [
                    'id' => 'asfd',
                    'field_name' => 'asdfasdf',
                ],
            ],
            'merge_fields' => [
                [
                    'id' => 'DRoHyZ6k',
                ],
            ],
            'primary_email_field' => 'email',
        ],
    ],

    /*
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'subscribers',
];

EXP;

        $this->assertSame($expected, $updater->getDocument());
    }
}
