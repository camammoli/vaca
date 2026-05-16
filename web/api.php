<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_ok(array $data): void {
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function get_input(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ── GET: get_vaca ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = trim($_GET['v'] ?? '');
    if ($id === '') {
        json_err('Falta el ID de la vaca');
    }
    if (!preg_match('/^[0-9a-f]{16}$/', $id)) {
        json_err('ID inválido');
    }

    $pdo  = get_db();
    $data = load_vaca($pdo, $id);
    if (!$data) {
        json_err('Vaca no encontrada', 404);
    }
    json_ok($data);
}

// ── POST ───────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Método no permitido', 405);
}

$input  = get_input();
$action = $input['action'] ?? '';

$pdo = get_db();

// ── create_vaca ────────────────────────────────────────────────────────────────
if ($action === 'create_vaca') {
    $name        = trim($input['name'] ?? '');
    $goal_amount = isset($input['goal_amount']) ? (float)$input['goal_amount'] : 0.0;
    $description = trim($input['description'] ?? '');
    $organizer   = trim($input['organizer'] ?? '');

    if ($name === '') {
        json_err('El nombre de la vaca es obligatorio');
    }
    if (mb_strlen($name) > 120) {
        json_err('El nombre es demasiado largo (máximo 120 caracteres)');
    }
    if ($goal_amount <= 0) {
        json_err('La meta debe ser mayor a cero');
    }

    $id = generate_id();
    $now = time();

    $stmt = $pdo->prepare(
        'INSERT INTO vacas (id, name, goal_amount, description, organizer, created_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $id,
        $name,
        $goal_amount,
        $description !== '' ? $description : null,
        $organizer   !== '' ? $organizer   : null,
        $now,
    ]);

    $data = load_vaca($pdo, $id);
    json_ok($data);
}

// ── add_contribution ───────────────────────────────────────────────────────────
if ($action === 'add_contribution') {
    $vaca_id          = trim($input['v'] ?? '');
    $contributor_name = mb_substr(trim($input['contributor_name'] ?? ''), 0, 50);
    $amount           = isset($input['amount']) ? (float)$input['amount'] : 0.0;
    $note             = trim($input['note'] ?? '');

    if (!preg_match('/^[0-9a-f]{16}$/', $vaca_id)) {
        json_err('ID inválido');
    }
    if ($contributor_name === '') {
        json_err('El nombre es obligatorio');
    }
    if ($amount <= 0) {
        json_err('El monto debe ser mayor a cero');
    }

    $vaca = load_vaca($pdo, $vaca_id);
    if (!$vaca) {
        json_err('Vaca no encontrada', 404);
    }
    if ((int)$vaca['vaca']['closed'] === 1) {
        json_err('Esta vaca está cerrada, no se pueden agregar aportes');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO contributions (vaca_id, contributor_name, amount, note, created_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $vaca_id,
        $contributor_name,
        $amount,
        $note !== '' ? $note : null,
        time(),
    ]);

    $data = load_vaca($pdo, $vaca_id);
    json_ok($data);
}

// ── delete_contribution ────────────────────────────────────────────────────────
if ($action === 'delete_contribution') {
    $vaca_id         = trim($input['v'] ?? '');
    $contribution_id = (int)($input['contribution_id'] ?? 0);

    if (!preg_match('/^[0-9a-f]{16}$/', $vaca_id)) {
        json_err('ID inválido');
    }
    if ($contribution_id <= 0) {
        json_err('ID de aporte inválido');
    }

    $vaca = load_vaca($pdo, $vaca_id);
    if (!$vaca) {
        json_err('Vaca no encontrada', 404);
    }

    $stmt = $pdo->prepare(
        'DELETE FROM contributions WHERE id = ? AND vaca_id = ?'
    );
    $stmt->execute([$contribution_id, $vaca_id]);

    if ($stmt->rowCount() === 0) {
        json_err('Aporte no encontrado', 404);
    }

    $data = load_vaca($pdo, $vaca_id);
    json_ok($data);
}

// ── toggle_closed ──────────────────────────────────────────────────────────────
if ($action === 'toggle_closed') {
    $vaca_id = trim($input['v'] ?? '');

    if (!preg_match('/^[0-9a-f]{16}$/', $vaca_id)) {
        json_err('ID inválido');
    }

    $vaca = load_vaca($pdo, $vaca_id);
    if (!$vaca) {
        json_err('Vaca no encontrada', 404);
    }

    $new_state = (int)$vaca['vaca']['closed'] === 1 ? 0 : 1;
    $stmt      = $pdo->prepare('UPDATE vacas SET closed = ? WHERE id = ?');
    $stmt->execute([$new_state, $vaca_id]);

    $data = load_vaca($pdo, $vaca_id);
    json_ok($data);
}

json_err('Acción desconocida');
