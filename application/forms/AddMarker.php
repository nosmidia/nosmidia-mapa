<?php

class Form_AddMarker extends Zend_Form
{

	public function init()
	{
		//nome do formulário
        $this->setName('add-marker');

        //elemento para o campo Endereço
        $address = new Zend_Form_Element_Text('address');
        $address->setLabel('Endereço')
            ->setRequired(true)
            ->addValidator('NotEmpty');

        //elemento para o campo Bairro
        $neighborhood = new Zend_Form_Element_Text('neighborhood');
        $neighborhood->setLabel('Bairro')
            ->setRequired(true)
            ->addValidator('NotEmpty');

        //elemento para o campo Cidade
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('Cidade')
            ->setRequired(true)
            ->addValidator('NotEmpty');

        //elemento para o campo Estado
       	$state = new Zend_Form_Element_Select('state');
        $options = Project_StaticData::getBrasilianStates();
        $state->addMultiOption(null, 'Escolha...');
        foreach($options as $key => $value )
        	$state->addMultiOption($key, $value);
        $state->setLabel('Estado')
        	 ->setRequired(true)
             ->addValidator('NotEmpty');


        //elemento para o campo Bairro
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Título')
            ->setRequired(true)
            ->addValidator('NotEmpty');


        //elemento para o campo Tipo
        $type = new Zend_Form_Element_Select('type');
        $options = Project_StaticData::getMarkerContentType();
        //$type->addMultiOption(null, 'Escolha...');
        foreach($options as $key => $value )
        	$type->addMultiOption($key, $value);

        $type->setLabel('Tipo')
        	 ->setRequired(true)
             ->addValidator('NotEmpty');
		
		$content = new Zend_Form_Element_Textarea('content');
        $content->setLabel('Conteudo')
            ->setRequired(true)
            ->setAttrib('rows', '2')
            ->setAttrib('cols', '30')
            ->addValidator('NotEmpty');


       	//elemento para o campo Categoria
       	$category = new Zend_Form_Element_Select('category');
       	$options = Model_Category::fetchAllCategories('parent_id = 0');
       	$category->addMultiOption(null, 'Escolha...');
       	foreach($options as $key => $value )
        	$category->addMultiOption($value->id, $value->category);
       	$category->setLabel('Categoria')
        	 ->setRequired(true)
             ->addValidator('NotEmpty');
             
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Descrição')
            ->setRequired(false)
            ->setAttrib('rows', '2')
            ->setAttrib('cols', '30');    


        //botão de submit
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Prosseguir');
        $submit->setAttrib('id', 'submit-add-marker')
        		->setAttrib('class', 'btn')
            	->setIgnore(true);


        //adicionar os campos ao formulário
        $this->addElements(array($address,$neighborhood,$city,$state,$title, $type,$content,$category, $description, $submit));
        //action e method
        $this->setAction('/mapa/cadastro')->setMethod('post')->setElementDecorators(array(
            array('ViewHelper'),
            array('Errors', array('class' => 'invalid')),
            array('Label', array()),
        ));
        
        //Remove o decorator do Submit
        $element = $this->getElement('submit');
		$element->removeDecorator('label');
        
	}

	public static function getFromCache()
	{
		$cache = Zend_Registry::get('cache');
		$cache_name = 'Form_AddMarker';
		$form = $cache->load($cache_name);
   		if($form===false)
   		{
			$form = new Form_AddMarker();
			$cache->save($form,$cache_name);
   		}
   		return $form;
	}
	
	
	public function isValid($data)
	{
		//Validaçao do Formulário
		$isValid = parent::isValid($data);
		$isValidSubcatetory = false;

		if(isset($data['sub_category']))
		{
			$isValidSubcatetory = (int) $data['sub_category'];
		}
		
		return ($isValid && $isValidSubcatetory);
	}
	

}