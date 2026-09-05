<?php
// 第 1 关：无防护CSRF
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/csrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $passed = false; $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpwd = $_POST['newpwd'] ?? '';
    if ($level === 'impossible') { $token = $_POST['token'] ?? ''; $exp = hash_hmac('sha256','csrf',session_id()); $passed = hash_equals($exp,$token); $msg = $passed ? '密码已修改' : 'Token验证失败'; }
    else { $msg = '密码已修改为：'.$newpwd; $passed = true; }
}
if ($passed) pass_stage('csrf',1,'csrf');

csrf_head(1, '无防护CSRF', '修改密码无任何CSRF防护');
$csrf_token = hash_hmac('sha256', 'csrf', session_id());
?>
<form method="post" class="lab"><div class="row"><label>新密码</label><input type="text" name="newpwd" style="width:300px"></div><?php if ($level === 'impossible'): ?><input type="hidden" name="token" value="<?= h($csrf_token) ?>"><?php endif; ?><div class="row"><input type="submit" value="修改密码"></div></form>
<?php if ($passed) csrf_pass(true); ?>
<?php
csrf_tail(['hint' => '构造跨站POST请求', 'full' => '<form action="http://靶场/stage1.php" method="POST"><input name="newpwd" value="hacked"></form>'], [
    '原理' => 'CSRF第1关：无CSRF Token',
    '漏洞代码' => '<pre>无任何CSRF防护</pre>',
    '攻击演示' => 'payload: POST改密码',
    'payload详解' => 'POST改密码<br>提示：构造跨站表单',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => 'CSRF Token+SameSite',
    '知识卡片' => 'CSRF·无防护'
], __FILE__);
