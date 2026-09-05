<?php
require_once dirname(__DIR__) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
render_header(APP_NAME);
$user = $_SESSION['user'] ?? null;
global $MODULES;

$total_stages = 0;
foreach ($MODULES as $m) $total_stages += $m[2];
$total_passed = 0;
if ($user) {
    foreach ($MODULES as $k => $m) {
        try { $r = db()->prepare("SELECT COUNT(*) FROM progress WHERE user=? AND module=? AND passed=1"); $r->execute([$user, $k]); $total_passed += (int)$r->fetchColumn(); } catch (Exception $e) {}
    }
}
$pct = $total_stages > 0 ? round($total_passed / $total_stages * 100) : 0;
?>

<?php if (!$user): ?>
<div class="text-center py-5">
  <div style="font-size:64px;margin-bottom:16px;">⚔️</div>
  <h1 class="fw-bold mb-3" style="color:#e6edf3;">PHP-Range 攻防靶场</h1>
  <p class="lead text-secondary mb-1">
    <?= $total_stages ?> 关 · <?= count($MODULES) ?> 个漏洞模块 · 四级难度 · 八段式教程
  </p>
  <p class="text-secondary mb-4"><i class="bi bi-exclamation-triangle text-warning"></i> 仅供本地学习网络攻防，禁止公网部署</p>
  <a href="/login.php" class="btn btn-hack btn-lg px-5 py-2 fw-bold">
    <i class="bi bi-box-arrow-in-right"></i> 开始学习
  </a>
  <p class="text-secondary mt-4">默认账号 <code>admin / admin123</code></p>
</div>
<?php else: ?>

<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h2 class="mb-1"><i class="bi bi-bar-chart-fill" style="color:#9fef00"></i> 学习概览</h2>
        <p class="text-secondary mb-0">欢迎回来，<?= h($user) ?>！继续你的攻防之旅。</p>
      </div>
      <div class="text-end">
        <div style="font-size:36px;font-weight:800;">
          <span style="color:#9fef00"><?= $total_passed ?></span><span class="text-secondary" style="font-size:20px"> / <?= $total_stages ?></span>
        </div>
        <div style="color:#3f75e8;font-size:18px;font-weight:600;"><?= $pct ?>%</div>
      </div>
    </div>
    <div class="progress" style="height:18px;background:#0d1117;">
      <div class="progress-bar" style="width:<?= $pct ?>%;background:#9fef00;"></div>
    </div>
    <div class="d-flex gap-2 mt-3 flex-wrap">
      <a href="/dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-speedometer2"></i> 仪表盘</a>
      <a href="/mistakes.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-journal-x"></i> 错题本</a>
      <a href="/quiz.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-patch-question"></i> 阶段测验</a>
      <a href="/cheatsheet.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-book"></i> 知识库</a>
      <a href="/progress.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-graph-up"></i> 详细进度</a>
    </div>
  </div>
</div>

<h2 class="mb-3"><i class="bi bi-shield-fill-check" style="color:#9fef00"></i> 漏洞模块</h2>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-4">
<?php foreach ($MODULES as $k => $m):
    $total = $m[2]; $done = 0;
    try { $r = db()->prepare("SELECT COUNT(*) FROM progress WHERE user=? AND module=? AND passed=1"); $r->execute([$user, $k]); $done = (int)$r->fetchColumn(); } catch (Exception $e) {}
    $mpct = $total > 0 ? round($done / $total * 100) : 0;
?>
  <div class="col">
    <a href="/modules/<?= $k ?>/index.php" class="mod-card">
      <h3><?= h($m[0]) ?></h3>
      <div class="desc"><?= h($m[1]) ?></div>
      <div class="progress mt-2" style="height:6px;background:#0d1117;">
        <div class="progress-bar" style="width:<?= $mpct ?>%;background:#9fef00;"></div>
      </div>
      <div class="meta"><?= $done ?>/<?= $total ?> 关 · <?= $mpct ?>%</div>
    </a>
  </div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<div class="card">
  <div class="card-body">
    <h2><i class="bi bi-signpost-2" style="color:#9fef00"></i> 推荐学习路径</h2>
    <div class="row g-3 mt-1">
      <div class="col-md-4">
        <div class="card h-100 border-success border-opacity-50">
          <div class="card-body">
            <h5 class="text-success"><i class="bi bi-flower1"></i> 入门</h5>
            <p class="text-secondary small mb-0">SQL注入(low) → XSS(反射型) → 命令注入(low) → 文件上传(无过滤)</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-primary border-opacity-50">
          <div class="card-body">
            <h5 class="text-primary"><i class="bi bi-graph-up-arrow"></i> 进阶</h5>
            <p class="text-secondary small mb-0">盲注 → 存储XSS → 上传绕过 → 文件包含 → CSRF → SSRF</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-danger border-opacity-50">
          <div class="card-body">
            <h5 class="text-danger"><i class="bi bi-fire"></i> 高级</h5>
            <p class="text-secondary small mb-0">XXE → 反序列化 → SSTI → 代码执行 → 越权+逻辑漏洞</p>
          </div>
        </div>
      </div>
    </div>
    <p class="text-secondary mt-3 small">
      <i class="bi bi-lightbulb text-warning"></i> 每关流程：先攻击 <b>low</b> → 尝试绕过 <b>medium</b> → 阅读 <b>high/impossible</b> 修复 → 看八段式教程巩固
    </p>
  </div>
</div>

<?php render_footer();
