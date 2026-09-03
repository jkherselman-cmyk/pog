<?php
// POG African Safaris — mailing list configuration
// CHANGE THIS PASSWORD before putting the site live.
define('MAILING_LIST_PASSWORD', 'ChangeMe-POG-2026');

// Where the CSV lives. The data/ folder is blocked from direct web access
// by data/.htaccess — the list is only reachable through mailing-list.php.
define('MAILING_LIST_CSV', __DIR__ . '/data/mailing-list.csv');

// Where contact-form enquiries are emailed.
define('ENQUIRY_TO', 'jkherselman@gmail.com');
// Envelope sender — must be a mailbox on your own domain for good deliverability.
define('ENQUIRY_FROM', 'info@poghunting.co.za');
