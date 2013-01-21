<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('car', 'automobiles');
Loader::model('color', 'automobiles');
Loader::model('body_type', 'automobiles');
Loader::model('manufacturer', 'automobiles');

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardAutomobilesCarsController extends CrudController {

	public function on_before_render() {
		//Load javascript and css into <head> of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers)
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->javascript('dashboard.js', 'automobiles'));
		$this->addHeaderItem($hh->css('dashboard.css', 'automobiles'));
	}
	
	public function view() {
		$btm = $this->model('body_type');
		$body_type_id = empty($_GET['type']) ? 0 : ( $btm->exists($_GET['type']) ? intval($_GET['type']) : 0 );
		$this->set('body_type_id', $body_type_id);
		$this->set('body_type_options', $btm->getSelectOptions());
		
		$this->set('cars', $this->model('car')->getByBodyTypeId($body_type_id));
	}
	
	public function add($body_type_id) {
		$this->edit(null, $body_type_id);
	}
	
	public function edit($id = null, $parent_id = null) { //2nd arg is for adding new records only
		//process the form
		$result = $this->process_edit_form($id, $this->model('car'));
		
		if ($result == 'success') {
			$this->flash('Car Saved!');
			$this->redirect("view?type={$_POST['bodyTypeId']}");
			
		} else if ($result == 'error') {
			$this->set('bodyTypeId', $parent_id);
			
			//Manually repopulate the checkbox list
			$colors = $this->model('color')->getAll();
			$chosen_color_ids = $this->post('colorIds', array());
			foreach ($colors as $key => $color) {
				$colors[$key]['has'] = in_array($color['id'], $chosen_color_ids);
			}
			
		} else if ($result == 'add') {
			$this->set('bodyTypeId', $parent_id);
			$colors = $this->model('color')->getAll();
			
		} else if ($result == 'edit') {
			$colors = $this->model('color')->getAllWithCar($id);
			
		}
		
		//populate data
		$this->set('colors', $colors);
		$this->set('body_type_options', $this->model('body_type')->getSelectOptions());
		$this->set('manufacturer_options', $this->model('manufacturer')->getSelectOptions());
		
		//display the form
		$this->render('edit');
	}
	
	
	public function delete($id) {
	//Note that we don't need to check for empty $id, because it's
	// a required function arg (so an error is thrown if it's missing).
	
		$model = $this->model('car');
		
		$record = $model->getById($id);
		if (!$record) {
			$this->render404AndExit();
		}
		
		if ($this->post()) {
			$model->delete($id);
			$this->flash('Car Deleted.');
			$this->redirect("view?type={$record['bodyTypeId']}");
		}
		
		$this->setArray($record);
		
		$this->render('delete');
	}
	
}