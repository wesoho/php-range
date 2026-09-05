<?php
// 第 4 关：验证码绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $captcha = $_POST['captcha'] ?? ''; $output = ''; $passed = false;
$expected_captcha = '1234';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($level === 'impossible') { $passed = hash_equals($expected_captcha, $captcha) && !isset($_SESSION['captcha_used']); if ($passed) $_SESSION['captcha_used'] = true; }
    else { $passed = ($captcha === $expected_captcha || empty($captcha) || !isset($_POST['captcha'])); }
    $output = $passed ? '验证通过' : '验证码错误';
}
if ($passed) pass_stage('logic',4,'captcha='.$captcha);

logic_head(4, '验证码绕过', '验证码可复用或可空');
?>
<form method="post" class="lab"><div class="row"><label>验证码(1234)</label><input type="text" name="captcha" style="width:200px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '验证码可留空绕过', 'full' => '不传captcha参数或留空'], [
    '原理' => '逻辑漏洞第4关：验证码可复用/可空',
    '漏洞代码' => '<pre>未检查验证码是否存在</pre>',
    '攻击演示' => 'payload: 不传captcha',
    'payload详解' => '不传captcha<br>留空绕过',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '强制验证+一次性使用',
    '知识卡片' => '逻辑漏洞·验证码绕过'
], __FILE__);
