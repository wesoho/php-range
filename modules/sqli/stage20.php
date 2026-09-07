<?php
// 第 20 关：update 注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$id = $_GET['id'] ?? '';
$rows = []; $err = ''; $passed = false; $affected = 0;
try {
    $pdo = db();
    // 练习副本表被清空时自动补种，方便反复练习
    if ($pdo->query("SELECT COUNT(*) FROM sqli_upd")->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO sqli_upd(id,username,email) VALUES(1,'admin','admin@range.local'),(2,'guest','guest@range.local'),(3,'test','test@range.local')");
    }
    if ($id !== '') {
        if ($level === 'impossible') {
            $st = $pdo->prepare("UPDATE sqli_upd SET email='hacked@range.local' WHERE id=?");
            $st->execute([$id]); $affected = $st->rowCount();
        } elseif ($level === 'high') {
            $id2 = intval($id);
            $affected = $pdo->exec("UPDATE sqli_upd SET email='hacked@range.local' WHERE id=$id2");
        } else {
            if ($level === 'medium') $id = addslashes($id);
            $affected = $pdo->exec("UPDATE sqli_upd SET email='hacked@range.local' WHERE id=$id");
        }
    }
    $rows = $pdo->query("SELECT id,username,email FROM sqli_upd")->fetchAll();
    $passed = $affected > 1;
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 20, $id);

sqli_head(20, 'update 注入', 'UPDATE 语句的 WHERE 注入：让更新影响预期之外的行数。');
sqli_form('id', $id);
sqli_error($err);
if ($affected > 0) echo '<div class="banner info">本条 UPDATE 影响了 ' . intval($affected) . ' 行（email 已被改写为 hacked@range.local）。</div>';
sqli_pass($passed, '通关！一条 UPDATE 篡改了全部用户数据。');
sqli_result($rows);
sqli_tail(
    ['hint' => '数字型 WHERE 注入，让条件恒真影响所有行', 'full' => '1 OR 1=1'],
    [
        '原理' => 'UPDATE 的 WHERE 拼接用户输入时，OR 1=1 让条件恒真，一条语句篡改全表。SET 值处拼接还可注入子查询窃取其他表数据。',
        '漏洞代码' => '<pre>$sql = "UPDATE sqli_upd SET email=\'hacked@range.local\' WHERE id=$id";</pre><span class="danger">↑ $id 直接拼入 WHERE</span>',
        '攻击演示' => "输入 1 OR 1=1<br>SQL 变为：WHERE id=1 OR 1=1 → 匹配全部 3 行 → 全表 email 被篡改 → 通关",
        'payload详解' => "payload <code class=\"payload\">1 OR 1=1</code>：OR 恒真扩大 WHERE 匹配范围。<br>SET 值注入（进阶）：payload <code class=\"payload\">1, email=(SELECT password FROM sqli_users WHERE id=1)--</code> 可把其他表数据写进当前列（堆叠/子查询场景）。",
        '工具实操' => 'sqlmap -u "stage20.php?id=1" --batch --dbs<br>Burp Repeater 观察影响行数变化。',
        '通关检测' => 'UPDATE 影响行数 &gt; 1 即通关（正常 id=1 仅影响 1 行）。',
        '防御修复' => 'medium: addslashes（SQLite/UTF-8 下引号未转义，可绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 update 注入危害=数据被批量篡改，比 SELECT 注入更具破坏性。<br>关联：insert 注入（19）→ update（本关）→ delete 注入（21）。',
    ],
    __FILE__
);
