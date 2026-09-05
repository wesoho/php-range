<?php
require_once APP_ROOT . '/includes/layout.php';
$INFOLEAK_STAGES = [1=>'目录列表', 2=>'备份文件', 3=>'错误信息', 4=>'源码泄露', 5=>'配置文件'];
function infoleak_head($n, $title, $desc) {
    global $INFOLEAK_STAGES;
    render_header("信息泄露 第{$n}关", 'infoleak');
    render_stage_nav('infoleak', $INFOLEAK_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}
function infoleak_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}
function infoleak_tail($hints, $tutorial, $source_file) {
    echo '</div>'; render_hint($hints); render_tutorial($tutorial); render_source($source_file); render_footer();
}
