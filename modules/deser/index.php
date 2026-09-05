<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'PHP反序列化',
    2=>'POP链构造',
    3=>'phar反序列化',
];
render_header('反序列化', 'deser');
?>
<div class="card">
  <h2>反序列化 · 3 关</h2>
  <p>PHP 反序列化/POP链/phar</p>
  <p style="color:#8b949e">每关四级难度 + 八段式教程。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title): ?>
      <a href="stage<?= $n ?>.php" class="stage <?= is_passed('deser',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title): ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= is_passed('deser',$n) ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
