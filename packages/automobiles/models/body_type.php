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
		$v->add_callback('url_slug', array($this, 'validate_url_slug_uniqueness'), 'URL Slug is already in use by another record.');

		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	} //Validation custom rules...
		public function validate_url_slug_format($value) {
			return !preg_match('/[^a-z0-9_-]/', $value);
		}
	
		public function validate_url_slug_uniqueness(KohanaValidation $v, $field_name) {
			$sql = "SELECT COUNT(*) FROM {$this->table} WHERE url_slug = ?";
			$vals = array($v['url_slug']);
	
			//if this is an UPDATE (as opposed to an INSERT), ignore this record's own url slug
			if (!empty($v[$this->pkid])) {
				$sql .= " AND {$this->pkid} <> ?";
				$vals[] = $v[$this->pkid];
			}
	
			$count = $this->db->GetOne($sql, $vals);
			if ($count) {
		        $v->add_error($field_name, 'validate_url_slug_uniqueness'); //2nd arg MUST match the function name
			}
		}
	//END Validation
	
	public function hasChildren($id) {
		$sql = "SELECT COUNT(*) FROM automobile_cars WHERE body_type_id = ?";
		$vals = array((int)$id);
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
}
