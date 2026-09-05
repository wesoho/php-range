<?php
// 第 5 关：参数缺失
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $output = ''; $passed = false;
$role = $_POST['role'] ?? 'admin';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($level === 'impossible') { $role = $_POST['role'] ?? 'user'; if (!in_array($role, ['user','editor'])) $role = 'user'; }
    else { if (!isset($_POST['role'])) $role = 'admin'; }
    $output = '分配角色：'.$role; if ($role === 'admin') $passed = true;
}
if ($passed) pass_stage('logic',5,'role='.$role);

logic_head(5, '参数缺失', '删除参数绕过检查使用默认值');
?>
<form method="post" class="lab"><div class="row"><label>角色(可留空)</label><input type="text" name="role" style="width:200px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '不传role参数使用默认admin', 'full' => '不传role参数'], [
    '原理' => '逻辑漏洞第5关：删除参数用默认值',
    '漏洞代码' => '<pre>$role = $_POST["role"] ?? "admin";</pre>',
    '攻击演示' => 'payload: 删除role参数',
    'payload详解' => '删除role参数<br>不传参数',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '显式检查参数存在+默认值设为最低权限',
    '知识卡片' => '逻辑漏洞·参数缺失'
], __FILE__);
