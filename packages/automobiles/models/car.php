<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'automobiles');
class CarModel extends BasicCRUDModel {
	
	protected $table = 'AutomobileCars';
	
	public function getById($id) {
		$sql = "SELECT car.*, body_type.name AS body_type_name, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN AutomobileBodyTypes body_type ON body_type.id = car.bodyTypeId"
		     . " INNER JOIN AutomobileManufacturers manufacturer ON manufacturer.id = car.manufacturerId"
		     . " WHERE car.{$this->pkid} = ?"
		     . " LIMIT 1";
		$vals = array($id);
		return $this->db->GetRow($sql, $vals);
	}
	
	public function getByBodyTypeId($bodyTypeId) {
		$sql = "SELECT car.*, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN AutomobileManufacturers manufacturer ON manufacturer.id = car.manufacturerId";
		$vals = array();
		
		if (!empty($bodyTypeId)) {
			$sql .= " WHERE car.bodyTypeId = ?";
			$vals[] = intval($bodyTypeId);
		}
		
		$sql .= " ORDER BY car.name";
		
		return $this->db->GetArray($sql, $vals);
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'bodyTypeId' => 'Body Type',
			'manufacturerId' => 'Manufacturer',
			'year' => 'Model Year',
			'name' => 'Name',
			'description' => 'Description',
			'photoFID' => 'Photo',
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
		private function saveColors($carId, $post) {
			$sql = "DELETE FROM AutomobileCarColors WHERE carId = ?";
			$vals = array($carId);
			$this->db->Execute($sql, $vals);
		
			$color_ids = empty($post['colorIds']) ? array() : $post['colorIds'];
			$stmt = $this->db->Prepare("INSERT INTO AutomobileCarColors (carId, colorId) VALUES (?, ?)");
			foreach ($color_ids as $colorId) {
				$vals = array($carId, intval($colorId));
				$this->db->Execute($stmt, $vals);
			}
		}
	/* end save */
	
	public function delete($id) {
		parent::delete($id);
		
		//delete car's color associations
		$sql = "DELETE FROM AutomobileCarColors WHERE carId = ?";
		$vals = array($id);
		$this->db->Execute($sql, $vals);
	}
	
	
}