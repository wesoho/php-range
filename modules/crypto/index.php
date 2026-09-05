<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$STAGES = [
    1=>'弱哈希',
    2=>'硬编码密钥',
    3=>'ECB模式',
    4=>'弱随机数',
    5=>'不安全比较',
];
render_header('不安全加密', 'crypto');
?>
<div class="card"><h2>不安全加密 · 5 关</h2><p>不安全加密漏洞</p></div>
<div class="stage-nav"><h3>关卡列表</h3><div class="stages">
<?php foreach ($STAGES as $n => $title): ?>
  <a href="stage<?= $n ?>.php" class="stage <?= is_passed('crypto',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
<?php endforeach; ?>
</div></div>
<div class="grid">
<?php foreach ($STAGES as $n => $title): ?>
  <a class="mod-card" href="stage<?= $n ?>.php"><h3>第 <?= $n ?> 关</h3><div class="desc"><?= h($title) ?></div><div class="meta"><?= is_passed('crypto',$n)?'✅ 已通关':'⬚ 未通关' ?></div></a>
<?php endforeach; ?>
</div>
<?php render_footer();
