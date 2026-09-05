<?php
// 第 7 关：反射型-过滤on事件
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xss_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$input = $_GET['keyword'] ?? '';
$output = '';
$passed = false;

if ($input !== '') {
    if ($level === 'impossible') {
        $output = h($input);
    } elseif ($level === 'high') {
        $output = h($input);
    } elseif ($level === 'medium') {
        $input = preg_replace('/on(\w+)\s*=/i', '', $input);
        $output = $input;
    } else {
        $input = preg_replace('/on(\w+)\s*=/i', '', $input);
        $output = $input;
    }
    $passed = xss_check_pass($output);
}

if ($passed) pass_stage('xss', 7, 'keyword=' . $input);

xss_head(7, '反射型-过滤on事件', '过滤 on 开头的事件处理器，但可用其他方式绕过。');
?>
<form method="get" class="lab">
  <div class="row"><label>输入</label><input type="text" name="keyword" value="<?= h($input) ?>" style="width:340px"></div>
  <div class="row"><input type="submit" value="提交"></div>
</form>
<?php if ($passed) xss_pass(true); ?>
<div class="result">
  <h3>页面输出：</h3>
  <div class="xss-output"><?= $output ?></div>
</div>
<?php
xss_tail(
    ['hint' => '过滤了 on 事件，但可以用 script 标签绕过', 'full' => '<script>alert(1)</script>'],
    [
    '原理' => '反射型 XSS 第 7 关：过滤 on 开头的事件处理器，但可用其他方式绕过。<br>用户输入通过 URL 参数传入，服务端处理后直接输出到 HTML。',
    '漏洞代码' => '<pre>$input = $_GET["keyword"];
        $input = preg_replace(\'/on(\\w+)\\s*=/i\', \'\', $input);
echo $input;</pre><span class="danger">↑ 输出未转义</span>',
    '攻击演示' => '1. 正常输入 test<br>2. 注入 XSS payload → 弹窗 → 通关<br>3. payload：<script>alert(1)</script>',
    'payload详解' => '<code class="payload"><script>alert(1)</script></code><br>提示：过滤了 on 事件，但可以用 script 标签绕过',
    '工具实操' => 'Burp Suite Repeater 调试 payload<br>浏览器 F12 → Elements 查看注入结果',
    '通关检测' => '页面输出中检测到未转义的 XSS 向量即通关。',
    '防御修复' => '<b>high 级</b>：htmlspecialchars 转义<br><b>impossible 级</b>：CSP 头 + 输出转义 + HttpOnly Cookie',
    '知识卡片' => '📌 XSS 三类型：反射型/存储型/DOM型<br>OWASP A03:2021-Injection<br>本关演示：反射型-过滤on事件'
],
    __FILE__
);
