<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'无过滤SSRF',
    2=>'IP黑名单绕过',
    3=>'协议绕过',
    4=>'DNS重绑定',
    5=>'盲注SSRF',
];
render_header('SSRF 服务端请求伪造', 'ssrf');
?>
<div class="card">
  <h2>SSRF 服务端请求伪造 · 5 关</h2>
  <p>服务端请求伪造，URL 请求/IP绕过/协议绕过/DNS重绑定/盲注</p>
  <p style="color:#8b949e">每关四级难度 + 八段式教程。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title): ?>
      <a href="stage<?= $n ?>.php" class="stage <?= is_passed('ssrf',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title): ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= is_passed('ssrf',$n) ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
