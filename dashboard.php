<?php
require_once __DIR__.'/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

function q($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return number_format((float)$n,2,","," "); }

$tab = $_GET['tab'] ?? 'capital';
$sub = $_GET['sub'] ?? 'saldo';

// Pre-calc
$inflows = db()->query("SELECT * FROM capital_inflows ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_all = 0.0;
foreach($inflows as $r){ $total_all += (float)$r['amount']; }

// Percent buckets (business logic from earlier)
$PCT = [
  'insurance'=>0.02,
  'it'=>0.09,
  'bank'=>0.05,        // SHMAT BANK
  'operational'=>0.06,
  'charity'=>0.04,
  'owner_capital'=>0.10,
  'for_invest'=>0.64
];

$sum_insurance = $total_all * $PCT['insurance'];
$sum_it = $total_all * $PCT['it'];
$sum_bank = $total_all * $PCT['bank'];
$sum_oper = $total_all * $PCT['operational'];
$sum_charity = $total_all * $PCT['charity'];
$sum_owner_capital = $total_all * $PCT['owner_capital'];
$sum_for_invest = $total_all * $PCT['for_invest'];

// Section helpers
function get_withdraw($table){
  $st = db()->query("SELECT * FROM $table ORDER BY id DESC");
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  $sum = 0.0; foreach($rows as $r) $sum += (float)$r['amount'];
  return [$rows,$sum];
}

?><!DOCTYPE html><html lang="uk"><head>
<meta charset="utf-8"><title>CorpOS</title>
<link rel="stylesheet" href="styles.css">
</head><body>
<div class="sidebar">
  <h2>CorpOS</h2>
  <div style="opacity:.8;margin-bottom:8px">Користувач: <?= q($_SESSION['username'] ?? 'admin') ?></div>
  <a href="?tab=capital">Рух капіталу</a>
  <a href="?tab=owner">Капітал власника</a>
  <a href="?tab=operational">Операційний капітал</a>
  <a href="?tab=it">IT компанія</a>
  <a href="?tab=charity&sub=fund">Благодійний фонд</a>
  <a href="?tab=insurance&sub=saldo">Страховий фонд</a>
  <a href="logout.php">Вийти</a>
</div>

<div class="content">
  <h2>Дашборд <span class="badge">v1.1</span></h2>

<?php if($tab==='capital'): ?>
  <div class="hstack" style="justify-content:space-between;margin-bottom:10px">
    <div class="alert">Всього надходжень: ₴<?= money($total_all) ?></div>
    <a class="btn primary" data-modal-open="m-inflow">Додати надходження</a>
  </div>
  <div class="card" style="margin-bottom:12px">
    <table class="table">
      <thead><tr>
        <th>Страховий фонд (2%)</th><th>IT (9%)</th><th>SHMAT BANK (5%)</th><th>Операційна діяльність (6%)</th>
        <th>Благодійний Фонд (4%)</th><th>Капітал власника (10%)</th><th>Капітал для інвестування (64%)</th>
      </tr></thead>
      <tbody><tr>
        <td>₴<?= money($sum_insurance) ?></td><td>₴<?= money($sum_it) ?></td><td>₴<?= money($sum_bank) ?></td>
        <td>₴<?= money($sum_oper) ?></td><td>₴<?= money($sum_charity) ?></td><td>₴<?= money($sum_owner_capital) ?></td>
        <td>₴<?= money($sum_for_invest) ?></td>
      </tr></tbody>
    </table>
  </div>
  <table class="table">
    <thead><tr><th>#</th><th>Дата</th><th>Сума</th><th>Джерело</th><th>Дії</th></tr></thead>
    <tbody>
    <?php $i=1; foreach($inflows as $r): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= q($r['created_at']) ?></td>
        <td>₴<?= money($r['amount']) ?></td>
        <td><?= q($r['source']) ?></td>
        <td><a class="btn danger" href="capital.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити запис?')">Видалити</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

<?php elseif($tab==='owner'): 
  list($rows,$sumOut)=get_withdraw('owner_withdrawals'); 
  $balance = $sum_owner_capital - $sumOut;
?>
  <div class="card" style="margin-bottom:12px">
    <table class="table">
      <thead><tr><th>Загальне надходження</th><th>Виведено капіталу</th><th>Залишок капіталу</th><th></th></tr></thead>
      <tbody><tr>
        <td>₴<?= money($sum_owner_capital) ?></td><td>₴<?= money($sumOut) ?></td><td>₴<?= money($balance) ?></td>
        <td style="text-align:right"><a class="btn primary" data-modal-open="m-owner-add">Вивести капітал</a></td>
      </tr></tbody>
    </table>
  </div>
  <div class="card"><h3 style="margin:0 0 8px">Історія</h3>
    <table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= q($r['created_at']) ?></td>
        <td>₴<?= money($r['amount']) ?></td>
        <td><?= q($r['note']) ?></td>
        <td class="hstack">
          <a class="btn" data-modal-open="m-owner-edit-<?= (int)$r['id'] ?>">Редагувати</a>
          <a class="btn danger" href="owner.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
  <!-- модалки owner -->
  <div class="modal" id="m-owner-add"><div class="modal-content">
    <div class="modal-header">Вивести капітал (Власника)</div>
    <form class="vstack" method="post" action="owner.php">
      <input type="hidden" name="action" value="create">
      <label>Сума<input class="input" type="number" name="amount" required></label>
      <label>Нотатка<input class="input" name="note" placeholder="Необов'язково"></label>
      <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
    </form>
  </div></div>
  <?php foreach($rows as $r): ?>
    <div class="modal" id="m-owner-edit-<?= (int)$r['id'] ?>"><div class="modal-content">
      <div class="modal-header">Редагувати</div>
      <form class="vstack" method="post" action="owner.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <label>Сума<input class="input" type="number" name="amount" value="<?= q($r['amount']) ?>" required></label>
        <label>Нотатка<input class="input" name="note" value="<?= q($r['note']) ?>"></label>
        <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form>
    </div></div>
  <?php endforeach; ?>

<?php elseif($tab==='operational'): 
  list($rows,$sumOut)=get_withdraw('operational_withdrawals'); 
  $balance = $sum_oper - $sumOut; ?>
  <div class="card" style="margin-bottom:12px">
    <table class="table"><thead><tr><th>Загальне надходження</th><th>Виведено капіталу</th><th>Залишок капіталу</th><th></th></tr></thead>
      <tbody><tr><td>₴<?= money($sum_oper) ?></td><td>₴<?= money($sumOut) ?></td><td>₴<?= money($balance) ?></td>
      <td style="text-align:right"><a class="btn primary" data-modal-open="m-oper-add">Вивести капітал</a></td></tr></tbody></table>
  </div>
  <div class="card"><h3 style="margin:0 0 8px">Історія</h3>
  <table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?><tr>
    <td><?= q($r['created_at']) ?></td><td>₴<?= money($r['amount']) ?></td><td><?= q($r['note']) ?></td>
    <td class="hstack"><a class="btn" data-modal-open="m-oper-edit-<?= (int)$r['id'] ?>">Редагувати</a>
    <a class="btn danger" href="operational.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <div class="modal" id="m-oper-add"><div class="modal-content"><div class="modal-header">Вивести капітал (Операційний)</div>
  <form class="vstack" method="post" action="operational.php"><input type="hidden" name="action" value="create">
  <label>Сума<input class="input" type="number" name="amount" required></label><label>Нотатка<input class="input" name="note"></label>
  <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div>
  <?php foreach($rows as $r): ?><div class="modal" id="m-oper-edit-<?= (int)$r['id'] ?>"><div class="modal-content"><div class="modal-header">Редагувати</div>
  <form class="vstack" method="post" action="operational.php"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
  <label>Сума<input class="input" type="number" name="amount" value="<?= q($r['amount']) ?>" required></label><label>Нотатка<input class="input" name="note" value="<?= q($r['note']) ?>"></label>
  <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div><?php endforeach; ?>

<?php elseif($tab==='it'): 
  list($rows,$sumOut)=get_withdraw('it_withdrawals'); 
  $balance = $sum_it - $sumOut; ?>
  <div class="card" style="margin-bottom:12px">
    <table class="table"><thead><tr><th>Загальне надходження</th><th>Виведено капіталу</th><th>Залишок капіталу</th><th></th></tr></thead>
    <tbody><tr><td>₴<?= money($sum_it) ?></td><td>₴<?= money($sumOut) ?></td><td>₴<?= money($balance) ?></td>
    <td style="text-align:right"><a class="btn primary" data-modal-open="m-it-add">Вивести капітал</a></td></tr></tbody></table>
  </div>
  <div class="card"><h3 style="margin:0 0 8px">Історія</h3><table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?><tr><td><?= q($r['created_at']) ?></td><td>₴<?= money($r['amount']) ?></td><td><?= q($r['note']) ?></td>
  <td class="hstack"><a class="btn" data-modal-open="m-it-edit-<?= (int)$r['id'] ?>">Редагувати</a>
  <a class="btn danger" href="it.php?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <div class="modal" id="m-it-add"><div class="modal-content"><div class="modal-header">Вивести капітал (IT)</div>
  <form class="vstack" method="post" action="it.php"><input type="hidden" name="action" value="create"><label>Сума<input class="input" type="number" name="amount" required></label><label>Нотатка<input class="input" name="note"></label><div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div>
  <?php foreach($rows as $r): ?><div class="modal" id="m-it-edit-<?= (int)$r['id'] ?>"><div class="modal-content"><div class="modal-header">Редагувати</div>
  <form class="vstack" method="post" action="it.php"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
  <label>Сума<input class="input" type="number" name="amount" value="<?= q($r['amount']) ?>" required></label><label>Нотатка<input class="input" name="note" value="<?= q($r['note']) ?>"></label><div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div><?php endforeach; ?>

<?php elseif($tab==='charity'):
  $sub = $_GET['sub'] ?? 'fund';
  $fund_general = $sum_charity;
  list($repRows,$repOut)=get_withdraw('charity_rep_withdrawals');
  list($donRows,$donOut)=get_withdraw('charity_don_withdrawals');
  $repIn = $fund_general * 0.25;
  $donIn = $fund_general * 0.75;
?>
  <div class="tabs" style="margin-bottom:10px">
    <a class="btn <?= $sub==='fund'?'primary':'' ?>" href="?tab=charity&sub=fund">Благодійний фонд</a>
    <a class="btn <?= $sub==='rep'?'primary':'' ?>" href="?tab=charity&sub=rep">Представницькі витрати (25%)</a>
    <a class="btn <?= $sub==='don'?'primary':'' ?>" href="?tab=charity&sub=don">Благодійні внески (75%)</a>
  </div>

  <?php if($sub==='fund'): ?>
    <div class="card">
      <table class="table">
        <thead><tr><th>Стаття</th><th>Сума</th></tr></thead>
        <tbody>
          <tr><td>Надходження капіталу від корпорації</td><td>₴<?= money($fund_general) ?></td></tr>
          <tr><td>Надходження капіталу від банків</td><td>₴0,00</td></tr>
          <tr><td>Загальне надходження капіталу</td><td>₴<?= money($fund_general) ?></td></tr>
        </tbody>
      </table>
    </div>
  <?php elseif($sub==='rep'): $balance=$repIn-$repOut; ?>
    <div class="card" style="margin-bottom:12px">
      <table class="table"><thead><tr><th>Стаття</th><th>Сума</th></tr></thead><tbody>
        <tr><td>Надходження капіталу (25%)</td><td>₴<?= money($repIn) ?></td></tr>
        <tr><td>Витрачено капіталу</td><td>₴<?= money($repOut) ?></td></tr>
        <tr><td><b>Залишок капіталу</b></td><td><b>₴<?= money($balance) ?></b></td></tr>
      </tbody></table>
      <div style="text-align:right;margin-top:8px"><a class="btn primary" data-modal-open="m-rep-add">Вивести капітал</a></div>
    </div>
    <div class="card"><h3 style="margin:0 0 8px">Історія</h3>
      <table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
        <?php foreach($repRows as $r): ?>
          <tr><td><?= q($r['created_at']) ?></td><td>₴<?= money($r['amount']) ?></td><td><?= q($r['note']) ?></td>
          <td class="hstack"><a class="btn" data-modal-open="m-rep-edit-<?= (int)$r['id'] ?>">Редагувати</a>
          <a class="btn danger" href="charity.php?action=delete&type=rep&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a></td></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
    <div class="modal" id="m-rep-add"><div class="modal-content"><div class="modal-header">Вивести капітал (25%)</div>
      <form class="vstack" method="post" action="charity.php">
        <input type="hidden" name="action" value="create"><input type="hidden" name="type" value="rep">
        <label>Сума<input class="input" type="number" name="amount" required></label>
        <label>Нотатка<input class="input" name="note"></label>
        <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></div></div>
      <?php foreach($repRows as $r): ?><div class="modal" id="m-rep-edit-<?= (int)$r['id'] ?>"><div class="modal-content"><div class="modal-header">Редагувати</div>
      <form class="vstack" method="post" action="charity.php"><input type="hidden" name="action" value="update"><input type="hidden" name="type" value="rep"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <label>Сума<input class="input" type="number" name="amount" value="<?= q($r['amount']) ?>" required></label><label>Нотатка<input class="input" name="note" value="<?= q($r['note']) ?>"></label>
      <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div><?php endforeach; ?>

  <?php elseif($sub==='don'): $balance=$donIn-$donOut; ?>
    <div class="card" style="margin-bottom:12px">
      <table class="table"><thead><tr><th>Стаття</th><th>Сума</th></tr></thead><tbody>
        <tr><td>Надходження капіталу (75%)</td><td>₴<?= money($donIn) ?></td></tr>
        <tr><td>Витрачено капіталу</td><td>₴<?= money($donOut) ?></td></tr>
        <tr><td><b>Залишок капіталу</b></td><td><b>₴<?= money($balance) ?></b></td></tr>
      </tbody></table>
      <div style="text-align:right;margin-top:8px"><a class="btn primary" data-modal-open="m-don-add">Вивести капітал</a></div>
    </div>
    <div class="card"><h3 style="margin:0 0 8px">Історія</h3>
      <table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
        <?php foreach($donRows as $r): ?>
          <tr><td><?= q($r['created_at']) ?></td><td>₴<?= money($r['amount']) ?></td><td><?= q($r['note']) ?></td>
          <td class="hstack"><a class="btn" data-modal-open="m-don-edit-<?= (int)$r['id'] ?>">Редагувати</a>
          <a class="btn danger" href="charity.php?action=delete&type=don&id=<?= (int)$r['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a></td></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
    <div class="modal" id="m-don-add"><div class="modal-content"><div class="modal-header">Вивести капітал (75%)</div>
      <form class="vstack" method="post" action="charity.php">
        <input type="hidden" name="action" value="create"><input type="hidden" name="type" value="don">
        <label>Сума<input class="input" type="number" name="amount" required></label>
        <label>Нотатка<input class="input" name="note"></label>
        <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></div></div>
      <?php foreach($donRows as $r): ?><div class="modal" id="m-don-edit-<?= (int)$r['id'] ?>"><div class="modal-content"><div class="modal-header">Редагувати</div>
      <form class="vstack" method="post" action="charity.php"><input type="hidden" name="action" value="update"><input type="hidden" name="type" value="don"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <label>Сума<input class="input" type="number" name="amount" value="<?= q($r['amount']) ?>" required></label><label>Нотатка<input class="input" name="note" value="<?= q($r['note']) ?>"></label>
      <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div></form></div></div><?php endforeach; ?>
  <?php endif; ?>

<?php elseif($tab==='insurance'):
  $sub = $_GET['sub'] ?? 'saldo';
  $uahIn = $sum_insurance * 0.40;
  $usdIn = $sum_insurance * 0.40;
  $metalIn = $sum_insurance * 0.20;
  $sources = db()->query("SELECT * FROM insurance_sources ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
  $invest = db()->query("SELECT i.*, s.name AS source_name FROM insurance_investments i LEFT JOIN insurance_sources s ON s.id=i.source_id ORDER BY i.id DESC")->fetchAll(PDO::FETCH_ASSOC);
  $sources_total = 0.0; foreach($sources as $s){ $sources_total += (float)$s['amount']; }
  $invest_total = 0.0; foreach($invest as $irow){ $invest_total += (float)$irow['amount']; }
  $uahBalance = $uahIn - $invest_total;
?>
  <div class="tabs" style="margin-bottom:10px">
    <a class="btn <?= $sub==='saldo'?'primary':'' ?>" href="?tab=insurance&sub=saldo">Сальдо</a>
    <a class="btn <?= $sub==='uah'?'primary':'' ?>" href="?tab=insurance&sub=uah">40% гривневий еквівалент</a>
    <a class="btn <?= $sub==='usd'?'primary':'' ?>" href="?tab=insurance&sub=usd">40% валютний еквівалент</a>
    <a class="btn <?= $sub==='metal'?'primary':'' ?>" href="?tab=insurance&sub=metal">20% метали/дор. метали</a>
    <a class="btn <?= $sub==='profit'?'primary':'' ?>" href="?tab=insurance&sub=profit">Розподіл прибутку</a>
  </div>

  <?php if($sub==='saldo'): ?>
    <div class="card">
      <table class="table"><thead><tr><th>Стаття</th><th>Сума</th></tr></thead><tbody>
        <tr><td>Надходження капіталу від корпорації</td><td>₴<?= money($sum_insurance) ?></td></tr>
        <tr><td>Надходження капіталу від банків</td><td>₴0,00</td></tr>
        <tr><td><b>Сальдо (загальне надходження)</b></td><td><b>₴<?= money($sum_insurance) ?></b></td></tr>
      </tbody></table>
      <div style="margin-top:12px">
        <table class="table"><thead><tr><th>Стаття</th><th>Сума</th></tr></thead><tbody>
          <tr><td>Грошовий еквівалент (40%)</td><td>₴<?= money($uahIn) ?></td></tr>
          <tr><td>Валютний еквівалент (40%)</td><td>₴<?= money($usdIn) ?></td></tr>
          <tr><td>Метали / дорогоцінні метали (20%)</td><td>₴<?= money($metalIn) ?></td></tr>
        </tbody></table>
      </div>
    </div>
  <?php elseif($sub==='uah'): ?>
    <div class="card" style="margin-bottom:12px">
      <table class="table"><thead><tr><th>Стаття</th><th>Сума</th></tr></thead><tbody>
        <tr><td>Грошовий еквівалент (40% від загального)</td><td>₴<?= money($uahIn) ?></td></tr>
        <tr><td>Витрачено капіталу (грн)</td><td>₴<?= money($invest_total) ?></td></tr>
        <tr><td><b>Залишок капіталу (грн)</b></td><td><b>₴<?= money($uahBalance) ?></b></td></tr>
      </tbody></table>
      <div style="text-align:right;margin-top:8px" class="hstack"><a class="btn primary" data-modal-open="m-create-source">Створити джерело доходу</a><a class="btn success" data-modal-open="m-invest">Інвестувати капітал</a></div>
    </div>

    <div class="card"><h3 style="margin:0 0 8px">Джерела доходу</h3>
      <table class="table"><thead><tr><th>Назва</th><th>Сума</th><th>Нотатка</th></tr></thead><tbody>
      <?php foreach($sources as $s): ?><tr><td><?= q($s['name']) ?></td><td>₴<?= money($s['amount']) ?></td><td><?= q($s['note']) ?></td></tr><?php endforeach; ?>
      <tr><td><b>Загальна сума капіталу у джерелах</b></td><td><b>₴<?= money($sources_total) ?></b></td><td></td></tr>
      </tbody></table>
    </div>

    <div class="card"><h3 style="margin:0 0 8px">Історія</h3>
      <table class="table"><thead><tr><th>Дата/час</th><th>Сума</th><th>Джерело</th><th>Нотатка</th><th>Дії</th></tr></thead><tbody>
      <?php foreach($invest as $irow): ?><tr>
        <td><?= q($irow['created_at']) ?></td><td>₴<?= money($irow['amount']) ?></td><td><?= q($irow['source_name']) ?></td><td><?= q($irow['note']) ?></td>
        <td class="hstack">
          <a class="btn" data-modal-open="m-invest-edit-<?= (int)$irow['id'] ?>">Редагувати</a>
          <a class="btn danger" href="insurance.php?action=invest_delete&id=<?= (int)$irow['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a>
        </td>
      </tr><?php endforeach; ?>
      </tbody></table>
    </div>

    <!-- модалки UAH -->
    <div class="modal" id="m-create-source"><div class="modal-content"><div class="modal-header">Створити джерело доходу</div>
      <form class="vstack" method="post" action="insurance.php">
        <input type="hidden" name="action" value="create_source">
        <label>Назва<input class="input" name="name" required></label>
        <label>Сума<input class="input" type="number" name="amount" required></label>
        <label>Нотатка<input class="input" name="note"></label>
        <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></div></div>

    <div class="modal" id="m-invest"><div class="modal-content"><div class="modal-header">Інвестувати капітал</div>
      <form class="vstack" method="post" action="insurance.php">
        <input type="hidden" name="action" value="invest_create">
        <label>Сума<input class="input" type="number" name="amount" required></label>
        <label>Джерело
          <select class="input" name="source_id">
            <?php foreach($sources as $s): ?><option value="<?= (int)$s['id'] ?>"><?= q($s['name']) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label>Нотатка<input class="input" name="note"></label>
        <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></div></div>

    <?php foreach($invest as $irow): ?>
      <div class="modal" id="m-invest-edit-<?= (int)$irow['id'] ?>"><div class="modal-content"><div class="modal-header">Редагувати інвестицію</div>
        <form class="vstack" method="post" action="insurance.php">
          <input type="hidden" name="action" value="invest_update"><input type="hidden" name="id" value="<?= (int)$irow['id'] ?>">
          <label>Сума<input class="input" type="number" name="amount" value="<?= q($irow['amount']) ?>" required></label>
          <label>Джерело
            <select class="input" name="source_id">
              <?php foreach($sources as $s): ?><option value="<?= (int)$s['id'] ?>" <?= ($s['id']==$irow['source_id']?'selected':'') ?>><?= q($s['name']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <label>Нотатка<input class="input" name="note" value="<?= q($irow['note']) ?>"></label>
          <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
        </form></div></div>
    <?php endforeach; ?>

  <?php elseif($sub==='usd' || $sub==='metal' || $sub==='profit'): ?>
    <div class="alert">Ця вкладка підготовлена до наступного етапу. Зараз дані показані у вкладці “Сальдо” та “40% гривневий еквівалент”.</div>
  <?php endif; ?>

<?php else: ?>
  <div class="alert">Оберіть розділ зліва.</div>
<?php endif; ?>

</div>

<!-- Modals shared -->
<div class="modal" id="m-inflow"><div class="modal-content">
  <div class="modal-header">Додати надходження</div>
  <form class="vstack" method="post" action="capital.php">
    <input type="hidden" name="action" value="create">
    <label>Сума<input class="input" type="number" name="amount" required></label>
    <label>Джерело
      <select class="input" name="source">
        <option value="owner">Власник</option>
        <option value="bank">Банк</option>
      </select>
    </label>
    <div class="modal-footer"><button class="btn" type="button" data-modal-close>Скасувати</button><button class="btn primary">Зберегти</button></div>
  </form>
</div></div>

<script src="modal.js"></script>
</body></html>
