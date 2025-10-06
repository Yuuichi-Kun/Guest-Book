<?php
// Returns a singleton PDO connection using configuration from config/config.php
function get_pdo_connection(): PDO {
	static $pdo = null;
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$config = require __DIR__ . '/../config/config.php';
	$host = $config['db_host'];
	$db = $config['db_name'];
	$user = $config['db_user'];
	$pass = $config['db_pass'];
	$port = (int)$config['db_port'];

	$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	$pdo = new PDO($dsn, $user, $pass, $options);
	return $pdo;
}

// Helper to ensure the required table exists (can be run once during deploy)
function ensure_guestbook_table(): void {
	$pdo = get_pdo_connection();
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS guest_entries (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(100) NOT NULL,
			email VARCHAR(190) NULL,
			message TEXT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
	);
}
