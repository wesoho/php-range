<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'水平越权',
    2=>'垂直越权',
    3=>'密码重置逻辑',
    4=>'验证码绕过',
    5=>'参数缺失',
    6=>'整数溢出',
    7=>'条件竞争',
    8=>'空指针/默认值',
];
render_header('逻辑漏洞', 'logic');
?>
<div class="card">
  <h2>逻辑漏洞 · 8 关</h2>
  <p>越权/水平越权/垂直越权/密码重置/验证码/参数缺失/整数溢出/条件竞争</p>
  <p style="color:#8b949e">每关四级难度 + 八段式教程。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title): ?>
      <a href="stage<?= $n ?>.php" class="stage <?= is_passed('logic',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title): ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= is_passed('logic',$n) ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
