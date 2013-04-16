<?php

if (is_file($file = __DIR__ . '/../cfg/local.cfg.php') and is_readable($file))
{
    try { $config = include $file; } catch(Exception $e) {}
    echo 'var Config = ' . json_encode((object)(is_array($config) ? $config : array())) . ';';
}

