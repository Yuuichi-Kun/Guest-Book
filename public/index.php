<?php
require_once __DIR__ . '/../lib/db.php';

session_start();

// Simple CSRF token generation
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = null;

// Handle create submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
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
			$pdo = get_pdo_connection();
			$stmt = $pdo->prepare('INSERT INTO guest_entries (name, email, message) VALUES (?, ?, ?)');
			$stmt->execute([$name, $email !== '' ? $email : null, $message]);
			$success = 'Berhasil menambahkan entri.';
		}
	}
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
		$errors[] = 'Invalid CSRF token.';
	} else {
		$id = (int)($_POST['id'] ?? 0);
		if ($id > 0) {
			$pdo = get_pdo_connection();
			$stmt = $pdo->prepare('DELETE FROM guest_entries WHERE id = ?');
			$stmt->execute([$id]);
			$success = 'Entri dihapus.';
		}
	}
}

// Ensure table exists
ensure_guestbook_table();

// Fetch entries
$pdo = get_pdo_connection();
$stmt = $pdo->query('SELECT id, name, email, message, created_at, updated_at FROM guest_entries ORDER BY created_at DESC');
$entries = $stmt->fetchAll();

?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Buku Tamu</title>
	<link rel="stylesheet" href="/styles.css" />
</head>
<body>
	<div class="container">
		<h1>Buku Tamu</h1>

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

		<section class="form-section">
			<h2>Tulis Entri Baru</h2>
			<form method="post" action="">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
				<input type="hidden" name="action" value="create" />
				<div class="field">
					<label for="name">Nama</label>
					<input type="text" id="name" name="name" required maxlength="100" />
				</div>
				<div class="field">
					<label for="email">Email (opsional)</label>
					<input type="email" id="email" name="email" maxlength="190" />
				</div>
				<div class="field">
					<label for="message">Pesan</label>
					<textarea id="message" name="message" rows="4" required></textarea>
				</div>
				<div class="actions">
					<button type="submit">Kirim</button>
				</div>
			</form>
		</section>

		<section class="list-section">
			<h2>Daftar Entri</h2>
			<?php if (!$entries): ?>
				<p>Belum ada entri.</p>
			<?php else: ?>
				<table>
					<thead>
						<tr>
							<th>Waktu</th>
							<th>Nama</th>
							<th>Email</th>
							<th>Pesan</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($entries as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
								<td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
								<td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
								<td><?= nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) ?></td>
								<td>
									<a href="/edit.php?id=<?= (int)$row['id'] ?>" style="margin-right:6px">Edit</a>
									<form method="post" action="" onsubmit="return confirm('Hapus entri ini?');" style="display:inline">
										<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
										<input type="hidden" name="action" value="delete" />
										<input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
										<button type="submit" class="danger">Hapus</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</section>
	</div>
</body>
</html>
