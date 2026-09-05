<?php
// 第 3 关：preg_replace /e
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/rce_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $code = $_GET['code'] ?? ''; $output = ''; $passed = false;
if ($code) {
    if ($level === 'impossible') { $output = '安全：禁用 /e 修饰符，改用 preg_replace_callback'; }
    else {
        // 模拟 preg_replace /e 修饰符行为（PHP7+ 已移除 /e，用 eval 等价实现）
        $output = @eval('return ' . $code . ';');
        if ($output !== false) $passed = true;
    }
}
if ($passed) pass_stage('rce',3,'code='.$code);
rce_head(3, 'preg_replace /e', 'preg_replace /e修饰符执行代码');
?>
<form method="get" class="lab"><div class="row"><label>代码</label><input type="text" name="code" value="<?= h($code) ?>" style="width:340px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>输出：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) rce_pass(true); ?>
<?php
rce_tail(['hint' => 'preg_replace /e修饰符', 'full' => 'code=phpinfo()'], [
    '原理' => 'RCE第3关：正则/e修饰符',
    '漏洞代码' => '<pre>preg_replace("/.*/e",$code,$text)</pre>',
    '攻击演示' => 'payload: code=phpinfo()',
    'payload详解' => 'code=phpinfo()<br>/e执行代码',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '禁用/e修饰符+不用preg_replace',
    '知识卡片' => 'RCE·preg_replace /e'
], __FILE__);
