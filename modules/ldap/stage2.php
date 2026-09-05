<?php
// 第 2 关：LDAP盲注
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ldap_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $output = ''; $passed = false;
if ($input) {
    if ($level === 'impossible') { $output = '安全'; }
    else { $filter = "(uid=$input)"; $exists = strlen($input) > 3; $output = $exists ? '用户存在' : '用户不存在'; if (strlen($input) > 3) $passed = true; }
}
if ($passed) pass_stage('ldap',2,'input='.$input);
ldap_head(2, 'LDAP盲注', '无回显用响应判断');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ldap_pass(true); ?>
<?php
ldap_tail(['hint' => '用响应判断用户存在', 'full' => 'input=admin*)(uid=*'], [
    '原理' => 'LDAP第2关：无回显判断',
    '漏洞代码' => '<pre>通过响应差异判断</pre>',
    '攻击演示' => 'payload: input=admin*)',
    'payload详解' => 'input=admin*)<br>盲注判断',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '转义+参数化',
    '知识卡片' => 'LDAP·LDAP盲注'
], __FILE__);
