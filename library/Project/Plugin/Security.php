<?php

/**
 * @see Zend_Controller_Plugin_Abstract
 */
require_once 'Zend/Controller/Plugin/Abstract.php';

class Project_Plugin_Security extends Zend_Controller_Plugin_Abstract
{

    /**
     * Checa se o usuario esta logado
     *
     * @author Rafael Campos @rafaelxy
     * @access public
     * @param Zend_Controller_Request_Abstract $request
     * @uses Zend_Controller_Request_Abstract
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	/* @var $freeAccess Modulos que são acessiveis sem login */
    	$freeeAccess      = array('default');
    	$restrictedAccess = array('adm');
        
        //recupera o objeto de autorizacao
        $auth = Zend_Auth::getInstance();

        //parametros da requisicao
        $moduleName = strtolower($request->getModuleName());
        
        //Verifica se o modulo que esta sendo acessado é restrito.
        if( in_array($moduleName,  $restrictedAccess) )
        {
        	//Se o Usuario nao está logado redireciona para home.
        	if( !$auth->hasIdentity() )
				$this->_redirect('/');
        	else 
        		$identity = $auth->getIdentity();
        	
        		 
        	//Verifica se o usuario logado é admin.
        	if(!isset($identity->id_admin))	 
        		$this->_redirect('/');
        	
        }
       
        
    }

    private function _redirect($url)
    {
        $front = Zend_Controller_Front::getInstance();
        $this->_response
                ->setRedirect('http://' . $_SERVER['HTTP_HOST'] . $url)
                ->sendHeaders();
    }

}