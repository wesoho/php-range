<?php
// public/router.php - PHP 内置服务器路由
// /modules/... 转发到项目根的 modules/ 目录，其余由内置服务器从 public/ 提供
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/modules/(.+\.php)$#', $uri, $m)) {
    $file = dirname(__DIR__) . '/modules/' . $m[1];
    $real = realpath($file);
    $base = realpath(dirname(__DIR__) . '/modules');
    if ($real && strpos($real, $base) === 0 && is_file($real)) {
        chdir(dirname($real));
        require $real;
        return true;
    }
    http_response_code(404);
    echo '404 Not Found';
    return true;
}
// 其余请求交给内置服务器处理（静态文件、public 下的 php）
return false;
