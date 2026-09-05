<?php
// 第 3 关：参数实体绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xxe_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $xml = $_POST['xml'] ?? ''; $output = ''; $passed = false;
if ($xml) {
    if ($level === 'impossible') { $output = '安全'; }
    else { $doc = new DOMDocument(); try { $doc->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD); $output = $doc->textContent; if (stripos($output,'root:') !== false || strlen($output) > 5) $passed = true; } catch (Exception $e) { $output = '错误：'.$e->getMessage(); } }
}
if ($passed) pass_stage('xxe',3,'xxe');

xxe_head(3, '参数实体绕过', '用%参数实体绕过普通实体过滤');
?>
<form method="post" class="lab"><div class="row"><label>XML</label><textarea name="xml" rows="5" style="width:340px"><?= h($xml) ?></textarea></div><div class="row"><input type="submit" value="解析"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) xxe_pass(true); ?>
<?php
xxe_tail(['hint' => '用参数实体绕过过滤', 'full' => '<!DOCTYPE x[<!ENTITY % e SYSTEM "file:///etc/passwd"><!ENTITY x "%e;">]><x>&x;</x>'], [
    '原理' => 'XXE第3关：用%实体绕过过滤',
    '漏洞代码' => '<pre>参数实体可绕过普通实体过滤</pre>',
    '攻击演示' => 'payload: <!ENTITY % e SYSTEM ...>',
    'payload详解' => '<!ENTITY % e SYSTEM ...><br>提示：参数实体绕过',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '禁用DTD+外部实体',
    '知识卡片' => 'XXE·参数实体'
], __FILE__);
