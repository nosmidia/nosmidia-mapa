<?php

class Project_Youtube
{
	public static function getYoutubeCode( $str )
	{
		$regex = "#youtu(be.com|.b)(/embed/|/v/|/watch\\?v=|e/|/watch(.+)v=)(.{11})#";
 
		preg_match_all($regex , $str, $matches);
 
		if(!empty($matches[4]))
		{
		    $codigos_unicos = array();
		    $quantidade_videos = count($matches[4]);
		    foreach($matches[4] as $code)
		    {
		        if(!in_array($code,$codigos_unicos))
		            array_push($codigos_unicos,$code);
		 
		    }
			
		    return current($codigos_unicos);
		 
		}else{
		  	return false;
		}
	}
}