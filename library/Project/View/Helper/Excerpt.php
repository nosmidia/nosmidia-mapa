<?php
/**
 * Helper para criar excerpts.
 * @author Emerson Brôga <@emersonbroga>
 * @package
 * @filesource
 *
 */
class Project_View_Helper_Excerpt
{
	public static function excerpt($text, $chars = 100 )
	{	
		if( strlen($text) > $chars )
		{
			$text = $text . " ";
		    $text = strip_tags($text);
		    $text = substr($text,0,$chars);
		    $text = substr($text,0,strrpos($text,' '));
		    $text = $text . "...";
		}
	    return $text; 
	}
}