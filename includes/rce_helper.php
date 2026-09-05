<?php
require_once APP_ROOT . '/includes/layout.php';
$RCE_STAGES = [1=>'eval注入', 2=>'assert注入', 3=>'preg_replace /e', 4=>'create_function', 5=>'动态调用'];
function rce_head($n, $title, $desc) {
    global $RCE_STAGES;
    render_header("代码执行 第{$n}关", 'rce');
    render_stage_nav('rce', $RCE_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function rce_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function rce_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
