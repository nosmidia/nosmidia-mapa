<?php

class UsuarioController extends Zend_Controller_Action
{
	protected $_params;
	protected $_language; 
	
    public function init()
    {   
    	$params = $this->_getAllParams();
   		
    	$defaults = array('page' => 1);
   		$params   = array_merge($defaults,$params );
     	$this->_params	 = $params;
     	
    	if($this->_request->isXmlHttpRequest())
		{
	    	Zend_Layout::getMvcInstance()->disableLayout();
		}
    
    }

    public function indexAction()
    {
    	
    	$user = Model_User::getIdentity();
    	if(!$user)
    	{
    		$formSignUp = new Form_SignUp();
			$formSignIn = new Form_SignIn();
	    	
			if( $this->getRequest()->isPost() )
			{
				$post = $this->getRequest()->getPost();
				
				if( $formSignUp->isValid($post) )
				{
					$user_id = Model_User::insertUser($post);
					if($user_id)
					{
						Model_User::authenticate($post['email'], $post['password']);
						$this->_redirect('/');
					}
				}
			}
			
			$this->view->formSignUp = $formSignUp;
			$this->view->formSignIn = $formSignIn;
    	}
    	$this->view->user = $user;
    	
   	}
    
   	
   	public function cadastroAction()
   	{
   		//$modelAdmins 	= new Model_Admins();
		$form = new Form_SignUp();
		
		if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if( $form->isValid($post) )
			{
				$user_id = Model_User::insertUser($post);
				if($user_id)
				{
					Model_User::authenticate($post['email'], $post['password']);
					$this->_redirect('/');
				}
			}
		}
		
		$this->view->form = $form;
   	}
   	
   	
   	public function loginAction()
   	{
   		$form = new Form_SignIn();
		
		if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if($form->isValid($post))
			{
				$this->_redirect('/');
			}
			
		}
		
		$this->view->form = $form;
	}
   	
    public function logoutAction()
    {
    	$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$this->_redirect('/');
    }
        
}


