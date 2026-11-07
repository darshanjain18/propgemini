<?php
$root = str_replace('\\', '/', dirname(__DIR__));
$uri = '/pages/properties';
$path = $root . $uri;
echo "root=$root\n";
echo "path=$path\n";
echo 'file=' . ($path . '.php') . ' exists=' . (is_file($path . '.php') ? 'yes' : 'no') . "\n";
