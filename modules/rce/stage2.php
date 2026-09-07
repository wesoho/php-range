<?php
// 第 2 关：assert注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全：assert 已不能执行字符串代码（PHP 8），应改用 eval 白名单校验'; }
    else {
        // assert() 在 PHP 8 中不再执行字符串代码，这里等价模拟其历史行为（PHP7 assert(_zend_eval_string)）
        ob_start();
        try { eval($code); $passed = true; }
        catch (Throwable $e) { echo 'PHP 报错：' . $e->getMessage(); }
        $output = ob_get_clean();
    }
}
if ($passed) pass_stage('rce',2,'code='.$code);
rce_head(2, 'assert注入', 'assert执行代码');
?>
<form method="get" class="lab"><div class="row"><label>代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => 'assert执行代码', 'full' => 'code=phpinfo();'], [
    '原理' => 'RCE第2关：assert执行用户输入。<b>注</b>：PHP 7.2 起弃用字符串断言，PHP 8 已完全移除该能力，本关用 eval 等价模拟旧行为供练习。',
    '漏洞代码' => '<pre>assert($code);</pre>',
    '攻击演示' => 'payload: code=phpinfo()',
    'payload详解' => 'code=phpinfo()<br>assert代码',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用assert+不执行用户代码',
    '知识卡片' => 'RCE·assert注入'
], __FILE__);
