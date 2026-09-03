<?php
// Password-protected view / download of the mailing list CSV.
require __DIR__ . '/config.php';
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: mailing-list.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    // Constant-time comparison so the password can't be guessed by timing.
    if (hash_equals(MAILING_LIST_PASSWORD, (string) $_POST['password'])) {
        session_regenerate_id(true);
        $_SESSION['ml_auth'] = true;
    } else {
        usleep(400000);
        $error = 'Incorrect password.';
    }
}

$authed = !empty($_SESSION['ml_auth']);

if ($authed && isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pog-mailing-list.csv"');
    if (file_exists(MAILING_LIST_CSV)) {
        readfile(MAILING_LIST_CSV);
    } else {
        echo "Date,Name,Email,Phone,Package,Message,Source\n";
    }
    exit;
}

$rows = [];
if ($authed && file_exists(MAILING_LIST_CSV)) {
    $fh = fopen(MAILING_LIST_CSV, 'r');
    while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
        $rows[] = $r;
    }
    fclose($fh);
}
$head = $rows ? array_shift($rows) : ['Date', 'Name', 'Email', 'Phone', 'Package', 'Message', 'Source'];
$rows = array_reverse($rows);
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Mailing List | POG African Safaris</title>
<link rel="stylesheet" href="assets/styles.css">
<link rel="icon" href="assets/Logo.png">
<style>
  .ml-wrap{max-width:1100px;margin:0 auto;padding:3rem 1.25rem}
  .ml-card{background:var(--cream);border:1px solid var(--border);border-radius:10px;padding:2rem;max-width:420px;margin:0 auto}
  .ml-table{width:100%;border-collapse:collapse;margin-top:1.5rem;font-size:.9rem;background:var(--cream);border-radius:10px;overflow:hidden}
  .ml-table th{background:#241210 !important;color:#f2e9e2 !important;text-align:left;padding:.65rem .8rem;font-family:'Playfair Display',serif}
  .ml-table td{padding:.6rem .8rem;border-top:1px solid var(--border);vertical-align:top}
  .ml-bar{display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between}
  .ml-err{color:#a65e2c;font-size:.9rem;margin-top:.75rem}
</style>
</head>
<body>
<div id="site-header"></div>
<main class="ml-wrap">
<?php if (!$authed): ?>
  <div class="ml-card">
    <h1 style="font-size:1.6rem;margin:0 0 .5rem">Mailing List</h1>
    <p style="font-size:.9rem;opacity:.8;margin:0 0 1.25rem">Enter the password to view or download the subscriber list.</p>
    <form method="post">
      <div class="form-group"><label>Password</label><input type="password" name="password" autocomplete="current-password" required autofocus></div>
      <button class="btn btn-gold" type="submit">Unlock</button>
      <?php if ($error): ?><p class="ml-err"><?= $e($error) ?></p><?php endif; ?>
    </form>
  </div>
<?php else: ?>
  <div class="ml-bar">
    <h1 style="font-size:1.8rem;margin:0">Mailing List <span style="font-size:1rem;opacity:.7">(<?= count($rows) ?> entries)</span></h1>
    <div style="display:flex;gap:.75rem">
      <a class="btn btn-gold" href="?download=1">Download CSV</a>
      <a class="btn" href="?logout=1">Log out</a>
    </div>
  </div>
  <?php if (!$rows): ?>
    <p style="margin-top:1.5rem">No signups yet.</p>
  <?php else: ?>
    <div style="overflow-x:auto">
      <table class="ml-table">
        <thead><tr><?php foreach ($head as $h): ?><th><?= $e($h) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr><?php foreach ($head as $i => $_): ?><td><?= $e($r[$i] ?? '') ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>
</main>
<div id="site-footer"></div>
<script src="assets/main.js"></script>
</body>
</html>
