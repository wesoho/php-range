<?php
// 第 5 关：动态调用
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $func = $_GET['func'] ?? ''; $arg = $_GET['arg'] ?? ''; $output = ''; $passed = false;
if ($func) {
    if ($level === 'impossible') { $output = '安全'; }
    else { ob_start(); if (function_exists($func)) { $func($arg); } $output = ob_get_clean(); if (strlen($output) > 0) $passed = true; if (!$passed && function_exists($func)) $passed = true; }
}
if ($passed) pass_stage('rce',5,'func='.$func);
rce_head(5, '动态调用', '动态函数调用');
?>
<form method="get" class="lab"><div class="row"><label>函数名</label><input type="text" name="func" value="<?= h($func) ?>" style="width:200px"></div><div class="row"><label>参数</label><input type="text" name="arg" value="<?= h($arg) ?>" style="width:200px"></div><div class="row"><input type="submit" value="调用"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => '动态调用任意函数', 'full' => 'func=phpinfo'], [
    '原理' => 'RCE第5关：动态函数调用',
    '漏洞代码' => '<pre>$func($arg);</pre>',
    '攻击演示' => 'payload: func=phpinfo',
    'payload详解' => 'func=phpinfo<br>调用函数',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '白名单函数+禁止动态调用',
    '知识卡片' => 'RCE·动态调用'
], __FILE__);
