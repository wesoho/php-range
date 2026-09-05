<?php
// 第 2 关：会话预测
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/session_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $passed = false; $output = '';
$sid = session_id(); $output = '当前SID: '.$sid;
if (strlen($sid) < 32 || preg_match('/^[0-9]{8}/', $sid)) { $output .= ' (SID可预测！)'; $passed = true; }
if (isset($_GET['check'])) $passed = true;
if ($passed) pass_stage('session',2,'session predictable');
session_head(2, '会话预测', '可预测的sessionID');
?>
<form method="get" class="lab"><div class="row"><input type="submit" name="check" value="检查SID"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) session_pass(true); ?>
<?php
session_tail(['hint' => '分析SID规律预测', 'full' => '观察SID模式'], [
    '原理' => '会话第2关：SID可预测',
    '漏洞代码' => '<pre>使用弱随机生成SID</pre>',
    '攻击演示' => 'payload: 观察规律',
    'payload详解' => '观察规律<br>预测SID',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '使用random_bytes生成SID',
    '知识卡片' => '会话·会话预测'
], __FILE__);
