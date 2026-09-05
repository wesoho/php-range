<?php
// 第 2 关：垂直越权
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $action = $_GET['action'] ?? 'view'; $output = ''; $passed = false;
$admin_actions = ['delete','create','update','admin'];
if ($level === 'impossible') { if (($_SESSION['role'] ?? 'user') !== 'admin') { $output = '拒绝：需要管理员权限'; } else { $output = '执行：'.$action; } }
else { if (in_array($action, $admin_actions)) { $output = '执行管理操作：'.$action; $passed = true; } else { $output = '普通操作：'.$action; } }
if ($passed) pass_stage('logic',2,'action='.$action);

logic_head(2, '垂直越权', '普通用户访问admin接口');
?>
<form method="get" class="lab"><div class="row"><label>操作</label><input type="text" name="action" value="<?= h($action) ?>" style="width:300px"></div><div class="row"><input type="submit" value="执行"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '普通用户执行admin操作', 'full' => 'action=delete'], [
    '原理' => '逻辑漏洞第2关：低权限访问高权限接口',
    '漏洞代码' => '<pre>未检查用户角色</pre>',
    '攻击演示' => 'payload: action=delete',
    'payload详解' => 'action=delete<br>执行admin操作',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '检查用户角色+权限矩阵',
    '知识卡片' => '逻辑漏洞·垂直越权'
], __FILE__);
