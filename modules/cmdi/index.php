<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'无过滤命令注入', 2=>'过滤分号', 3=>'过滤管道符',
    4=>'过滤反引号', 5=>'过滤多种符号', 6=>'过滤空格',
    7=>'过滤cat命令', 8=>'内联注释绕过', 9=>'变量绕过',
    10=>'盲注命令注入',
];
render_header('命令注入', 'cmdi');
?>
<div class="card">
  <h2>命令注入 · 10 关</h2>
  <p>覆盖 OS 命令注入的各种形式：管道符、分号、反引号、$()、空格绕过、命令替换、盲注等。</p>
  <p style="color:#8b949e">目标：通过注入执行额外命令（如 id、ls /etc、cat /etc/passwd）。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title): ?>
      <a href="stage<?= $n ?>.php" class="stage <?= is_passed('cmdi',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title): ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= is_passed('cmdi',$n) ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
