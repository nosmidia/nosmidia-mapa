<?php

class IndexController extends Zend_Controller_Action
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
    	$categories = Model_Category::fetchAllCategories('parent_id = 0');

    	
    	$this->view->categories = $categories;
    	$this->view->params = $this->_params;
    	
   	}
    
   	public function subcategoriesAction()
   	{
   		Zend_Layout::getMvcInstance()->disableLayout();
   		
   		$parent_category_id = (int) $this->_params['category'];
   		$categories = null;
   		
   		if($parent_category_id)
   		{
   		
   			$where = sprintf('parent_id = %d', $parent_category_id);
   			$categories = Model_Category::fetchAllCategories($where);
   		}
   		
   		$this->view->categories = $categories;
   		
   		
   		
   	}
   	
    public function logoutAction()
    {
    	$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$this->_redirect('/');
    }
    
    public function oProjetoAction()
    {
		
    }
   
    public function comoUsarAction()
    {
		
    }
        
}


