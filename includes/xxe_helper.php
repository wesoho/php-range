<?php
// XXE XML外部实体 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$XXE_STAGES = [1=>'基础XXE', 2=>'盲注XXE', 3=>'参数实体绕过'];

function xxe_head($n, $title, $desc) {
    global $XXE_STAGES;
    render_header("XXE 第{$n}关", 'xxe');
    render_stage_nav('xxe', $XXE_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function xxe_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function xxe_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
