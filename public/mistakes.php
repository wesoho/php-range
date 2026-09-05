<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$user = $_SESSION['user'];
global $MODULES;

render_header('错题本', null);
?>

<h2>📝 错题本</h2>

<?php
$has_unpassed = false;
foreach ($MODULES as $mkey => $minfo) {
    [$mname, $mdesc, $mcount] = $minfo;
    $unpassed = [];
    for ($i = 1; $i <= $mcount; $i++) {
        if (!is_passed($mkey, $i)) $unpassed[] = $i;
    }
    if (!$unpassed) continue;
    $has_unpassed = true;

    $fails = db()->prepare("SELECT level_no,payload,ts FROM attempts WHERE user=? AND module=? AND passed=0 ORDER BY ts DESC LIMIT 5");
    $fails->execute([$user, $mkey]);
    $fail_rows = $fails->fetchAll();
    ?>
    <div class="card">
      <h3><?= h($mname) ?> · 未通关 <?= count($unpassed) ?> 关</h3>
      <div class="stage-grid">
        <?php foreach ($unpassed as $n): ?>
          <a href="/modules/<?= $mkey ?>/stage<?= $n ?>.php" class="stage-badge todo"><?= $n ?></a>
        <?php endforeach; ?>
      </div>
      <?php if ($fail_rows): ?>
        <h4>最近失败记录：</h4>
        <table class="result">
          <tr><th>关卡</th><th>payload</th><th>时间</th><th>重试</th></tr>
          <?php foreach ($fail_rows as $r): ?>
            <tr>
              <td>第 <?= intval($r['level_no']) ?> 关</td>
              <td><code><?= h(mb_substr($r['payload']??'',0,60)) ?></code></td>
              <td><?= h($r['ts']) ?></td>
              <td><a href="/modules/<?= $mkey ?>/stage<?= intval($r['level_no']) ?>.php" class="btn-mini">重试</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
    <?php
}
if (!$has_unpassed) {
    echo '<div class="card"><p>🎉 所有关卡均已通关！</p></div>';
}
?>

<div class="card">
  <h3>💡 通关技巧</h3>
  <ul>
    <li><b>SQL 注入</b>：先判断闭合方式，再选 UNION/报错/盲注策略</li>
    <li><b>XSS</b>：分析输出上下文（HTML/属性/JS），选对应 payload</li>
    <li><b>文件上传</b>：先测无过滤，再逐步绕过黑名单/白名单/MIME</li>
    <li><b>命令注入</b>：试 ; | & $() ` 等分隔符，注意过滤绕过</li>
    <li><b>提示模式</b>：卡住时切换提示为"思路"或"完整"获得帮助</li>
  </ul>
</div>

<?php render_footer();
