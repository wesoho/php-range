<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'无过滤任意上传', 2=>'前端JS验证', 3=>'MIME类型验证',
    4=>'扩展名黑名单', 5=>'扩展名白名单', 6=>'双扩展名绕过',
    7=>'大小写绕过', 8=>'空字节绕过', 9=>'.htaccess绕过',
    10=>'.phtml绕过', 11=>'Content-Type绕过', 12=>'图片马绕过',
    13=>'条件竞争', 14=>'路径穿越', 15=>'ZIP滑块',
];
render_header('文件上传', 'upload');
?>
<div class="card">
  <h2>文件上传 · 15 关</h2>
  <p>覆盖前端验证绕过、MIME 绕过、扩展名黑白名单绕过、图片马、条件竞争、路径穿越等文件上传漏洞。</p>
  <p style="color:#8b949e">目标：上传可执行文件（.php 等）到服务器并成功访问。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title):
        $done = is_passed('upload', $n);
    ?>
      <a href="stage<?= $n ?>.php" class="stage <?= $done?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title):
      $done = is_passed('upload', $n);
  ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= $done ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
