<?php

class Adm_PontosController extends Zend_Controller_Action
{
	const PER_PAGE = 20;
	protected $_params;
	
    public function init()
    {       
        $params = $this->_getAllParams();
   		
        $defaults = array('page' => 1);
   		$params   = array_merge($defaults,$params );
     	$this->_params	= $params;
     	
    }

    public function indexAction()
    {
    	$order = sprintf('%s.title ASC', Model_MapPoint::TABLE_NAME);
		$points = Model_MapPoint::fetchAllMapPoint(null, $order);
		
		$page = $this->_params['page'];
		$paginator = Zend_Paginator::factory($points);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(self::PER_PAGE);
		
    	$this->view->points		= $paginator;
    	$this->view->params 	= $this->_params;
    }
    
    public function showContentAction()
    {
    	Zend_Layout::getMvcInstance()->disableLayout();
    	
    	$content_id = (int) $this->_params['id'];
    	
    	$where = sprintf('%s.id = %d', Model_MapPoint::TABLE_NAME, $content_id);
    	$result= Model_MapPoint::fetchAllMapPoint($where);
    	
    	$this->view->result = $result;
    }
    
    

    public function deleteAction()
    {
    	$this->view->message()->show('Não foi possivel excluir.','warning');
    	
    	$delete_id = (int) $this->_params['id'];
    	$model = new Model_MapPoint();
    	if($delete_id){
    		$result = Model_MapPoint::deleteMapPoint($delete_id);
    		if($result)
    			$this->view->message()->show('Ponto no mapa excluído com sucesso.','done');
    	}
    	$this->indexRedirect();
    		
    }
    
    public function indexRedirect()
    {
    	$params = $this->_params;
    	$array  = array();
    	//Set Defaults
    	$array['module'] 	 = 'adm';
    	$array['controller'] = 'pontos';
    	$array['action']	 = 'index';
    	$array['page']		 = $params['page'];
    	
    	$this->_redirect($this->view->url($array,null,true));
    }
}

