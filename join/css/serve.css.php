<?php

header('Content-Type: text/css');
if (is_file($local = __DIR__ . '/local.css'))
{
    echo @file_get_contents($local);
}
