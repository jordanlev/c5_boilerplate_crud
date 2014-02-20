<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('car', 'automobiles');
Loader::model('color', 'automobiles');
Loader::model('body_type', 'automobiles');
Loader::model('manufacturer', 'automobiles');

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardAutomobilesMiscController extends CrudController {
	
	public function on_before_render() {
		//Load css into the <head> and javascript into the footer of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem/addFooterItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers [??])
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->css('dashboard.css', 'automobiles'));
		$this->addFooterItem($hh->javascript('dashboard.js', 'automobiles'));
	}
	
	public function view() {
		//this page just shows some links ot other pages, so we don't need to do anything here
	}
	
	
	
	
	/*** BODY TYPES **********************************************************/
	
	public function body_types_list() {
		$this->set('body_types', BodyTypeModel::factory()->getAll());
		$this->render('body_types/list');
	}
	
	public function body_types_add() {
		$this->body_types_edit(null);
	}
	
	public function body_types_edit($id = null) {
		$result = $this->processEditForm($id, BodyTypeModel::factory());
		if ($result == 'success') {
			$this->flash('Body Type Saved!');
			$this->redirect('body_types_list');
		}
		
		$this->render('body_types/edit');
	}
	
	public function body_types_sort() {
		if ($this->post()) {
			$ids = explode(',', $this->post('ids', ''));
			BodyTypeModel::factory()->setDisplayOrder($ids);
		}
		exit; //this is an ajax function, so no need to render anything
	}
	
	public function body_types_delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = new BodyTypeModel;
		
		if ($model->hasChildren($id)) {
			$this->set('error', 'This body type cannot be deleted because one or more cars is assigned to it.');
			$this->set('disabled', true);
		} else if ($this->post()) {
			$model->delete($id);
			$this->flash('Body Type Deleted.');
			$this->redirect('body_types_list');
		}
		
		$this->setArray($model->getById($id));
		
		$this->render('body_types/delete');
	}
	
	
	
	/*** COLORS **************************************************************/
	
	public function colors_list() {
		$this->set('colors', ColorModel::factory()->getAll());
		$this->render('colors/list');
	}
	
	public function colors_add() {
		$this->colors_edit(null);
	}
	
	public function colors_edit($id = null) {
		$result = $this->processEditForm($id, ColorModel::factory());
		if ($result == 'success') {
			$this->flash('Color Saved!');
			$this->redirect('colors_list');
		}
		
		$this->render('colors/edit');
	}
	
	public function colors_delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = new ColorModel;
		
		if ($model->hasChildren($id)) {
			$this->set('error', 'This color cannot be deleted because it is assigned to one or more cars.');
			$this->set('disabled', true);
		} else if ($this->post()) {
			$model->delete($id);
			$this->flash('Color Deleted.');
			$this->redirect('colors_list');
		}
		
		$this->setArray($model->getById($id));
		
		$this->render('colors/delete');
	}
	

	
	/*** MANUFACTURERS *******************************************************/
	
	public function manufacturers_list() {
		$this->set('manufacturers', ManufacturerModel::factory()->getAll());
		$this->render('manufacturers/list');
	}
	
	public function manufacturers_add() {
		$this->manufacturers_edit(null);
	}
	
	public function manufacturers_edit($id = null) {
		$result = $this->processEditForm($id, ManufacturerModel::factory());
		if ($result == 'success') {
			$this->flash('Manufacturer Saved!');
			$this->redirect('manufacturers_list');
		}
		
		//populate lookup data
		$this->set('country_options', array(
			'' => '&lt;Choose One&gt;',
			'Japan' => 'Japan',
			'Germany' => 'Germany',
			'USA' => 'USA',
			'Australia' => 'Australia',
		));
		
		$this->render('manufacturers/edit');
	}
	
	public function manufacturers_delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = new ManufacturerModel;
		
		if ($model->hasChildren($id)) {
			$this->set('error', 'This manufacturer cannot be deleted because it has one or more cars.');
			$this->set('disabled', true);
		} else if ($this->post()) {
			$model->delete($id);
			$this->flash('Manufacturer Deleted.');
			$this->redirect('manufacturers_list');
		}
		
		$this->setArray($model->getById($id));
		
		$this->render('manufacturers/delete');
	}
	
}