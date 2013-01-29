<?php 

class Model_Admin extends Project_Db_Table_Abstract
{
        protected $_name = 'admin';

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
	
	        $admin = self::findUser($where);
	        if($admin)
	        {
				self::updateStorage($admin);
	            return $admin;
	
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

        public static function sendNewPassword( $email )
        {
        	/*
                $where  = sprintf('u_email = "%s"', $email);
                $user   = self::findUser($where);

                $return = false;

                if($user)
                {
                        //Gera um password aleatorio.
                        $raw_password = Bolt_Utilities::generatePassword();
                        $password = self::cryptPassword($raw_password);

                        //Salva esse novo password no banco.
                        $array = array('u_password' => $password);
                        $model = new Model_Users();
                        $model->update($array, $where);

                        //Envia o email ao usuario.
                        $mail = new Bolt_Mail();

                        $mail
                        ->setScriptPath(APPLICATION_PATH. '/views/scripts/email/')
                    ->setView('new-password')
                    ->setViewParams(array('email' => $email, 'password' => $raw_password))
                    ->setBodyHtml()
                    ->setFrom( EMAIL_USER , EMAIL_NAME )
                    ->setSubject('[Bolt Cloud] Nova senha diretamente das nuvens')
                    ->addTo($email)
                    ->send();

                        $return = true;

                }

                return $return;
                */

        }

    public static function findUser( $where )
    {
       	$model_admin = new Model_Admin();
        $admin = $model_admin->fetchRow($where);

        return $admin;
	}





}