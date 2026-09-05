<?php
// XSS 跨站脚本公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$XSS_STAGES = [
    1=>'反射型-无过滤', 2=>'反射型-双引号属性', 3=>'反射型-单引号属性',
    4=>'反射型-过滤script', 5=>'反射型-过滤大小写', 6=>'反射型-双写绕过',
    7=>'反射型-过滤on事件', 8=>'反射型-img标签', 9=>'反射型-svg标签',
    10=>'反射型-HTML实体编码', 11=>'存储型-无过滤', 12=>'存储型-过滤script',
    13=>'存储型-过滤on事件', 14=>'DOM型-eval', 15=>'DOM型-innerHTML',
];

function xss_head($n, $title, $desc) {
    global $XSS_STAGES;
    render_header("XSS 第{$n}关", 'xss');
    render_stage_nav('xss', $XSS_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function xss_form($name, $value, $label = '输入') {
    echo '<form method="get" class="lab"><div class="row"><label>' . h($label) . '</label>'
       . '<input type="text" name="' . h($name) . '" value="' . h($value ?? '') . '" style="width:340px"></div>'
       . '<div class="row"><input type="submit" value="提交"></div></form>';
}

function xss_pass($passed, $msg = '通关！成功触发 XSS') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function xss_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}

// 检测输出中是否包含未转义的 XSS 向量
function xss_check_pass($output) {
    $patterns = [
        '/<script[^>]*>/i',
        '/on\w+\s*=\s*["\']?[^"\'>\s]/i',
        '/<img[^>]+onerror/i',
        '/<svg[^>]+onload/i',
        '/<iframe[^>]*>/i',
        '/javascript:/i',
        '/<body[^>]+onload/i',
        '/<details[^>]+ontoggle/i',
        '/<input[^>]+onfocus/i',
        '/<mark[^>]+onmouseover/i',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $output)) return true;
    }
    return false;
}
