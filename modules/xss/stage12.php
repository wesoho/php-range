<?php
// 第 12 关：存储型-过滤script
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/xss_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$content = $_POST['content'] ?? '';
$passed = false;
$pdo = db();

if ($content !== '') {
    $to_store = $content;
    if ($level === 'impossible' || $level === 'high') {
        $to_store = h($content);
    } elseif ($level === 'medium') {
        $to_store = str_replace('<script>', '', $to_store);
    } else {
        $to_store = str_replace('<script>', '', $to_store);
    }
    $pdo->prepare("INSERT INTO messages(user,content,ts) VALUES(?,?,datetime('now'))")
        ->execute([$_SESSION['user'] ?? 'anon', $to_store]);
}

$msgs = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 10")->fetchAll();
foreach ($msgs as $m) {
    if (xss_check_pass($m['content'])) { $passed = true; break; }
}
if ($passed) pass_stage('xss', 12, 'content=' . $content);

xss_head(12, '存储型-过滤script', '留言板过滤 script，但可用其他标签绕过。');
?>
<form method="post" class="lab">
  <div class="row"><label>留言内容</label><input type="text" name="content" value="" style="width:340px"></div>
  <div class="row"><input type="submit" value="发表留言"></div>
</form>
<?php if ($passed) xss_pass(true); ?>
<div class="result">
  <h3>留言列表：</h3>
  <?php foreach ($msgs as $m): ?>
    <div class="msg-item">
      <span class="msg-user"><?= h($m['user']) ?></span>
      <span class="msg-content"><?= $m['content'] ?></span>
      <span class="msg-ts"><?= h($m['ts']) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php
xss_tail(
    ['hint' => '过滤了 script，用 img onerror 绕过', 'full' => '<img src=x onerror=alert(1)>'],
    [
    '原理' => '存储型 XSS 第 12 关：留言板过滤 script，但可用其他标签绕过。<br>用户提交的留言存入数据库，后续访问时输出到页面。攻击持久存在。',
    '漏洞代码' => '<pre>$content = $_POST["content"];
$pdo->prepare("INSERT INTO messages...")->execute([$content]);
echo $row["content"];</pre><span class="danger">↑ 存入和输出均未转义</span>',
    '攻击演示' => '1. 提交正常留言<br>2. 提交 XSS payload：<img src=x onerror=alert(1)><br>3. 刷新页面，payload 仍在 → 持久触发 → 通关',
    'payload详解' => '<code class="payload"><img src=x onerror=alert(1)></code><br>存储型 XSS 一次注入持久生效，影响所有访问者。',
    '工具实操' => 'Burp Suite POST 提交留言<br>刷新页面验证持久性',
    '通关检测' => '留言输出中检测到未转义的 XSS 向量即通关。',
    '防御修复' => '<b>high 级</b>：存入前 htmlspecialchars 转义<br><b>impossible 级</b>：输出转义 + CSP + HttpOnly Cookie',
    '知识卡片' => '📌 存储型 XSS 危害最大<br>Samy 蠕虫 2005 MySpace 24h 感染 100 万用户<br>本关演示：存储型-过滤script'
],
    __FILE__
);
