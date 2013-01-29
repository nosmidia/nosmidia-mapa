<?php

class Form_Admin_Category extends Zend_Form
{
	
	public function init()
	{
		 //nome do formulário
        $this->setName('admCategory');
        //elemento para o campo username
        $category = new Zend_Form_Element_Text('category');
        //configurar o label, dizer q é obrigatório, adicionar um filtro e um validador
        $category->setLabel('Categoria')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty');
            
        //Categoria Pai
       	$parent_id = new Zend_Form_Element_Select('parent_id');
        $options = Model_Category::fetchAllCategories('parent_id = 0');
        $parent_id->addMultiOption(0, 'Escolha...');
        foreach($options as $key => $value )
        	$parent_id->addMultiOption($value->id, $value->category);
        $parent_id->setLabel('Categoria Pai')
        	 ->setRequired(false)
             ->addValidator('NotEmpty');     
            
            
       	$icon = new Zend_Form_Element_File('icon_file');
       	$icon->setLabel('Icone do menu');
		// limite de tamanho
        $icon->addValidator('Size', false, 1024000);
		// extensões: JPEG, PNG, GIFs
        $icon->addValidator('Extension', false, 'png');
        $icon->setRequired(false);
        
        $marker = new Zend_Form_Element_File('marker');
       	$marker->setLabel('Marcador do mapa');
		// limite de tamanho
        $marker->addValidator('Size', false, 1024000);
		// extensões: JPEG, PNG, GIFs
        $marker->addValidator('Extension', false, 'png');
        $marker->setRequired(false);
        
        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Cadastrar');
        $submit->setAttrib('id', 'Entrar')
            	->setIgnore(true);

        //exemplo de class css
        //$submit->setAttrib('class', 'verde buttonBar');
        //adicionar os campos ao formulário
        $this->addElements(array($category,$parent_id, $icon, $marker, $submit));
        //action e method
        $this->setAction('/adm/categorias/add')->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');
	}	
	
	public function isValid($data)
	{
		//Validaçao do Formulário
		$isValid = parent::isValid($data);
		
		if(!$isValid)
		{
			$elements = $this->getElements();
			foreach($elements as $element)
			{
				$element->removeDecorator('Errors');
			}
		}
		
		return $isValid;
	}
	
	public function populate($values)
	{
		if($values['category'])
    	{
			$values['category'] = stripslashes($values['category']);
       	}
       	parent::populate($values);
	}
}