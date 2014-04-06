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
		$body_type_model = BodyTypeModel::factory();
		$body_type_id = empty($_GET['body_type']) ? 0 : ( $body_type_model->exists($_GET['body_type']) ? intval($_GET['body_type']) : 0 );
		$this->set('body_type_id', $body_type_id);
		$this->set('body_type_options', $body_type_model->getSelectOptions(array(0 => '&lt;Choose One&gt;')));
		
		$this->set('cars', CarModel::factory()->getByBodyTypeId($body_type_id));
	}
	
	public function add($body_type_id = null) {
		$this->edit(null, $body_type_id);
	}
	
	public function edit($id = null, $parent_id = null) { //2nd arg is for adding new records only
		$model = CarModel::factory();
		
		//This function serves several purposes:
		// * Display "add new" form
		// * Display "edit existing" form
		// * Process submitted form (validate data and save to db)
		//
		//We can determine which action to take based on a combination of
		// whether or not valid data was POST'ed, and whether or not an $id was provided...
		if ($_POST) {
			$error = $model->validate($_POST);
			$result = $error->has() ? 'error' : 'success';
		} else {
			$result = empty($id) ? 'add' : 'edit';
		}
		
		
		//form was submitted and data is valid -- save to db and redirect...
		if ($result == 'success') {
			$id = $model->save($_POST);
			$this->flash('Car Saved!');
			if($_POST['save']) { //only redirect to list if save is clicked
				$this->redirect("view?body_type={$_POST['body_type_id']}");
			} elseif ($_POST['add-new']) {
				$this->redirect("add/{$_POST['body_type_id']}");
			} elseif ($_POST['duplicate']) { //sets colors for duplicated item, but continues script
				$this->set('colors', ColorModel::factory()->getAllWithCar($id)); //populate the 'colors' checkbox list with previous choices checked
			}		
		
		//form was submitted with invalid data -- display errors and repopulate form fields with user's submitted data...
		} else if ($result == 'error') {
			$this->set('error', $error); //C5 automagically displays these errors for us in the view
			
			//C5 form helpers will automatically repopulate form fields from $_POST data,
			// but we need to manually repopulate any data that isn't in $_POST,
			// or data that is used in places other than form fields...
			
			$this->set('body_type_id', $this->post('body_type_id', $parent_id)); //for the 'add' form action and the cancel button
			
			//Populate the 'colors' checkbox list with user's submitted choices checked
			$colors = ColorModel::factory()->getAll();
			$chosen_color_ids = $this->post('color_ids', array());
			foreach ($colors as $key => $color) {
				$colors[$key]['has'] = in_array($color['id'], $chosen_color_ids);
			}
			$this->set('colors', $colors);
			
		
		//form was not submitted, user wants to add a new record -- populate any form fields that should have default values...
		} else if ($result == 'add') {
			$this->set('body_type_id', $parent_id); //for the form action and the cancel button
			$this->set('colors', ColorModel::factory()->getAll()); //populate the 'colors' checkbox list with nothing checked
		
		
		//form was not submitted, user wants to edit an existing record -- populate form fields with db data...
		} else if ($result == 'edit') {
			$record = $model->getById($id);
			if (!$record) {
				$this->render404AndExit();
			}
			
			$this->setArray($record); //sets variables for every field in $record
			
			$this->set('colors', ColorModel::factory()->getAllWithCar($id)); //populate the 'colors' checkbox list with saved choices checked
		}
		
		//now populate data that is the same regardless of the action taken...
		//if duplicating data, force this to be empty
		$this->set('id', ($_POST['duplicate'] ? null: $id));
		$this->set('body_type_options', BodyTypeModel::factory()->getSelectOptions(array(0 => '&lt;Choose One&gt;')));
		$this->set('manufacturer_options', ManufacturerModel::factory()->getSelectOptions(array(0 => '&lt;Choose One&gt;')));
		$this->set('currency_symbol', Package::getByHandle('automobiles')->config('currency_symbol'));
		
		//finally, display the form with the data we populated above
		$this->render('edit');
	}
	
	
	public function delete($id) {
		if (empty($id) || !intval($id)) {
			$this->render404AndExit();
		}
		
		$model = CarModel::factory();
		
		$record = $model->getById($id);
		if (!$record) {
			$this->render404AndExit();
		}
		
		if ($this->post()) {
			$model->delete($id);
			$this->flash('Car Deleted.');
			$this->redirect("view?body_type={$record['body_type_id']}");
		}
		
		$this->setArray($record);
		
		$this->render('delete');
	}
	
}
