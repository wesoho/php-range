<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$user = $_SESSION['user'];
global $MODULES;

render_header('复盘仪表盘', null);

$total_stages = 0;
$total_passed = 0;
$mod_stats = [];

// 三条 GROUP BY 一次取回，避免每模块 3 次 COUNT（N+1）
$passed_map = $attempt_map = $fail_map = [];
try {
    $st = db()->prepare("SELECT module, COUNT(*) AS c FROM progress WHERE user=? AND passed=1 GROUP BY module");
    $st->execute([$user]);
    foreach ($st->fetchAll() as $r) $passed_map[$r['module']] = (int)$r['c'];
    $st = db()->prepare("SELECT module, COUNT(*) AS c FROM attempts WHERE user=? GROUP BY module");
    $st->execute([$user]);
    foreach ($st->fetchAll() as $r) $attempt_map[$r['module']] = (int)$r['c'];
    $st = db()->prepare("SELECT module, COUNT(*) AS c FROM attempts WHERE user=? AND passed=0 GROUP BY module");
    $st->execute([$user]);
    foreach ($st->fetchAll() as $r) $fail_map[$r['module']] = (int)$r['c'];
} catch (Exception $e) {}

foreach ($MODULES as $mkey => $minfo) {
    [$mname, $mdesc, $mcount] = $minfo;
    $passed = $passed_map[$mkey] ?? 0;
    $attempts = $attempt_map[$mkey] ?? 0;
    $fails = $fail_map[$mkey] ?? 0;
    $pct = $mcount > 0 ? round($passed / $mcount * 100) : 0;
    $total_stages += $mcount;
    $total_passed += $passed;
    $mod_stats[$mkey] = ['name'=>$mname, 'count'=>$mcount, 'passed'=>$passed, 'pct'=>$pct, 'attempts'=>$attempts, 'fails'=>$fails];
}

$overall_pct = $total_stages > 0 ? round($total_passed / $total_stages * 100) : 0;
?>

<h2>📊 复盘仪表盘</h2>

<div class="card">
  <h3>总体进度</h3>
  <div class="big-stat">
    <span class="stat-num"><?= $total_passed ?></span> / <span class="stat-den"><?= $total_stages ?></span>
    <span class="stat-pct"><?= $overall_pct ?>%</span>
  </div>
  <div class="progress-bar big"><div class="progress-fill" style="width:<?= $overall_pct ?>%"></div></div>
</div>

<div class="card">
  <h3>各模块进度</h3>
  <table class="result">
    <tr><th>模块</th><th>通关</th><th>总数</th><th>进度</th><th>尝试次数</th><th>失败次数</th><th>操作</th></tr>
    <?php foreach ($mod_stats as $mkey => $s): ?>
      <tr>
        <td><?= h($s['name']) ?></td>
        <td><?= $s['passed'] ?></td>
        <td><?= $s['count'] ?></td>
        <td>
          <div class="progress-bar mini"><div class="progress-fill" style="width:<?= $s['pct'] ?>%"></div></div>
          <?= $s['pct'] ?>%
        </td>
        <td><?= $s['attempts'] ?></td>
        <td><?= $s['fails'] ?></td>
        <td><a href="/modules/<?= $mkey ?>/index.php" class="btn-mini">进入</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php
$recent = db()->prepare("SELECT module,level_no,passed,payload,ts FROM attempts WHERE user=? ORDER BY ts DESC LIMIT 15");
$recent->execute([$user]);
$rows = $recent->fetchAll();
?>
<div class="card">
  <h3>最近活动</h3>
  <?php if ($rows): ?>
  <table class="result">
    <tr><th>时间</th><th>模块</th><th>关卡</th><th>结果</th><th>payload</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= h($r['ts']) ?></td>
        <td><?= h($r['module']) ?></td>
        <td>第 <?= intval($r['level_no']) ?> 关</td>
        <td><?= $r['passed'] ? '✅' : '❌' ?></td>
        <td><code><?= h(mb_substr($r['payload']??'',0,50)) ?></code></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
    <p>暂无活动记录，开始挑战吧！</p>
  <?php endif; ?>
</div>

<div class="card">
  <h3>📚 学习建议</h3>
  <?php
  if ($overall_pct == 0) {
      echo '<p>建议从 <a href="/modules/sqli/index.php">SQL 注入</a> 第 1 关开始，这是最基础的漏洞类型。</p>';
  } elseif ($overall_pct < 30) {
      echo '<p>已入门！继续完成 SQL 注入和 XSS 模块，打好基础。</p>';
  } elseif ($overall_pct < 60) {
      echo '<p>不错！尝试文件上传和命令注入，这些漏洞危害更大。</p>';
  } elseif ($overall_pct < 100) {
      echo '<p>很强！挑战剩余关卡，并尝试 <a href="/quiz.php">阶段测验</a> 巩固知识。</p>';
  } else {
      echo '<p>🎉 全部通关！你已掌握核心 Web 漏洞攻防技能。尝试 <a href="/quiz.php">阶段测验</a> 检验理论掌握度。</p>';
  }
  ?>
  <p>
    <a href="/progress.php" class="btn-mini">详细进度</a>
    <a href="/mistakes.php" class="btn-mini">错题本</a>
    <a href="/quiz.php" class="btn-mini">阶段测验</a>
    <a href="/cheatsheet.php" class="btn-mini">知识库</a>
  </p>
</div>

<?php render_footer();
