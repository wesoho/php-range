<?php
// 第 3 关：密码重置逻辑
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $token = $_GET['token'] ?? ''; $output = ''; $passed = false;
$expected_token = $level === 'impossible' ? hash_hmac('sha256','reset',session_id()) : substr(md5(date('Y-m-d')),0,8);
if ($token) { if ($level === 'impossible') { $passed = hash_equals($expected_token, $token); } else { $passed = ($token === $expected_token || $token === 'admin' || strlen($token) > 0); } $output = $passed ? '密码已重置' : 'token错误'; }
if ($passed) pass_stage('logic',3,'token='.$token);

logic_head(3, '密码重置逻辑', '重置token可预测或可绕过');
?>
<form method="get" class="lab"><div class="row"><label>重置Token</label><input type="text" name="token" value="<?= h($token) ?>" style="width:300px"></div><div class="row"><input type="submit" value="重置密码"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => 'Token可预测或任意值绕过', 'full' => 'token=admin'], [
    '原理' => '逻辑漏洞第3关：token可预测',
    '漏洞代码' => '<pre>token=md5(date())</pre>',
    '攻击演示' => 'payload: token=admin',
    'payload详解' => 'token=admin<br>预测token',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '随机token+一次性使用',
    '知识卡片' => '逻辑漏洞·密码重置'
], __FILE__);
