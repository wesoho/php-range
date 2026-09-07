<?php
// 命令注入公共辅助函数
require_once APP_ROOT . '/includes/layout.php';

$CMDI_STAGES = [
    1=>'无过滤命令注入', 2=>'过滤分号', 3=>'过滤管道符',
    4=>'过滤反引号', 5=>'过滤多种符号', 6=>'过滤空格',
    7=>'过滤cat命令', 8=>'内联注释绕过', 9=>'变量绕过',
    10=>'盲注命令注入',
];

function cmdi_head($n, $title, $desc) {
    global $CMDI_STAGES;
    render_header("命令注入 第{$n}关", 'cmdi');
    render_stage_nav('cmdi', $CMDI_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function cmdi_form($param, $value, $label = '输入') {
    echo '<form method="get" class="lab"><div class="row"><label>' . h($label) . '</label>'
       . '<input type="text" name="' . h($param) . '" value="' . h($value ?? '') . '" style="width:340px"></div>'
       . '<div class="row"><input type="submit" value="执行"></div></form>';
}

function cmdi_pass($passed, $msg = '通关！成功注入命令') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function cmdi_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}

// 通关判定：输出中包含注入命令的结果
function cmdi_check_pass($output, $markers = null) {
    if ($markers === null) {
        $markers = ['root:','/bin/','/etc/','uid=','Linux','total ','drwx','flag{','[fonts]','for 16-bit'];
    }
    foreach ($markers as $m) {
        if (stripos($output, $m) !== false) return true;
    }
    // 跨平台通用判定：输出中存在"非 ping 特征"的行，即为注入命令的输出
    // （Windows whoami→用户名行；Linux id→uid= 行；type win.ini→[fonts] 行）
    foreach (preg_split('/?
/', (string)$output) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (preg_match('/ping|TTL=|Reply from|Pinging|bytes of data|statistics|Packets|Approximate|Minimum|Maximum|Average|来自|数据包|往返|Request timed out|无法访问|一般故障/i', $line)) continue;
        if (preg_match('/不是内部或外部命令|is not recognized|command not found|no such file/i', $line)) continue;
        return true;
    }
    return false;
}
