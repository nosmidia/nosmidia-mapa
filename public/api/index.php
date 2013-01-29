<?php

require_once 'Simple_DB.php';
defineDBInfo();



defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

defined('STATUS_OK') || define('STATUS_OK', 'ok');
defined('STATUS_ERROR') || define('STATUS_ERROR', 'error');

defined('URL') || define('URL','http://'.$_SERVER['HTTP_HOST']);

defined('UPLOAD_PATH') || define('UPLOAD_PATH', dirname(dirname(__FILE__)) . '/uploads');

$return = array('status' => STATUS_ERROR, 'status_msg' => 'Parametros inválidos.');

if(APPLICATION_ENV == 'production'){
	$post = $_POST;
	$get  = $_GET;
	$files= $_FILES;
}else{
	$post = $_REQUEST;
	$get  = $_REQUEST;
	$files= $_FILES; 
}

if(isset($_REQUEST['api_type']))
{
	$return = array('status' => STATUS_ERROR, 'status_msg' => 'Erro ao processar requisição.');

	switch($_REQUEST['api_type']){
		
		case 'login':
			$return = apiUserLogin($post);
			break;
		case 'register':
			$return = apiUserRegister($post);
			break;
		case 'map-points':
			$return = apiMapPoints(array_merge($post, $get));
			break;
		case 'map-categories':
			$return = apiMapCategories($get);
			break;
		case 'add-map-point':
			$return = apiAddMapPoint($post, $files);
			break;
	}
	

}


echo json_encode($return);
die();


function apiUserLogin($data)
{
	$url = URL . '/ajax/login/';
	
	$return = array('status' => STATUS_ERROR, 'status_msg' => 'Credenciais inválidas.');
	
	if(isset($data['email']) && isset($data['password'])){
		
		$result = curl( $url, 'post', $data);
		if($result->status === 200){
			$returnJson = json_decode($result->content);
			$return['status']		= $returnJson->status;
			$return['status_msg']	= $returnJson->status_msg;
			$return['user']		 	= getUserStatus();
			
		}
	}
	
	return $return;
	
}

function apiUserRegister($data)
{
	$url = URL . '/ajax/cadastro/';
	
	$return = array('status' => STATUS_ERROR, 'status_msg' => 'Dados incorretos.');
	
	
	if(isset($data['name']) && isset($data['email']) && isset($data['password']) &&  isset($data['password_confirm']) ){
		
		$result = curl( $url, 'post', $data);
		if($result->status === 200){
			$returnJson = json_decode($result->content);
			$return['status'] 	  = $returnJson->status;
			$return['status_msg'] = $returnJson->status_msg;
			$return['user'] 	  = getUserStatus();
			
		}
	}
	
	return $return;
	
}

function getUserStatus()
{
	$user = null;
	$userStatus = curl( URL . '/ajax/user-status/');

	if($userStatus->status===200){
		
		$user = array();
		
		$temp = json_decode($userStatus->content);
		$temp = $temp->user;
		foreach($temp as $key => $value)
			$user[$key] = $value;
	}
	return $user;
}


function apiMapPoints($data)
{
	$url = URL . '/ajax/get-markers/';
	if(isset($data['s'])){
		$url = URL . '/ajax/get-markers/s='.$data['s'];
	}
	
	
	$result = curl( $url, 'post', $data);
	if($result->status === 200)
		$return = json_decode($result->content);
	else 
		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Requisição inválida.');
		
	return $return;
}

function apiMapCategories($data)
{
	$subcategory = (isset($data['parent_id'])) ? $data['parent_id'] : 0;
	
	$url =  URL . '/ajax/select-category/?parent_id='.$subcategory;
	
	$result = curl( $url, 'post', $data);
	if($result->status === 200)
		$return = json_decode($result->content);
	else 
		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Requisição inválida.');
		
	return $return;
}

function apiAddMapPoint($data, $files)
{
	defined('UPLOAD_PATH') || define('UPLOAD_PATH', dirname(dirname(__FILE__)) . '/uploads');
	
	$coord = getCoord( $data['address'] . ' - ' . $data['neighborhood'] . ' - ' . $data['city'] .' - ' . $data['state'] );
	$data['latitude'] =  $coord['lat'];
	$data['longitude'] = $coord['lng'];
	
	$data['sub_category'] = $data['category_id'];
	$data['state'] = getStateAbreviation($data['state']);

	
	$data['type'] = 3;
	
	$return['data'] = $data;
	
	if( count($files) ){
		
		$files = current($files);
		$filename = uniqid('iphone_upload_').'.jpg';
		
		if( copy($files['tmp_name'], UPLOAD_PATH . '/images/'. $filename) ){
			$data['content'] = URL . '/uploads/images/' . $filename;
			$data['type'] = 2;
		}
	}
	
	$url =  URL . '/ajax/add-marker/';
	
	$result = curl( $url, 'post', $data);
	if($result->status === 200)
		$return = json_decode($result->content);
	else 
		$return = array('status' => STATUS_ERROR, 'status_msg' => 'as dfasfas f', 'data' => $data, 'content' => $result->content);
		
	return $return;
}

function getStateAbreviation( $state )
{
	 $stateAbbr = strtoupper(substr($state, 0,2));
	  
	 $states = array(
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
	 
	 $state_slug = slugify($state);
	 foreach($states as $abbr => $st){
	 	$st = slugify($st);
	 	if($state_slug == $st)
	 		$stateAbbr = $abbr;
	 }
	 
	 return $stateAbbr;
}

function slugify($text)
{ 
	// replace non letter or digits by -
	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
	
	// trim
	$text = trim($text, '-');

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	
	// lowercase
	$text = strtolower($text);
	
	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);
	
	if (empty($text))
	{
		return 'n-a';
	}
	
	return $text;
}


function getCoord($address)
{
	$lat = null;
    $lng = null;

    $request_url = 'http://maps.google.com/maps/geo?output=xml&q='.urlencode( $address );
    $xml = simplexml_load_file($request_url) or die("url not loading");
    $status = $xml->Response->Status->code;
    if (strcmp($status, "200") == 0)
    {
        // Successful geocode
       $coordinates = $xml->Response->Placemark->Point->coordinates;
       $coordinatesSplit = explode(",", $coordinates);
       // Format: Longitude, Latitude, Altitude
       $lat = $coordinatesSplit[1];
       $lng = $coordinatesSplit[0];
    }
    
    return array('lat'=> $lat,  'lng' => $lng );
	
}

function defineDBInfo()
{

	$dbInfo = array();
	$env = (defined('APPLICATION_ENV')) ? APPLICATION_ENV : 'production';
	
	switch($env)
	{
		case 'development':
			defined('DB_USER') || define('DB_USER', '');
			defined('DB_PASS') || define('DB_PASS', '');
			defined('DB_HOST') || define('DB_HOST', '');
			defined('DB_NAME') || define('DB_NAME', '');
			break;
		case 'staging':
			defined('DB_USER') || define('DB_USER', 'nosmidia');
			defined('DB_PASS') || define('DB_PASS', '8xB5cUTu');
			defined('DB_HOST') || define('DB_HOST', 'mysql.emersonbroga.com');
			defined('DB_NAME') || define('DB_NAME', 'h_nosmidia');
			//App ID:	439702049425738
			//App Secret:	1d379710bcfe5280546d6189f38349eb(reset)
			
			break;
		default:
			defined('DB_USER') || define('DB_USER', 'nosmidiasite');
			defined('DB_PASS') || define('DB_PASS', 'nosmidia09W');
			defined('DB_HOST') || define('DB_HOST', 'mysql.nosmidia.com.br');
			defined('DB_NAME') || define('DB_NAME', 'nosmidia');
			break;
	}
}

function getSimpleDB()
{
	$dbInfo = apiGetDBInfo();
	return new Simple_DB($dbInfo['dbuser'],$dbInfo['dbpass'],$dbInfo['dbhost'],$dbInfo['dbname']);
}

function curl($url, $method = 'get', $data = array())
{
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_COOKIEJAR,  md5($_SERVER['HTTP_HOST']). '.txt');
	curl_setopt($curl, CURLOPT_COOKIEFILE, md5($_SERVER['HTTP_HOST']). '.txt');
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
    
    
    
    if($method == 'get') {
    
    	curl_setopt($curl, CURLOPT_URL, $url . (strpos($url, '?') === FALSE ? '?' . http_build_query($data) : ''));
    
    }elseif($method == 'post') {
    	
    	curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	   
    $content = curl_exec($curl);
    $status  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
        
    return (object) array(
    	'status' => $status,
        'content' => $content
        );
	}