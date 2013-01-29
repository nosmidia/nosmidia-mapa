<?php 

class Model_User extends Project_Db_Table_Abstract
{
	const TABLE_NAME = 'user';
	
	protected $_name = self::TABLE_NAME;
	
	public static function insertUser($data)
	{
		$model = new Model_User();
		
		$fields = array();
		$fields['name']  	 = $data['name'];
		$fields['email']  	 = trim(strtolower($data['email']));
		$fields['password']	 = self::cryptPassword($data['password']);
		
		$fields['created_at']= date('Y-m-d H:i:s');
		
		return $model->insert($fields);
	}
	

    public static function cryptPassword( $raw_password )
    {
		if(!defined('SALT'))
        	die('Please define the constant SALT in your Bootstrap.');    
			
		return md5(SALT. $raw_password);
	}

    public static function authenticate( $email , $raw_password  )
    {
		$password = self::cryptPassword($raw_password);
	
	    $where = sprintf('email = "%s" AND password = "%s"', $email , $password );
		$user = self::findUser($where);
	    if($user)
	    {
			self::updateStorage($user); 
	        return $user;
	
		}else{
			return false;
		}
	
	}

    public static function updateStorage( $user )
    {
    	$auth   = Zend_Auth::getInstance();
       	$auth->getStorage()->write($user);
	}

    public static function getIdentity()
    {
    	$auth = Zend_Auth::getInstance();
		if( $auth->hasIdentity() )
        {
        	return $auth->getIdentity();
		}
        else
        {
        	return false;
		}
	}



    public static function findUser( $where )
    {
       	$model = new Model_User();
        $user = $model->fetchRow($where);

        return $user;
	}

}