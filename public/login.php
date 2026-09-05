<?php
require_once dirname(__DIR__) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $st = db()->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $st->execute([$u, $p]);
    $row = $st->fetch();
    if ($row) {
        $_SESSION['user'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        header('Location: /index.php');
        exit;
    }
    $err = '用户名或密码错误';
}
render_header('登录');
?>
<div class="row justify-content-center mt-5">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-lg" style="border-color:#30363d;">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <div style="font-size:56px;margin-bottom:12px;">⚔️</div>
          <h2 class="fw-bold mb-1" style="color:#e6edf3;">登录靶场</h2>
          <p class="text-secondary small">PHP-Range 攻防靶场</p>
        </div>
        <?php if ($err): ?>
          <div class="alert alert-danger text-center py-2"><i class="bi bi-x-circle"></i> <?= h($err) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text" style="background:#0d1117;border-color:#30363d;color:#8b949e;"><i class="bi bi-person"></i></span>
              <input name="username" class="form-control" placeholder="用户名" required autofocus>
            </div>
          </div>
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text" style="background:#0d1117;border-color:#30363d;color:#8b949e;"><i class="bi bi-lock"></i></span>
              <input name="password" type="password" class="form-control" placeholder="密码" required>
            </div>
          </div>
          <button type="submit" class="btn btn-hack w-100 py-2 fw-bold">
            <i class="bi bi-box-arrow-in-right"></i> 登 录
          </button>
        </form>
        <div class="alert alert-secondary text-center mt-3 mb-0 py-2 small">
          默认账号：<code>admin</code> / <code>admin123</code>
        </div>
      </div>
    </div>
    <p class="text-center text-secondary small mt-3">
      <i class="bi bi-exclamation-triangle text-warning"></i> 仅供本地学习网络攻防，禁止公网部署
    </p>
  </div>
</div>
<?php render_footer();
