<?php
require_once dirname(__DIR__) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        @unlink(DB_PATH);
        $pdo = db();
        $sql = file_get_contents(DB_INIT_SQL);
        $pdo->exec($sql);
        $msg = '数据库已初始化，所有数据已重置。';
    } catch (Exception $e) {
        $err = '初始化失败：' . $e->getMessage();
    }
}
render_header('环境设置');
?>
<div class="card">
  <h3>初始化 / 重置靶场环境</h3>
  <?php if ($msg) echo '<div class="banner ok">' . h($msg) . '</div>'; ?>
  <?php if ($err) echo '<div class="banner fail">' . h($err) . '</div>'; ?>
  <p style="margin:10px 0">这将删除并重建数据库，恢复所有初始数据。<b>通关进度也会被清空。</b></p>
  <form method="post"><input type="submit" value="确认重置数据库"></form>
  <p style="margin-top:14px;color:#8b949e">数据库文件：<code><?= h(DB_PATH) ?></code></p>
  <p style="color:#8b949e">已注册模块：
    <?php global $MODULES; foreach ($MODULES as $k=>$m) echo h($m[0]).'('.$m[2].'关) '; ?>
  </p>
</div>
<?php render_footer();
