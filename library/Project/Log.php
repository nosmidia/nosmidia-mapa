<?php
class Project_Log
{
	public static function log($txt, $file = 'log.txt')
	{ 
		$time = date("F jS Y, h:iA"); 
		$fp   = fopen( APPLICATION_PATH. '/../public/'. $file, "a");
		fputs($fp, "LOG: $time: $txt\n\n");
		fclose($fp); //closing the function
	}
}