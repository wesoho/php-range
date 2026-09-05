<?php
// 第 1 关：水平越权
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/logic_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $id = $_GET['id'] ?? '1'; $output = ''; $passed = false; $pdo = db();
if ($level === 'impossible') { $st = $pdo->prepare("SELECT id,username,email FROM sqli_users WHERE id=?"); $st->execute([intval($id)]); }
else { $st = $pdo->prepare("SELECT id,username,password,email FROM sqli_users WHERE id=?"); $st->execute([$id]); }
$row = $st->fetch(); if ($row) { $output = json_encode($row); if ($row['id'] != 1 && array_key_exists('password',$row)) $passed = true; }
if ($passed) pass_stage('logic',1,'id='.$id);

logic_head(1, '水平越权', '修改id参数查看他人数据');
?>
<form method="get" class="lab"><div class="row"><label>用户ID</label><input type="text" name="id" value="<?= h($id) ?>" style="width:300px"></div><div class="row"><input type="submit" value="查询"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) logic_pass(true); ?>
<?php
logic_tail(['hint' => '修改id=2查看他人数据', 'full' => 'id=2'], [
    '原理' => '逻辑漏洞第1关：修改id查看他人数据',
    '漏洞代码' => '<pre>SELECT * FROM users WHERE id=$id</pre>',
    '攻击演示' => 'payload: id=2',
    'payload详解' => 'id=2<br>改id',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '检查当前用户对资源的权限',
    '知识卡片' => '逻辑漏洞·水平越权'
], __FILE__);
