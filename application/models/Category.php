<?php

class Model_Category extends Project_Db_Table_Abstract
{
	const TABLE_NAME = 'category';
	protected $_name = self::TABLE_NAME;
	protected $_rowClass = 'Model_Entity_Category';
	
	public static function fetchAllCategories($where = null, $order = null, $count = null, $offset = null)
	{
		$model = new Model_Category();
		
		return $model->fetchAll($where, $order, $count, $offset);
	}
	
	public static function getCategory( $category_id )
	{
		$model = new Model_Category();
		return $model->find($category_id);
	}
	
	public static function insertCategory( $data )
	{
		$model = new Model_Category();
		
		$fields = array();
		$fields['category']  = $data['category'];
		$fields['icon_file'] = $data['icon_file'];
		$fields['slug']		 = Project_View_Helper_SanitizeString::sanitizeString($data['category']); 
		$fields['parent_id'] = $data['parent_id'];
		
		//Slug unico
		$fields['slug'] = $model->incrementSlug($fields['slug'], 'slug');

		return $model->insert($fields);
	}
	
	public static function updateCategory( $data, $where )
	{
		$model = new Model_Category();
		
		$fields = array();
		$fields['category']  = $data['category'];
		$fields['icon_file'] = $data['icon_file'];
		$fields['slug']		 = Project_View_Helper_SanitizeString::sanitizeString($data['category']); 
		$fields['parent_id'] = 0;
		
		//Slug único
		$fields['slug'] = $model->incrementSlug($fields['slug'], 'slug',$data['id'], 'id');

		return $model->update($fields, $where);
	}
	
	public static function deleteCategory( $id )
	{
		$result = null;
		
		$model = new Model_Category();
		
		$category = $model->find($id)->current();
		if($category)
		{
			//TODO: Update em todos os pontos para categoria ZERO
			
			self::deleteIcon($category->icon_file);
			
			$where = sprintf("id= %d", $id);
			$result = $model->delete($where);
		}

		return $result;
	}
	
	public static function uploadIcon( $uploaded_file )
	{
		
		$result = null;
		
		$upload = new Project_Upload($uploaded_file);
		if ($upload->uploaded) 
		{
			$upload->file_new_name_body = uniqid('mapicon_');
		  	$upload->dir_auto_create    = true;
			
			$upload->process(UPLOAD_PATH . '/icon_file');
			if ($upload->processed) {
		    	$result = $upload->file_dst_name;
		  	}
		}
		return $result;
	}
	
	public static function uploadMarker( $uploaded_file, $filename)
	{
		$filename = substr($filename, 0,strrpos($filename,'.'));
		
		$result = null;
		
		$upload = new Project_Upload($uploaded_file);
		if ($upload->uploaded) 
		{
			$upload->file_new_name_body = $filename;
		  	$upload->dir_auto_create    = true;
			
			$upload->process(UPLOAD_PATH . '/marker');
			if ($upload->processed) {
		    	$result = $upload->file_dst_name;
		  	}
		}
		return $result;
	}
	
	public static function deleteIcon( $icon )
	{
		$path = UPLOAD_PATH . '/icon_file/'. $icon;
		if( file_exists($path) && is_file($path) )
		{
			unlink($path);
		}
	}
	
	public static function deleteMarker( $icon )
	{
		$path = UPLOAD_PATH . '/marker/'. $icon;
		if( file_exists($path) && is_file($path) )
		{
			unlink($path);
		}
	}
	
	public static function iconUrl( $icon_file )
	{
		return URL . '/uploads/icon_file/'. $icon_file;
	}
	
	public static function markerUrl( $icon_file )
	{
		return URL . '/uploads/marker/'. $icon_file;
	}
	
    
}