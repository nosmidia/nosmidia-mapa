<?php

class Model_Entity_Category extends Zend_Db_Table_Row
{
	
    public function __get($columnName)
    {
    	$data = parent::__get($columnName);
    	
    	if($columnName == 'category')
    	{
			$data = stripslashes($data);
       	}
    	
       	return $data;
    }
}