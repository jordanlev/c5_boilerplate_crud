<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//Provides some improved convenience methods for our single_page controllers

class CrudController extends Controller {
	
	public function __construct() {
		parent::__construct();
		$this->initCSRFToken();
		$this->initFlash();
	}
	
	private function initCSRFToken() {
		$token = Loader::helper('validation/token');
		if (!empty($_POST) && !$token->validate()) {
			die($token->getErrorMessage());
		}
		$this->set('token', $token->output('', true));
	}
	
	private function initFlash() {
		$types = array('message', 'success', 'error');
		foreach ($types as $type) {
			$key = "flash_{$type}";
			if (!empty($_SESSION[$key])) {
				$this->set($type, $_SESSION[$key]); //C5 automagically displays 'message', 'success', and 'error' for us in dashboard views
				unset($_SESSION[$key]);
			}
		}
	}
	
	
	//Uses session to set 'message', 'success', or 'error' variable next time the page is loaded
	public function flash($text, $type = 'message') {
		$key = "flash_{$type}";
		$_SESSION[$key] = $text;
	}
	
	//Redirect to an action in this controller
	public function redirect($action) {
		//Do some fancy php stuff so we can accept and pass along
		// a variable number of args (anything after the $action arg).
		$args = func_get_args();
		array_unshift($args, $this->path());
		call_user_func_array(array('parent', 'redirect'), $args);
	}
	
	//Render a view file with the given name that exists in the single_pages
	// directory that corresponds with this controller's location / class name.
	//NOTE: Requires Concrete 5.5+ (or for 5.4 compatibility you could hack the core file concrete/libraries/view.php, as per https://github.com/concrete5/concrete5/pull/147/files)
	public function render($view) {
		$path = $this->path($view);
		parent::render($path);
	}
	
	//Return this controller's page path, with optional other things appended to it.
	//Note that a controller's path is wherever the single_page lives
	// in the sitemap -- not a specific action or view that is being displayed.
	public function path($append = '') {
		$path = $this->getCollectionObject()->getCollectionPath();
		if (!empty($append)) {
			$path .= '/' . $append;
		}
		return $path;
	}
	
	//Wrapper around View::url that always passes the controller's path as the url path
	// so you can call url('task', etc.) instead of url('path/to/controller', 'tasks', etc.).
	public function url($task = null) {
		//Do some fancy php stuff so we can accept and pass along
		// a variable number of args (anything after the $task arg).
		$args = func_get_args();
		array_unshift($args, $this->path());
		return call_user_func_array(array('View', 'url'), $args);
	}
	
	//Sets controller variables from an associative array (keys become variable names)
	//Optionally pass an array of key names that we should use (we'll ignore everything else).
	public function setArray($arr, $restrict_to_keys = array()) {
		$restrict_to_keys = empty($restrict_to_keys) ? array_keys($arr) : $restrict_to_keys;
		foreach ($restrict_to_keys as $key) {
			$this->set($key, $arr[$key]);
		}
	}
	
	//Renders the 404 page (and send appropriate http header).
	//Useful when the user hits an actual controller method,
	// but passes in the wrong id number or some other parameter makes the request invalid.
	public function render404() {
		header("HTTP/1.0 404 Not Found");
		parent::render('/page_not_found');
	}
	
	public function render404AndExit() {
		$this->render404();
		exit;
	}
	
	//processEditForm()
	//
	//Pass in the record id (or null for new records)
	// and the corresponding model object (which must extend BasicCRUDModel or SortableCRUDModel).
	//We check $_POST and do various things to it, then return a code that tells you the result.
	//
	// * If data has been POSTed, we validate the data (via model's validate() method).
	//    ->If validation succeeds, we save the data (via model's save() method),
	//      set the given $id to the record's id (useful for new records), and return code 'success'.
	//    ->If validation false, we send error messages to the view, and return code 'error'.
	// * If no data has been POSTed and the given $id is empty, we do nothing and return code 'add'.
	// * If no data has been POSTed and the given $id is not empty, we retrieve the record (via model's getById() method).
	//    -> If record is found, we send its data to the view and return code 'edit'.
	//    -> If no record is found for the given id, we render the 404 page and halt execution.
	//
	// Regardless of the result code, we also always call $this->set('id', $id) for you.
	//
	//WHAT YOU SHOULD DO WITH THE RETURNED RESULT CODE:
	// Under normal circumstances, the only result code you need to worry about is 'success',
	//  in which case you should set a flash message and redirect.
	// But occasionally you might need to do things for other result codes as well:
	//  -'add': initialize default form fields for new records (unless their default is empty/0, in which case you don't need to do anything)
	//  -'edit': populate data that doesn't come from the database record (unless it's common to all results -- see below)
	//  -'error': populate data that isn't in $_POST (unless it's common to all results -- see below)
	// (Note that the situations where you'd need to do something for 'edit' and 'error' are extremely rare
	//   [e.g. repopulating checkbox lists that represent many-to-many relationships, since C5 form helpers don't handle those]
	//   so don't worry about them until you run into a problem where it's obvious that's what you need to do!)
	//
	// Under most circumstances, you should also always do the following 2 things which are common to all results:
	// 1) set data that doesn't come from the database and isn't POSTed (e.g. dropdown list choices)
	// 2) call the render function to display the form
	//
	//EXAMPLE USAGE:
	// public function edit($id = null) {
	//     $model = new ThingyModel;
	//     
	//     $result = $this->processEditForm($id, $model);
	//     if ($result == 'success') {
	//         $this->flash('Thingy Saved!');
	//         $this->redirect('view');
	//     }
	//     
	//     $choice_options = array('0' => 'Choose One', '1' => 'First Choice', '2' => 'Second Choice', '3' => 'Third Choice');
	//     $this->set('choice_options', $choice_options);
	//     
	//     $this->render('edit');
	// }
	public function processEditForm(&$id, $model) {
		$this->set('id', $id);
		
		$post = $this->post();
		if ($post) {
			$error = $model->validate($post);
			if ($error->has()) {
				$this->set('error', $error); //C5 automagically displays these errors for us in the view
				//C5 form helpers will automatically repopulate form fields from $_POST data
				return 'error'; // caller should manually repopulate data that isn't in $_POST
			} else {
				$id = $model->save($post);
				return 'success'; // caller should set flash message and redirect
			}
		} else if (empty($id)) {
			return 'add'; // caller should initialize form fields that don't start out empty/0
		} else {
			//Populate form fields with existing record data
			$record = $model->getById($id);
			if (!$record) {
				$this->render404AndExit();
			}
			$this->setArray($record);
			
			return 'edit'; // caller should populate form fields with existing record data
		}
	}
	
	//Return an instantiated model class (cuts down on lines-of-code needed to call a model method).
	public function model($entity) {
		$class = Loader::helper('text')->camelcase($entity) . 'Model';
		$model = new $class;
		return $model;
	}
}
