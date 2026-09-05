<?php
// 第 2 关：盲注XXE
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xxe_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level(); $xml = $_POST['xml'] ?? ''; $output = ''; $passed = false;
if ($xml) {
    if ($level === 'impossible') { $output = '安全'; }
    else { $doc = new DOMDocument(); libxml_use_internal_errors(true); $ret = @$doc->loadXML($xml, LIBXML_NOENT); if ($ret) { $output = $doc->textContent; } else { $errs = libxml_get_errors(); $output = '错误：'.implode('; ', array_map(fn($e)=>$e->message, $errs)); } if (stripos($output,'root:') !== false || stripos($output,'No such file') !== false || strlen($output) > 5) $passed = true; }
}
if ($passed) pass_stage('xxe',2,'xxe');

xxe_head(2, '盲注XXE', '无回显用错误信息判断');
?>
<form method="post" class="lab"><div class="row"><label>XML</label><textarea name="xml" rows="5" style="width:340px"><?= h($xml) ?></textarea></div><div class="row"><input type="submit" value="解析"></div></form>
<?php if ($output): ?><div class="result"><h3>结果：</h3><pre class="code"><?= h($output) ?></pre></div><?php endif; ?>
<?php if ($passed) xxe_pass(true); ?>
<?php
xxe_tail(['hint' => '用错误信息判断文件是否存在', 'full' => '<!DOCTYPE x[<!ENTITY e SYSTEM "file:///etc/passwd">]><x>&e;</x>'], [
    '原理' => 'XXE第2关：无回显用错误判断',
    '漏洞代码' => '<pre>通过错误信息判断文件</pre>',
    '攻击演示' => 'payload: 读不存在的文件看错误',
    'payload详解' => '读不存在的文件看错误<br>提示：错误信息判断',
    '工具实操' => 'Burp Suite调试',
    '通关检测' => '触发漏洞效果即通关',
    '防御修复' => '禁用外部实体+错误处理',
    '知识卡片' => 'XXE·盲注XXE'
], __FILE__);
