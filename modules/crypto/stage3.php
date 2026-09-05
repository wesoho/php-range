<?php
// 第 3 关：ECB模式
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crypto_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $passed = false; $output = '';
if ($input) {
    if ($level === 'impossible') { $iv = random_bytes(16); $output = '安全(CBC+随机IV)'; }
    else { $key = '16bytesecretkey'; $enc = openssl_encrypt($input, 'AES-128-ECB', $key); $output = 'ECB加密: '.$enc.'
（ECB无IV，相同明文→相同密文，可重放攻击）'; $passed = true; }
}
if ($passed) pass_stage('crypto',3,'ECB mode');
crypto_head(3, 'ECB模式', 'ECB模式无IV相同明文产生相同密文');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) crypto_pass(true); ?>
<?php
crypto_tail(['hint' => 'ECB模式可重放', 'full' => 'input=hello'], [
    '原理' => '加密第3关：无IV可重放',
    '漏洞代码' => '<pre>openssl_encrypt($data,"AES-128-ECB")</pre>',
    '攻击演示' => 'payload: input=hello',
    'payload详解' => 'input=hello<br>ECB弱点',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '使用CBC/GCM+随机IV',
    '知识卡片' => '加密·ECB模式'
], __FILE__);
