<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('car', 'automobiles');
Loader::model('color', 'automobiles');
Loader::model('body_type', 'automobiles');
Loader::model('manufacturer', 'automobiles');

Loader::library('crud_controller', 'automobiles'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardAutomobilesCarsController extends CrudController {

	public function on_before_render() {
		//Load css into the <head> and javascript into the footer of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem/addFooterItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers [??])
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->css('dashboard.css', 'automobiles'));
		$this->addFooterItem($hh->javascript('dashboard.js', 'automobiles'));
	}
	
	public function view() {
		$btm = $this->model('body_type');
		$body_type_id = empty($_GET['type']) ? 0 : ( $btm->exists($_GET['type']) ? intval($_GET['type']) : 0 );
		$this->set('body_type_id', $body_type_id);
		$this->set('body_type_options', $btm->getSelectOptions());
		
		$this->set('cars', $this->model('car')->getByBodyTypeId($body_type_id));
	}
	
	public function add($body_type_id = null) {
		$this->edit(null, $body_type_id);
	}
	
	public function edit($id = null, $parent_id = null) { //2nd arg is for adding new records only
		//process the form
		$result = $this->processEditForm($id, $this->model('car'));
		
		if ($result == 'success') {
			$this->flash('Car Saved!');
			$this->redirect("view?type={$_POST['body_type_id']}");
			
		} else if ($result == 'error') {
			$this->set('body_type_id', $parent_id);
			
			//Manually repopulate the checkbox list
			$colors = $this->model('color')->getAll();
			$chosen_color_ids = $this->post('color_ids', array());
			foreach ($colors as $key => $color) {
				$colors[$key]['has'] = in_array($color['id'], $chosen_color_ids);
			}
			
		} else if ($result == 'add') {
			$this->set('body_type_id', $parent_id);
			$colors = $this->model('color')->getAll();
			
		} else if ($result == 'edit') {
			$colors = $this->model('color')->getAllWithCar($id);
			
		}
		
		//populate data
		$this->set('colors', $colors);
		$this->set('body_type_options', $this->model('body_type')->getSelectOptions());
		$this->set('manufacturer_options', $this->model('manufacturer')->getSelectOptions());
		
		$this->set('currency_symbol', Package::getByHandle('automobiles')->config('currency_symbol'));
		//display the form
		$this->render('edit');
	}
	
	
	public function delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = $this->model('car');
		
		$record = $model->getById($id);
		if (!$record) {
			$this->render404AndExit();
		}
		
		if ($this->post()) {
			$model->delete($id);
			$this->flash('Car Deleted.');
			$this->redirect("view?type={$record['body_type_id']}");
		}
		
		$this->setArray($record);
		
		$this->render('delete');
	}
	
}