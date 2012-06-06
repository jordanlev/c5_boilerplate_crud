<?php defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('crud_controller', 'boilerplate_crud'); //Superset of Concrete5's Controller class -- provides simpler interface and some extra useful features.
class DashboardBoilerplateCrudController extends CrudController {

	public $helpers = array('form'); //Makes form helper automatically available to all views
	
	public function on_start() {
		Loader::model('category', 'boilerplate_crud');
		Loader::model('widget', 'boilerplate_crud');
		$this->category = new CategoryModel;
		$this->widget = new WidgetModel;
	}
	
	public function on_before_render() {
		//Load javascript and css into <head> of all views for this controller
		// (If you want to load js/css only for one action, put the addHeaderItem call in that action's method instead)
		//DEV NOTE: we use "on_before_render()" instead of "on_page_view()" (on_page_view only works in block controllers [??])
		$hh = Loader::helper('html');
		$this->addHeaderItem($hh->javascript('boilerplate_crud_dashboard_ui.js', 'boilerplate_crud'));
		$this->addHeaderItem($hh->css('boilerplate_crud_dashboard.css', 'boilerplate_crud'));
	}
	
	public function view() {
		//Go directly to some data. But if we wanted a "Welcome" page or some other non-data-specific
		// default page, we could use the view.php template file and remove this redirect
		// (the view.php file would get rendered automatically, unlike our other controller actions
		// which need an explicit call to $this->render()).
		$this->redirect('category_list');
	}
	
	//Utility: hidden address you can hit to refresh package's top-level db.xml file
	// (as well as db.xml files for this package's blocks).
	public function refresh_schema() {
		Package::getByHandle('boilerplate_crud')->upgrade();
		$this->set('message', 'Package Schema Refreshed!');
	    $this->category_list();
	}
	
	
	/***
	 * "Category" (parent record) actions
	 */
	public function category_list() {
		$categories = $this->category->getCategories();
		$this->set('categories', $categories);
		
		$this->render('category/list');
	}
	
	//This is kind of unnecessary, but keeps consistent with the widget_add()/widget_edit()
	public function category_add() {
		$this->category_edit(null);
	}
	
	public function category_edit($id = null) {
		if ($this->post()) {
			$error = $this->category->validate($this->post());
			if ($error->has()) {
				$this->set('error', $error); //C5 automagically displays these errors for us in the view
				//C5 form helpers will automatically repopulate form fields from $_POST data
			} else {
				$this->category->save($this->post());
				$this->flash('Category Saved!');
				$this->redirect('category_list');
			}
		} else if (empty($id)) {
			//Initialize form fields that don't start out empty/0
			//(not applicable to this form)
		} else {
			//Populate form fields with existing record data
			$category = $this->category->findById($id);
			$this->setArray($category);
		}
		
		$this->set('id', $id);
		$this->render('category/edit');
	}
	
	public function category_delete($id) {
		if ($this->post() && !empty($id)) {
			$this->category->delete($id);
			$this->flash('Category Deleted.');
			$this->redirect('category_list');
		}
		
		$category = $this->category->findById($id);
		$this->setArray($category);
		
		$this->render('category/delete');
	}
	
		
	/***
	 * "Widget" (child record) actions
	 */
	public function widget_list($categoryId) {
		$category = $this->category->findById($categoryId);
		$widgets = $this->widget->getCategoryWidgets($categoryId);
		$this->set('category', $category);
		$this->set('widgets', $widgets);
		
		$this->render('widget/list');
	}
	
	public function widget_add($categoryId) {
		$this->widget_edit(null, $categoryId);
	}
	
	public function widget_edit($id = null, $categoryId = null) {
		if (empty($id) && empty($categoryId)) {
			die('ERROR: No widget or category ID provided!');
		}
		
		$category = empty($categoryId) ? $this->category->getCategoryForWidget($id) : $this->category->findById($categoryId);
		$this->set('category', $category);
		
		if ($this->post()) {
			$_POST['isSomething'] = !empty($_POST['isSomething']); //Must do this for all checkbox fields (html forms don't POST the field at all if unchecked)
			$error = $this->widget->validate($this->post());
			if ($error->has()) {
				$this->set('error', $error); //C5 automagically displays these errors for us in the view
				//C5 form helpers will automatically repopulate form fields from $_POST data
			} else {
				$this->widget->save($this->post());
				$this->flash('Widget Saved!');
				$this->redirect('widget_list', $category['id']);
			}
		} else if (empty($id)) {
			//Initialize form fields that don't start out empty/0
			$this->set('rating', '5.0');
		} else {
			//Populate form fields with existing record data
			$widget = $this->widget->findById($id);
			$this->setArray($widget);
		}
		
		$this->set('id', $id);
		$this->render('widget/edit');
	}
	
	public function widget_delete($id) {
		if (empty($id)) {
			die('ERROR: No widget ID provided!');
		}
		
		$widget = $this->widget->findById($id); //do this first so if we delete it we know which parent page to redirect to
		
		if ($this->post()) {
			$this->widget->delete($id);
			$this->flash('Widget Deleted.');
			$this->redirect('widget_list', $widget['categoryId']);
		}
		
		$this->setArray($widget);
		$this->render('widget/delete');
	}
	
}
