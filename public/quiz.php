<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once APP_ROOT . '/includes/layout.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$user = $_SESSION['user'];
global $MODULES;

$QUIZZES = [
    'sqli' => [
        ['SQL 注入中，整数型参数无需闭合什么？', ['引号','括号','分号','无需闭合'], 3],
        ['布尔盲注的核心特征是？', ['页面报错','页面有/无数据两种状态','响应延迟','HTTP 状态码'], 1],
        ['防御 SQL 注入的最佳方案是？', ['过滤关键字','使用 ORM','参数化查询','WAF'], 2],
        ['宽字节注入利用的是？', ['GBK 编码','UTF-8 BOM','base64','URL 编码'], 0],
        ['时间盲注常用函数是？', ['SLEEP()','WAIT()','DELAY()','PAUSE()'], 0],
    ],
    'xss' => [
        ['反射型 XSS 与存储型 XSS 的主要区别是？', ['是否存入数据库','是否用 JS','是否弹窗','是否用 cookie'], 0],
        ['DOM 型 XSS 的特点是？', ['经过服务端','不经过服务端','需要数据库','需要文件'], 1],
        ['防御 XSS 的核心函数是？', ['addslashes()','htmlspecialchars()','strip_tags()','escape()'], 1],
        ['<img src=x onerror=alert(1)> 利用了什么？', ['img 标签','onerror 事件','src 属性','alert 函数'], 1],
        ['CSP 头防御 XSS 的原理是？', ['过滤输入','限制脚本来源','加密页面','禁用 JS'], 1],
    ],
    'upload' => [
        ['文件上传最安全的验证方式是？', ['黑名单','白名单+内容检测','检查扩展名','检查 MIME'], 1],
        ['图片马绕过的是什么检测？', ['扩展名检测','文件头检测','大小检测','路径检测'], 1],
        ['.htaccess 上传攻击的原理是？', ['执行 PHP','改变文件解析规则','删除文件','创建目录'], 1],
        ['条件竞争利用的是？', ['文件删除前的时间窗口','并发请求','缓存失效','会话过期'], 0],
        ['双扩展名 file.php.jpg 在哪种配置下生效？', ['Apache 解析','Nginx 解析','IIS 解析','Tomcat 解析'], 0],
    ],
    'cmdi' => [
        ['命令注入中 ; 的作用是？', ['逻辑与','顺序执行','管道','后台执行'], 1],
        ['过滤了空格可用什么绕过？', ['$IFS','${IFS}','%20','以上都可'], 3],
        ['escapeshellarg() 的作用是？', ['过滤命令','转义参数为安全字符串','禁止执行','加密命令'], 1],
        ['盲注命令注入如何判断？', ['看输出','看响应时间','看状态码','看头信息'], 1],
        ['$() 与反引号的共同点是？', ['都是注释','都是命令替换','都是管道','都是变量'], 1],
    ],
    'csrf' => [
        ['CSRF 的全称是？', ['跨站脚本攻击','跨站请求伪造','跨站资源伪造','客户端请求伪造'], 1],
        ['CSRF 与 XSS 的主要区别是？', ['CSRF 不注入代码','XSS 不用 cookie','CSRF 需要 JS','XSS 需要表单'], 0],
        ['防御 CSRF 最有效的手段是？', ['Referer 检查','CSRF Token','HTTPS','验证码'], 1],
        ['SameSite Cookie 属性的作用是？', ['防 XSS','限制跨站携带 Cookie','加密 Cookie','延长 Cookie'], 1],
        ['CSRF 攻击需要受害者？', ['点击链接','输入密码','安装软件','修改配置'], 0],
    ],
    'ssrf' => [
        ['SSRF 的全称是？', ['服务端脚本伪造','服务端请求伪造','安全请求伪造','会话请求伪造'], 1],
        ['SSRF 的主要危害是？', ['读取内网资源','篡改页面','窃取 Cookie','执行命令'], 0],
        ['file:// 协议在 SSRF 中可用来？', ['执行命令','读取本地文件','访问内网','上传文件'], 1],
        ['防御 SSRF 应？', ['禁止内网访问','白名单+禁止内网+限制协议','过滤 URL','用 HTTPS'], 1],
        ['0.0.0.0 绕过的是什么？', ['白名单','127.0.0.1 黑名单','端口检查','协议检查'], 1],
    ],
    'lfi' => [
        ['LFI 的全称是？', ['本地文件入侵','本地文件包含','逻辑文件包含','链接文件包含'], 1],
        ['路径穿越使用什么字符序列？', ['..//','../','./..','~/..'], 1],
        ['php://filter 的作用是？', ['执行代码','读写/转换文件流','上传文件','删除文件'], 1],
        ['防御 LFI 最佳方案是？', ['过滤 ../','白名单+路径规范化','检查扩展名','限制长度'], 1],
        ['双写绕过 str_replace 的原理是？', ['编码绕过','替换后重新组合','大小写','空字节'], 1],
    ],
    'xxe' => [
        ['XXE 利用的是 XML 的什么特性？', ['命名空间','外部实体','CDATA','注释'], 1],
        ['XXE 读取文件用的是什么协议？', ['http://','file://','ftp://','gopher://'], 1],
        ['防御 XXE 应？', ['过滤 XML','禁用外部实体解析','不用 XML','加密 XML'], 1],
        ['盲注 XXE 如何外带数据？', ['用 file://','用 OOB 外带到攻击者服务器','用 base64','用 gzip'], 1],
        ['libxml_disable_entity_loader() 的作用是？', ['禁用 XML','禁用外部实体加载','禁用 DTD','禁用命名空间'], 1],
    ],
    'deser' => [
        ['PHP 反序列化用哪个函数？', ['serialize()','unserialize()','json_decode()','eval()'], 1],
        ['反序列化漏洞触发点通常是？', ['__construct','__wakeup/__destruct','__get','__set'], 1],
        ['POP 链是指？', ['POP3 协议','面向属性编程的调用链','弹出链','属性链'], 1],
        ['phar 反序列化利用的是？', ['phar 文件元数据','phar 文件名','phar 内容','phar 签名'], 0],
        ['防御反序列化的根本方法是？', ['过滤输入','不反序列化用户输入','用 JSON','签名验证'], 1],
    ],
    'logic' => [
        ['水平越权是指？', ['普通用户访问管理员功能','同级用户访问彼此数据','未登录访问','绕过验证码'], 1],
        ['垂直越权是指？', ['同级用户互访','低权限访问高权限功能','向上修改','向下访问'], 1],
        ['条件竞争漏洞利用的是？', ['时间差','空间差','逻辑差','权限差'], 0],
        ['防御越权的核心是？', ['隐藏 URL','检查当前用户对资源的权限','用 POST','加验证码'], 1],
        ['验证码绕过常见方式是？', ['OCR 识别','验证码可复用/可空/可预测','暴力破解','社交工程'], 1],
    ],
    'session' => [
        ['会话固定攻击的核心是？', ['预测SID','强制用户使用攻击者指定的SID','窃取SID','删除SID'], 1],
        ['防御会话固定的方法是？', ['定期换SID','登录后 session_regenerate_id(true)','用 HTTPS','加密 Cookie'], 1],
        ['会话劫持的前提是？', ['知道目标 SID','知道密码','知道用户名','知道 IP'], 0],
        ['HttpOnly Cookie 属性防止什么？', ['XSS 窃取 Cookie','CSRF 攻击','SQL 注入','文件上传'], 0],
        ['session.use_strict_mode 的作用是？', ['拒绝未初始化的 SID','加密会话','限制会话数','延长会话'], 0],
    ],
    'infoleak' => [
        ['目录列表漏洞是指？', ['列出文件内容','列出目录文件','删除目录','创建目录'], 1],
        ['备份文件泄露常见于？', ['.bak/.old/.swp 文件','图片文件','JS 文件','CSS 文件'], 0],
        ['phpinfo() 泄露什么？', ['PHP 配置信息','数据库密码','用户密码','文件内容'], 0],
        ['防御信息泄露应？', ['关闭错误显示+删除敏感文件','加密页面','用 HTTPS','用 WAF'], 0],
        ['.git 目录泄露的是？', ['源代码版本库','数据库','配置','日志'], 0],
    ],
    'crypto' => [
        ['MD5 不安全的原因是？', ['长度太短','存在碰撞/可彩虹表破解','不可逆','太慢'], 1],
        ['AES-ECB 模式的缺陷是？', ['速度慢','相同明文产生相同密文','密钥太短','不可逆'], 1],
        ['安全的密码存储应使用？', ['md5+salt','bcrypt/PBKDF2/Argon2','base64','明文'], 1],
        ['弱随机数 mt_rand() 的问题？', ['太慢','可预测种子','范围太小','不可重复'], 1],
        ['hash_equals() 防止什么攻击？', ['SQL 注入','时序攻击','XSS','CSRF'], 1],
    ],
    'ssti' => [
        ['SSTI 的全称是？', ['服务端模板注入','客户端模板注入','安全模板注入','会话模板注入'], 0],
        ['Twig 模板注入用哪个表达式？', ['{{7*7}}','${7*7}','<%=7*7%>','#{7*7}'], 0],
        ['SSTI 可导致？', ['信息泄露','RCE','SQL 注入','以上都可'], 3],
        ['防御 SSTI 应？', ['过滤输入','使用沙箱/白名单模板引擎','不用模板','加密模板'], 1],
        ['Jinja2 沙箱逃逸利用？', ['__class__','__subclasses__','__builtins__','以上都可'], 3],
    ],
    'rce' => [
        ['eval() 的危险在于？', ['执行任意 PHP 代码','速度慢','内存泄漏','不可逆'], 0],
        ['preg_replace /e 修饰符的作用？', ['正则替换','执行替换结果作为 PHP 代码','加密','转义'], 1],
        ['create_function() 等价于？', ['eval','匿名函数创建+可注入代码','define','call_user_func'], 1],
        ['防御 RCE 的根本是？', ['过滤关键字','不执行用户输入的代码','用 HTTPS','用 WAF'], 1],
        ['assert() 在 PHP 中类似？', ['echo','eval（执行字符串为代码）','check','verify'], 1],
    ],
    'ldap' => [
        ['LDAP 注入利用的是？', ['SQL 语法','LDAP 查询语法','XML 语法','JS 语法'], 1],
        ['LDAP 查询中 * 的含义？', ['乘法','通配符','注释','变量'], 1],
        ['XPath 注入针对的是？', ['SQL 数据库','XML 文档','JSON 数据','CSV 数据'], 1],
        ['防御 LDAP 注入应？', ['过滤特殊字符 ( ) * & |','用 ORM','用 WAF','加密输入'], 0],
    ],
    'redirect' => [
        ['开放重定向是指？', ['跳转到任意 URL','跳转到首页','跳转到 HTTPS','跳转到登录'], 0],
        ['开放重定向可用于？', ['钓鱼攻击','XSS','SQL 注入','RCE'], 0],
        ['防御开放重定向应？', ['过滤 URL','白名单目标 URL','用 POST','加密 URL'], 1],
    ],
    'crlf' => [
        ['CRLF 注入使用什么字符？', ['\\r\\n (回车换行)','空格','分号','引号'], 0],
        ['HTTP 响应拆分利用 CRLF 注入？', ['注入 HTTP 头/Body','注入 SQL','注入 JS','注入 HTML'], 0],
        ['防御 CRLF 注入应？', ['过滤 \\r\\n','过滤空格','用 HTTPS','用 POST'], 0],
    ],
];

render_header('阶段测验', null);

$selected_module = $_GET['module'] ?? '';
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($submitted && isset($_POST['module']) && isset($QUIZZES[$_POST['module']])) {
    $mod = $_POST['module'];
    $questions = $QUIZZES[$mod];
    $score = 0;
    foreach ($questions as $i => $q) {
        $ans = $_POST['q'.$i] ?? -1;
        if ($ans == $q[2]) $score++;
    }
    $total = count($questions);
    db()->prepare("INSERT INTO quiz_scores(user,category,score,total,ts) VALUES(?,?,?,?,datetime('now'))")
       ->execute([$user, $mod, $score, $total]);
    $pct = round($score / $total * 100);
    ?>
    <h2>测验结果：<?= h($MODULES[$mod][0]) ?></h2>
    <div class="card">
      <div class="big-stat">
        <span class="stat-num"><?= $score ?></span> / <span class="stat-den"><?= $total ?></span>
        <span class="stat-pct"><?= $pct ?>%</span>
      </div>
      <?php if ($pct >= 80): ?>
        <div class="banner ok">🎉 优秀！你已充分掌握该模块知识。</div>
      <?php elseif ($pct >= 60): ?>
        <div class="banner info">👍 及格，但还有提升空间。建议复习错题。</div>
      <?php else: ?>
        <div class="banner fail">❌ 未及格，建议重新学习该模块关卡。</div>
      <?php endif; ?>
      <table class="result">
        <?php foreach ($questions as $i => $q): 
            $ans = $_POST['q'.$i] ?? -1;
            $correct = $ans == $q[2];
        ?>
          <tr class="<?= $correct?'':'fail-row' ?>">
            <td><?= $i+1 ?>. <?= h($q[0]) ?></td>
            <td>你的答案：<?= isset($q[1][$ans]) ? h($q[1][$ans]) : '未作答' ?></td>
            <td>正确答案：<?= h($q[1][$q[2]]) ?></td>
            <td><?= $correct ? '✅' : '❌' ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
      <p><a href="/quiz.php" class="btn-mini">返回测验列表</a></p>
    </div>
    <?php
    render_footer();
    exit;
}

if ($selected_module && isset($QUIZZES[$selected_module])) {
    $questions = $QUIZZES[$selected_module];
    ?>
    <h2>测验：<?= h($MODULES[$selected_module][0]) ?></h2>
    <form method="post" class="card">
      <input type="hidden" name="module" value="<?= h($selected_module) ?>">
      <?php foreach ($questions as $i => $q): ?>
        <div class="quiz-q">
          <h4><?= $i+1 ?>. <?= h($q[0]) ?></h4>
          <?php foreach ($q[1] as $j => $opt): ?>
            <label class="quiz-opt">
              <input type="radio" name="q<?= $i ?>" value="<?= $j ?>" required>
              <?= h($opt) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <div class="row"><input type="submit" value="提交答卷"></div>
    </form>
    <?php
} else {
    ?>
    <h2>📝 阶段测验</h2>
    <p>选择模块开始测验，检验理论掌握程度。</p>
    <div class="grid">
      <?php foreach ($QUIZZES as $mod => $questions):
          $best = db()->prepare("SELECT MAX(score*100.0/total) as pct FROM quiz_scores WHERE user=? AND category=?");
          $best->execute([$user, $mod]);
          $best_pct = round($best->fetchColumn() ?? 0);
      ?>
        <a class="mod-card" href="/quiz.php?module=<?= $mod ?>">
          <h3><?= h($MODULES[$mod][0]) ?></h3>
          <div class="desc"><?= count($questions) ?> 道题</div>
          <div class="meta">最佳成绩：<?= $best_pct ?>%</div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
}

render_footer();
