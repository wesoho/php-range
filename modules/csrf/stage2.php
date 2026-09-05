<?php
// 第 2 关：Referer验证绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/csrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $passed = false; $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpwd = $_POST['newpwd'] ?? '';
    if ($level === 'impossible') { $token = $_POST['token'] ?? ''; $passed = hash_equals(hash_hmac('sha256','csrf',session_id()),$token); $msg = $passed?'成功':'失败'; }
    elseif ($level === 'high') { $ref = $_SERVER['HTTP_REFERER'] ?? ''; $passed = strpos($ref, $_SERVER['HTTP_HOST'] ?? '') !== false; $msg = $passed?'修改成功':'Referer验证失败'; }
    else { $ref = $_SERVER['HTTP_REFERER'] ?? ''; if (strpos($ref, $_SERVER['HTTP_HOST'] ?? '') !== false || empty($ref)) { $msg = '密码已修改'; $passed = true; } else { $msg = 'Referer验证失败'; } }
}
if ($passed) pass_stage('csrf',2,'csrf');

csrf_head(2, 'Referer验证绕过', '检查Referer但可伪造或留空');
?>
<form method="post" class="lab"><div class="row"><label>新密码</label><input type="text" name="newpwd" style="width:300px"></div><div class="row"><input type="submit" value="修改密码"></div></form>
<?php if ($passed) csrf_pass(true); ?>
<?php
csrf_tail(['hint' => '删除Referer头或伪造', 'full' => 'Burp删除Referer头'], [
    '原理' => 'CSRF第2关：检查Referer但可伪造',
    '漏洞代码' => '<pre>检查Referer但留空可绕过</pre>',
    '攻击演示' => 'payload: Burp删除Referer',
    'payload详解' => 'Burp删除Referer<br>提示：删除Referer',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => 'CSRF Token比Referer更可靠',
    '知识卡片' => 'CSRF·Referer验证'
], __FILE__);
