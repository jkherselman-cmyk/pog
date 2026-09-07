<?php
// Receives footer newsletter signups and contact-form enquiries,
// appends them to the mailing list CSV.
require __DIR__ . '/config.php';

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

// ── Spam protection: honeypot + minimum fill time ────────────────────────────
// Bots fill every field, including the hidden one.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    fail('Submission rejected.');
}
// Bots submit instantly; the page script holds real submissions back for 3 seconds.
$elapsed = isset($_POST['elapsed']) ? (int) $_POST['elapsed'] : -1;
if ($elapsed >= 0 && $elapsed < 3) {
    fail('Submission rejected.');
}
if ($elapsed < 0) {
    // No timing info at all (script bypassed) - treat as a bot.
    fail('Submission rejected.');
}

$clean = function ($key, $max = 500) {
    $v = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
    $v = str_replace(["\r", "\n"], ' ', $v);
    return mb_substr($v, 0, $max);
};

$email = $clean('email', 255);
$name = $clean('name', 120);
$phone = $clean('phone', 60);
$package = $clean('package', 120);
$message = $clean('message', 2000);
$source = $clean('source', 40);

$emailPattern = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/';
if ($email === '' || !preg_match($emailPattern, $email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}
if (!in_array($source, ['newsletter', 'contact'], true)) {
    $source = 'website';
}

// Guard against CSV formula injection when the file is opened in Excel.
$safe = function ($v) {
    return (isset($v[0]) && strpos("=+-@\t\r", $v[0]) !== false) ? "'" . $v : $v;
};

$dir = dirname(MAILING_LIST_CSV);
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
    fail('Could not create data directory.', 500);
}

// Skip storing an address that is already on the list.
$alreadyStored = false;
if (file_exists(MAILING_LIST_CSV)) {
    $rh = fopen(MAILING_LIST_CSV, 'r');
    if ($rh) {
        flock($rh, LOCK_SH);
        while (($row = fgetcsv($rh, 0, ',', '"', '\\')) !== false) {
            if (isset($row[2]) && strcasecmp(trim($row[2]), $email) === 0) {
                $alreadyStored = true;
                break;
            }
        }
        flock($rh, LOCK_UN);
        fclose($rh);
    }
}

$isNew = !file_exists(MAILING_LIST_CSV) || filesize(MAILING_LIST_CSV) === 0;
if (!$alreadyStored) {
    $fh = fopen(MAILING_LIST_CSV, 'a');
    if (!$fh) {
        fail('Could not open mailing list.', 500);
    }
    flock($fh, LOCK_EX);
    if ($isNew) {
        fputcsv($fh, ['Date', 'Name', 'Email', 'Phone', 'Source'], ',', '"', '\\');
    }
    fputcsv($fh, array_map($safe, [
        gmdate('Y-m-d H:i:s') . ' UTC',
        $name,
        $email,
        $phone,
        $source,
    ]), ',', '"', '\\');
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
}

// ── Email the enquiry through to the office ──────────────────────────────────
if ($source === 'contact') {
    $divider = str_repeat('-', 40);
    $body  = "NEW ENQUIRY - POG AFRICAN SAFARIS\n" . str_repeat('=', 40) . "\n\n";
    $body .= "CONTACT DETAILS\n$divider\n";
    $body .= "Name:     " . ($name ?: '-') . "\n";
    $body .= "Email:    $email\n";
    $body .= "Phone:    " . ($phone ?: '-') . "\n\n";
    $body .= "ENQUIRY\n$divider\n";
    $body .= "Package:  " . ($package ?: '-') . "\n\n";
    $body .= "MESSAGE\n$divider\n";
    $body .= ($message ?: 'No message provided.') . "\n\n";
    $body .= str_repeat('=', 40) . "\nSent via the POG African Safaris contact form.\n";

    $replyName = $name !== '' ? $name : 'Website enquiry';

    // Mail headers
    $to      = "jkherselman@gmail.com";
    $subject = 'New Enquiry from POG African Safaris' . ($name !== '' ? " - $name" : '');
    $headers = implode("\r\n", [
        'From: POG African Safaris <' . ENQUIRY_FROM . '>',
        'Reply-To: ' . $replyName . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ]);

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send your enquiry. Please try again or contact us directly.']);
    }
}

echo json_encode(['ok' => true]);
