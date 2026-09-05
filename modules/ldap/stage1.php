<?php
// 第 1 关：LDAP注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/ldap_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $input = $_GET['input'] ?? ''; $output = ''; $passed = false;
if ($input) {
    if ($level === 'impossible') { $input = preg_replace('/[^a-zA-Z0-9]/','',$input); $output = '安全过滤: '.$input; }
    else { $filter = "(uid=$input)"; $output = 'LDAP查询: '.$filter; if (strpos($input,'*)') !== false || strpos($input,'=*)') !== false) $passed = true; }
}
if ($passed) pass_stage('ldap',1,'input='.$input);
ldap_head(1, 'LDAP注入', '搜索过滤器注入绕过认证');
?>
<form method="get" class="lab"><div class="row"><label>输入</label><input type="text" name="input" value="<?= h($input ?? '') ?>" style="width:340px"></div><div class="row"><input type="submit" value="提交"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) ldap_pass(true); ?>
<?php
ldap_tail(['hint' => '注入*绕过认证', 'full' => 'input=*)(&'], [
    '原理' => 'LDAP第1关：过滤器注入',
    '漏洞代码' => '<pre>"(uid=$input)"</pre>',
    '攻击演示' => 'payload: input=*)(&',
    'payload详解' => 'input=*)(&<br>注入*',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '转义特殊字符+参数化查询',
    '知识卡片' => 'LDAP·LDAP注入'
], __FILE__);
