<?php

class Adm_CategoriasController extends Zend_Controller_Action
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
    	$m_category = new Model_Category();
		$categories = $m_category->fetchAll('parent_id = 0');
		
		$page = $this->_params['page'];
		$paginator = Zend_Paginator::factory($categories);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(self::PER_PAGE);
		
    	$this->view->categories	= $paginator;
    	$this->view->params 	= $this->_params;
    }
    
	public function subcategoriasAction()
    {
    	$m_category = new Model_Category();
    	
    	$parent_id = (int) $this->_params['id'];
    	
    	$parent = $m_category->find($parent_id);
    	
    	$where = sprintf('parent_id = %d', $parent_id);
		$categories = $m_category->fetchAll($where);
		$page = $this->_params['page'];
		$paginator = Zend_Paginator::factory($categories);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(self::PER_PAGE);
		
		$this->view->parent     = $parent->current(); 
    	$this->view->categories	= $paginator;
    	$this->view->params 	= $this->_params;
    }
    
    public function addAction()
    {
    	$form = new Form_Admin_Category();
    	
    	if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if($form->isValid($post))
			{
				$post['icon_file'] = Model_Category::uploadIcon($_FILES['icon_file']);
				$post['marker']    = Model_Category::uploadMarker($_FILES['marker'], $post['icon_file']);
				
				$category_id = Model_Category::insertCategory($post);
				
				if($category_id)
				{
					$this->view->message()->show('Categoria inserida com sucesso.','done');
					$this->indexRedirect();
				}
				else 
				{
					$this->view->message()->show('Erro ao adicionar categoria.','error');
					Model_Category::deleteIcon($post['icon_file']);
					Model_Category::deleteMarker($post['icon_file']);
				}
			}
		}
    	
    	$this->view->form = $form;
    }
	
	public function editAction()
    {
    	$form  = new Form_Admin_Category();
    	$model = new Model_Category();
    	
    	$categoria_id = (int) $this->_params['id'];
    	
    	$categoria = $model->find($categoria_id)->current();
    	if(!$categoria){
    		$this->view->message()->show('Categoria não encontrada.','warning');
			$this->indexRedirect();
    	}

    	$form->setAction('/adm/categorias/edit/id/'.$categoria_id)->setMethod('post');
    	$form->getElement('submit')->setLabel('Editar');
    	$form->populate($categoria->toArray());
    	
    	if( $this->getRequest()->isPost() )
		{
			$post = $this->getRequest()->getPost();
			
			if($form->isValid($post))
			{
				$post['id'] = $categoria_id;
				
				$post['icon_file'] = Model_Category::uploadIcon($_FILES['icon_file']);
				if( $post['icon_file'] )
					Model_Category::deleteIcon($categoria->icon_file);
				else 
					$post['icon_file'] = $categoria->icon_file;

					
				$post['marker'] = Model_Category::uploadMarker($_FILES['marker'], $post['icon_file']);
				if( $post['icon_file'] )
					Model_Category::deleteMarker($categoria->icon_file);
				else 
					$post['icon_file'] = $categoria->icon_file; 
				
				$where = sprintf("id = %d", $post['id']);	
				$updated_rows = Model_Category::updateCategory($post, $where);
				
				$this->view->message()->show('Categoria alterada com sucesso.','done');
				$this->indexRedirect();
			}
		}
    	
    	$this->view->form = $form;
    }
    
    public function deleteAction()
    {
    	$this->view->message()->show('Não foi possivel excluir.','warning');
    	
    	$delete_id = (int) $this->_params['id'];
    	$model = new Model_Category();
    	if($delete_id){
    		$result = Model_Category::deleteCategory($delete_id);
    		if($result)
    			$this->view->message()->show('Categoria excluída com sucesso.','done');
    	}
    		
    	$this->indexRedirect();
    		
    }
    
    public function indexRedirect()
    {
    	$params = $this->_params;
    	$array  = array();
    	//Set Defaults
    	$array['module'] 	 = 'adm';
    	$array['controller'] = 'categorias';
    	$array['action']	 = 'index';
    	$array['page']		 = $params['page'];
    	
    	$this->_redirect($this->view->url($array,null,true));
    }
}

