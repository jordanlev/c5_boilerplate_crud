<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'automobiles');
class BodyTypeModel extends SortableCRUDModel {
	
	protected $table = 'automobile_body_types';
	
	public function getSelectOptions() {
		return $this->selectOptionsFromArray($this->getAll(), 'id', 'name', array(0 => '&lt;Choose One&gt;'));
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'name' => 'Name',
		));
		// Don't validate displayOrder -- that's handled separately (not during a normal save() operation).
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	public function hasChildren($id) {
		$sql = "SELECT COUNT(*) FROM automobile_cars WHERE body_type_id = ?";
		$vals = array($id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}
