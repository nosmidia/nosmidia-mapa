<?php

class MapaController extends Zend_Controller_Action
{
	protected $_params;
	protected $_language; 
	
    public function init()
    {   
    	$params = $this->_getAllParams();
   		
    	$defaults = array('page' => 1);
   		$params   = array_merge($defaults,$params );
     	$this->_params	 = $params;
     	
    	Zend_Layout::getMvcInstance()->disableLayout();
   
    	
    	/**
    	if( !$this->_request->isXmlHttpRequest() )
		{
	    	$this->_redirect('/');
		}
		
		if( isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != URL.'/')
		{
			$this->_redirect('/');
		}
		*/
	}

   	public function cadastroAction()
   	{
   		$form = Form_AddMarker::getFromCache();
		
		if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if( $form->isValid($post) )
			{
				throw  new Exception('Erro ao cadastrar ponto. Tente novamente mais tarde.');
			}
		}
		
		$form->populate(array('type' => Project_StaticData::MARKER_CONTENT_TYPE_TEXT ));
		
		$this->view->form = $form;
   	}
   	
   	public function infoAction()
   	{
   		$isNew = false;
   		$parent_category = null;
   		
   		$map_point = null; 
   		$params = $this->_getAllParams();
   		if(isset($params[1]) && !empty($params[1]))
   		{
   			$slug  = str_replace('mapa/', '', $params[1]);
   			
   			if($slug == 'cadastro')
   			{
   				//Mandei redirecionar por causa da rota.
   				$this->_helper->actionStack('cadastro','mapa','default', array());
   				return;
   			}
   			
   			
   			if( isset($params['type']) )
   			{
   				$where = sprintf('%s.id = %d', Model_MapPoint::TABLE_NAME ,$slug);
   				$isNew = true;
   			}
   			else 
   				$where = sprintf('%s.slug = "%s"', Model_MapPoint::TABLE_NAME ,$slug);
   				
   			$cache = Zend_Registry::get('cache');
			$cache_name = md5($where);
			$map_point = $cache->load($cache_name);
	   		if( $map_point === false )
	   		{
				$results = Model_MapPoint::fetchAllMapPoint($where);
   				if($results)
   				{
   					$map_point = $results->current();
   					$cache->save($map_point,$cache_name);
   				
   					
   				}
	   		}
	   		
	   		
	   		if($map_point)
	   		{
	   			$parent_category = Model_Category::getCategory( $map_point->parent_id );
				if($parent_category)
            	{
            		$parent_category = $parent_category->current();
				}
   			}
	   		
		}
   		
   		$this->view->map_point = $map_point;
   		$this->view->isNew = $isNew;
   		$this->view->parent = $parent_category;
   	}

   	
}


