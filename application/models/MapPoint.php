<?php

class Model_MapPoint extends Project_Db_Table_Abstract
{
	const TABLE_NAME = 'map_point';
	protected $_primary	= 'id';
	protected $_name = self::TABLE_NAME;
	
	public static function fetchAllMapPoint($where = null, $order = null, $count = null, $offset = null)
	{
		$model_category = new Model_Category();
		$model_user		= new Model_User();
		$model_map_point= new Model_MapPoint();
		
		$select = $model_map_point->select();
		$select->setIntegrityCheck(false);
		$select->from(self::TABLE_NAME);
		$cond = sprintf('%s = %s', Model_User::TABLE_NAME.'.id', Model_MapPoint::TABLE_NAME.'.user_id');
		$select->join(Model_User::TABLE_NAME,$cond, array( 'name', 'email'));
		$cond = sprintf('%s = %s', Model_Category::TABLE_NAME.'.id', Model_MapPoint::TABLE_NAME.'.category_id');
		$select->join(Model_Category::TABLE_NAME,$cond,array('category', 'category-slug' => 'slug','icon_file', 'parent_id'));
		
		if($where)
			$select->where($where);
		if($order)
			$select->order($order);
		
		$select->limit( $count, $offset);
		
		return $model_map_point->fetchAll($select);
	}
	
	
	public static function insertMapPoint($data)
	{
		$model = new Model_MapPoint();
		
		$fields = array();
		$fields['address']	 		= $data['address'];
		$fields['neighborhood'] 	= $data['neighborhood'];
		$fields['city'] 			= $data['city'];
		$fields['state'] 			= $data['state'];
		$fields['category_id']		= $data['sub_category'];
		$fields['latitude']			= $data['latitude'];
		$fields['longitude']		= $data['longitude'];
		$fields['title']			= $data['title'];
		$fields['type']				= $data['type'];
		$fields['content']			= $data['content'];
		$fields['description']		= $data['description'];
		$fields['user_id']			= $data['user_id'];
		$fields['slug']				= Project_View_Helper_SanitizeString::sanitizeString($data['title']);
		//Slug unico
		$fields['slug'] 			= $model->incrementSlug($fields['slug'], 'slug');
		
		$fields['created_at']		= date('Y-m-d H:i:s');
		
		
		//Link do Youtube;
		if( $fields['type'] == 1 )
		{
			$fields['content'] = Project_Youtube::getYoutubeCode($fields['content']);
		}
		
		return $model->insert($fields);
	}
	
	public static function deleteMapPoint( $id )
	{
		$result = null;
		
		$model = new Model_MapPoint();
		
		$point = $model->find($id)->current();
		if($point)
		{
			$where = sprintf("%s.id= %d", Model_MapPoint::TABLE_NAME, $id);
			$result = $model->delete($where);
		}

		return $result;
	}
	
	public static function urlToLink($html)
	{
		$html = preg_replace('/\\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', "<a href=\"$0\" target=\"_blank\">\$0</a>", $html);
        return $html;
	}
}