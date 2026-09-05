<?php
// 第 7 关：条件竞争
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $output = ''; $passed = false; $pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($level === 'impossible') { $pdo->beginTransaction(); $cnt = $pdo->query("SELECT COUNT(*) FROM attempts WHERE user=".db()->quote($_SESSION['user'])." AND module='logic' AND level_no=7")->fetchColumn(); if ($cnt > 0) { $pdo->rollBack(); $output = '已领取过'; } else { $pdo->exec("INSERT INTO attempts(user,module,level_no,passed,payload,ts) VALUES(".db()->quote($_SESSION['user']).",'logic',7,1,'race',datetime('now'))"); $pdo->commit(); $output = '领取成功（安全：事务保证）'; } }
    else { $cnt = $pdo->query("SELECT COUNT(*) FROM attempts WHERE user=".db()->quote($_SESSION['user'])." AND module='logic' AND level_no=7 AND payload='race'")->fetchColumn(); $output = '第'.($cnt+1).'次领取'; if ($cnt >= 1) $passed = true; $pdo->exec("INSERT INTO attempts(user,module,level_no,passed,payload,ts) VALUES(".db()->quote($_SESSION['user']).",'logic',7,0,'race',datetime('now'))"); }
}
if ($passed) pass_stage('logic',7,'race');

logic_head(7, '条件竞争', '并发请求导致重复操作');
?>
<form method="post" class="lab"><div class="row"><input type="submit" value="领取奖励（多次点击）"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '并发多次请求', 'full' => '快速多次点击提交'], [
    '原理' => '逻辑漏洞第7关：并发请求利用时间窗口',
    '漏洞代码' => '<pre>无原子性保证</pre>',
    '攻击演示' => 'payload: 多次快速提交',
    'payload详解' => '多次快速提交<br>并发请求',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '数据库事务+锁+幂等设计',
    '知识卡片' => '逻辑漏洞·条件竞争'
], __FILE__);
