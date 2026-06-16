<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$vaca_id = trim($_GET['v'] ?? '');
$is_vaca = $vaca_id !== '';

$page_data = null;
if ($is_vaca) {
    if (!preg_match('/^[0-9a-f]{16}$/', $vaca_id)) {
        header('Location: /vaca/');
        exit;
    }
    $pdo       = get_db();
    $page_data = load_vaca($pdo, $vaca_id);
    if (!$page_data) {
        header('Location: /vaca/');
        exit;
    }
}

// ── Open Graph meta ────────────────────────────────────────────────────────────
$og_title = 'Vaca Compartida';
$og_desc  = 'Juntemos para el regalo';
$og_url   = 'https://mammoli.ar/vaca/';

if ($is_vaca && $page_data) {
    $v        = $page_data['vaca'];
    $fmt      = fn(float $n) => '$' . number_format($n, 0, ',', '.');
    $og_title = htmlspecialchars($v['name'], ENT_QUOTES);
    $og_desc  = 'Vaca compartida · ' . $fmt($page_data['total_collected']) . ' de ' . $fmt((float)$v['goal_amount']);
    $og_url   = 'https://mammoli.ar/vaca/?v=' . $vaca_id;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $is_vaca && $page_data ? htmlspecialchars($page_data['vaca']['name']) . ' · Vaca Compartida' : 'Vaca Compartida' ?></title>
<meta name="description" content="<?= $og_desc ?>">

<!-- Open Graph -->
<meta property="og:title"       content="<?= $og_title ?>">
<meta property="og:description" content="<?= $og_desc ?>">
<meta property="og:url"         content="<?= $og_url ?>">
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="Vaca Compartida · mammoli.ar">

<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Vaca Compartida","url":"https://mammoli.ar/vaca/","description":"Organizá una vaca para regalar en grupo. Compartí el link, todos aportan, vos cobrás.","applicationCategory":"LifestyleApplication","operatingSystem":"Web","inLanguage":"es","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"Carlos Mammoli","url":"https://mammoli.ar/"}}
</script>

<!-- PWA -->
<link rel="manifest" href="/vaca/manifest.json">
<link rel="icon" href="/vaca/icon.svg">
<meta name="theme-color" content="#3b82f6">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Vaca">
<link rel="apple-touch-icon" href="/vaca/icon.svg">

<style>
/* ── Variables ──────────────────────────────────────────────── */
:root {
  --bg:      #f8fafc;
  --surface: #ffffff;
  --border:  #e2e8f0;
  --text:    #1e293b;
  --muted:   #64748b;
  --accent:  #3b82f6;
  --success: #22c55e;
  --danger:  #ef4444;
  --radius:  12px;
  --shadow:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
}

/* ── Reset / Base ───────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
a { color: var(--accent); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ── Layout ─────────────────────────────────────────────────── */
.container {
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  padding: 0 16px 80px;
}

/* ── Sticky header ──────────────────────────────────────────── */
.site-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  padding: 12px 16px;
}
.site-header-inner {
  max-width: 480px;
  margin: 0 auto;
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}
.site-header h1 {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  min-width: 0;
}
.site-header .meta {
  font-size: 0.8rem;
  color: var(--muted);
  white-space: nowrap;
}

/* ── Cards ──────────────────────────────────────────────────── */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  margin-top: 16px;
  box-shadow: var(--shadow);
}
.card-title {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 14px;
}

/* ── Landing ────────────────────────────────────────────────── */
.landing-hero {
  text-align: center;
  padding: 40px 0 8px;
}
.landing-hero .cow { font-size: 3rem; line-height: 1; }
.landing-hero h2 {
  font-size: 1.5rem;
  font-weight: 800;
  margin: 12px 0 6px;
}
.landing-hero p {
  color: var(--muted);
  font-size: 0.95rem;
}

/* ── Forms ──────────────────────────────────────────────────── */
.form-group { margin-bottom: 14px; }
.form-group label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--muted);
  margin-bottom: 5px;
}
.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--text);
  background: var(--bg);
  transition: border-color .15s;
  -webkit-appearance: none;
}
.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--accent);
  background: #fff;
}
.form-group textarea { resize: vertical; min-height: 64px; }
.form-group input::placeholder,
.form-group textarea::placeholder { color: #b0bec5; }
.form-group .hint {
  font-size: 0.75rem;
  color: var(--muted);
  margin-top: 4px;
}

/* ── Buttons ─────────────────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 11px 20px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: opacity .15s, transform .1s;
  -webkit-tap-highlight-color: transparent;
}
.btn:active { transform: scale(.97); }
.btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.btn-primary  { background: var(--accent);   color: #fff; width: 100%; }
.btn-success  { background: var(--success);  color: #fff; width: 100%; }
.btn-danger   { background: transparent; color: var(--danger); border: 1.5px solid var(--danger); width: 100%; }
.btn-outline  { background: transparent; color: var(--accent); border: 1.5px solid var(--accent); width: 100%; }
.btn-ghost    { background: transparent; color: var(--muted); padding: 6px 10px; font-size: 0.85rem; }
.btn-sm       { padding: 5px 10px; font-size: 0.78rem; border-radius: 6px; }

/* ── Progress ────────────────────────────────────────────────── */
.progress-section { text-align: center; }
.progress-bar-wrap {
  background: var(--border);
  border-radius: 99px;
  height: 18px;
  overflow: hidden;
  margin: 16px 0 12px;
}
.progress-bar-fill {
  height: 100%;
  border-radius: 99px;
  transition: width .6s cubic-bezier(.4,0,.2,1), background-color .4s;
  background: var(--accent);
  min-width: 4px;
}
.progress-bar-fill.complete { background: var(--success); }

.progress-amounts {
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--text);
  margin-bottom: 4px;
}
.progress-amounts .collected { color: var(--accent); }
.progress-amounts.complete .collected { color: var(--success); }

.progress-remaining {
  font-size: 0.9rem;
  color: var(--muted);
  margin-bottom: 8px;
}
.progress-goal-reached {
  font-size: 1rem;
  font-weight: 700;
  color: var(--success);
  margin-bottom: 8px;
}
.progress-stats {
  font-size: 0.8rem;
  color: var(--muted);
}

/* ── Banner: cerrada ─────────────────────────────────────────── */
.banner-closed {
  background: #fef3c7;
  border: 1px solid #fcd34d;
  border-radius: var(--radius);
  padding: 12px 16px;
  margin-top: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #92400e;
}

/* ── Contribution cards ──────────────────────────────────────── */
.contrib-list { display: flex; flex-direction: column; gap: 10px; }
.contrib-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-left: 4px solid var(--accent);
  border-radius: 8px;
  position: relative;
}
.contrib-body { flex: 1; min-width: 0; }
.contrib-name {
  font-weight: 700;
  font-size: 0.92rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.contrib-amount {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--success);
}
.contrib-note {
  font-size: 0.78rem;
  color: var(--muted);
  margin-top: 2px;
  word-break: break-word;
}
.contrib-date {
  font-size: 0.72rem;
  color: #94a3b8;
  margin-top: 3px;
}
.contrib-delete {
  flex-shrink: 0;
  background: none;
  border: none;
  font-size: 1rem;
  color: #cbd5e1;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color .15s;
  line-height: 1;
}
.contrib-delete:hover { color: var(--danger); }

/* ── Share section ───────────────────────────────────────────── */
.share-url {
  font-size: 0.8rem;
  color: var(--muted);
  word-break: break-all;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 8px 10px;
  margin-bottom: 10px;
  font-family: monospace;
}

/* ── Toast ───────────────────────────────────────────────────── */
#toast-container {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
  pointer-events: none;
  width: calc(100% - 32px);
  max-width: 440px;
}
.toast {
  background: #1e293b;
  color: #fff;
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0,0,0,.2);
  animation: toast-in .25s ease;
  text-align: center;
}
.toast.success { background: var(--success); }
.toast.error   { background: var(--danger); }
@keyframes toast-in {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Footer ──────────────────────────────────────────────────── */
footer {
  margin-top: auto;
  text-align: center;
  padding: 24px 16px 32px;
  font-size: 0.75rem;
  color: #94a3b8;
}
footer a { color: #94a3b8; }
footer a:hover { color: var(--muted); }

/* ── Spinner ─────────────────────────────────────────────────── */
.spinner {
  display: inline-block;
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin .6s linear infinite;
  vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Empty state ─────────────────────────────────────────────── */
.empty-state {
  text-align: center;
  padding: 28px 0 8px;
  color: var(--muted);
  font-size: 0.9rem;
}
.empty-state .icon { font-size: 2rem; margin-bottom: 8px; }
</style>
</head>
<body>

<?php if (!$is_vaca): ?>
<!-- ══════════════════════════════════════════════════════════════
     LANDING — crear vaca
═══════════════════════════════════════════════════════════════ -->
<div class="container">
  <div class="landing-hero">
    <div class="cow">🐄</div>
    <h2>Vaca Compartida</h2>
    <p>Juntemos para el regalo</p>
  </div>

  <div class="card">
    <div class="card-title">Nueva vaca</div>
    <form id="create-form" autocomplete="off">
      <div class="form-group">
        <label for="name">Nombre de la vaca</label>
        <input id="name" type="text" name="name" maxlength="120"
               placeholder="ej: Regalo para Laura" required>
      </div>
      <div class="form-group">
        <label for="goal_amount">Meta en $</label>
        <input id="goal_amount" type="number" name="goal_amount"
               min="1" step="any" placeholder="ej: 15000" required>
        <div class="hint">Monto total que quieren juntar</div>
      </div>
      <div class="form-group">
        <label for="description">Descripción (opcional)</label>
        <textarea id="description" name="description" maxlength="500"
                  placeholder="ej: Para el cumpleaños del 20/06, regalo + torta"></textarea>
      </div>
      <div class="form-group">
        <label for="organizer">Organizador (opcional)</label>
        <input id="organizer" type="text" name="organizer" maxlength="50"
               placeholder="Tu nombre">
      </div>
      <button type="submit" class="btn btn-primary" id="create-btn">
        🐄 Crear vaca
      </button>
    </form>
  </div>
</div>

<script>
document.getElementById('create-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('create-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Creando…';

  const goal = parseFloat(document.getElementById('goal_amount').value);
  if (!goal || goal <= 0) {
    showToast('La meta debe ser mayor a cero', 'error');
    btn.disabled = false;
    btn.innerHTML = '🐄 Crear vaca';
    return;
  }

  const body = {
    action:       'create_vaca',
    name:         document.getElementById('name').value.trim(),
    goal_amount:  goal,
    description:  document.getElementById('description').value.trim(),
    organizer:    document.getElementById('organizer').value.trim(),
  };

  try {
    const res  = await fetch('api.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
    const json = await res.json();
    if (json.ok) {
      window.location.href = '?v=' + json.data.vaca.id;
    } else {
      showToast(json.error || 'Error al crear', 'error');
      btn.disabled = false;
      btn.innerHTML = '🐄 Crear vaca';
    }
  } catch(err) {
    showToast('Error de conexión', 'error');
    btn.disabled = false;
    btn.innerHTML = '🐄 Crear vaca';
  }
});
</script>

<?php else: /* ── VACA PAGE ─────────────────────────────────────────── */
  $v     = $page_data['vaca'];
  $total = $page_data['total_collected'];
  $goal  = (float)$v['goal_amount'];
  $pct   = $page_data['percent'];
  $fmt   = fn(float $n): string => '$' . number_format($n, 0, ',', '.');
  $closed = (int)$v['closed'] === 1;
?>

<!-- Sticky header -->
<header class="site-header">
  <div class="site-header-inner">
    <h1>🐄 <?= htmlspecialchars($v['name']) ?></h1>
    <span class="meta">meta: <?= $fmt($goal) ?></span>
  </div>
</header>

<div class="container" id="app">

  <!-- Banner cerrada -->
  <?php if ($closed): ?>
  <div class="banner-closed" id="closed-banner">
    ✓ Esta vaca está cerrada
  </div>
  <?php endif; ?>

  <!-- Progress card -->
  <div class="card" id="progress-card">
    <div class="card-title">Progreso</div>
    <div class="progress-section">
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill <?= $pct >= 100 ? 'complete' : '' ?>"
             id="progress-fill"
             style="width: 0%"
             data-target="<?= min(100, $pct) ?>">
        </div>
      </div>
      <div class="progress-amounts <?= $pct >= 100 ? 'complete' : '' ?>" id="progress-amounts">
        <span class="collected" id="collected-amt"><?= $fmt($total) ?></span>
        <span style="color:var(--muted);font-weight:400"> de </span>
        <span id="goal-amt"><?= $fmt($goal) ?></span>
      </div>
      <?php if ($pct >= 100): ?>
      <div class="progress-goal-reached" id="goal-label">🎉 ¡Meta alcanzada!</div>
      <?php else: ?>
      <div class="progress-remaining" id="goal-label">Falta <?= $fmt($page_data['remaining']) ?></div>
      <?php endif; ?>
      <div class="progress-stats" id="progress-stats">
        <?= $page_data['contributor_count'] ?> <?= $page_data['contributor_count'] === 1 ? 'contribución' : 'contribuciones' ?>
      </div>
    </div>
  </div>

  <!-- Add contribution form -->
  <div class="card" id="contrib-form-card">
    <div class="card-title">Agregar aporte</div>
    <?php if ($closed): ?>
    <p style="color:var(--muted);font-size:.9rem;text-align:center;padding:8px 0">
      Esta vaca está cerrada. Reabrila para agregar aportes.
    </p>
    <?php else: ?>
    <form id="add-form" autocomplete="off">
      <div class="form-group">
        <label for="c-name">Tu nombre</label>
        <input id="c-name" type="text" maxlength="50" placeholder="ej: Martín" required>
      </div>
      <div class="form-group">
        <label for="c-amount">Monto $</label>
        <input id="c-amount" type="number" min="1" step="any" placeholder="ej: 2500" required>
      </div>
      <div class="form-group">
        <label for="c-note">Nota (opcional)</label>
        <input id="c-note" type="text" maxlength="200" placeholder="ej: pago en 2 cuotas">
      </div>
      <button type="submit" class="btn btn-success" id="add-btn">
        💸 Puse mi parte
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- Contributions list -->
  <div class="card">
    <div class="card-title">Aportes</div>
    <div class="contrib-list" id="contrib-list">
      <?php if (empty($page_data['contributions'])): ?>
      <div class="empty-state" id="empty-contrib">
        <div class="icon">💰</div>
        Todavía no hay aportes. ¡Sé el primero!
      </div>
      <?php else:
        $colors = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899'];
        foreach ($page_data['contributions'] as $i => $c):
          $color    = $colors[$i % 8];
          $date_str = date('d/m/Y H:i', (int)$c['created_at']);
      ?>
      <div class="contrib-card" style="border-left-color: <?= $color ?>"
           data-id="<?= (int)$c['id'] ?>">
        <div class="contrib-body">
          <div class="contrib-name"><?= htmlspecialchars($c['contributor_name']) ?></div>
          <div class="contrib-amount"><?= $fmt((float)$c['amount']) ?></div>
          <?php if ($c['note']): ?>
          <div class="contrib-note"><?= htmlspecialchars($c['note']) ?></div>
          <?php endif; ?>
          <div class="contrib-date"><?= $date_str ?></div>
        </div>
        <button class="contrib-delete" title="Eliminar" data-id="<?= (int)$c['id'] ?>">×</button>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Share -->
  <div class="card">
    <div class="card-title">Compartir</div>
    <div class="share-url" id="share-url"><?= htmlspecialchars('https://mammoli.ar/vaca/?v=' . $vaca_id) ?></div>
    <button class="btn btn-outline" id="copy-btn">📋 Copiar enlace</button>
  </div>

  <!-- Toggle closed -->
  <div class="card" style="margin-top:16px">
    <button class="btn <?= $closed ? 'btn-primary' : 'btn-danger' ?>" id="toggle-btn">
      <?= $closed ? '🔓 Reabrir vaca' : '🔒 Cerrar vaca' ?>
    </button>
    <?php if (!$closed): ?>
    <p style="font-size:.75rem;color:var(--muted);text-align:center;margin-top:8px">
      Cerrar deshabilita nuevos aportes y marca la vaca como finalizada.
    </p>
    <?php endif; ?>
  </div>

</div><!-- /#app -->

<script>
(function () {
  const VACA_ID      = <?= json_encode($vaca_id) ?>;
  const API          = 'api.php';
  const COLORS       = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899'];
  let   lastHash     = '';
  let   refreshTimer = null;

  // ── Format ─────────────────────────────────────────────────────────────────
  const fmt = n => new Intl.NumberFormat('es-AR', {
    style: 'currency', currency: 'ARS', minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(n);

  function fmtDate(ts) {
    const d = new Date(ts * 1000);
    const pad = n => String(n).padStart(2,'0');
    return pad(d.getDate()) + '/' + pad(d.getMonth()+1) + '/' + d.getFullYear()
         + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  // ── Render ─────────────────────────────────────────────────────────────────
  function renderProgress(data) {
    const total   = data.total_collected;
    const goal    = parseFloat(data.vaca.goal_amount);
    const pct     = data.percent;
    const reached = pct >= 100;

    const fill = document.getElementById('progress-fill');
    if (fill) {
      fill.style.width = Math.min(100, pct) + '%';
      fill.classList.toggle('complete', reached);
    }

    const amounts = document.getElementById('progress-amounts');
    if (amounts) {
      amounts.className = 'progress-amounts' + (reached ? ' complete' : '');
      const col = amounts.querySelector('#collected-amt');
      if (col) col.textContent = fmt(total);
      const gl = amounts.querySelector('#goal-amt');
      if (gl)  gl.textContent  = fmt(goal);
    }

    const lbl = document.getElementById('goal-label');
    if (lbl) {
      if (reached) {
        lbl.className   = 'progress-goal-reached';
        lbl.textContent = '🎉 ¡Meta alcanzada!';
      } else {
        lbl.className   = 'progress-remaining';
        lbl.textContent = 'Falta ' + fmt(data.remaining);
      }
    }

    const stats = document.getElementById('progress-stats');
    if (stats) {
      const n = data.contributor_count;
      stats.textContent = n + (n === 1 ? ' contribución' : ' contribuciones');
    }
  }

  function renderContribs(data) {
    const list = document.getElementById('contrib-list');
    if (!list) return;

    const contribs = data.contributions;
    if (!contribs || contribs.length === 0) {
      list.innerHTML = `<div class="empty-state">
        <div class="icon">💰</div>
        Todavía no hay aportes. ¡Sé el primero!
      </div>`;
      return;
    }

    list.innerHTML = contribs.map((c, i) => {
      const color = COLORS[i % COLORS.length];
      const note  = c.note
        ? `<div class="contrib-note">${escHtml(c.note)}</div>`
        : '';
      return `<div class="contrib-card" style="border-left-color:${color}" data-id="${c.id}">
        <div class="contrib-body">
          <div class="contrib-name">${escHtml(c.contributor_name)}</div>
          <div class="contrib-amount">${fmt(parseFloat(c.amount))}</div>
          ${note}
          <div class="contrib-date">${fmtDate(c.created_at)}</div>
        </div>
        <button class="contrib-delete" data-id="${c.id}" title="Eliminar">×</button>
      </div>`;
    }).join('');
  }

  function renderClosed(data) {
    const closed = parseInt(data.vaca.closed) === 1;
    const app    = document.getElementById('app');

    // Banner
    let banner = document.getElementById('closed-banner');
    if (closed && !banner) {
      banner = document.createElement('div');
      banner.className = 'banner-closed';
      banner.id = 'closed-banner';
      banner.textContent = '✓ Esta vaca está cerrada';
      app.insertBefore(banner, app.firstChild);
    } else if (!closed && banner) {
      banner.remove();
    }

    // Form
    const formCard = document.getElementById('contrib-form-card');
    if (formCard) {
      if (closed) {
        formCard.innerHTML = `<div class="card-title">Agregar aporte</div>
          <p style="color:var(--muted);font-size:.9rem;text-align:center;padding:8px 0">
            Esta vaca está cerrada. Reabrila para agregar aportes.
          </p>`;
      } else if (!formCard.querySelector('#add-form')) {
        formCard.innerHTML = `<div class="card-title">Agregar aporte</div>
          <form id="add-form" autocomplete="off">
            <div class="form-group">
              <label for="c-name">Tu nombre</label>
              <input id="c-name" type="text" maxlength="50" placeholder="ej: Martín" required>
            </div>
            <div class="form-group">
              <label for="c-amount">Monto $</label>
              <input id="c-amount" type="number" min="1" step="any" placeholder="ej: 2500" required>
            </div>
            <div class="form-group">
              <label for="c-note">Nota (opcional)</label>
              <input id="c-note" type="text" maxlength="200" placeholder="ej: pago en 2 cuotas">
            </div>
            <button type="submit" class="btn btn-success" id="add-btn">💸 Puse mi parte</button>
          </form>`;
        attachAddForm();
      }
    }

    // Toggle button
    const tb = document.getElementById('toggle-btn');
    if (tb) {
      tb.className   = 'btn ' + (closed ? 'btn-primary' : 'btn-danger');
      tb.textContent = closed ? '🔓 Reabrir vaca' : '🔒 Cerrar vaca';
    }
  }

  function renderAll(data) {
    renderProgress(data);
    renderContribs(data);
    renderClosed(data);
    attachDeleteButtons();
    const hash = JSON.stringify([data.total_collected, data.contributor_count, data.vaca.closed]);
    lastHash = hash;
  }

  // ── API calls ──────────────────────────────────────────────────────────────
  async function apiPost(body) {
    const res  = await fetch(API, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(body),
    });
    return res.json();
  }

  async function apiGet() {
    const res = await fetch(API + '?v=' + encodeURIComponent(VACA_ID));
    return res.json();
  }

  // ── Add contribution ────────────────────────────────────────────────────────
  function attachAddForm() {
    const form = document.getElementById('add-form');
    if (!form) return;
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn    = document.getElementById('add-btn');
      const name   = document.getElementById('c-name').value.trim();
      const amount = parseFloat(document.getElementById('c-amount').value);
      const note   = document.getElementById('c-note') ? document.getElementById('c-note').value.trim() : '';

      if (!name)           { showToast('Ingresá tu nombre', 'error'); return; }
      if (!amount || amount <= 0) { showToast('Monto inválido', 'error'); return; }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner"></span> Enviando…';

      try {
        const json = await apiPost({ action:'add_contribution', v:VACA_ID, contributor_name:name, amount, note });
        if (json.ok) {
          renderAll(json.data);
          form.reset();
          showToast('✓ Aporte registrado', 'success');
        } else {
          showToast(json.error || 'Error al guardar', 'error');
        }
      } catch(err) {
        showToast('Error de conexión', 'error');
      }

      btn.disabled = false;
      btn.innerHTML = '💸 Puse mi parte';
    });
  }

  // ── Delete contribution ────────────────────────────────────────────────────
  function attachDeleteButtons() {
    document.querySelectorAll('.contrib-delete').forEach(btn => {
      btn.addEventListener('click', async function() {
        if (!confirm('¿Eliminar este aporte?')) return;
        const cid = parseInt(this.dataset.id);
        try {
          const json = await apiPost({ action:'delete_contribution', v:VACA_ID, contribution_id:cid });
          if (json.ok) {
            renderAll(json.data);
            showToast('Aporte eliminado', 'success');
          } else {
            showToast(json.error || 'No se pudo eliminar', 'error');
          }
        } catch(err) {
          showToast('Error de conexión', 'error');
        }
      });
    });
  }

  // ── Toggle closed ──────────────────────────────────────────────────────────
  document.getElementById('toggle-btn').addEventListener('click', async function() {
    try {
      const json = await apiPost({ action:'toggle_closed', v:VACA_ID });
      if (json.ok) {
        renderAll(json.data);
        const closed = parseInt(json.data.vaca.closed) === 1;
        showToast(closed ? '🔒 Vaca cerrada' : '🔓 Vaca reabierta', 'success');
      } else {
        showToast(json.error || 'Error', 'error');
      }
    } catch(err) {
      showToast('Error de conexión', 'error');
    }
  });

  // ── Copy URL ───────────────────────────────────────────────────────────────
  document.getElementById('copy-btn').addEventListener('click', function() {
    const url = document.getElementById('share-url').textContent.trim();
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(() => showToast('Enlace copiado', 'success'));
    } else {
      const ta = document.createElement('textarea');
      ta.value = url;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
      showToast('Enlace copiado', 'success');
    }
  });

  // ── Auto-refresh (60 s) ────────────────────────────────────────────────────
  function scheduleRefresh() {
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(async () => {
      try {
        const json = await apiGet();
        if (json.ok) {
          const hash = JSON.stringify([json.data.total_collected, json.data.contributor_count, json.data.vaca.closed]);
          if (hash !== lastHash) renderAll(json.data);
        }
      } catch(_) {}
      scheduleRefresh();
    }, 60000);
  }

  // ── Helpers ────────────────────────────────────────────────────────────────
  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Init ───────────────────────────────────────────────────────────────────
  attachAddForm();
  attachDeleteButtons();

  // Animate progress bar on load
  requestAnimationFrame(() => {
    const fill   = document.getElementById('progress-fill');
    const target = fill ? fill.dataset.target : '0';
    setTimeout(() => { if (fill) fill.style.width = target + '%'; }, 80);
  });

  // Set initial hash
  const initTotal = document.getElementById('collected-amt');
  if (initTotal) {
    lastHash = JSON.stringify([
      <?= json_encode($total) ?>,
      <?= json_encode($page_data['contributor_count']) ?>,
      <?= json_encode((int)$v['closed']) ?>
    ]);
  }

  scheduleRefresh();
})();
</script>
<?php endif; ?>

<div id="toast-container"></div>

<footer>
  <a href="https://cafecito.app/mammoli" target="_blank" rel="noopener">☕ Invitame un café</a>
  &nbsp;·&nbsp;
  <a href="https://mammoli.ar" target="_blank" rel="noopener">mammoli.ar</a>
</footer>

<script>
function showToast(msg, type = '') {
  const container = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = 'toast' + (type ? ' ' + type : '');
  t.textContent = msg;
  container.appendChild(t);
  setTimeout(() => {
    t.style.opacity    = '0';
    t.style.transition = 'opacity .3s';
    setTimeout(() => t.remove(), 300);
  }, 3000);
}
</script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/vaca/sw.js', { scope: '/vaca/' });
}
</script>
</body>
</html>
