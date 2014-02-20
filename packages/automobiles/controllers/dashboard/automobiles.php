<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//This controller serves as a placeholder for the top-level item in the C5 dashboard.

Loader::library('crud_controller', 'automobiles');
class DashboardAutomobilesController extends CrudController {
	
	public function view() {
		$this->redirect('cars');
	}
	
}
