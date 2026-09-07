<?php
// 第 21 关：delete 注入
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$id = $_GET['id'] ?? '';
$rows = []; $err = ''; $passed = false; $affected = 0; $before = 0;
try {
    $pdo = db();
    // 练习副本表被清空时自动补种，方便反复练习
    $before = (int)$pdo->query("SELECT COUNT(*) FROM sqli_del")->fetchColumn();
    if ($before == 0) {
        $pdo->exec("INSERT INTO sqli_del(id,username,email) VALUES(1,'admin','admin@range.local'),(2,'guest','guest@range.local'),(3,'test','test@range.local')");
        $before = 3;
    }
    if ($id !== '') {
        if ($level === 'impossible') {
            $st = $pdo->prepare("DELETE FROM sqli_del WHERE id=?");
            $st->execute([$id]); $affected = $st->rowCount();
        } elseif ($level === 'high') {
            $id2 = intval($id);
            $affected = $pdo->exec("DELETE FROM sqli_del WHERE id=$id2");
        } else {
            if ($level === 'medium') $id = addslashes($id);
            $affected = $pdo->exec("DELETE FROM sqli_del WHERE id=$id");
        }
    }
    $rows = $pdo->query("SELECT id,username,email FROM sqli_del")->fetchAll();
    $passed = $affected > 1;
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 21, $id);

sqli_head(21, 'delete 注入', 'DELETE 语句的 WHERE 注入：一条语句删除全表数据。');
sqli_form('id', $id);
sqli_error($err);
if ($affected > 0) echo '<div class="banner info">本条 DELETE 删除了 ' . intval($affected) . ' 行（表中剩余 ' . count($rows) . ' 行，刷新可自动补种继续练习）。</div>';
sqli_pass($passed, '通关！OR 1=1 一条语句删掉了整张表。');
sqli_result($rows);
sqli_tail(
    ['hint' => '数字型 WHERE 注入，让条件恒真删掉所有行', 'full' => '1 OR 1=1'],
    [
        '原理' => 'DELETE 的 WHERE 拼接用户输入时，OR 1=1 让条件恒真，一条语句清空全表。这是危害最直接的注入形态——无需回显，数据直接消失。',
        '漏洞代码' => '<pre>$sql = "DELETE FROM sqli_del WHERE id=$id";</pre><span class="danger">↑ $id 直接拼入 WHERE</span>',
        '攻击演示' => "输入 1 OR 1=1<br>SQL 变为：WHERE id=1 OR 1=1 → 匹配全部 3 行 → 全表被删除 → 通关",
        'payload详解' => 'payload <code class="payload">1 OR 1=1</code>：OR 恒真使 WHERE 对每行都成立。生产中此类漏洞常配合无事务备份导致数据永久丢失。',
        '工具实操' => 'sqlmap -u "stage21.php?id=1" --batch --dbs<br>Burp Repeater 观察影响行数。',
        '通关检测' => 'DELETE 影响行数 &gt; 1 即通关（正常 id=1 仅删 1 行）。',
        '防御修复' => 'medium: addslashes（SQLite/UTF-8 下引号未转义，可绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。',
        '知识卡片' => '📌 delete 注入是"破坏型"注入的代表：不偷数据、只毁数据。<br>关联：insert（19）→ update（20）→ delete（本关）→ 搜索型注入（22）。',
    ],
    __FILE__
);
