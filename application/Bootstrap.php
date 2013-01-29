<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
 	protected function _initAutoLoader() {
 		
 		//LOCALE
 		date_default_timezone_set('America/Sao_Paulo');
		setlocale(LC_ALL, 'pt_BR');
 		
        $autoloader = Zend_Loader_Autoloader::getInstance ();
		$autoloader->registerNamespace("Project");
		
		return $autoloader;
    }

	protected function _initRouter()
    {
    	$ctrl  = Zend_Controller_Front::getInstance();
        $router = $ctrl->getRouter();
        
        $route = new Zend_Controller_Router_Route_Regex(
        	'(mapa/[a-z0-9-]+$)',
            array(
            	'controller' => 'mapa',
                'action'     => 'info'
			));
		$router->addRoute('mapa-info', $route);
    	
	}
    
    
	protected function _initDb()
	{
		date_default_timezone_set('America/Sao_Paulo');
		
		$config = new Zend_Config_Ini(
			APPLICATION_PATH . '/configs/application.ini',
			APPLICATION_ENV
		);
		$db = Zend_Db::factory($config->db);
		$db->query("SET NAMES 'utf8'");
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
		Zend_Registry::set('db', $db);
		return $db;
	}
	
	protected function _initFrontController()
	{
		//inicia os Models
		$autoloader = new Zend_Application_Module_Autoloader(array(
			'namespace'	=> '',
			'basePath'	=> APPLICATION_PATH
		));
		
		//Inicia os Modulos
		$front = Zend_Controller_Front::getInstance();
		$front->addModuleDirectory(APPLICATION_PATH . '/modules');
		$front->setDefaultModule('default');
		
		//Plugins
		$front->registerPlugin(new Project_Plugin_Security());
		
		return $front;
	}

	protected function _initMvc()
	{
		Zend_Layout::startMVC(array(
			'layoutPath'=> APPLICATION_PATH . '/views/scripts/layout'
		));
	}	
	

    protected function _initView()
    {
        $view = new Zend_View();
		$view->addScriptPath(APPLICATION_PATH . '/views/scripts/partials');
		$view->addHelperPath('Project/View/Helper', 'Project_View_Helper_');
		
        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        // Return it, so that it can be stored by the bootstrap
        return $view;
    }
	
	protected function _initConstants()
	{
		$config = new Zend_Config_Ini(
			APPLICATION_PATH . '/configs/application.ini',
			APPLICATION_ENV
		);
		
		defined('URL') || define('URL','http://'.$_SERVER['HTTP_HOST']);
		defined('ADM_PER_PAGE') || define('ADM_PER_PAGE',20);
		
		defined('CACHE_PATH') || define('CACHE_PATH', APPLICATION_PATH . '/data/cache');
		defined('UPLOAD_PATH') || define('UPLOAD_PATH', dirname(APPLICATION_PATH) . '/public/uploads');
	
		defined('SALT') || define('SALT', 'LS^teM&3|3sCp&y+rWf.>G,NM!gq4d.<+PiXOb.Tos4fgtZa+P=N=M$R*VcXRHYp');
		
		defined('FACEBOOK_APPID') || define('FACEBOOK_APPID', $config->api->facebook->id);	
		defined('FACEBOOK_SECRET') || define('FACEBOOK_SECRET', $config->api->facebook->secret);

		
		defined('STATUS_OK') 	|| define('STATUS_OK', 'ok');	
		defined('STATUS_ERROR') || define('STATUS_ERROR', 'error');	

		defined('SITE_EMAIL') || define('SITE_EMAIL', 'emerson.broga@gmail.com');	
		defined('SITE_NAME')  || define('SITE_NAME', 'Nós Mídia');	
	}
	
	protected function _initFolders()
	{
		if( !defined('HAS_FOLDERS') )
		{
			if( !is_dir(CACHE_PATH) && !mkdir(CACHE_PATH, 0755, true) )
			{ 
				die('Programmer Please: Don\'t forget the cache folder at '.CACHE_PATH);
			}
			
			if( !is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0755, true) )
			{ 
				die('Programmer Please: Don\'t forget the upload folder at '.UPLOAD_PATH);
			}
			
			define('HAS_FOLDERS', true);
		}
	}
	
	
	protected function _initCache()
	{
		//Password 123456
		//0b835ec33ee43d12897c25102ae6c055
		
		$frontendOptions = array( 'lifetime'  => 7200, 'automatic_serialization' => true );
		$backendOptions  = array( 'cache_dir' => CACHE_PATH );
		$cache           = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
		
		Zend_Registry::set('cache', $cache);
	}
	
}

