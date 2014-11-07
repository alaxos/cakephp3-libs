<?php

use Alaxos\Lib\StringTool;

function ___($singular, $args = null)
{
	$arguments = func_get_args();
	$translation = __($singular, array_slice($arguments, 1));
	
    $ucf_translation = StringTool::mb_ucfirst($translation);
    
    return $ucf_translation;
}

function ___d($domain, $msg, $args = null)
{
	$arguments = func_get_args();
	$translation = __d($domain, $msg, array_slice($arguments, 2));
	
	$ucf_translation = StringTool::mb_ucfirst($translation);
	
	return $ucf_translation;
}