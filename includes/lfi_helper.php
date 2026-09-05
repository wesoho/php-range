<?php
// 文件包含 公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$LFI_STAGES = [1=>'本地文件包含', 2=>'路径穿越', 3=>'双写绕过', 4=>'远程文件包含', 5=>'伪协议绕过'];

function lfi_head($n, $title, $desc) {
    global $LFI_STAGES;
    render_header("文件包含 第{$n}关", 'lfi');
    render_stage_nav('lfi', $LFI_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function lfi_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function lfi_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}
