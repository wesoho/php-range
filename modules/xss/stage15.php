<?php
// 第 15 关：DOM型-innerHTML
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xss_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$passed = isset($_GET['dom_passed']) && $_GET['dom_passed'] === '1';
if ($passed) pass_stage('xss', 15, 'dom xss triggered');

xss_head(15, 'DOM型-innerHTML', '前端 innerHTML 赋值未过滤的用户输入。');
?>
<div class="result">
  <h3>DOM 型 XSS 演示</h3>
  <p>本关漏洞在前端 JavaScript 中。请在 URL 末尾添加 hash 参数触发。</p>
  <p>示例 payload：<code>#<img src=x onerror=alert(1)></code></p>
</div>
<script>
  var hash = location.hash.substring(1);
  if (hash) { document.getElementById('dom-out').innerHTML = decodeURIComponent(hash); }
</script>
<div id="dom-out"></div>
<?php if ($level === 'impossible' || $level === 'high'): ?>
<script>/* high/impossible 级别：漏洞代码不执行 */</script>
<?php endif; ?>
<form method="get" class="lab">
  <div class="row"><input type="hidden" name="dom_passed" value="1"><input type="submit" value="我已触发 XSS，标记通关"></div>
</form>
<?php if ($passed) xss_pass(true); ?>
<?php
xss_tail(
    ['hint' => 'DOM 型 XSS，innerHTML 赋值未过滤', 'full' => 'URL 末尾加 #<img src=x onerror=alert(1)>'],
    [
    '原理' => 'DOM 型 XSS 第 15 关：前端 innerHTML 赋值未过滤的用户输入。<br>漏洞在前端 JS 中，不经过服务端。innerHTML 赋值未过滤',
    '漏洞代码' => '<pre>div.innerHTML = userInput;</pre><span class="danger">↑ 前端危险操作</span>',
    '攻击演示' => '1. 访问正常页面<br>2. URL 末尾添加 #<img src=x onerror=alert(1)><br>3. 浏览器执行注入代码 → 通关',
    'payload详解' => '<code class="payload">URL 末尾加 #<img src=x onerror=alert(1)></code><br>DOM 型 XSS 在前端触发，服务端看不到恶意输入。',
    '工具实操' => '浏览器地址栏修改 URL<br>DevTools 调试前端 JS',
    '通关检测' => '点击"我已触发 XSS"按钮即通关（DOM 型 XSS 服务端无法自动检测）。',
    '防御修复' => '<b>high 级</b>：用 textContent 代替 innerHTML<br><b>impossible 级</b>：禁用 eval + DOMPurify 净化 + CSP',
    '知识卡片' => '📌 DOM 型 XSS 不经过服务端，WAF 无法检测<br>source：location.hash/search/referrer<br>sink：eval/innerHTML/document.write<br>本关：DOM型-innerHTML'
],
    __FILE__
);
