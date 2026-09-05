<?php
// CSRF 跨站请求伪造 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$CSRF_STAGES = [1=>'无防护CSRF', 2=>'Referer验证绕过', 3=>'Token可预测', 4=>'SameSite绕过', 5=>'CSRF正确防护'];

function csrf_head($n, $title, $desc) {
    global $CSRF_STAGES;
    render_header("CSRF 第{$n}关", 'csrf');
    render_stage_nav('csrf', $CSRF_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function csrf_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function csrf_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
