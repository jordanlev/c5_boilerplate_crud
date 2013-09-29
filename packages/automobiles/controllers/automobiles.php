<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('car', 'automobiles');

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class AutomobilesController extends CrudController {
	
	public function on_before_render() {
		//Load css into the <head> and javascript into the footer of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem/addFooterItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers [??])
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->css('automobiles.css', 'automobiles'));
		$this->addFooterItem($hh->javascript('automobiles.js', 'automobiles'));
	}
	
	public function view($body_type_url_slug = null) {
		if (!empty($body_type_url_slug)) {
			$cars = $this->model('car')->getByBodyTypeUrlSlug($body_type_url_slug);
		} else {
			$cars = $this->model('car')->getAll();
		}
		
		$this->set('cars', $cars);
	}
	
}