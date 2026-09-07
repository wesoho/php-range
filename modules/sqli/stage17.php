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

sqli_head(17, '编码转义与宽字节', 'addslashes 转义引号。SQLite/UTF-8 下它对引号无效（可直接绕过），MySQL GBK 下可用宽字节 %df\' 绕过。');
sqli_form('id', $id);
sqli_error($err); sqli_pass($passed);
sqli_result($rows);
sqli_tail(
    ['hint' => 'SQLite 直接用引号即可；MySQL GBK 场景用 %df 宽字节', 'full' => "1' OR '1'='1　（MySQL GBK 场景：%df' OR 1=1-- ）"],
    [
        '原理' => "addslashes 把引号前加反斜线，意图阻止引号逃逸。<b>本靶场（SQLite + UTF-8）下该转义根本不生效</b>：SQLite 字符串不把反斜线当转义符，引号照常闭合，直接注入即可。<br><b>宽字节（MySQL GBK 专有）</b>：GBK 下 %df%5c 组成合法双字节汉字，反斜线被吃掉，引号逃逸——这是 addslashes 在 GBK 连接下的经典缺陷，本关保留其原理讲解供打 MySQL 靶场时使用。",
        '漏洞代码' => '<pre>$sql = "SELECT ... WHERE id=\'$id\'"; // medium 级 addslashes($id)</pre><span class="danger">↑ 依赖 addslashes 防注入在 SQLite/UTF-8 下完全失效</span>',
        '攻击演示' => "SQLite：输入 1' OR '1'='1 直接返回全部行 → 通关（addslashes 未阻止）<br>MySQL GBK（原理演示）：输入 %df'，addslashes 变 %df%5c'，%df%5c 是一个 GBK 字符，' 逃逸",
        'payload详解' => '本关 payload：<code class="payload">1\' OR \'1\'=\'1</code><br>宽字节 payload（MySQL GBK）：<code class="payload">%df\' OR 1=1-- </code>',
        '工具实操' => 'sqlmap -u "stage17.php?id=1" --batch --dbs<br>打 MySQL GBK 靶场时：--tamper=unmagicquotes 模拟宽字节。',
        '通关检测' => '返回行数 &gt; 1 或结果含 flag 即通关。',
        '防御修复' => 'medium: addslashes（本关已证明不可靠）；high: intval 强转；impossible: PDO 预处理参数化查询。MySQL 下应设置连接编码 SET NAMES utf8mb4 并用参数化。',
        '知识卡片' => '📌 addslashes 从来不是可靠的防注入手段：SQLite/UTF-8 下直接失效，MySQL GBK 下被宽字节打穿。正确姿势永远是参数化查询。<br>关联：字符型注入（2）→ 编码绕过（本关）→ like 注入（18）。',
    ],
    __FILE__
);
