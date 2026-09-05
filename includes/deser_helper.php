<?php
// 反序列化 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$DESER_STAGES = [1=>'PHP反序列化', 2=>'POP链构造', 3=>'phar反序列化'];

function deser_head($n, $title, $desc) {
    global $DESER_STAGES;
    render_header("反序列化 第{$n}关", 'deser');
    render_stage_nav('deser', $DESER_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function deser_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function deser_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
