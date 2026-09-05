<?php
// 第 3 关：日志注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crlf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $msg = $_GET['msg'] ?? ''; $passed = false;
if ($msg) {
    if ($level === 'impossible') { $msg = str_replace(["\r","\n","%0d","%0a"], '', $msg); }
    else { if (strpos($msg, "\n") !== false || strpos($msg, "%0a") !== false) $passed = true; }
    $log = date('Y-m-d H:i:s') . ' ' . ($_SESSION['user'] ?? 'anon') . ': ' . $msg;
    @file_put_contents('/tmp/range.log', $log . "\n", FILE_APPEND);
}
if ($passed) pass_stage('crlf',3,'msg='.$msg);
crlf_head(3, '日志注入', '日志中注入CRLF伪造日志条目');
?>
<form method="get" class="lab"><div class="row"><label>日志消息</label><input type="text" name="msg" value="<?= h($msg) ?>" style="width:340px"></div><div class="row"><input type="submit" value="记录"></div></form>
<?php if ($passed) crlf_pass(true); ?>
<?php
crlf_tail(['hint' => '注入换行伪造日志', 'full' => 'msg=test%0a2024-01-01 admin: login success'], [
    '原理' => 'CRLF第3关：日志注入CRLF',
    '漏洞代码' => '<pre>file_put_contents($log);</pre>',
    '攻击演示' => 'payload: msg=x%0aFAKE LOG',
    'payload详解' => 'msg=x%0aFAKE LOG<br>伪造日志',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '过滤换行+日志转义',
    '知识卡片' => 'CRLF·日志注入'
], __FILE__);
