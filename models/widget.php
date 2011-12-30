<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'boilerplate_crud');
class WidgetModel extends BasicCRUDModel {
	
	protected $table = 'BoilerplateCrudWidgets';
	
	public function getCategoryWidgets($categoryId) {
		$sql = "SELECT * FROM BoilerplateCrudWidgets WHERE categoryId = ? ORDER BY name";
		$vals = array($categoryId);
		return $this->db->GetArray($sql, $vals);
	}
	
	public function validate($post) {
		Loader::library('kohana_validation', 'boilerplate_crud');
		$v = new KohanaValidation($post);
		
		$v->add_rule('name', 'required', 'Name is required.');
		$v->add_rule('name', 'length[0,255]', 'Name cannot exceed 255 characters in length.');
		$v->add_rule('rating', 'numeric', 'Rating must be a number.');
		$v->add_rule('categoryId', 'required', 'Error: Missing Category!'); //this shouldn't happen
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
}