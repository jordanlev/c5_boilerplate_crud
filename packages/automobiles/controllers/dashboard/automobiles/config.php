<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//This controller is different than the others
// because it does *not* serve as an example of a CRUD interface.
//Instead, it is a more general-purpose controller that automatically
// displays and saves this package's config settings (which should have been
// declared in the pakcage controller's install() method).

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardAutomobilesConfigController extends CrudController {
	
	public function view() {
		$pkg = Package::getByHandle('automobiles');
		$configs = Config::getListByPackage($pkg);
		
		$post = $this->post();
		if ($post) {
			if ($this->validate($post, $configs)) {
				$this->save($pkg, $post, $configs);
				$this->flash('Configuration saved!', 'success');
				$this->redirect(''); //redirect to ourself
			} else {
				$this->set('error', 'Error: All configuration settings are required.');
			}
		}
		
		$this->set('configs', $configs);
		$this->addHeaderItem(Loader::helper('html')->css('dashboard.css', 'automobiles'));
	}

	private function validate($post, $configs) {
		foreach ($configs as $config) {
			if (empty($post[$config->key])) {
				return false;
			}
		}
		return true;
	}
	
	private function save($pkg, $post, $configs) {
		foreach ($configs as $config) {
			$pkg->saveConfig($config->key, $post[$config->key]);
		}
	}
}