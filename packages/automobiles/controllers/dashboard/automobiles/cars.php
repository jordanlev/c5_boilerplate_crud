<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('car', 'automobiles');
Loader::model('color', 'automobiles');
Loader::model('manufacturer', 'automobiles');

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardAutomobilesCarsController extends CrudController {

	public function on_start() {
		$this->model = new CarModel;
	}
	
	public function on_before_render() {
		//Load javascript and css into <head> of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers)
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->javascript('dashboard.js', 'automobiles'));
		$this->addHeaderItem($hh->css('dashboard.css', 'automobiles'));
	}
	
	public function view() {
		$this->set('cars', $this->model->getAll());
	}
	
	public function add() {
		$this->edit(null);
	}
	
	public function edit($id = null) {
		//process the form
		$result = $this->process_edit_form($id, $this->model);
		if ($result == 'success') {
			$this->flash('Car Saved!');
			$this->redirect('view');
		}
		
		//populate lookup data
		$colorModel = new ColorModel;
		$this->set('color_options', $colorModel->getSelectOptions());
		$manufacturerModel = new ManufacturerModel;
		$this->set('manufacturer_options', $manufacturerModel->getSelectOptions());
		
		//display the form
		$this->render('edit');
	}
	
	
	public function delete($id) {
	//Note that we don't need to check for empty $id, because it's
	// a required function arg (so an error is thrown if it's missing).
		
		if ($this->post()) {
			$this->model->delete($id);
			$this->flash('Car Deleted.');
			$this->redirect('view');
		}
		
		$record = $this->model->getById($id);
		$this->setArray($record);
		
		$this->render('delete');
	}
	
}