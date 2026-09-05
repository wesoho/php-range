<?php
// 第 4 关：SameSite绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/csrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $passed = false; $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpwd = $_POST['newpwd'] ?? '';
    if ($level === 'impossible') { $token = $_POST['token'] ?? ''; $passed = hash_equals(hash_hmac('sha256','csrf',session_id()),$token); }
    else { $msg = '密码已修改（Cookie无SameSite，跨站自动携带）'; $passed = true; }
}
if ($passed) pass_stage('csrf',4,'csrf');

csrf_head(4, 'SameSite绕过', 'Cookie无SameSite属性可跨站发送');
?>
<form method="post" class="lab"><div class="row"><label>新密码</label><input type="text" name="newpwd" style="width:300px"></div><div class="row"><input type="submit" value="修改密码"></div></form>
<?php if ($passed) csrf_pass(true); ?>
<?php
csrf_tail(['hint' => 'Cookie无SameSite跨站携带', 'full' => '构造跨站表单自动携带Cookie'], [
    '原理' => 'CSRF第4关：Cookie无SameSite',
    '漏洞代码' => '<pre>Cookie无SameSite属性</pre>',
    '攻击演示' => 'payload: 构造跨站表单',
    'payload详解' => '构造跨站表单<br>提示：跨站携带Cookie',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '设置SameSite=Strict/Lax',
    '知识卡片' => 'CSRF·SameSite绕过'
], __FILE__);
