<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('basic_crud_model', 'automobiles');
class CarModel extends BasicCRUDModel {
	
	protected $table = 'AutomobileCars';
	
	public function getAll() {
		$sql = "SELECT car.*, body_type.name AS body_type_name, color.name AS color_name, manufacturer.name AS manufacturer_name"
		     . " FROM {$this->table} car"
		     . " INNER JOIN AutomobileBodyTypes body_type ON body_type.id = car.bodyTypeId"
		     . " INNER JOIN AutomobileColors color ON color.id = car.colorId"
		     . " INNER JOIN AutomobileManufacturers manufacturer ON manufacturer.id = car.manufacturerId"
		     . " ORDER BY car.name";
		return $this->db->GetArray($sql);
	}
	
	public function validate(&$post) {
		Loader::library('kohana_validation', 'automobiles');
		$v = new KohanaValidation($post);
		
		$this->add_standard_rules($v, array(
			'bodyTypeId' => 'Body Type',
			'manufacturerId' => 'Manufacturer',
			'colorId' => 'Color',
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
}