<?php

class Project_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

	public function incrementSlug($value, $field, $exclude_id = null, $exclude_id_field = null)
    {
    	
    	$where = sprintf( '%s = "%s"', $field, $value);
    	
    	
    	if($exclude_id && $exclude_id_field )
    		$where = sprintf( '%s = "%s" AND %d != %d', $field, $value, $exclude_id , $exclude_id_field);
    	
       	$slug_check = $this->fetchRow($where);

        if($slug_check)
        {
            do{
            	$slug_check = (is_object($slug_check))?$slug_check->$field : $slug_check;
               	$arr_slug = explode('-', $slug_check);
                $num_slug = (int) end($arr_slug);
                if($num_slug){
                	$num_slug++;
                    $len = strlen($num_slug);
                    $value = substr($slug_check,0, -$len);
				}
                else
                {
                 	$num_slug = '-2';
                }
                
               	$value = $value . $num_slug;
                $where = sprintf( '%s = "%s"', $field, $value);
                
                if($exclude_id && $exclude_id_field )
    				$where = sprintf( '%s = "%s" AND %d != %d', $field, $value, $exclude_id , $exclude_id_field);
                
                $slug_check = $this->fetchRow($where);
                       
			}while($slug_check);
        }
        
        return $value;
       
    }
	
    
    public function exists($value, $field = null)
    {
        if(is_array($value)) {
            foreach($value as $k => $v) {
                if($this->checkExists($v, $k)) {
                    return true;
                }
            }
        }
        elseif(null === $field) {
            throw new Zend_Db_Exception(__METHOD__ . ' precisa de um parametro $field caso o $value seja string');
        }

        if(is_string($value)) {
            $value = "'" . $value . "'";
        }

        $db = $this->getAdapter();
        $select = $db->select();

        $rs = $this->fetchAll($field . ' = ' . $value);

        if($rs->count() > 0) {
            return $rs;
        }
        return false;
    }
    
    
    public function count( $where = null )
    {
    	$db = $this->getAdapter();
        $select = $db->select();

        $select->from( array($this->_name), array('count' => new Zend_Db_Expr('COUNT(*)') ));
		if($where)
		{
			$select->where($where);
		}
            
            
        $result = $db->fetchRow($select);
        return (int) $result['count'];
    }
}