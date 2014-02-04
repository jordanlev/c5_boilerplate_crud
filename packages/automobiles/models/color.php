<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_model', 'automobiles');
class ColorModel extends BasicCRUDModel {
	
	protected $table = 'automobile_colors';
	
	public function getAll() {
		$sql = "SELECT * FROM {$this->table} ORDER BY name";
		return $this->db->GetArray($sql);
	}
	
	//returns *all* color records, with an additional column called 'has'
	// that denotes if the given car has the color or not
	public function getAllWithCar($car_id) {
		$sql = "SELECT *, (SELECT COUNT(*) FROM automobile_car_colors AS cc WHERE cc.car_id = ? AND cc.color_id = c.{$this->pkid}) AS has FROM {$this->table} AS c ORDER BY name";
		$vals = array((int)$car_id);
		return $this->db->GetArray($sql, $vals);
	}
	
	public function getSelectOptions($header_item = array()) {
		return $this->selectOptionsFromArray($this->getAll(), 'id', 'name', $header_item);
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'name' => 'Name',
		));
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	public function hasChildren($id) {
		$sql = "SELECT COUNT(*) FROM automobile_car_colors WHERE color_id = ?";
		$vals = array((int)$id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}