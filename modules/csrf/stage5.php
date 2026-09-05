<?php
// 第 5 关：CSRF正确防护
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/csrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $passed = false; $msg = '';
$token = hash_hmac('sha256','csrf_stage5',session_id());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpwd = $_POST['newpwd'] ?? ''; $user_token = $_POST['token'] ?? '';
    if (hash_equals($token, $user_token)) { $msg = '密码已修改（Token验证通过）'; $passed = true; }
    else { $msg = 'CSRF Token验证失败'; }
}
if ($passed) pass_stage('csrf',5,'csrf');

csrf_head(5, 'CSRF正确防护', 'Impossible级：Token+SameSite+验证');
?>
<form method="post" class="lab"><input type="hidden" name="token" value="<?= $token ?>"><div class="row"><label>新密码</label><input type="text" name="newpwd" style="width:300px"></div><div class="row"><input type="submit" value="修改密码"></div></form>
<?php if ($passed) csrf_pass(true); ?>
<?php
csrf_tail(['hint' => '使用正确Token提交', 'full' => '表单已包含正确Token'], [
    '原理' => 'CSRF第5关：Token+SameSite',
    '漏洞代码' => '<pre>正确实现CSRF防护</pre>',
    '攻击演示' => 'payload: 表单已含Token',
    'payload详解' => '表单已含Token<br>提示：使用正确Token',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '此关为安全标杆',
    '知识卡片' => 'CSRF·正确防护'
], __FILE__);
