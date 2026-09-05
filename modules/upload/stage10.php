<?php
// 第 10 关：.phtml绕过
require_once dirname(__DIR__, 2) . '/config.php';
require_once APP_ROOT . '/includes/upload_helper.php';
if (empty($_SESSION['user'])) { header('Location: /login.php'); exit; }

$level = get_level();
$passed = false;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $name = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];
    $allow = false;

    if ($level === 'impossible') {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $white = ["jpg","png","gif"];
        $allow = in_array($ext, $white);
        if ($allow) {
            $head = file_get_contents($_FILES["file"]["tmp_name"], false, null, 0, 6);
            $allow = in_array($head, ["GIF89a","GIF87a","\x89PNG"]) || substr($head,0,2)==="\xff\xd8";
        }
        $newname = bin2hex(random_bytes(8)) . '.' . strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($allow) move_uploaded_file($tmp, UPLOAD_DIR . $newname);
        $msg = $allow ? '上传成功（已重命名）' : '拒绝：仅允许 jpg/png/gif 且需真实图片';
    } elseif ($level === 'high') {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $white = ["jpg","png","gif"];
        $allow = in_array($ext, $white);
        if ($allow) {
            $head = file_get_contents($_FILES["file"]["tmp_name"], false, null, 0, 6);
            $allow = in_array($head, ["GIF89a","GIF87a","\x89PNG"]) || substr($head,0,2)==="\xff\xd8";
        }
        if ($allow) move_uploaded_file($tmp, UPLOAD_DIR . $name);
        $msg = $allow ? '上传成功' : '拒绝';
    } else {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $black = ["php","php3","php5"];
        $allow = !in_array($ext, $black);
        if ($allow) {
            move_uploaded_file($tmp, UPLOAD_DIR . $name);
            $msg = '上传成功：' . $name;
            $passed = upload_check_pass($name);
        } else {
            $msg = '拒绝';
        }
    }
}

if ($passed) pass_stage('upload', 10, 'upload ' . ($_FILES['file']['name'] ?? ''));

upload_head(10, '.phtml绕过', '黑名单不含 .phtml，可上传执行。');
?>

<form method="post" enctype="multipart/form-data" class="lab">
  <div class="row"><label>选择文件</label><input type="file" name="file"></div>
  <div class="row"><input type="submit" value="上传"></div>
</form>
<?php if ($msg) echo '<div class="banner info">' . h($msg) . '</div>'; ?>
<?php if ($passed) upload_pass(true); ?>
<?php upload_list(); ?>
<?php
upload_tail(
    ['hint' => '黑名单不含 .phtml', 'full' => '上传 shell.phtml'],
    [
    '原理' => '文件上传第 10 关：黑名单不含 .phtml，可上传执行。<br>文件上传漏洞核心：服务器未正确验证上传文件类型，导致攻击者上传可执行文件（如 .php）获取 webshell。',
    '漏洞代码' => '<pre>$name = $_FILES["file"]["name"];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $black = ["php","php3","php5"];
        $allow = !in_array($ext, $black);
if ($allow) move_uploaded_file($_FILES["file"]["tmp_name"], UPLOAD_DIR.$name);</pre><span class="danger">↑ 验证不充分</span>',
    '攻击演示' => '1. 尝试上传 .php 文件<br>2. 根据验证逻辑构造绕过 payload<br>3. 上传成功后访问 /uploads/shell.php → 通关<br>payload：上传 shell.phtml',
    'payload详解' => '<code class="payload">上传 shell.phtml</code><br>提示：黑名单不含 .phtml',
    '工具实操' => 'curl -F file=@shell.php http://靶场/stage10.php<br>Burp Suite 抓包修改 filename / Content-Type<br>上传后访问 /uploads/验证执行',
    '通关检测' => '上传文件扩展名为 php/phtml/pht/htaccess 等可执行类型即通关。',
    '防御修复' => '<b>high 级</b>：白名单 + MIME 验证 + 文件头验证 + 重命名<br><b>impossible 级</b>：白名单 + 文件内容检测 + 随机重命名 + 存储到非 web 目录 + 禁止执行<br><pre>$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
if (!in_array($ext, ["jpg","png","gif"])) die("拒绝");
$newname = bin2hex(random_bytes(16)) . "." . $ext;</pre>',
    '知识卡片' => '📌 文件上传漏洞可导致 RCE（远程代码执行）<br>OWASP A04:2021-Insecure Design<br>关键防御：白名单 > 黑名单，内容检测 > 扩展名检测<br>本关演示：.phtml绕过'
],
    __FILE__
);
