<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$STAGES = [
    1=>'eval注入',
    2=>'assert注入',
    3=>'preg_replace /e',
    4=>'create_function',
    5=>'动态调用',
];
render_header('代码执行', 'rce');
?>
<div class="card"><h2>代码执行 · 5 关</h2><p>代码执行漏洞</p></div>
<div class="stage-nav"><h3>关卡列表</h3><div class="stages">
<?php foreach ($STAGES as $n => $title): ?>
  <a href="stage<?= $n ?>.php" class="stage <?= is_passed('rce',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
<?php endforeach; ?>
</div></div>
<div class="grid">
<?php foreach ($STAGES as $n => $title): ?>
  <a class="mod-card" href="stage<?= $n ?>.php"><h3>第 <?= $n ?> 关</h3><div class="desc"><?= h($title) ?></div><div class="meta"><?= is_passed('rce',$n)?'✅ 已通关':'⬚ 未通关' ?></div></a>
<?php endforeach; ?>
</div>
<?php render_footer();
