<?php
// 第 23 关：WAF-内联注释
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/sqli_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$id = $_GET['id'] ?? '1';
$rows = []; $err = ''; $passed = false;
try {
    $pdo = db();
    if ($level === 'impossible') {
        $st = $pdo->prepare("SELECT id,username,email FROM sqli_users WHERE id=?");
        $st->execute([$id]); $rows = $st->fetchAll();
    } elseif ($level === 'high') {
        $id = intval($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    } else {
        // WAF: 过滤空格。SQLite 不支持 MySQL 的 /*!50000...*/ 版本注释，
        // 但 /**/ 注释本身可作为空白分隔符，等价实现"注释绕过 WAF"的练习
        $id = str_replace(' ', '', $id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id=$id")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 23, $id);

sqli_head(23, 'WAF-内联注释/空白绕过', 'WAF 过滤空格，用注释 /**/ 作为空白分隔符绕过。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => '数字与关键字之间也要用注释隔开，如 1/**/UNION/**/SELECT', 'full' => '1/**/UNION/**/SELECT/**/1,2,password/**/FROM/**/sqli_users'],
    [
        '原理' => 'WAF 常用"删空格"或"正则匹配整词"拦截 UNION SELECT。SQL 中注释等价于一个空白字符，UNION/**/SELECT 既是合法 SQL 又不含空格，可同时绕过两类过滤。<br><b>方言差异</b>：MySQL 独有的 /*!50000UNION*/ 版本内联注释在 SQLite 不支持，本关用 /**/ 空白注释达到同样的教学目的；打 MySQL 时两种都可用。',
        '漏洞代码' => '<pre>$id = str_replace(" ", "", $id);<br>$sql = "SELECT ... WHERE id=$id";</pre><span class="danger">↑ 只过滤空格，未考虑注释即空白</span>',
        '攻击演示' => '过滤空格后 UNION/**/SELECT 拼接仍为合法 SQL，返回全部行 → 通关。',
        'payload详解' => 'payload：<code class="payload">1/**/UNION/**/SELECT/**/1,2,password/**/FROM/**/sqli_users</code><br>注意 1 和 UNION 之间同样要用注释隔开——空格会被 WAF 删掉，注释不会。/**/ 在 SQLite/MySQL 中都被解析为空白；MySQL 下还可写 /*!UNION*/（版本注释，WAF 更难识别）。',
        '工具实操' => 'sqlmap -u "stage23.php?id=1" --batch --dbs --tamper=space2comment<br>space2comment 插件即自动把空格替换为 /**/。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（可绕过）；high: intval 强转；impossible: PDO 预处理参数化查询。WAF 侧应参数化而非黑名单。',
        '知识卡片' => '📌 注释=空白 是最经典的 WAF 绕过之一，sqlmap tamper 脚本大量使用。<br>关联：大小写/双写（24/25）→ 注释空白（本关）→ 编码绕过。',
    ],
    __FILE__
);
