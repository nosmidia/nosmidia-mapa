<?php

class Form_SignIn extends Zend_Form
{
	
	public function init()
	{
		 //nome do formulário
        $this->setName('sign-in');

        //elemento para o campo email
        $email = new Zend_Form_Element_Text('email');
        //configurar o label, dizer q é obrigatório, adicionar um filtro e um validador
        $email->setLabel('Email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator(new Zend_Validate_EmailAddress())
            ->addValidator('NotEmpty');
            
        //elemento para a senha
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Senha')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
        
        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Login');
        $submit->setAttrib('id', 'submit-sign-in')
      		    ->setAttrib('class', 'btn')
            	->setIgnore(true);

        //exemplo de class css
        //$submit->setAttrib('class', 'verde buttonBar');
        //adicionar os campos ao formulário
        $this->addElements(array($email, $password, $submit));
        
        //action e method
        $this->setAction('/usuario/login')->setMethod('post')->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('class' => 'invalid')),
            array('Label', array()),
        ));
        
        //Remove o decorator do Submit
        $element = $this->getElement('submit');
		$element->removeDecorator('label');
        
        
        
	}	
	
	
	/*
	 * Validação e Autenticação.
	 * @author Emerson Carvalho<emerson.broga@gmail.com>
	 * @since 1/05/2011
	 */
	public function isValid($data)
	{
		//Validaçao do Formulário
		$isValid = parent::isValid($data);
		
		//Authenticate.
		if($isValid )
		{
			$model = new Model_User();
			if( ! $model->authenticate($data['email'], $data['password']) )
			{
				$isValid = false;
			}
			
		}
		
		return $isValid; 
	}
	
}