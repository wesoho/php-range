<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$STAGES = [
    1=>'整数型无闭合', 2=>'单引号闭合', 3=>'双引号闭合', 4=>'单引号+括号', 5=>'双引号+括号',
    6=>'报错-floor', 7=>'报错-updatexml', 8=>'报错-extractvalue',
    9=>'布尔盲注-整数', 10=>'布尔盲注-单引号', 11=>'布尔盲注-双引号', 12=>'布尔盲注-括号',
    13=>'时间盲注-单引号', 14=>'时间盲注-双引号',
    15=>'堆叠注入', 16=>'二次注入', 17=>'宽字节注入', 18=>'like 注入',
    19=>'insert 注入', 20=>'update 注入', 21=>'delete 注入', 22=>'搜索型注入',
    23=>'WAF-内联注释', 24=>'WAF-等价函数', 25=>'WAF-大小写/双写',
];
render_header('SQL 注入', 'sqli');
?>
<div class="card">
  <h2>SQL 注入 · 25 关</h2>
  <p>对标 sqli-labs，覆盖全部闭合方式、报错注入、布尔/时间盲注、堆叠、二次、宽字节、insert/update/delete、搜索型、WAF 绕过。</p>
  <p style="color:#8b949e">每关四级难度（low/medium/high/impossible）+ 八段式教程 + 真实 CVE 引用。建议从第 1 关开始。</p>
</div>
<div class="stage-nav">
  <h3>关卡列表（绿=已通关）</h3>
  <div class="stages">
    <?php foreach ($STAGES as $n => $title):
        $done = is_passed('sqli', $n);
        $cls = $done ? 'done' : '';
    ?>
      <a href="stage<?= $n ?>.php" class="stage <?= $cls ?>" title="<?= h($title) ?>"><?= $n ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="grid">
  <?php foreach ($STAGES as $n => $title):
      $done = is_passed('sqli', $n);
  ?>
    <a class="mod-card" href="stage<?= $n ?>.php">
      <h3>第 <?= $n ?> 关</h3>
      <div class="desc"><?= h($title) ?></div>
      <div class="meta"><?= $done ? '✅ 已通关' : '⬚ 未通关' ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php render_footer();
