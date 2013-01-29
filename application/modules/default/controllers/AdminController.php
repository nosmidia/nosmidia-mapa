<?php

class AdminController extends Zend_Controller_Action
{
	public function indexAction()
	{
		Zend_Layout::getMvcInstance()->disableLayout();
		
		//$modelAdmins 	= new Model_Admins();
		$formLoginAdmin = new Form_LoginAdmin();
		
		if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if($formLoginAdmin->isValid($post))
				$this->_redirect('/adm');
			
		}
		
		$this->view->formLoginAdmin = $formLoginAdmin;
	}
}