<?php
// 第 1 关：HTTP响应拆分
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/crlf_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $val = $_GET['val'] ?? ''; $passed = false;
if ($val) {
    if ($level === 'impossible') { $val = str_replace(["\r","\n","%0d","%0a"], '', $val); header('Set-Cookie: test='.$val); $passed = false; }
    else { header('Set-Cookie: test='.$val); if (strpos($val, "\r") !== false || strpos($val, "\n") !== false || strpos($val, "%0d") !== false || strpos($val, "%0a") !== false) $passed = true; }
}
if ($passed) pass_stage('crlf',1,'val='.$val);
crlf_head(1, 'HTTP响应拆分', 'header注入CRLF添加响应头');
?>
<form method="get" class="lab"><div class="row"><label>Cookie值</label><input type="text" name="val" value="<?= h($val) ?>" style="width:340px"></div><div class="row"><input type="submit" value="设置"></div></form>
<?php if ($passed) crlf_pass(true); ?>
<?php
crlf_tail(['hint' => '注入CRLF添加响应头', 'full' => 'val=test%0d%0aX-Injected:evil'], [
    '原理' => 'CRLF第1关：header注入CRLF',
    '漏洞代码' => '<pre>header("Set-Cookie: ".$val);</pre>',
    '攻击演示' => 'payload: val=x%0d%0aX-Evil:1',
    'payload详解' => 'val=x%0d%0aX-Evil:1<br>注入CRLF',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '过滤\\r\\n+不拼接用户输入到header',
    '知识卡片' => 'CRLF·响应拆分'
], __FILE__);
