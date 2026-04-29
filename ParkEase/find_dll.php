<?php
$html = file_get_contents('https://windows.php.net/downloads/pecl/releases/mongodb/1.20.1/');
preg_match_all('/href="(php_mongodb-1\.20\.1-8\.4-ts-vs17-x64\.zip)"/i', $html, $matches);
if (empty($matches[1])) {
    preg_match_all('/href="(php_mongodb-1\.20\.1-8\.4-ts-.*?\.zip)"/i', $html, $matches);
}
print_r($matches[1]);
