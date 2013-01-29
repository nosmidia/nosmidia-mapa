<?php

class Form_SignUp extends Zend_Form
{
	
	public function init()
	{
		
		$validate_password_confirmation = new Project_Validate_PasswordConfirmation();
		
		 //nome do formulário
        $this->setName('sign-up');
        
        //elemento para o campo username
        $name = new Zend_Form_Element_Text('name');
        //configurar o label, dizer q é obrigatório, adicionar um filtro e um validador
        $name->setLabel('Nome')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
        
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
            ->addValidator($validate_password_confirmation)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
        
        //elemento para confirmacao de senha    
        $password_confirm = new Zend_Form_Element_Password('password_confirm');
        $password_confirm->setLabel('Confirme sua senha')
            ->setRequired(true)
            ->addValidator($validate_password_confirmation)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Cadastrar');
        $submit->setAttrib('id', 'submit-sign-up') 
        		->setAttrib('class', 'btn')
            	->setIgnore(true);

        //exemplo de class css
        //$submit->setAttrib('class', 'verde buttonBar');
        //adicionar os campos ao formulário
        $this->addElements(array($name,$email, $password,$password_confirm, $submit));
        //action e method
        $this->setAction('/usuario/cadastro')->setMethod('post')->setElementDecorators(array(
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
		$emailValid = false;
		
		if(!empty($data['email']))
		{
			$model = new Model_User();
			$emailValid = (!$model->exists($data['email'], 'email'));
		}
		
		return ($isValid && $emailValid); 
	}
	
}