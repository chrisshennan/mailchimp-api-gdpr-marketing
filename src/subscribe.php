<?php

require __DIR__ . '/../vendor/autoload.php';

/*
 * Step 1 - Prerequites
 * 
 * EDIT CONFIG VARIABLES AS NECESSARY
 */

// MailChimp API & List ID 
$apiKey = '[MAILCHIMP_API_KEY]';
$listId = '[MAILCHIMP_LIST_ID]';

// Marketing Preference Mapping for embedded form -> mailchimp permissions.
$marketingPreferencesMapping = [
    'email' => '[MARKETING_PREFERENCE_ID_FOR_EMAIL]',
    'direct_mail' => '[MARKETING_PREFERENCE_ID_FOR_DIRECT_MAIL]',
    'customized_online_advertising' => '[MARKETING_PREFERENCE_ID_FOR_CUSTOMIZED_ONLINE_ADVERTISING',
];


// Email Address obtained from the embedded newsletter signup form
$email = '[SUBSCRIBER_EMAIL_ADDRESS]';

// Marketing Permissions obtained from the embeded newsletter signup form
// We're going to assume email and customized_online_advertising was selected in the form
$selectedMarketingPermissions = [
    'email',
    'customized_online_advertising',
];

/*
 * NO EDITS REQUIRED BEYOND THIS POINT
 */

/*
 * Step 2 - Create the user
 */
$mailChimp = new \DrewM\MailChimp\MailChimp($apiKey);

// Add user to the newsletter
$contact = $mailChimp->post("lists/$listId/members", [
    'email_address' => $email,
    'status'        => 'subscribed',
]);

if (!$contact) {
    throw \RuntimeException('Error adding contact to MailChimp');
}

/*
 * Step 2 - Retrieve User
 */
$subscriberHash = \DrewM\MailChimp\MailChimp::subscriberHash($email);
$contact = $mailChimp->get("lists/$listId/members/$subscriberHash");
/*
 * Step 3 - Set GDPR preferences 
 */

// Get the appropriate GDPR options
$enabledMarketingPermissions = [];
foreach ($contact['marketing_permissions'] as $marketingPermission) {
    // Get the key for the marketing permission id
    $key = array_search($marketingPermission['marketing_permission_id'], $marketingPreferencesMapping);

    // If the key isn't one in the selected marketing permissions the continue to next one
    if (!in_array($key, $selectedMarketingPermissions)) {
        continue;
    }

    // Set the marketing permission to enabled
    $marketingPermission['enabled'] = true;
    $enabledMarketingPermissions[] = $marketingPermission;
}

// Update the GDPR marketing permissions
$contact = $mailChimp->patch("lists/$listId/members/$subscriberHash", [
    "marketing_permissions" => $enabledMarketingPermissions,
]);
