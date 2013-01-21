<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'automobiles');
class ColorModel extends BasicCRUDModel {
	
	protected $table = 'AutomobileColors';
	
	public function getAll() {
		$sql = "SELECT * FROM {$this->table} ORDER BY name";
		return $this->db->GetArray($sql);
	}
	
	//returns *all* color records, with an additional column called 'has'
	// that denotes if the given car has the color or not
	public function getAllWithCar($carId) {
		$sql = "SELECT *, (SELECT COUNT(*) FROM AutomobileCarColors AS cc WHERE cc.carId = ? AND cc.colorId = c.id) AS has FROM {$this->table} AS c ORDER BY name";
		$vals = array($carId);
		return $this->db->GetArray($sql, $vals);
	}
	
	public function getSelectOptions() {
		return $this->selectOptionsFromArray($this->getAll(), 'id', 'name', array(0 => '&lt;Choose One&gt;'));
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
		$sql = "SELECT COUNT(*) FROM AutomobileCarColors WHERE colorId = ?";
		$vals = array($id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}