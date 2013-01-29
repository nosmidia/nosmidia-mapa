<?php

class ContatoController extends Zend_Controller_Action
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
    	
    	$formContact = new Form_Contact();
		
    	if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if( $formContact->isValid($post) )
			{
				$mail = new Project_Mail();
		        $mail
					->setView('contact')
		            ->setViewParams($post)
		            ->setBodyHtml()
		            ->setFrom($post['email'], $post['name'])
		            ->setSubject('['.SITE_NAME.'] - Contato pelo site')
		            ->addTo(SITE_EMAIL, SITE_NAME);
		
				$mail->send();
			}
		}
		
		$this->view->formContact = $formContact;
	}
    
}


