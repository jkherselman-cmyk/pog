<?php
// Receives footer newsletter signups and contact-form enquiries,
// saves them to the mailing list CSV and emails enquiries to the office.
require __DIR__ . '/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Sanitise helper
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

// ── Spam protection: honeypot + time check ───────────────────────────────────
// Reject if the honeypot field was filled (bot behaviour)
if (!empty($_POST['website'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Submission rejected.']);
    exit;
}

// Reject if the form was submitted in under 3 seconds (bot behaviour)
$form_loaded = (int)($_POST['form_loaded'] ?? 0);
if ($form_loaded === 0 || (time() - $form_loaded) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Submission rejected.']);
    exit;
}

// ── Collect and sanitise inputs ──────────────────────────────────────────────
$name   = clean($_POST['name'] ?? '');
$email  = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone  = clean($_POST['phone'] ?? '');
$source = clean($_POST['source'] ?? '');
if (!in_array($source, ['newsletter', 'contact'], true)) {
    $source = 'website';
}

// Server-side validation
$emailPattern = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/';
if ($email === '' || !preg_match($emailPattern, $email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if ($source === 'contact' && $name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Prevent email header injection
foreach ([$name, $email, $phone] as $field) {
    if (preg_match('/[\r\n]/', $field)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input detected.']);
        exit;
    }
}

// ── Save to the mailing list CSV ─────────────────────────────────────────────
// Guard against CSV formula injection when the file is opened in Excel.
$safe = function ($v) {
    return (isset($v[0]) && strpos("=+-@\t\r", $v[0]) !== false) ? "'" . $v : $v;
};

$dir = dirname(MAILING_LIST_CSV);
if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not create data directory.']);
    exit;
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

if (!$alreadyStored) {
    $isNew = !file_exists(MAILING_LIST_CSV) || filesize(MAILING_LIST_CSV) === 0;
    $fh = fopen(MAILING_LIST_CSV, 'a');
    if (!$fh) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not open mailing list.']);
        exit;
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

    $body  = "NEW ENQUIRY - POG AFRICAN SAFARIS\n";
    $body .= str_repeat('=', 40) . "\n\n";

    $body .= "CONTACT DETAILS\n";
    $body .= "$divider\n";
    $body .= "Name:     $name\n";
    $body .= "Email:    $email\n";
    $body .= "Phone:    " . ($phone ?: '-') . "\n\n";

    $body .= str_repeat('=', 40) . "\n";
    $body .= "Sent via the POG African Safaris contact form.\n";

    $to      = ENQUIRY_TO;
    $subject = "New Enquiry from POG African Safaris - $name";
    $headers = implode("\r\n", [
        'From: POG African Safaris <' . ENQUIRY_FROM . '>',
        "Reply-To: $name <$email>",
        'Content-Type: text/plain; charset=UTF-8',
    ]);

    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send your enquiry. Please try again or contact us directly.']);
    }
    exit;
}

echo json_encode(['success' => true]);
