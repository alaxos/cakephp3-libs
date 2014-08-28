<?php

use Alaxos\Lib\StringTool;

function ___($singular, $args = null)
{
    $ucf_singular = StringTool::mb_ucfirst($singular);
    
    $arguments = func_get_args();
    return __($ucf_singular, array_slice($arguments, 1));
}

function ___d($domain, $msg, $args = null)
{
    $ucf_msg = StringTool::mb_ucfirst($msg);
    
    $arguments = func_get_args();
    return __d($domain, $ucf_msg, array_slice($arguments, 1));
}