<?php 

class Model_Facebook extends Project_Db_Table_Abstract
{
	const TABLE_NAME = 'facebook';
	protected $_name = self::TABLE_NAME;
	
	public static function insertFacebook($data)
	{
		$model = new Model_Facebook();
		
		$fields = array();
		$fields['facebook_id']	 		 = $data['facebook_id'];
		$fields['facebook_access_token'] = $data['facebook_access_token'];
		$fields['user_id'] 				 = (int) $data['user_id'];
		
		return $model->insert($fields);
	}
	
	public static function authenticate( $access_token  )
    {
	
    	$where = sprintf('facebook_access_token = "%s"', $access_token );
    	
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
       	$model = new Model_Facebook();
       	$select = $model->select();
       	$select->setIntegrityCheck(false);
       	$select->from(Model_Facebook::TABLE_NAME, array('*'));
       	$select->join(array(  'u' => Model_User::TABLE_NAME ), 'u.id = user_id', array('*') );
       	$select->where($where);
       	$user = $model->fetchRow($select);

        return $user;
	}

}