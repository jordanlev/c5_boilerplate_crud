<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//This controller serves as a placeholder for the top-level item in the C5 dashboard.

Loader::library('crud_controller', 'automobiles');
class DashboardAutomobilesController extends CrudController {
	
	public function view() {
		$this->redirect('cars');
	}
	
	//Utility method for refreshing the package schema
	// (go to http://localhost/your_site/dashboard/automobiles/refresh in your browser)
	public function refresh() {
		$u = new User;
		if ($u->isSuperUser() && Config::get('SITE_DEBUG_LEVEL')) {
			Package::getByHandle('automobiles')->upgrade();
			$this->flash('Package Schema Refreshed!');
			$this->view();
		}
	}
}
