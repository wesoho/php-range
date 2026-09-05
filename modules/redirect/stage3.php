<?php
// 第 3 关：相对路径绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/redirect_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }
$level = get_level(); $url = $_GET['url'] ?? ''; $passed = false;
if ($url) {
    if ($level === 'impossible') { if (strpos($url,'://') !== false || strpos($url,'//') !== false) echo '拒绝'; else { header('Location: '.$url); exit; } }
    else { if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) { echo '拒绝：不允许绝对URL'; } else { header('Location: '.$url); $passed = true; } }
}
if ($passed) pass_stage('redirect',3,'url='.$url);
redirect_head(3, '相对路径绕过', '检查http://但可用//evil绕过');
?>
<form method="get" class="lab"><div class="row"><label>跳转URL</label><input type="text" name="url" value="<?= h($url) ?>" style="width:340px"></div><div class="row"><input type="submit" value="跳转"></div></form>
<?php if ($passed) redirect_pass(true); ?>
<?php
redirect_tail(['hint' => '用//evil.com绕过', 'full' => 'url=//evil.com'], [
    '原理' => '重定向第3关：检查http://但//绕过',
    '漏洞代码' => '<pre>检查http://前缀</pre>',
    '攻击演示' => 'payload: url=//evil.com',
    'payload详解' => 'url=//evil.com<br>//绕过',
    '工具实操' => 'Burp调试',
    '通关检测' => '触发效果即通关',
    '防御修复' => '检查//和协议+白名单',
    '知识卡片' => '重定向·相对路径绕过'
], __FILE__);
