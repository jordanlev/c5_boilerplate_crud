<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_model', 'automobiles');
class ManufacturerModel extends BasicCRUDModel {
	
	protected $table = 'automobile_manufacturers';
	
	public function getAll() {
		$sql = "SELECT * FROM {$this->table} ORDER BY name";
		return $this->db->GetArray($sql);
	}
	
	public function getSelectOptions() {
		return $this->selectOptionsFromArray($this->getAll(), 'id', 'name', array(0 => '&lt;Choose One&gt;'));
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'name' => 'Name',
			'country' => 'Country',
		));
		// Don't validate is_luxury -- it's a boolean / checkbox so it either exists or it doesn't.
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	public function hasChildren($id) {
		$sql = "SELECT COUNT(*) FROM automobile_cars WHERE manufacturer_id = ?";
		$vals = array($id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}
