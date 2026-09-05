<?php
// 第 1 关：eval注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全：禁止eval'; }
    else { ob_start(); $ret = @eval($code); $output = ob_get_clean(); if ($ret !== false || strlen($output) > 0) $passed = true; }
}
if ($passed) pass_stage('rce',1,'code='.$code);
rce_head(1, 'eval注入', 'eval直接执行用户输入');
?>
<form method="get" class="lab"><div class="row"><label>PHP代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => '输入PHP代码', 'full' => 'code=echo 123;'], [
    '原理' => 'RCE第1关：eval用户输入',
    '漏洞代码' => '<pre>eval($code);</pre>',
    '攻击演示' => 'payload: code=echo 123;',
    'payload详解' => 'code=echo 123;<br>输入代码',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用eval+不执行用户代码',
    '知识卡片' => 'RCE·eval注入'
], __FILE__);
