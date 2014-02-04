<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_model', 'automobiles');
class CarModel extends BasicCRUDModel {
	
	protected $table = 'automobile_cars';
	
	public function getById($id) {
		$sql = "SELECT car.*, body_type.name AS body_type_name, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN automobile_body_types body_type ON body_type.id = car.body_type_id"
		     . " INNER JOIN automobile_manufacturers manufacturer ON manufacturer.id = car.manufacturer_id"
		     . " WHERE car.{$this->pkid} = ?"
		     . " LIMIT 1";
		$vals = array((int)$id);
		return $this->db->GetRow($sql, $vals);
	}
	
	public function getByBodyTypeId($body_type_id) {
		$sql = "SELECT car.*, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN automobile_manufacturers manufacturer ON manufacturer.id = car.manufacturer_id";
		$vals = array();
		
		if (!empty($body_type_id)) {
			$sql .= " WHERE car.body_type_id = ?";
			$vals[] = (int)$body_type_id;
		}
		
		$sql .= " ORDER BY car.name";
		
		return $this->db->GetArray($sql, $vals);
	}
	
	public function getByBodyTypeUrlSlug($body_type_url_slug) {
		$sql = "SELECT car.*, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN automobile_manufacturers manufacturer ON manufacturer.id = car.manufacturer_id"
		     . " INNER JOIN automobile_body_types body_type ON body_type.id = car.body_type_id"
		     . " WHERE body_type.url_slug = ?"
		     . " ORDER BY car.name";
		$vals = array($body_type_url_slug);
		
		return $this->db->GetArray($sql, $vals);
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'body_type_id' => 'Body Type',
			'manufacturer_id' => 'Manufacturer',
			'year' => 'Model Year',
			'name' => 'Name',
			'description' => 'Description',
			'photo_fID' => 'Photo',
			'price' => 'Price',
		));
		
		$v->add_rule('year', 'inrange[1900,2200]', 'Model Year must be a 4-digit year.');
		
		$v->pre_filter(array($this, 'filter_strip_commas'), 'price'); //watch out -- param order is reverse of add_rule()!
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	public function filter_strip_commas($value) {
		return str_replace(',', '', $value);
	}
	
	public function save($post) {
		$id = parent::save($post);
		$this->saveColors($id, $post);
		return $id;
	}
		private function saveColors($car_id, $post) {
			$sql = "DELETE FROM automobile_car_colors WHERE car_id = ?";
			$vals = array((int)$car_id);
			$this->db->Execute($sql, $vals);
		
			$color_ids = empty($post['color_ids']) ? array() : $post['color_ids'];
			$stmt = $this->db->Prepare("INSERT INTO automobile_car_colors (car_id, color_id) VALUES (?, ?)");
			foreach ($color_ids as $color_id) {
				$vals = array((int)$car_id, (int)$color_id);
				$this->db->Execute($stmt, $vals);
			}
		}
	/* end save */
	
	public function delete($id) {
		//delete car's color associations
		$sql = "DELETE FROM automobile_car_colors WHERE car_id = ?";
		$vals = array((int)$id);
		$this->db->Execute($sql, $vals);
		
		parent::delete($id);
	}
	
	
}