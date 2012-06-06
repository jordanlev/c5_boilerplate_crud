<?php defined('C5_EXECUTE') or die(_("Access Denied."));

//Provides some improved convenience methods for our single_page controllers

class CrudController extends Controller {
	
	public function __construct() {
		parent::__construct();
		
		if (!empty($_SESSION['flash_message'])) {
			$this->set('message', $_SESSION['flash_message']); //C5 automagically displays this message for us in the view
			unset($_SESSION['flash_message']);
		}
	}
	
	//Uses session to set 'message' variable next time the page is loaded
	public function flash($message) {
		$_SESSION['flash_message'] = $message;
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
	
}
