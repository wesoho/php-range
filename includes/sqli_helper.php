<?php
// SQL 注入公共辅助函数（减少各关卡重复代码）
require_once APP_ROOT . '/includes/layout.php';

$SQLI_STAGES = [
    1=>'整数型无闭合', 2=>'单引号闭合', 3=>'双引号闭合', 4=>'单引号+括号', 5=>'双引号+括号',
    6=>'报错-floor', 7=>'报错-updatexml', 8=>'报错-extractvalue',
    9=>'布尔盲注-整数', 10=>'布尔盲注-单引号', 11=>'布尔盲注-双引号', 12=>'布尔盲注-括号',
    13=>'时间盲注-单引号', 14=>'时间盲注-双引号', 15=>'堆叠注入', 16=>'二次注入', 17=>'宽字节注入',
    18=>'like注入', 19=>'insert注入', 20=>'update注入', 21=>'delete注入', 22=>'搜索型注入',
    23=>'WAF-内联注释', 24=>'WAF-等价函数', 25=>'WAF-大小写/双写',
];

function sqli_head($n, $title, $desc) {
    global $SQLI_STAGES;
    render_header("SQL注入 第{$n}关", 'sqli');
    render_stage_nav('sqli', $SQLI_STAGES, $n);
    echo '<div class="card"><h2>第 ' . $n . ' 关 · ' . h($title) . '</h2><p>' . $desc . '</p>';
}

function sqli_form($name, $value, $label = '输入') {
    echo '<form method="get" class="lab"><div class="row"><label>' . h($label) . '</label>'
       . '<input type="text" name="' . h($name) . '" value="' . h($value ?? '') . '" style="width:340px"></div>'
       . '<div class="row"><input type="submit" value="提交查询"></div></form>';
}

function sqli_result($rows, $cols = null) {
    if (!$rows) return;
    echo '<div class="result"><table>';
    if ($cols === null) $cols = array_keys($rows[0]);
    echo '<tr>';
    foreach ($cols as $c) echo '<th>' . h($c) . '</th>';
    echo '</tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($cols as $c) echo '<td>' . h($r[$c] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</table></div>';
}

function sqli_error($err) {
    if ($err) echo '<div class="banner fail">⚠ SQL 错误：' . h($err) . '</div>';
}

function sqli_pass($passed, $msg = '通关！') {
    if ($passed) echo '<div class="banner ok">🎉 ' . h($msg) . '</div>';
}

function sqli_tail($hints, $tutorial, $source_file) {
    echo '</div>';
    render_hint($hints);
    render_tutorial($tutorial);
    render_source($source_file);
    render_footer();
}

// 通用通关判定：返回行数 > 预期，或结果含指定关键字
function sqli_check_pass($rows, $expected_max = 1, $keyword = null) {
    if (is_array($rows) && count($rows) > $expected_max) return true;
    if ($keyword && is_array($rows)) {
        foreach ($rows as $r) {
            foreach ($r as $v) if (stripos((string)$v, $keyword) !== false) return true;
        }
    }
    return false;
}
