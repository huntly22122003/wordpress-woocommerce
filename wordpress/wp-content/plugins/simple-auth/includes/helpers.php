<?php

if (!defined('ABSPATH')) exit;

function sa_console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}