<?php
// 第 1 关：会话固定
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/session_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$old_sid = session_id();
if (isset($_GET['login'])) {
    if ($level === 'impossible') { session_regenerate_id(true); $passed = true; }
    else { $_SESSION['user'] = 'test'; $passed = true; }
    $new_sid = session_id(); $output = '旧SID: '.$old_sid.' 新SID: '.$new_sid; if ($old_sid === $new_sid) $output .= ' (未变！会话固定漏洞)';
}
if ($passed) pass_stage('session',1,'session fixation');
session_head(1, '会话固定', '不重新生成sessionID');
?>
<form method="get" class="lab"><div class="row"><input type="submit" name="login" value="登录"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) session_pass(true); ?>
<?php
session_tail(['hint' => '登录后sessionID未变', 'full' => '传入固定PHPSESSID后登录'], [
    '原理' => '会话第1关：不重新生成SID',
    '漏洞代码' => '<pre>不调用session_regenerate_id</pre>',
    '攻击演示' => 'payload: 传入固定SID',
    'payload详解' => '传入固定SID<br>固定SID',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '登录后session_regenerate_id(true)',
    '知识卡片' => '会话·会话固定'
], __FILE__);
