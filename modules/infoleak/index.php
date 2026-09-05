<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$STAGES = [
    1=>'目录列表',
    2=>'备份文件',
    3=>'错误信息',
    4=>'源码泄露',
    5=>'配置文件',
];
render_header('信息泄露', 'infoleak');
?>
<div class="card"><h2>信息泄露 · 5 关</h2><p>信息泄露漏洞</p></div>
<div class="stage-nav"><h3>关卡列表</h3><div class="stages">
<?php foreach ($STAGES as $n => $title): ?>
  <a href="stage<?= $n ?>.php" class="stage <?= is_passed('infoleak',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
<?php endforeach; ?>
</div></div>
<div class="grid">
<?php foreach ($STAGES as $n => $title): ?>
  <a class="mod-card" href="stage<?= $n ?>.php"><h3>第 <?= $n ?> 关</h3><div class="desc"><?= h($title) ?></div><div class="meta"><?= is_passed('infoleak',$n)?'✅ 已通关':'⬚ 未通关' ?></div></a>
<?php endforeach; ?>
</div>
<?php render_footer();
