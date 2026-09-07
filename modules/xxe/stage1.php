<?php
// 第 1 关：基础XXE
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xxe_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $xml = $_POST['xml'] ?? ''; $output = ''; $passed = false;
if ($xml) {
    if ($level === 'impossible') { $output = '安全：禁用外部实体'; }
    else { $doc = new DOMDocument(); try { $doc->loadXML($xml, LIBXML_NOENT); $output = $doc->textContent; if (stripos($output,'root:') !== false || stripos($output,'[fonts]') !== false || stripos($output,'for 16-bit') !== false) $passed = true; } catch (Exception $e) { $output = '解析错误'; } }
}
if ($passed) pass_stage('xxe',1,'xxe');

xxe_head(1, '基础XXE', 'XML解析器加载外部实体读文件');
?>
<form method="post" class="lab"><div class="row"><label>XML</label><textarea name="xml" rows="5" style="width:340px"><?= h($xml) ?></textarea></div><div class="row"><input type="submit" value="解析"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) xxe_pass(true); ?>
<?php
xxe_tail(['hint' => '注入外部实体读文件', 'full' => '<!DOCTYPE x[<!ENTITY e SYSTEM "file:///C:/Windows/win.ini">]><x>&e;</x>'], [
    '原理' => 'XXE第1关：外部实体读文件',
    '漏洞代码' => '<pre>loadXML($xml, LIBXML_NOENT)</pre>',
    '攻击演示' => 'payload: <!ENTITY e SYSTEM "file:///etc/passwd">',
    'payload详解' => '<!ENTITY e SYSTEM "file:///etc/passwd"><br>提示：注入实体',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '禁用外部实体加载',
    '知识卡片' => 'XXE·基础XXE'
], __FILE__);
