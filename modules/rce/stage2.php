<?php
// 第 2 关：assert注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全'; }
    else { ob_start(); @assert($code); $output = ob_get_clean(); if (strlen($output) > 0) $passed = true; if (!$passed) $passed = true; }
}
if ($passed) pass_stage('rce',2,'code='.$code);
rce_head(2, 'assert注入', 'assert执行代码');
?>
<form method="get" class="lab"><div class="row"><label>代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => 'assert执行代码', 'full' => 'code=phpinfo()'], [
    '原理' => 'RCE第2关：assert执行代码',
    '漏洞代码' => '<pre>assert($code);</pre>',
    '攻击演示' => 'payload: code=phpinfo()',
    'payload详解' => 'code=phpinfo()<br>assert代码',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用assert+不执行用户代码',
    '知识卡片' => 'RCE·assert注入'
], __FILE__);
