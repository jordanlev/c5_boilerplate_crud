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
	
	public function body_types_duplicate($duplicate_id) {
		if (empty($duplicate_id) || !intval($duplicate_id)) {
			$this->render404AndExit();
		}
		$this->body_types_edit(null, $duplicate_id);
	}
	
	public function body_types_edit($id = null, $duplicate_id = null) { //2nd arg is for duplicating existing records only
		$model = BodyTypeModel::factory();
		
		//This function serves several purposes:
		// * Display "add new" form
		// * Display "edit existing" form
		// * Process submitted form (validate data and save to db)
		//
		//We can determine which action to take based on a combination of
		// whether or not valid data was POST'ed and whether or not an $id was provided...
		if ($_POST) {
			$error = $model->validate($_POST);
			$result = $error->has() ? 'error' : 'success';
		} else {
			$result = (empty($id) && empty($duplicate_id)) ? 'add' : 'edit';
		}
		
		
		//form was submitted and data is valid -- save to db and redirect...
		if ($result == 'success') {
			$id = $model->save($_POST);
			
			$this->flash('Body Type Saved!');
			
			if (!empty($_POST['save-and-add'])) {
				$this->redirect('body_types_add');
			} else if (!empty($_POST['save-and-duplicate'])) {
				$this->redirect('body_types_duplicate', $id);
			} else {
				$this->redirect('body_types_list');
			}
		
		
		//form was submitted with invalid data -- display errors and repopulate form fields with user's submitted data...
		} else if ($result == 'error') {
			$this->set('error', $error); //C5 automagically displays these errors for us in the view
			
			//C5 form helpers will automatically repopulate form fields from $_POST data,
			// but we need to manually repopulate any data that isn't in $_POST,
			// or data that is used in places other than form fields...
			
			//[nothing needs to be done here]
		
			
		//form was not submitted, user wants to add a new record -- populate any form fields that should have default values...
		} else if ($result == 'add') {
			
			//[nothing needs to be done here]
		
		
		//form was not submitted, user wants to edit or duplicate an existing record -- populate form fields with db data...
		} else if ($result == 'edit') {
			$retrieve_id = empty($duplicate_id) ? $id : $duplicate_id;
			$record = $model->getById($retrieve_id);
			if (!$record) {
				$this->render404AndExit();
			}
			
			$this->setArray($record); //sets variables for every field in $record
		}
		
		//now populate data that is the same regardless of the action taken...
		$this->set('id', $id);
		
		//finally, display the form with the data we populated above
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
		
		$model = BodyTypeModel::factory();
		
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
	
	public function colors_duplicate($duplicate_id) {
		if (empty($duplicate_id) || !intval($duplicate_id)) {
			$this->render404AndExit();
		}
		$this->colors_edit(null, $duplicate_id);
	}
	
	public function colors_edit($id = null, $duplicate_id = null) { //2nd arg is for duplicating existing records only
		$model = ColorModel::factory();
		
		//This function serves several purposes:
		// * Display "add new" form
		// * Display "edit existing" form
		// * Process submitted form (validate data and save to db)
		//
		//We can determine which action to take based on a combination of
		// whether or not valid data was POST'ed and whether or not an $id was provided...
		if ($_POST) {
			$error = $model->validate($_POST);
			$result = $error->has() ? 'error' : 'success';
		} else {
			$result = (empty($id) && empty($duplicate_id)) ? 'add' : 'edit';
		}
		
		
		//form was submitted and data is valid -- save to db and redirect...
		if ($result == 'success') {
			$id = $model->save($_POST);
			
			$this->flash('Color Saved!');
			
			if (!empty($_POST['save-and-add'])) {
				$this->redirect('colors_add');
			} else if (!empty($_POST['save-and-duplicate'])) {
				$this->redirect('colors_duplicate', $id);
			} else {
				$this->redirect('colors_list');
			}
		
		
		//form was submitted with invalid data -- display errors and repopulate form fields with user's submitted data...
		} else if ($result == 'error') {
			$this->set('error', $error); //C5 automagically displays these errors for us in the view
			
			//C5 form helpers will automatically repopulate form fields from $_POST data,
			// but we need to manually repopulate any data that isn't in $_POST,
			// or data that is used in places other than form fields...
			
			//[nothing needs to be done here]
		
			
		//form was not submitted, user wants to add a new record -- populate any form fields that should have default values...
		} else if ($result == 'add') {
			
			//[nothing needs to be done here]
		
		
		//form was not submitted, user wants to edit or duplicate an existing record -- populate form fields with db data...
		} else if ($result == 'edit') {
			$retrieve_id = empty($duplicate_id) ? $id : $duplicate_id;
			$record = $model->getById($retrieve_id);
			if (!$record) {
				$this->render404AndExit();
			}
			
			$this->setArray($record); //sets variables for every field in $record
		}
		
		//now populate data that is the same regardless of the action taken...
		$this->set('id', $id);
		
		//finally, display the form with the data we populated above
		$this->render('colors/edit');
	}
	
	public function colors_delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = ColorModel::factory();
		
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
	
	public function manufacturers_duplicate($duplicate_id) {
		if (empty($duplicate_id) || !intval($duplicate_id)) {
			$this->render404AndExit();
		}
		$this->manufacturers_edit(null, $duplicate_id);
	}
	
	public function manufacturers_edit($id = null, $duplicate_id = null) { //2nd arg is for duplicating existing records only
		$model = ManufacturerModel::factory();
		
		//This function serves several purposes:
		// * Display "add new" form
		// * Display "edit existing" form
		// * Process submitted form (validate data and save to db)
		//
		//We can determine which action to take based on a combination of
		// whether or not valid data was POST'ed and whether or not an $id was provided...
		if ($_POST) {
			$error = $model->validate($_POST);
			$result = $error->has() ? 'error' : 'success';
		} else {
			$result = (empty($id) && empty($duplicate_id)) ? 'add' : 'edit';
		}
		
		
		//form was submitted and data is valid -- save to db and redirect...
		if ($result == 'success') {
			$id = $model->save($_POST);
			
			$this->flash('Manufacturer Saved!');
			
			if (!empty($_POST['save-and-add'])) {
				$this->redirect('manufacturers_add');
			} else if (!empty($_POST['save-and-duplicate'])) {
				$this->redirect('manufacturers_duplicate', $id);
			} else {
				$this->redirect('manufacturers_list');
			}
		
		
		//form was submitted with invalid data -- display errors and repopulate form fields with user's submitted data...
		} else if ($result == 'error') {
			$this->set('error', $error); //C5 automagically displays these errors for us in the view
			
			//C5 form helpers will automatically repopulate form fields from $_POST data,
			// but we need to manually repopulate any data that isn't in $_POST,
			// or data that is used in places other than form fields...
			
			//[nothing needs to be done here]
		
			
		//form was not submitted, user wants to add a new record -- populate any form fields that should have default values...
		} else if ($result == 'add') {
			
			//[nothing needs to be done here]
		
		
		//form was not submitted, user wants to edit or duplicate an existing record -- populate form fields with db data...
		} else if ($result == 'edit') {
			$retrieve_id = empty($duplicate_id) ? $id : $duplicate_id;
			$record = $model->getById($retrieve_id);
			if (!$record) {
				$this->render404AndExit();
			}
			
			$this->setArray($record); //sets variables for every field in $record
		}
		
		//now populate data that is the same regardless of the action taken...
		$this->set('id', $id);
		
		$this->set('country_options', array(
			'' => '&lt;Choose One&gt;',
			'Japan' => 'Japan',
			'Germany' => 'Germany',
			'USA' => 'USA',
			'Australia' => 'Australia',
		));
		
		
		//finally, display the form with the data we populated above
		$this->render('manufacturers/edit');
	}
	
	public function manufacturers_delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = ManufacturerModel::factory();
		
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