<?php
// 第 4 关：弱随机数
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crypto_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
if ($level === 'impossible') { $token = bin2hex(random_bytes(16)); $output = '安全随机: '.$token; }
else { $token = rand(1000, 9999); $output = '弱随机(rand): '.$token.'
（rand()可预测，不安全）'; $passed = true; }
if ($passed) pass_stage('crypto',4,'weak random');
crypto_head(4, '弱随机数', '使用rand()而非random_bytes()');
?>
<form method="get" class="lab"><div class="row"><input type="submit" value="生成随机数"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) crypto_pass(true); ?>
<?php
crypto_tail(['hint' => 'rand()可预测', 'full' => '观察随机数规律'], [
    '原理' => '加密第4关：rand()可预测',
    '漏洞代码' => '<pre>rand(1000,9999)</pre>',
    '攻击演示' => 'payload: 观察规律',
    'payload详解' => '观察规律<br>预测随机',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '使用random_bytes/random_int',
    '知识卡片' => '加密·弱随机数'
], __FILE__);
