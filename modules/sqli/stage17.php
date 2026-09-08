<?php
// 第 17 关：宽字节注入
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
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    } elseif ($level === 'medium') {
        $id = addslashes($id);
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    } else {
        $rows = $pdo->query("SELECT id,username,email FROM sqli_users WHERE id='$id'")->fetchAll();
    }
    $passed = sqli_check_pass($rows, 1, 'flag');
} catch (Exception $e) { $err = $e->getMessage(); }
if ($passed) pass_stage('sqli', 17, $id);

sqli_head(17, '编码转义与宽字节', 'addslashes 转义引号。SQLite/UTF-8 下它不构成转义但会破坏语法（引号 payload 报错），MySQL GBK 下可用宽字节 %df\' 绕过。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => 'low 级无过滤直接注入；medium 级 addslashes 在 SQLite 下会令引号 payload 语法报错', 'full' => "1' OR '1'='1"],
    [
        '原理' => 'addslashes 把引号前加反斜线，意图阻止引号逃逸。<b>本靶场（SQLite + UTF-8）实测</b>：SQLite 不把反斜线当转义符，addslashes 的产物（如 1 反斜线引号 AND …）会让字符串在引号处提前断开、残余的反斜线引号成为非法 token，SQL 直接报语法错误。字符型注入在 medium 被意外挡住（并非被正确转义）；数字型注入（无引号）不受影响。SQLite 语境下正确转义应使用 str_replace 双写单引号。<br><b>宽字节（MySQL GBK 专有）</b>：GBK 下 %df%5c 组成合法双字节汉字，反斜线被吃掉，引号逃逸——addslashes 在 GBK 连接下的经典缺陷，本关保留原理讲解供打 MySQL 靶场使用。',
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=\'$id\'"; // medium 级 addslashes($id)</pre><span class="danger">↑ addslashes 在 SQLite 下非转义而是破坏语法（unrecognized token），不可作为防注入手段</span>',
        '攻击演示' => "SQLite low：输入 1' OR '1'='1 直接返回全部行 → 通关<br>SQLite medium：同 payload 报语法错误（addslashes 残留裸反斜线）<br>MySQL GBK（原理演示）：输入 %df'，addslashes 变 %df%5c'，%df%5c 是一个 GBK 字符，引号逃逸",
        'payload详解' => '本关 payload：<code class="payload">1\' OR \'1\'=\'1</code><br>宽字节 payload（MySQL GBK）：<code class="payload">%df\' OR 1=1-- </code>',
        '工具实操' => 'sqlmap -u "stage17.php?id=1" --batch --dbs<br>打 MySQL GBK 靶场时：--tamper=unmagicquotes 模拟宽字节。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（SQLite 下靠报错碰巧挡住，属偶然非设计）；high: intval 强转；impossible: PDO 预处理参数化查询。MySQL 下应设置连接编码 SET NAMES utf8mb4 并用参数化。',
        '知识卡片' => '📌 addslashes 从来不是可靠的防注入手段：SQLite/UTF-8 下靠语法报错碰巧挡住引号 payload，MySQL GBK 下被宽字节打穿。正确姿势永远是参数化查询。<br>关联：字符型注入（2）→ 编码绕过（本关）→ like 注入（18）。',
    ],
    __FILE__
);
