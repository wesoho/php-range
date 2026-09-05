<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$STAGES = [
    1=>'LDAP注入',
    2=>'LDAP盲注',
    3=>'XPath注入',
    4=>'XPath盲注',
];
render_header('LDAP/XPath注入', 'ldap');
?>
<div class="card"><h2>LDAP/XPath注入 · 4 关</h2><p>LDAP/XPath注入漏洞</p></div>
<div class="stage-nav"><h3>关卡列表</h3><div class="stages">
<?php foreach ($STAGES as $n => $title): ?>
  <a href="stage<?= $n ?>.php" class="stage <?= is_passed('ldap',$n)?'done':'' ?>" title="<?= h($title) ?>"><?= $n ?></a>
<?php endforeach; ?>
</div></div>
<div class="grid">
<?php foreach ($STAGES as $n => $title): ?>
  <a class="mod-card" href="stage<?= $n ?>.php"><h3>第 <?= $n ?> 关</h3><div class="desc"><?= h($title) ?></div><div class="meta"><?= is_passed('ldap',$n)?'✅ 已通关':'⬚ 未通关' ?></div></a>
<?php endforeach; ?>
</div>
<?php render_footer();
