<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'反射型-无过滤', 2=>'反射型-双引号属性', 3=>'反射型-单引号属性',
    4=>'反射型-过滤script', 5=>'反射型-过滤大小写', 6=>'反射型-双写绕过',
    7=>'反射型-过滤on事件', 8=>'反射型-img标签', 9=>'反射型-svg标签',
    10=>'反射型-HTML实体编码', 11=>'存储型-无过滤', 12=>'存储型-过滤script',
    13=>'存储型-过滤on事件', 14=>'DOM型-eval', 15=>'DOM型-innerHTML',
];
render_header('XSS 跨站脚本', 'xss');
?>
<div class="card">
  <h2>XSS 跨站脚本 · 15 关</h2>
  <p>覆盖反射型 XSS、存储型 XSS、DOM 型 XSS，以及各种过滤绕过技巧（大小写、双写、编码、标签变换）。</p>
  <p style="color:#8b949e">每关四级难度 + 八段式教程。目标：在页面中成功注入并执行 JavaScript 代码。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表（绿=已通关）</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title):
        $done = is_passed('xss', $n);
        $cls = $done ? 'done' : '';
    ?>
      <a href="stage<?= $n ?>.php" class="stage <?= $cls ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title):
      $done = is_passed('xss', $n);
  ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= $done ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
