<?php

class Project_StaticData
{
	const MARKER_CONTENT_TYPE_YOUTUBE 	= 1;
	const MARKER_CONTENT_TYPE_IMAGE 	= 2;
	const MARKER_CONTENT_TYPE_TEXT	 	= 3;
	
	public static function getMarkerContentType( $key = null )
	{
		$array = array();
		$array[1] = 'Link do Youtube';
		$array[2] = 'Link de imagem';
		$array[3] = 'Texto Livre';
		
		if( $key && array_key_exists($key, $array) )
		{
			return $array[$key];
		}
		else 
		{
			return $array;
		}
	}
	
	public static function getBrasilianStates( $key = null )
	{
		$array = array(
			'AC'	=>	'Acre', 
			'AL'	=>	'Alagoas', 
			'AM'	=>	'Amazonas', 
			'AP'	=>	'Amapá',	
			'BA'	=>	'Bahia',
			'CE'	=>	'Ceará',
			'DF'	=>	'Distrito Federal',
			'ES'	=>	'Espírito Santo',
			'GO'	=>	'Goiás',
			'MA'	=>	'Maranhão',
			'MT'	=>	'Mato Grosso',
			'MS'	=>	'Mato Grosso do Sul',
			'MG'	=>	'Minas Gerais',
			'PA'	=>	'Pará',	
			'PB'	=>	'Paraíba',
			'PR'	=>	'Paraná',
			'PE'	=>	'Pernambuco',
			'PI'	=>	'Piauí',
			'RJ'	=>	'Rio de Janeiro',
			'RN'	=>	'Rio Grande do Norte',	
			'RO'	=>	'Rondônia',
			'RS'	=>	'Rio Grande do Sul',
			'RR'	=>	'Roraima',	
			'SC'	=>	'Santa Catarina',
			'SE'	=>	'Sergipe',
			'SP'	=>	'São Paulo',
			'TO'	=>	'Tocantins' );
		
		if( $key && array_key_exists($key, $array) )
		{
			return $array[$key];
		}
		else 
		{
			return $array;
		}
		
	}
}