<?php
class ModelExtensionModuleProductsAttributes extends Model {
	public function getProductsByAttribute($product_id, $attribute, $limit) {
		if(!empty($attribute['attribute_id']) && !empty($attribute['text'])) {
			$sql = "SELECT DISTINCT product_id FROM " . DB_PREFIX . "product_attribute WHERE attribute_id=" . (int)$attribute['attribute_id'] . " AND text='". $this->db->escape($attribute['text']) ."' AND product_id != " . (int)$product_id . " LIMIT " . (int)$limit . "";
			$results = $this->db->query($sql);
			return $results->rows;
		}
		return;
	}
}