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

// Email Address obtained from the embedded newsletter signup form
$email = 'chris@supercoolwidgets.xyz';

/*
 * Retrieve User Details
 */
$mailChimp = new \DrewM\MailChimp\MailChimp($apiKey);

$subscriberHash = \DrewM\MailChimp\MailChimp::subscriberHash($email);
$contact = $mailChimp->get("lists/$listId/members/$subscriberHash");

echo json_encode($contact, JSON_PRETTY_PRINT) . "\n";