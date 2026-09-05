<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'本地文件包含',
    2=>'路径穿越',
    3=>'双写绕过',
    4=>'远程文件包含',
    5=>'伪协议绕过',
];
render_header('文件包含', 'lfi');
?>
<div class="card">
  <h2>文件包含 · 5 关</h2>
  <p>本地文件包含/路径穿越/双写绕过/远程包含/伪协议</p>
  <p style="color:#8b949e">每关四级难度 + 八段式教程。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title): ?>
      <a href="stage<?= $n ?>.php" class="stage <?= is_passed('lfi',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title): ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= is_passed('lfi',$n) ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
