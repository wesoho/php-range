<?php
// 第 6 关：整数溢出
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $amount = $_GET['amount'] ?? '100'; $output = ''; $passed = false;
$amt = intval($amount);
if ($level === 'impossible') { if ($amt > 10000 || $amt < 0) { $output = '拒绝：金额超限'; } else { $output = '转账：'.$amt; } }
else { $balance = 1000; if ($amt > $balance) { $output = '余额不足'; } else { $output = '转账'.$amt.'成功'; } if ($amt < 0 || $amt > PHP_INT_MAX - 1) $passed = true; }
if ($passed) pass_stage('logic',6,'amount='.$amount);

logic_head(6, '整数溢出', '数值溢出导致逻辑错误');
?>
<form method="get" class="lab"><div class="row"><label>转账金额</label><input type="text" name="amount" value="<?= h($amount) ?>" style="width:300px"></div><div class="row"><input type="submit" value="转账"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '用负数或大数溢出', 'full' => 'amount=-999999'], [
    '原理' => '逻辑漏洞第6关：数值溢出',
    '漏洞代码' => '<pre>intval溢出</pre>',
    '攻击演示' => 'payload: amount=-999999',
    'payload详解' => 'amount=-999999<br>负数绕过',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '范围检查+使用BC Math',
    '知识卡片' => '逻辑漏洞·整数溢出'
], __FILE__);
