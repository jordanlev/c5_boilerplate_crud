<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'boilerplate_crud');
class CategoryModel extends BasicCRUDModel {
	
	protected $table = 'BoilerplateCrudCategories';
	
	public function getCategories() {
		$sql = "SELECT * FROM BoilerplateCrudCategories ORDER BY name";
		return $this->db->GetArray($sql);
	}
	
	public function getCategoryForWidget($widgetId) {
		$sql = "SELECT c.* FROM BoilerplateCrudCategories c INNER JOIN BoilerplateCrudWidgets w ON c.id = w.categoryId WHERE w.id = ?";
		$vals = array($widgetId);
		return $this->db->query($sql, $vals)->fetchRow();
	}
	
	public function validate($post) {
		Loader::library('kohana_validation', 'boilerplate_crud');
		$v = new KohanaValidation($post);
		
		$v->add_rule('name', 'required', 'Name is required.');
		$v->add_rule('name', 'length[0,255]', 'Name cannot exceed 255 characters in length.');
		$v->add_callback('name', array($this, 'validateUniqueName'), 'Name is already in use.');
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	//Example of a custom callback validation for the KohanaValidation library.
	//Note that when registering this custom validation rule you must use add_callback()
	// instead of add_rule(), because we may need values from more than one field
	// (i.e. when validating an existing record, we also need the id so we don't consider ourself a dup).
	public function validateUniqueName(KohanaValidation $array, $field_name) {
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$field_name} = ?";
		$vals = array($array[$field_name]);
		if (!empty($array[$this->pkid])) { //Ignore ourself if this is an UPDATE
			$sql .= " AND {$this->pkid} <> ?";
			$vals[] = $array[$this->pkid];
		}
		$count = $this->db->GetOne($sql, $vals);

		if ($count > 0) {
			$array->add_error($field_name, 'validateUniqueName'); //2nd arg MUST match the function name
		}
	}
	
	public function delete($id) {
		$sql = "DELETE FROM BoilerplateCrudWidgets WHERE categoryId = ?";
		$vals = array($id);
		$this->db->Execute($sql, $vals);
		
		parent::delete($id);
	}

}