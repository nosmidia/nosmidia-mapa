<?php

class Form_Contact extends Zend_Form
{
	
	public function init()
	{
		//nome do formulário
        $this->setName('contact');
        
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
            
       	$message = new Zend_Form_Element_Textarea('message');
        $message->setLabel('Mensagem')
            ->setRequired(true)
            ->setAttrib('rows', '5')
            ->setAttrib('cols', '60')
            ->addValidator('NotEmpty');     
       
        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Enviar');
        $submit->setAttrib('id', 'submit-contact') 
        		->setAttrib('class', 'btn')
            	->setIgnore(true);

        //adicionar os campos ao formulário
        $this->addElements(array($name,$email, $message, $submit));
        //action e method
        $this->setAction('/contato/')->setMethod('post');
	}	
	
	
}