<?php
// 文件上传公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

define('UPLOAD_DIR', APP_ROOT . '/public/uploads/');

$UPLOAD_STAGES = [
    1=>'无过滤任意上传', 2=>'前端JS验证', 3=>'MIME类型验证',
    4=>'扩展名黑名单', 5=>'扩展名白名单', 6=>'双扩展名绕过',
    7=>'大小写绕过', 8=>'空字节绕过', 9=>'.htaccess绕过',
    10=>'.phtml绕过', 11=>'Content-Type绕过', 12=>'图片马绕过',
    13=>'条件竞争', 14=>'路径穿越', 15=>'ZIP滑块',
];

function upload_head($n, $title, $desc) {
    global $UPLOAD_STAGES;
    render_header("文件上传 第{$n}关", 'upload');
    render_stage_nav('upload', $UPLOAD_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function upload_form($extra_html = '') {
    echo '<form method="post" enctype="multipart/form-data" class="lab">'
       . '<div class="row"><label>选择文件</label><input type="file" name="file"></div>'
       . '<div class="row"><input type="submit" value="上传"></div>'
       . $extra_html
       . '</form>';
}

function upload_pass($passed, $msg = '通关！成功上传可执行文件') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function upload_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}

// 通关判定：上传的文件是否为可执行扩展名
function upload_check_pass($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $dangerous = ['php','phtml','pht','php3','php4','php5','php7','phps','htaccess'];
    return in_array($ext, $dangerous);
}

// 列出已上传文件
function upload_list() {
    $files = glob(UPLOAD_DIR . '*');
    if (!$files) return;
    echo '<div class="result"><h3>已上传文件：</h3><table><tr><th>文件名</th><th>大小</th><th>访问</th></tr>';
    foreach ($files as $f) {
        $bn = basename($f);
        $sz = filesize($f);
        echo '<tr><td>' . h($bn) . '</td><td>' . $sz . ' B</td>'
           . '<td><a href="/uploads/' . urlencode($bn) . '" target="_blank">访问</a></td></tr>';
    }
    echo '</table></div>';
}
