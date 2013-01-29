<?php

class Adm_IndexController extends Zend_Controller_Action
{
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
    	
    	$buttons = array();
    	
    	$m_category = new Model_Category();
    	$categories = $m_category->count();
    	$buttons[] = array('title'=> 'Categorias', 'controller'=>'categorias', 'count'=>$categories);
    	
    	$m_map_point = new Model_MapPoint();
    	$map_points = $m_map_point->count();
    	$buttons[] = array('title'=> 'Pontos no mapa', 'controller'=>'pontos', 'count'=>$map_points);
    	
    	
    	$this->view->buttons = $buttons;
   		$this->view->params = $this->_params;
    }
	
    
}

