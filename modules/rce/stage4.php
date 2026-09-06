<?php
// 第 4 关：create_function
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全：create_function 已在 PHP 8 移除，应改用匿名函数'; }
    else {
        // create_function() 在 PHP 8 已移除，这里等价模拟其历史行为：
        // 内部就是 eval('function __lambda_func(){' . $code . '}')
        ob_start();
        try {
            eval('function __lambda_func_r4(){' . $code . '}');
            __lambda_func_r4();
            $passed = true;
        } catch (Throwable $e) { echo 'PHP 报错：' . $e->getMessage(); }
        $output = ob_get_clean();
    }
}
if ($passed) pass_stage('rce',4,'code='.$code);
rce_head(4, 'create_function', 'create_function执行代码');
?>
<form method="get" class="lab"><div class="row"><label>代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => 'create_function创建匿名函数', 'full' => 'code=echo 123;'], [
    '原理' => 'RCE第4关：create_function 底层即 eval 拼接函数体，注入 } 可逃逸出字符串上下文。<b>注</b>：该函数已在 PHP 8 移除，本关等价模拟其行为。',
    '漏洞代码' => '<pre>create_function("",$code);</pre>',
    '攻击演示' => 'payload: code=echo 123;',
    'payload详解' => 'code=echo 123;<br>create_function',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用create_function+用闭包',
    '知识卡片' => 'RCE·create_function'
], __FILE__);
