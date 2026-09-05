<?php
// 第 1 关：整数型 SQL 注入（无闭合）—— 对标 sqli-labs Less-1
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
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } elseif ($level === 'medium') {
        $id = str_replace("'", "", $id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    }
    if (count($rows) > 1) $passed = true;
} catch (Exception $e) { $err = $e->getMessage(); }

if ($passed) pass_stage('sqli', 1, 'id=' . ($_GET['id'] ?? ''));

$STAGES = [
    1=>'整数型无闭合',2=>'单引号闭合',3=>'双引号闭合',4=>'单引号+括号',5=>'双引号+括号',
    6=>'报错-floor',7=>'报错-updatexml',8=>'报错-extractvalue',
    9=>'布尔盲注-整数',10=>'布尔盲注-单引号',11=>'布尔盲注-双引号',12=>'布尔盲注-括号',
    13=>'时间盲注-单引号',14=>'时间盲注-双引号',15=>'堆叠注入',16=>'二次注入',17=>'宽字节注入',
    18=>'like注入',19=>'insert注入',20=>'update注入',21=>'delete注入',22=>'搜索型注入',
    23=>'WAF-内联注释',24=>'WAF-等价函数',25=>'WAF-大小写/双写',
];
render_header('SQL注入 第1关', 'sqli');
render_stage_nav('sqli', $STAGES, 1);
?>
<div class="card">
  <h2>第 1 关 · 整数型 SQL 注入（无闭合）</h2>
  <p>根据用户 ID 查询信息，参数 <code>id</code> 以整数形式直接拼入 SQL。目标：通过注入获取<b>所有用户</b>数据。</p>
  <form method="get" class="lab">
    <div class="row"><label>用户 ID</label><input type="text" name="id" value="<?= h($_GET['id'] ?? '1') ?>" style="width:300px"></div>
    <div class="row"><input type="submit" value="查询"></div>
  </form>
  <?php if ($err) echo '<div class="banner fail">⚠ SQL 错误：' . h($err) . '</div>'; ?>
  <?php if ($passed) echo '<div class="banner ok">🎉 通关！通过注入获取了多行数据。</div>'; ?>
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
    'hint' => 'id 是整数型，无引号闭合，可直接拼接 SQL 逻辑。想想如何让条件恒真。',
    'full'  => 'id=1 OR 1=1　或　id=-1 UNION SELECT id,username,email FROM sqli_users',
]);
render_tutorial([
    '原理' => '当参数为数字且<b>未加引号</b>直接拼入 SQL，攻击者可输入 SQL 关键字改变查询逻辑。<br>本关 SQL：<code>SELECT id,username,email FROM sqli_users WHERE id=$id</code><br>因 id 无引号，输入 <code>1 OR 1=1</code> 后变为 <code>WHERE id=1 OR 1=1</code>，条件恒真，返回所有行。',
    '漏洞代码' => '<pre>$id = $_GET["id"];\n$sql = "SELECT id,username,email FROM sqli_users WHERE id=$id";\n$rows = $pdo->query($sql)->fetchAll();</pre><span class="danger">↑ $id 直接拼接，无任何过滤</span>',
    '攻击演示' => '步骤：<br>1. 输入 id=1，正常返回 1 行<br>2. 输入 id=1 OR 1=1，返回所有 5 行 → 通关<br>3. 进阶：id=-1 UNION SELECT id,username,password,email FROM sqli_users 可查密码列',
    'payload详解' => '<code class="payload">id=1 OR 1=1</code><br>• <code>1</code> 原始值<br>• <code>OR</code> 逻辑或，拼接新条件<br>• <code>1=1</code> 恒真条件<br>组合后 WHERE id=1 OR 1=1 对所有行成立。<br><br>UNION 法：<code class="payload">id=-1 UNION SELECT 1,2,3</code>（-1 使原查询无结果，UNION 拼接自定义结果）',
    '工具实操' => 'sqlmap 自动化：<br><code>sqlmap -u "http://靶场/stage1.php?id=1" --batch --dbs</code><br>Burp Suite：抓包改 id 参数，Repeater 调试 payload。<br>hackbar：浏览器直接构造 URL。',
    '通关检测' => '本关判定：返回行数 &gt; 1 即通关（正常 id=1 仅 1 行）。已注入 OR 1=1 返回 5 行 → 通过。',
    '防御修复' => '<b>high 级</b>：用 <code>intval($id)</code> 强转为整数，非数字变 0。<br><b>impossible 级</b>：用 PDO 预处理参数化查询，id 作为绑定参数，彻底隔离代码与数据。<br><pre>$st = $pdo->prepare("...WHERE id=?");\n$st->execute([$id]);</pre>',
    '知识卡片' => '📌 整数型注入无需闭合引号，是最简单的注入类型。<br>关联：字符型注入（需闭合引号）→ 报错注入 → 盲注。<br>真实案例：2008 年 Heartland 支付系统 SQL 注入泄露 1.34 亿张卡号（CVE 类）。',
]);
render_source(__FILE__);
render_footer();
