
<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
require_login();

$TAB = $_GET['tab'] ?? 'capital';

// Percent schema (should sum to 100)
$PCTS = [
  'Страховий фонд' => 2,
  'IT' => 9,
  'SHMAT BANK' => 5,
  'Операційна діяльність' => 6,
  'Благодійний Фонд' => 4,
  'Капітал власника' => 10,
  'Капітал для інвестування' => 64,
];

function money($n) { return number_format((float)$n, 2, ',', ' '); }

// fetch data
$rows = db()->query('SELECT * FROM capital_inflows ORDER BY datetime(created_at) DESC')->fetchAll();
$total = 0.0;
foreach ($rows as $r) { $total += (float)$r['amount']; }

// totals per category
$totals = [];
foreach ($PCTS as $name => $p) { $totals[$name] = $total * $p / 100.0; }

?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CorpOS — Дашборд</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="brand">CorpOS</div>
    <div class="small">Користувач: <b><?=htmlspecialchars($_SESSION['username'])?></b></div>
    <div class="section-title">Корпорація</div>
    <nav class="nav">
      <a href="/dashboard.php?tab=capital" class="<?= $TAB==='capital'?'active':'' ?>">Рух капіталу</a>
      <a href="#" onclick="return false;">Капітал власника</a>
      <a href="#" onclick="return false;">Операційний капітал</a>
      <a href="#" onclick="return false;">ІТ компанія</a>
      <a href="#" onclick="return false;">Благодійний фонд</a>
      <a href="#" onclick="return false;">Страховий фонд</a>
      <a href="#" onclick="return false;">Фонд облігацій</a>
      <a href="#" onclick="return false;">Біржовий фонд</a>
      <a href="#" onclick="return false;">Бізнес</a>
    </nav>
    <hr class="hr">
    <a class="btn" href="/logout.php">Вийти</a>
  </aside>
  <main class="main">
    <div class="topbar">
      <div class="header">Дашборд</div>
      <span class="badge">v0.1 prototype</span>
    </div>

    <?php if ($TAB==='capital'): ?>
    <div class="card">
      <div class="header">Рух капіталу</div>

      <form class="grid2" method="post" action="/capital.php">
        <input type="hidden" name="action" value="add">
        <label>Сума (USD)
          <input class="input" type="number" step="0.01" min="0" name="amount" required placeholder="Напр. 2000">
        </label>
        <div class="hstack">
          <label>Дата/час
            <input class="input" type="datetime-local" name="date">
          </label>
          <button class="btn primary" type="submit">Додати надходження</button>
        </div>
      </form>

      <hr class="hr">

      <div class="hstack">
        <div><b>Всього надходжень:</b> $<?= money($total) ?></div>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>№</th>
            <th>Дата запису</th>
            <th>Сума</th>
            <?php foreach ($PCTS as $name=>$pct): ?>
              <th><?= htmlspecialchars($name) ?> (<?= $pct ?>%)</th>
            <?php endforeach; ?>
            <th>Дії</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ($rows as $r): $amt = (float)$r['amount']; ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
              <td><?= money($amt) ?></td>
              <?php foreach ($PCTS as $name=>$pct): $val = $amt * $pct / 100.0; ?>
                <td><?= money($val) ?></td>
              <?php endforeach; ?>
              <td>
                <a class="btn danger" href="/capital.php?action=delete&id=<?= intval($r['id']) ?>" onclick="return confirm('Видалити запис?')">Видалити</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3">Підсумок</th>
            <?php foreach ($PCTS as $name=>$pct): ?>
              <th><?= money($totals[$name]) ?></th>
            <?php endforeach; ?>
            <th></th>
          </tr>
        </tfoot>
      </table>
      <div class="small" style="margin-top:8px;">Відсотки фіксовані: <?php foreach($PCTS as $n=>$p){ echo htmlspecialchars($n) . " — " . $p . "%; "; } ?></div>
    </div>
    <?php else: ?>
      <div class="card">
        <div class="alert">Ця вкладка ще в розробці. Зараз реалізовано лише «Рух капіталу».</div>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
