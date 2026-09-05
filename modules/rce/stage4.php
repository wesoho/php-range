<?php
// 第 4 关：create_function
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全'; }
    else { $func = @create_function('', $code); if ($func) { ob_start(); $func(); $output = ob_get_clean(); $passed = true; } }
}
if ($passed) pass_stage('rce',4,'code='.$code);
rce_head(4, 'create_function', 'create_function执行代码');
?>
<form method="get" class="lab"><div class="row"><label>代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => 'create_function创建匿名函数', 'full' => 'code=echo 123;'], [
    '原理' => 'RCE第4关：创建匿名函数',
    '漏洞代码' => '<pre>create_function("",$code);</pre>',
    '攻击演示' => 'payload: code=echo 123;',
    'payload详解' => 'code=echo 123;<br>create_function',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用create_function+用闭包',
    '知识卡片' => 'RCE·create_function'
], __FILE__);
