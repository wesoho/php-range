<?php
// 第 3 关：Token可预测
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/csrf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $passed = false; $msg = '';
$expected_token = $level === 'high' ? hash_hmac('sha256','csrf',session_id()) : substr(md5(time()),0,8);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpwd = $_POST['newpwd'] ?? ''; $token = $_POST['token'] ?? '';
    if ($level === 'impossible') { $passed = hash_equals(hash_hmac('sha256','csrf',session_id()),$token); }
    else { $passed = ($token === $expected_token); $msg = $passed?'修改成功':'Token错误'; }
}
if ($passed) pass_stage('csrf',3,'csrf');

csrf_head(3, 'Token可预测', 'Token基于时间生成可预测');
?>
<form method="post" class="lab"><input type="hidden" name="token" value="<?= $expected_token ?>"><div class="row"><label>新密码</label><input type="text" name="newpwd" style="width:300px"></div><div class="row"><input type="submit" value="修改密码"></div></form>
<?php if ($passed) csrf_pass(true); ?>
<?php
csrf_tail(['hint' => 'Token 是 substr(md5(time()),0,8)，可预测', 'full' => 'token=substr(md5(time()),0,8)（提交时实时计算，跨秒重试）'], [
    '原理' => 'CSRF第3关：Token=md5(time())',
    '漏洞代码' => '<pre>Token基于时间可预测</pre>',
    '攻击演示' => 'payload: 分析规律伪造Token',
    'payload详解' => '分析规律伪造Token<br>提示：预测时间Token',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '随机Token+绑定会话',
    '知识卡片' => 'CSRF·Token可预测'
], __FILE__);
