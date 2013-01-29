<?php

class AjaxController extends Zend_Controller_Action
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
     	
    	/*if( !$this->_request->isXmlHttpRequest() && $_SERVER['HTTP_REFERER'] != URL.'/' )
		{
	    	$this->_redirect('/');
		}*/
    }

    public function indexAction()
    {
    	
   	}
   	
   	public function cadastroAction()
   	{
   		$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.');
   		
   		$form = new Form_SignUp();
		if( $form->isValid($post) )
		{
			$user_id = Model_User::insertUser($post);
			if($user_id)
			{
				Model_User::authenticate($post['email'], $post['password']);
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Cadastro efetuado com sucesso.';
			}
		}
		
		echo json_encode($return);
		die();
			
	}
	
	public function cadastroFacebookAction()
   	{
   		$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.');
   		
   		$user = Model_Facebook::authenticate($post['facebook_access_token']);
   		if($user)
   		{
   			$return['status'] 		= STATUS_OK;
			$return['status_msg'] 	= 'Login efetuado com sucesso.';
   		}
   		else
   		{
   			$model = new Model_User();
			$emailExists = $model->exists($post['email'], 'email');
			
			//Se ja tiver o email cadastrado, insere os dados do facebook.
			if($emailExists)
			{
				$post['user_id'] = (int) $emailExists->current()->id;
				
				$model = new Model_Facebook();
				$user = $model->exists($post['facebook_id'], 'facebook_id');
				if($user)
				{
					//Salva o novo AccessToken
					$array = array();
					$array['facebook_access_token'] = $post['facebook_access_token'];
					$where = sprintf('facebook_id = "%s"', $post['facebook_id'] );
					$model->update($array, $where);
				}
				else
				{
					Model_Facebook::insertFacebook($post);
				}

				$user = Model_Facebook::authenticate($post['facebook_access_token']);
				
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Login efetuado com sucesso.';
			}
			
			//Se o email nao estiver cadastrado, cadastra todos os dados do usuario.
			else 
			{
				$post['password'] = Project_Strings::generateNewPassword(); 
				
				$post['user_id']  = Model_User::insertUser($post);
				
				Model_Facebook::insertFacebook($post);
				
				$user = Model_Facebook::authenticate($post['facebook_access_token']);
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Login efetuado com sucesso.';
			}
   		}
   		
   		
   		echo json_encode($return);
		die();
			
	}
	
	public function loginAction()
   	{
   		$post = $this->_params;
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Credenciais inválidas.');
   		
   		$form = new Form_SignIn();
		if( $form->isValid($post) )
		{
			$return['status'] 		= STATUS_OK;
			$return['status_msg'] 	= 'Login efetuado com sucesso.';
		}
		
		echo json_encode($return);
		die();
			
	}
	
	public function contatoAction()
   	{
   		$post = $this->_params;
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Erro ao enviar.');
   		
   		$form = new Form_Contact();
		if( $form->isValid($post) )
		{
			
			$mail = new Project_Mail();
		    $mail
				->setView('contact')
		        ->setViewParams($post)
		        ->setBodyHtml()
		        ->setFrom($post['email'], $post['name'])
		        ->setSubject('['.SITE_NAME.'] - Contato pelo site')
		        ->addTo(SITE_EMAIL, SITE_NAME);
		
			if($mail->send())
			{
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Contato enviado com sucesso.';
			}
			
		}
		
		echo json_encode($return);
		die();
			
	}
	
	public function aboutAction()
	{
		
	}
	
	public function userStatusAction()
	{
		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Usuario não logado.');
		
		$user = Model_User::getIdentity();
		if($user)
		{
			//Verifica se nao é admin;
			if( ! isset($user->id_admin) )
			{
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Usuario logado.';
				$return['user']			= $user->toArray();
			}
		}
		
		echo json_encode($return);
		die();
	}
   	
	public function checkEmailAction()
	{
		$post = $this->_params;
		
		$model = new Model_User();
		$emailExists = $model->exists($post['email'], 'email');
		if($emailExists)
		{
			echo 'false';
		}
		else 
		{
			echo 'true';
		}
		
		die();
	}

	public function addMarkerStep1Action()
	{
		$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.');
   		
   		$form = Form_AddMarker::getFromCache();
		if( $form->isValid($post) )
		{
			$return['status'] 		= STATUS_OK;
			$return['status_msg'] 	= 'Carregando próximo passo.';
			
			$session = new Zend_Session_Namespace('AddMarker');
			$session->step1 = $post;
		
		}
		
		echo json_encode($return);
		die();
			
	}
	
	public function addMarkerStep2Action()
	{
		$return = array();
		$return['status'] 		= STATUS_ERROR;
		$return['status_msg'] 	= 'Erro ao adicionar ponto.';
		
		$isValid = false;
		$post = $this->_params;
		
		
		//Cadastro
		if( $post['form'] == 'signup' )
		{
			$form = new Form_SignUp();
			if( $form->isValid($post) )
			{
				$user_id = Model_User::insertUser($post);
				if($user_id)
				{
					Model_User::authenticate($post['email'], $post['password']);
					$isValid = true;
				}else{
					$return['status'] 		= STATUS_ERROR;
					$return['status_msg'] 	= 'Erro ao autenticar após cadastro.';
				}	
				
			}else{
				$return['status'] 		= STATUS_ERROR;
				$return['status_msg'] 	= 'Erro ao cadastrar.';
			}
		}
		
		//Login Simples
		else if( $post['form'] == 'signin' )
		{
			$form = new Form_SignIn();
			if( $form->isValid($post) )
			{
				$isValid = true;
			}else{
				$return['status'] 		= STATUS_ERROR;
				$return['status_msg'] 	= 'Erro ao autenticar.';
			}
		}
		//Login facebook	
		else if( $post['form'] == 'facebook' )
		{
			/// FACEBOOK /////
			
			
			$user = Model_Facebook::authenticate($post['facebook_access_token']);
   			if($user)
   			{
   				$isValid = true;
   			}
   			else
   			{
   				$model = new Model_User();
				$emailExists = $model->exists($post['email'], 'email');
			
				//Se ja tiver o email cadastrado, insere os dados do facebook.
				if($emailExists)
				{
					$post['user_id'] = (int) $emailExists->current()->id;
				
					$model = new Model_Facebook();
					$user = $model->exists($post['facebook_id'], 'facebook_id');
					if($user)
					{
						//Salva o novo AccessToken
						$array = array();
						$array['facebook_access_token'] = $post['facebook_access_token'];
						$where = sprintf('facebook_id = "%s"', $post['facebook_id'] );
						$model->update($array, $where);
					}
					else
					{
						Model_Facebook::insertFacebook($post);
					}

					$user = Model_Facebook::authenticate($post['facebook_access_token']);
				
					$isValid = true;
				}
			
				//Se o email nao estiver cadastrado, cadastra todos os dados do usuario.
				else 
				{
					$post['password'] = Project_Strings::generateNewPassword(); 
					
					$post['user_id']  = Model_User::insertUser($post);
					
					Model_Facebook::insertFacebook($post);
					
					$user = Model_Facebook::authenticate($post['facebook_access_token']);
					
					$isValid = true;
				}
   			}
			
			/// END FACEBOOK /////
			
		}
		//Usuário já está logado
		else if( $post['form'] == 'none' )
		{
			$isValid = true;
		}
		
		//Verifica se o usuario está logado.
		$user = Model_User::getIdentity();
		
		
		//Salva tudo no banco.
		if($isValid && $user)
		{
			//Recupera os dados do passo 1
			$session = new Zend_Session_Namespace('AddMarker');
			
			$session->step1['user_id'] = $user->id;
			$map_point_id = Model_MapPoint::insertMapPoint($session->step1);
			if($map_point_id)
			{
			
				$return['map_point']    = $map_point_id;
				
				$return['status'] 		= STATUS_OK;
				$return['status_msg'] 	= 'Ponto adicionado com sucesso.';
				
				//Limpa a sessao
				$session->step1 = null;
				
			}else{
				$return['status'] 		= STATUS_ERROR;
				$return['status_msg'] 	= 'Não foi possível adicionar ponto.';
			}
			
		}
		
		echo json_encode($return);
		die();
		 
	}
	
	public function addMarkerAction()
	{
		$post = $_POST;
		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Não foi possível adicionar ponto.');
   		
	   	$map_point_id = Model_MapPoint::insertMapPoint($post);
		if($map_point_id)
		{
			$return['status'] 		= STATUS_OK;
			$return['status_msg'] 	= 'Ponto adicionado com sucesso.';
			$return['map_point_id'] = $map_point_id;
		}
		echo json_encode($return);
		die();
	}
	
	
	public function getMarkersAction()
	{
       	$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.', 'markers' => array() );
   		
   		$where = null;
   		$term  = null;
   		if(!empty($post['s']))
   		{
   			$term = $post['s'];
   			$where = sprintf('title LIKE "%%%s%%" OR content LIKE "%%%s%%" OR city LIKE "%%%s%%"', $term,$term,$term);
   		}
   		
        $markers = Model_MapPoint::fetchAllMapPoint($where);
        if( $markers )
        {
        	$markers  = $markers->toArray();
        	foreach($markers as $key => $marker)
            {
            	$marker['address'] 			= stripslashes($marker['address']);
            	$marker['neighborhood'] 	= stripslashes($marker['neighborhood']);
            	$marker['city'] 			= stripslashes($marker['city']);
            	$marker['content'] 			= $this->getMarkerContent($marker);
            	$marker['name'] 			= stripslashes($marker['name']);
            	$marker['category'] 		= stripslashes($marker['category']);
            	$marker['parent']			= null;
            	$marker['slug'] 			= '/mapa/' . stripslashes($marker['slug']);
            	
            	$parent_category = Model_Category::getCategory($marker['parent_id']);
            	if($parent_category)
            	{
            		$marker['parent'] = current( $parent_category->toArray() );
            		$marker['icon_file'] = $marker['parent']['icon_file'];
            	}
            	
            	array_push($return['markers'], $marker);
            	
            	
        	}
        	$return['status'] 		= STATUS_OK;
        	$return['count']  		= count($markers);
        	$return['status_msg']   = 'Pontos carregados com sucesso.';
        	
        	if($term)
        	{
        		$return['status_msg'] ='Resultados da busca por: "'.$term.'"'; 
        	}
        	
        	
        }
        else
        {
         	$return['maps'] 		= array();
            $return['status'] 		= STATUS_OK;
        	$return['count']  		= 0;
        	$return['status_msg'] 	= 'Nenum ponto cadastrado.';
        	if($term)
        	{
        		$return['status_msg'] ='Nenhum ponto encontrado.'; 
        	} 
               
        }
       
        $json = json_encode($return);
        die($json);
	
	}
	
	public function getMarkerContent($marker_array)
	{
		if($marker_array['type'] == Project_StaticData::MARKER_CONTENT_TYPE_TEXT)
		{
			return  Project_Strings::excerpt(nl2br(stripslashes($marker_array['content'])), 300);
		}
	
		if($marker_array['type'] == Project_StaticData::MARKER_CONTENT_TYPE_YOUTUBE)
		{
			return '<iframe width="420" height="315" src="http://www.youtube.com/embed/'.$marker_array['content'].'" frameborder="0" allowfullscreen></iframe>';
		}
		
		
		if($marker_array['type'] == Project_StaticData::MARKER_CONTENT_TYPE_IMAGE)
		{
			return  '<img src="'.$marker_array['content'].'" width="420" />';
		}
	}
	
	public function selectCategoryAction()
	{
		$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.', 'categories' => array(), 'count' => 0 );
   		
   		if(isset($post['parent_id']))
   		{
   			$where = sprintf( 'parent_id = %d', $post['parent_id']);
   			$results = Model_Category::fetchAllCategories($where);
   			if( $results->count() )
   			{
   				$return['categories']     = $results->toArray();
   				$return['status'] 		  = STATUS_OK;
        		$return['count']  		  = $results->count();
        		$return['status_msg']     = 'Categorias carregados com sucesso.';
   			}
   		}
   		
   		$json = json_encode($return);
        die($json);
   	}
	
	
	public function selectSubcategoryAction()
	{
		$post = $this->_params;
   		
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Ocorreu um erro.', 'sub_categories' => array(), 'count' => 0 );
   		
   		if(isset($post['parent_id']))
   		{
   			$where = sprintf( 'parent_id = %d', $post['parent_id']);
   			$results = Model_Category::fetchAllCategories($where);
   			if( $results->count() )
   			{
   				$return['sub_categories'] = $results->toArray();
   				$return['status'] 		  = STATUS_OK;
        		$return['count']  		  = $results->count();
        		$return['status_msg']     = 'Pontos carregados com sucesso.';
   			}
   		}
   		
   		$json = json_encode($return);
        die($json);
   	}
   	
   	public function getMarkerInfoAction()
   	{
   		
   		$parent_category = null;
   		$map_point = null;
   		
   		$post = $this->_params;
   		$id = (isset($this->_params['id'])) ? $this->_params['id'] : null ;
   		
   		$where = sprintf('%s.id = %d', Model_MapPoint::TABLE_NAME ,$id);
   				
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
		
		$this->view->map_point = $map_point;
   		$this->view->parent = $parent_category;
		
   	}
   	
   	public function facebookLoginAction()
   	{
   		$return = array('status' => STATUS_ERROR, 'status_msg' => 'Credenciais inválidas.');
   		
   		$facebook = new Project_Facebook(FACEBOOK_APPID, FACEBOOK_SECRET);
        $facebook->setCallbackUrl(URL .'/ajax/facebook-login/');
   		
        if(!isset($this->_params['code'])){
        
        	$request = $facebook->authorize('email');
            $this->_redirect($request->url);
                
        }else{
                
			$access_token = null; 
                
            $result = $facebook->authenticate($this->_params['code']);
            $result = Project_Facebook::curl($result->url, $result->method);
                 
            if($result->status == 200 ){
            	parse_str($result->content,$array);
                extract($array);
                if($access_token){
                	$facebook->setUser($access_token);
                	$info = $facebook->getInfo( 'uid','name', 'email');
                	$result = Project_Facebook::curl($info->url, $info->method);
                	if($result->status == 200 ){
                		
						$user = json_decode($result->content);
						$user = $user->data[0];
						
						$post = array();
						$post['facebook_access_token'] = $access_token;
						$post['name'] =  $user->name;
						$post['email'] =  $user->email;
						$post['facebook_id'] = $user->uid;
						
						$result = Project_Facebook::curl( URL . '/ajax/cadastro-facebook/', 'post', $post);
                	
						$userStatus = Project_Facebook::curl( URL . '/ajax/user-status/');
						if($userStatus->status===200){
							$user = array();
							$temp = json_decode($userStatus->content);
							$temp = $temp->user;
							foreach($temp as $key => $value)
								$user[$key] = $value;
								
							$return['status'] 	  = STATUS_OK;
							$return['status_msg'] = 'Login efetuado com sucesso.';
							$return['user'] 	  = $user;
								
						}
					}
                }
			}
        }
        
        
        echo json_encode($return);
		die();
        
        
        
   	}
   	
}


