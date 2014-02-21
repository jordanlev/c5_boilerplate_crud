<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_model', 'automobiles');
class BodyTypeModel extends SortableCRUDModel {
	
	protected $table = 'automobile_body_types';
	
	//use this for one-off's to reduce a line of code -- e.g. BodyTypeModel::factory()->getAll()
	public static function factory() {
		return new BodyTypeModel;
	}
	
	public function getSelectOptions($header_item = array()) {
		return $this->selectOptionsFromArray($this->getAll(), 'id', 'name', $header_item);
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'name' => 'Name',
			'url_slug' => 'URL Slug',
		));
		// Don't validate displayOrder -- that's handled separately (not during a normal save() operation).
		
		$v->add_rule('url_slug', array($this, 'validate_url_slug_format'), 'URL Slug can only contain lowercase letters, numbers, dashes, and underscores');
		$v->add_callback('url_slug', array($this, 'validate_uniqueness'), 'URL Slug is already in use by another record.');

		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}
	
	//Custom validation rules (must be public so they can be called via add_rule/add_callback/pre_filter)...
		public function validate_url_slug_format($value) {
			return !preg_match('/[^a-z0-9_-]/', $value);
		}
	
		public function validate_uniqueness(KohanaValidation $v, $field_name) {
			$sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$field_name} = ?";
			$vals = array($v[$field_name]);
	
			//if this is an UPDATE (as opposed to an INSERT), ignore this record's existing value
			if (!empty($v[$this->pkid])) {
				$sql .= " AND {$this->pkid} <> ?";
				$vals[] = (int)$v[$this->pkid];
			}
	
			$count = $this->db->GetOne($sql, $vals);
			if ($count) {
				$v->add_error($field_name, __FUNCTION__);
			}
		}
	//END Custom validation rules
	
	public function hasChildren($id) {
		$sql = "SELECT COUNT(*) FROM automobile_cars WHERE body_type_id = ?";
		$vals = array((int)$id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}
