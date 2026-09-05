<?php
// 第 2 关：单引号闭合字符型注入 —— 对标 sqli-labs Less-2
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$id = $_GET['id'] ?? '1';
$rows = []; $err = ''; $passed = false;

try {
    $pdo = db();
    if ($level === 'impossible') {
        $st = $pdo->prepare("SELECT id,username,email FROM sqli_users WHERE id=?");
        $st->execute([$id]);
        $rows = $st->fetchAll();
    } elseif ($level === 'high') {
        $id = intval($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    }
    if (count($rows) > 1) $passed = true;
} catch (Exception $e) { $err = $e->getMessage(); }

if ($passed) pass_stage('sqli', 2, 'id=' . ($_GET['id'] ?? ''));

$STAGES = [
    1=>'整数型无闭合',2=>'单引号闭合',3=>'双引号闭合',4=>'单引号+括号',5=>'双引号+括号',
    6=>'报错-floor',7=>'报错-updatexml',8=>'报错-extractvalue',9=>'布尔盲注-整数',10=>'布尔盲注-单引号',
    11=>'布尔盲注-双引号',12=>'布尔盲注-括号',13=>'时间盲注-单引号',14=>'时间盲注-双引号',15=>'堆叠注入',
    16=>'二次注入',17=>'宽字节注入',18=>'like注入',19=>'insert注入',20=>'update注入',21=>'delete注入',
    22=>'搜索型注入',23=>'WAF-内联注释',24=>'WAF-等价函数',25=>'WAF-大小写/双写',
];
render_header('SQL注入 第2关', 'sqli');
render_stage_nav('sqli', $STAGES, 2);
?>
<div class="card">
  <h2>第 2 关 · 单引号闭合字符型注入</h2>
  <p>SQL：<code>SELECT id,username,email FROM sqli_users WHERE id='<?= h($id) ?>'</code>（参数被单引号包裹）。目标：闭合单引号并注入。</p>
  <form method="get" class="lab">
    <div class="row"><label>用户 ID</label><input type="text" name="id" value="<?= h($_GET['id'] ?? '1') ?>" style="width:320px"></div>
    <div class="row"><input type="submit" value="查询"></div>
  </form>
  <?php if ($err) echo '<div class="banner fail">⚠ SQL 错误：' . h($err) . '</div>'; ?>
  <?php if ($passed) echo '<div class="banner ok">🎉 通关！成功闭合单引号并注入。</div>'; ?>
  <?php if ($rows): ?>
    <div class="result"><table>
      <tr><th>ID</th><th>用户名</th><th>邮箱</th></tr>
      <?php foreach ($rows as $r): ?>
        <tr><td><?= h($r['id']) ?></td><td><?= h($r['username']) ?></td><td><?= h($r['email']) ?></td></tr>
      <?php endforeach; ?>
    </table></div>
  <?php endif; ?>
</div>
<?php
render_hint([
    'hint' => '参数在单引号内，需先用单引号闭合前半段，再注入，最后注释掉剩余部分。',
    'full'  => "id=1' OR '1'='1　或　id=-1' UNION SELECT id,username,email FROM sqli_users-- ",
]);
render_tutorial([
    '原理' => '字符型注入参数被引号包裹：<code>WHERE id=\'$id\'</code>。要注入需<b>闭合单引号</b>，使后续内容逃逸出字符串上下文成为 SQL 代码。',
    '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=\'$id\'";</pre><span class="danger">↑ 单引号包裹但仍直接拼接，可闭合</span>',
    '攻击演示' => "输入 id=1' OR '1'='1<br>SQL 变为：WHERE id='1' OR '1'='1'<br>• 第一个 ' 闭合原字符串<br>• OR '1'='1 恒真<br>• 末尾 ' 与 SQL 原有的闭合引号配对<br>返回所有行 → 通关",
    'payload详解' => "payload <code class=\"payload\">1' OR '1'='1</code> 拆解：<br>• 1 原值<br>• ' 闭合前单引号<br>• OR '1'='1 注入条件（注意引号配对）<br><br>UNION 法：<code class=\"payload\">-1' UNION SELECT id,username,email FROM sqli_users-- </code>（-- 注释掉末尾的 '）",
    '工具实操' => "sqlmap 自动识别闭合：<br><code>sqlmap -u \"stage2.php?id=1\" --batch --dbs</code><br>sqlmap 会自动测试引号闭合方式。",
    '通关检测' => '返回行数 &gt; 1 即通关。',
    '防御修复' => "<b>medium</b>：addslashes 转义引号（但可被宽字节绕过，见第17关）。<br><b>high</b>：intval 强转。<br><b>impossible</b>：PDO 预处理参数化，引号由数据库处理，注入无效。",
    '知识卡片' => "📌 字符型注入核心：闭合引号 + 注入 + 注释/配对剩余引号。<br>三种闭合策略：① OR '1'='1（配对）② -- 注释 ③ # 注释。<br>关联：第1关(整数型)→本关(字符型)→第17关(宽字节绕过addslashes)。",
]);
render_source(__FILE__);
render_footer();
