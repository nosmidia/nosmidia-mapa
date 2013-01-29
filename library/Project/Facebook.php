<?php

class Project_Facebook
{
	
	const URL_AUTH = 'https://graph.facebook.com/oauth/authorize';
    const URL_TOKEN = 'https://graph.facebook.com/oauth/access_token';
    const URL_PERMISSIONS = 'https://graph.facebook.com/me/permissions';
    
    const URL_FQL = 'https://graph.facebook.com/fql';

  
    private $appId, $appSecret, $callbackUrl,$id,$token,$fields;

    public function __construct($appId, $appSecret)
    {
        if($appId == '')
            throw new \InvalidArgumentException('You must provide an ID');

        if($appSecret == '')
            throw new \InvalidArgumentException('You must provide a Secret');

        if(!ctype_digit($appId))
            throw new \InvalidArgumentException('You must provide a valid Id');

        if(!ctype_xdigit($appSecret))
            throw new \InvalidArgumentException('You must provide a valid Secret');

        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function setCallbackUrl($url)
    {
        if(empty($url) || !is_scalar($url) || strlen($url) < 3)
            throw new \InvalidArgumentException('You must provide a valid Callback URL');

        $this->callbackUrl = $url;
        return $this;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    public function authorize()
    {
        if(!$this->callbackUrl)
            throw new \InvalidArgumentException('You must provide a valid Callback URL');

        $args = array(
            'client_id' => $this->appId,
            'redirect_uri' => $this->callbackUrl,
            'scope' => implode(',', func_get_args()),
        );

        return (object) array(
            'method' => 'get',
            'url' => sprintf('%s?%s', self::URL_AUTH, http_build_query($args))
        );
    }

    public function authenticate($code)
    {
        if(!$code)
            throw new \InvalidArgumentException('You must provide a code');

        $args = array(
            'client_id' => $this->appId,
            'redirect_uri' => $this->callbackUrl,
            'client_secret' => $this->appSecret,
            'code' => $code,
        );

        return (object) array(
            'method' => 'get',
            'url' => sprintf('%s?%s', self::URL_TOKEN, http_build_query($args))
        );
    }

    public function getPermissions($token)
    {
        if(!$token)
            throw new \InvalidArgumentException('You must provide a token');

        $args = array(
            'access_token' => $token,
        );

        return (object) array(
            'method' => 'get',
            'url' => sprintf('%s?%s', self::URL_PERMISSIONS, http_build_query($args))
        );
    }
	
    


    public function setUser($token, $id = null)
    {
    	if($token == '')
            throw new \InvalidArgumentException('You must provide a Token');

        if(!ctype_alnum($token))
            throw new \InvalidArgumentException('You must provide a valid Token');

        if(!is_null($id) && !ctype_digit($id))
            throw new \InvalidArgumentException('You must provide a valid Id');

        $this->token = $token;
        $this->id = $id ? : 'me';
        $this->fields = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    
   	public function __call( $name , $value)
   	{
   		$key = strtolower(substr($name,3));
   		$value = (is_array($value)) ? current($value) : $value;
   		
   		if(stripos($name, 'set') === 0){
   			$this->fields[$key] = $value;
   		}elseif (stripos($name,'get') === 0){
   	 		return $this->fields[$key];
   	 		
   		}else{
   	 		throw new \Exception('Invalid method: '. $name );
   	 	}
   	 	
   	 }
    
   	
	public function getInfo( )
	{

		if(func_num_args() ==  0 )
			$fields = 'uid,name';
		else 
			$fields = implode(',', func_get_args());

		$userId = ($this->getId() === 'me') ? 'me()' : $this->getId();	

		$args = array(
            'q' => sprintf('SELECT %s FROM user WHERE uid = %s',$fields,$userId),
		 	'access_token' => $this->getToken()
        );

        return (object) array(
            'method' => 'get',
            'url' => sprintf('%s?%s', self::URL_FQL, http_build_query($args))
        );
	}
    

	public static function curl($url, $method = 'get', $data = array())
    {
    	$curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
 		if($method == 'get') {
        	curl_setopt($curl, CURLOPT_URL, $url . (strpos($url, '?') === FALSE ? '?' . http_build_query($data) : ''));
		}
        elseif($method == 'post') {
        	curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
        
        $content = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return (object) array(
        	'status' => $status,
            'content' => $content
		);
	}
	

}