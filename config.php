<?php
// ============================================================
// PHP-Range 攻防靶场 - 全局配置
// 仅供学习网络攻防，禁止部署到公网或任何可被外部访问的环境
// ============================================================

define('APP_NAME', 'PHP-Range 攻防靶场');
define('APP_ROOT', dirname(__FILE__));
define('DB_PATH', APP_ROOT . '/data/range.db');
define('DB_INIT_SQL', APP_ROOT . '/data/init.sql');

// 四级难度: low(无防护) / medium(错误防护可绕过) / high(强防护) / impossible(不可攻破标杆)
define('DEFAULT_LEVEL', 'low');
// 提示模式: none(无提示) / hint(思路提示) / full(完整payload)
define('DEFAULT_HINT', 'none');

// 会话安全
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// 靶场需要看到报错以学习报错注入等
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 漏洞模块注册表: key => [名称, 描述, 关卡数]
$MODULES = [
    'sqli' => ['SQL 注入', '25 关，对标 sqli-labs，覆盖全部闭合/盲注/堆叠/二次/绕过', 25],
    'xss'  => ['XSS 跨站脚本', '15 关，反射型/存储型/DOM型，覆盖各种过滤绕过', 15],
    'upload' => ['文件上传', '15 关，前端绕过/MIME绕过/扩展名绕过/图片马/条件竞争等', 15],
    'cmdi'  => ['命令注入', '10 关，OS命令注入/管道/分号/反引号/各种绕过', 10],
    'csrf'  => ['CSRF 跨站请求伪造', '5 关，无防护/Referer绕过/Token绕过/SameSite/正确防护', 5],
    'ssrf'  => ['SSRF 服务端请求伪造', '5 关，URL请求/IP绕过/协议绕过/DNS重绑定/盲注', 5],
    'lfi'   => ['文件包含', '5 关，LFI/路径穿越/双写绕过/RFI/伪协议', 5],
    'xxe'   => ['XXE XML外部实体', '3 关，基础/盲注/参数实体绕过', 3],
    'deser' => ['反序列化', '3 关，PHP反序列化/POP链/phar', 3],
    'logic' => ['逻辑漏洞', '8 关，越权/密码重置/验证码/参数缺失/溢出/竞争', 8],
    'ssti'  => ['SSTI 模板注入', '5 关，服务端模板注入/过滤绕过', 5],
    'rce'   => ['代码执行', '5 关，eval/assert/preg_replace_e/create_function/动态调用', 5],
    'ldap'  => ['LDAP/XPath注入', '4 关，LDAP注入/盲注/XPath注入/盲注', 4],
    'redirect' => ['开放重定向', '3 关，无过滤/白名单绕过/相对路径', 3],
    'crlf'  => ['CRLF注入', '3 关，响应拆分/邮件注入/日志注入', 3],
    'session' => ['会话管理', '5 关，会话固定/预测/超时/Cookie属性/劫持', 5],
    'infoleak' => ['信息泄露', '5 关，目录列表/备份文件/错误信息/源码/配置', 5],
    'crypto' => ['不安全加密', '5 关，弱哈希/硬编码密钥/ECB/弱随机/不安全比较', 5],
];

// 数据库连接（SQLite，零配置）
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $need_init = !file_exists(DB_PATH);
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if ($need_init && file_exists(DB_INIT_SQL)) {
            $pdo->exec(file_get_contents(DB_INIT_SQL));
        }
    }
    return $pdo;
}

// 获取当前安全级别
function get_level() {
    return $_SESSION['level'] ?? DEFAULT_LEVEL;
}

// 获取当前提示模式
function get_hint() {
    return $_SESSION['hint'] ?? DEFAULT_HINT;
}

// HTML 输出转义（防御 XSS 的基础工具，供 high/impossible 级使用）
function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
