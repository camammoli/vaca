<?php
declare(strict_types=1);

define('DB_PATH', __DIR__ . '/data/vaca.db');

function get_db(): PDO {
    $data_dir = __DIR__ . '/data';

    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }

    $htaccess = $data_dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Deny from all\n");
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA foreign_keys=ON');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS vacas (
            id          TEXT    PRIMARY KEY,
            name        TEXT    NOT NULL,
            goal_amount REAL    NOT NULL,
            description TEXT,
            organizer   TEXT,
            currency    TEXT    DEFAULT 'ARS',
            created_at  INTEGER,
            closed      INTEGER DEFAULT 0
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contributions (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            vaca_id         TEXT    NOT NULL,
            contributor_name TEXT   NOT NULL,
            amount          REAL    NOT NULL,
            note            TEXT,
            created_at      INTEGER,
            FOREIGN KEY (vaca_id) REFERENCES vacas(id) ON DELETE CASCADE
        )
    ");

    return $pdo;
}

function generate_id(): string {
    return bin2hex(random_bytes(8));
}

function load_vaca(PDO $pdo, string $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM vacas WHERE id = ?');
    $stmt->execute([$id]);
    $vaca = $stmt->fetch();

    if (!$vaca) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT * FROM contributions WHERE vaca_id = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->execute([$id]);
    $contributions = $stmt->fetchAll();

    $total_collected = 0.0;
    foreach ($contributions as $c) {
        $total_collected += (float)$c['amount'];
    }

    $goal         = (float)$vaca['goal_amount'];
    $remaining    = max(0.0, $goal - $total_collected);
    $percent      = $goal > 0 ? (int)min(100, round(($total_collected / $goal) * 100)) : 0;
    $contributor_count = count($contributions);

    return [
        'vaca'              => $vaca,
        'contributions'     => $contributions,
        'total_collected'   => $total_collected,
        'remaining'         => $remaining,
        'percent'           => $percent,
        'contributor_count' => $contributor_count,
    ];
}
