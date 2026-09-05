<?php
// 第 2 关：硬编码密钥
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crypto_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$secret_key = 'my_secret_12345';
$output = '硬编码密钥: '.$secret_key.'
（密钥在源码中，可通过源码泄露/反编译获取）'; $passed = true;
if ($passed) pass_stage('crypto',2,'hardcoded key');
crypto_head(2, '硬编码密钥', '密钥写在源码中');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="查看密钥"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) crypto_pass(true); ?>
<?php
crypto_tail(['hint' => '源码中包含密钥', 'full' => '查看源码找密钥'], [
    '原理' => '加密第2关：密钥在源码',
    '漏洞代码' => '<pre>$key = "my_secret";</pre>',
    '攻击演示' => 'payload: 查看源码',
    'payload详解' => '查看源码<br>读源码',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '密钥放环境变量/KMS',
    '知识卡片' => '加密·硬编码密钥'
], __FILE__);
