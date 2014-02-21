<?php defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('car', 'automobiles');
Loader::model('body_type', 'automobiles');

class AutomobilesCarListBlockController extends BlockController {
		
	protected $btName = 'Car List';
	protected $btTable = 'btAutomobilesCarList';
	
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;
	
	public function add() {
		$this->edit();
	}
	
	public function edit() {
		$body_type_options = BodyTypeModel::factory()->getSelectOptions(array(0 => '&lt;Choose One&gt;'));
		$this->set('body_type_options', $body_type_options);
	}
	
	public function validate($args) {
		$e = Loader::helper('validation/error');
		
		if (empty($args['body_type_id'])) {
			$e->add('You must choose a body type');
		}
		
		return $e;
	}
	
	public function view() {
		$body_type = BodyTypeModel::factory()->getById($this->body_type_id);
		$cars = CarModel::factory()->getByBodyTypeId($this->body_type_id);
		
		$this->set('body_type', $body_type);
		$this->set('cars', $cars);
	}
	
}
