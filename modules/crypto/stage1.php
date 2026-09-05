<?php
// 第 1 关：弱哈希
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crypto_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $passed = false; $output = '';
if ($input) {
    if ($level === 'impossible') { $hash = password_hash($input, PASSWORD_BCRYPT); $output = '安全哈希(bcrypt): '.$hash; }
    else { $hash = md5($input); $output = '弱哈希(md5): '.$hash; $passed = true; }
}
if ($passed) pass_stage('crypto',1,'weak hash');
crypto_head(1, '弱哈希', '使用md5/sha1存储密码');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) crypto_pass(true); ?>
<?php
crypto_tail(['hint' => 'md5可被彩虹表破解', 'full' => 'input=password123'], [
    '原理' => '加密第1关：md5存密码',
    '漏洞代码' => '<pre>md5($password)</pre>',
    '攻击演示' => 'payload: input=password123',
    'payload详解' => 'input=password123<br>破解md5',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '使用password_hash+bcrypt',
    '知识卡片' => '加密·弱哈希'
], __FILE__);
