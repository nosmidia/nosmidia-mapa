<?php

class Form_LoginAdmin extends Zend_Form
{
	
	public function init()
	{
		 //nome do formulário
        $this->setName('LoginAdmin');
        //elemento para o campo username
        $username = new Zend_Form_Element_Text('email');
        //configurar o label, dizer q é obrigatório, adicionar um filtro e um validador
        $username->setLabel('Email')
        	->setAttrib('class', 'input-text')	
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator(new Zend_Validate_EmailAddress())
            ->addValidator('NotEmpty');
        //elemento para a senha
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Senha')
        	->setAttrib('class', 'input-text')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Entrar');
        $submit->setAttrib('id', 'Entrar')
            	->setIgnore(true);

        //exemplo de class css
        //$submit->setAttrib('class', 'verde buttonBar');
        //adicionar os campos ao formulário
        $this->addElements(array($username, $password, $submit));
        //action e method
        $this->setAction('/admin')->setMethod('post');
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
			$model = new Model_Admin();
			if( ! $model->authenticate($data['email'], $data['password']) )
			{
				$isValid = false;
			}
			
		}
		
		if(!$isValid)
		{
			$this->getElement('email')->removeDecorator('Errors');
			$this->getElement('password')->removeDecorator('Errors');

		}
		
		return $isValid;
	}
	
}