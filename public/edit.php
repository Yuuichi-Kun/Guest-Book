<?php
require_once __DIR__ . '/../lib/db.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = get_pdo_connection();
$errors = [];
$success = null;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
	http_response_code(400);
	echo 'ID tidak valid';
	exit;
}

// Fetch existing entry
$stmt = $pdo->prepare('SELECT id, name, email, message FROM guest_entries WHERE id = ?');
$stmt->execute([$id]);
$entry = $stmt->fetch();
if (!$entry) {
	http_response_code(404);
	echo 'Entri tidak ditemukan';
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
		$errors[] = 'Invalid CSRF token.';
	} else {
		$name = trim((string)($_POST['name'] ?? ''));
		$email = trim((string)($_POST['email'] ?? ''));
		$message = trim((string)($_POST['message'] ?? ''));

		if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
		if ($message === '') { $errors[] = 'Pesan wajib diisi.'; }
		if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Format email tidak valid.'; }

		if (!$errors) {
			$upd = $pdo->prepare('UPDATE guest_entries SET name = ?, email = ?, message = ? WHERE id = ?');
			$upd->execute([$name, $email !== '' ? $email : null, $message, $id]);
			$success = 'Perubahan disimpan.';
			// Refresh the entry values
			$entry['name'] = $name;
			$entry['email'] = $email !== '' ? $email : null;
			$entry['message'] = $message;
		}
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Edit Entri</title>
	<link rel="stylesheet" href="/styles.css" />
</head>
<body>
	<div class="container">
		<h1>Edit Entri</h1>

		<?php if ($errors): ?>
			<div class="alert alert-error">
				<ul>
					<?php foreach ($errors as $e): ?>
						<li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ($success): ?>
			<div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
		<?php endif; ?>

		<form method="post" action="">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
			<input type="hidden" name="action" value="update" />
			<div class="field">
				<label for="name">Nama</label>
				<input type="text" id="name" name="name" required maxlength="100" value="<?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?>" />
			</div>
			<div class="field">
				<label for="email">Email (opsional)</label>
				<input type="email" id="email" name="email" maxlength="190" value="<?= htmlspecialchars($entry['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
			</div>
			<div class="field">
				<label for="message">Pesan</label>
				<textarea id="message" name="message" rows="4" required><?= htmlspecialchars($entry['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
			</div>
			<div class="actions">
				<button type="submit">Simpan</button>
				<a href="/" style="margin-left:8px; text-decoration:none">Batal</a>
			</div>
		</form>
	</div>
</body>
</html>
