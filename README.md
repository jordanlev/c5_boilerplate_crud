# Boilerplate code for basic dashboard CRUD operations in Concrete5

A Concrete5 package containing a basic dashboard interface that can serve as a skeleton for new projects. Contains a sample implementation of a two-table data model (with a parent-to-child relationship) and a dashboard UI for editing that data.

This code demonstrates one method of structuring files and interacting with the Concrete5 API (including controller routing and view rendering). It's definitely not the only way you could do things, but I happen to like it and have honed it down over the course of several real-world projects.

My aims were to have both a skeleton file/directory structure and to simplify and DRY up the code used for the standard CRUD pattern (one page to list all records, one to add/edit an a record, and one to delete a record).

## "Improvements" to Concrete5
I started off following the general pattern outlined in this most excellent how-to:
[How-To: Build a Single-Page Powered Editing Interface for concrete5 Pages](http://www.concrete5.org/documentation/how-tos/developers/build-a-single-page-powered-editing-interface-for-concrete5/), then added a few libraries to smooth out the rough edges -- routing/rendering/redirecting, validation, and basic database CRUD operations:

 * `libraries/better_controller.php`: overrides render(), redirect(), and url() methods of the base Controller class to make them easier to call (fewer and shorter arguments), and provides simple "flash message" functionality for displaying a message after a redirect.
 * `libraries/kohana_validation.php`: provides a *much* more robust set of built-in validation rules than C5's Validation helpers. (Ported from the amazing Kohana 2.3.4 framework.)
 * `libraries/basic_crud_model.php`: a super basic data model object you can build your own model classes on -- provides automatic INSERT/UPDATE, DELETE, and SELECT functionality for a single database record, and that's it! All it does is allow you easily perform single record inserts/updates/deletes -- everything else always requires custom queries so I don't bother.

### Controller
In the controller (`controllers/dashboard/boilerplate_crud.php`), I tried to use a very streamlined and DRY approach. Notably, I prefer to have a separate view file for each action even when those action methods are in a single controller.

The general workflow is this:
There are 4 pages for each data entity, corresponding to the 4 CRUD operations: List all records, add a new record, edit an existing record, delete a record. Each of these pages has a separate view file, but all action methods are in a single controller file (I actually combine the actions of two entities in the controller, but splitting it up into two controllers wouldn't be a terrible idea). The add and edit pages are combined into one page because they are basically the same thing (one just starts out with populated fields), and the workflow can be distilled down to this:

	public function something_edit($id = null) { //no id means "insert"
		if ($this->post()) { //check if the form was submitted (versus initial form display)
			$error = $this->data->validate($this->post()); //call our own specific validation function (defined by us in the data model)
			if ($error->has()) { //check if validation failed
				$this->set('error', $error); //redisplay form with error messages at top (C5 will automatically repopulate user's POST'ed data via the form helpers in the view)
			} else { //validation succeeded
				$this->data->save($this->post()); //built-in function, automatically (and safely) saves POST'ed data to database record
				$this->flash('Something Saved!'); //set message to be display AFTER the redirect (without having to redirect to a special method that sets the message)
				$this->redirect('something_list'); //overridden redirect method assumes an action in the same controller
			}
		} else { //initial form display
			$this->setArray($this->data->findById($id)); //built-in functions to retrieve database record and to send them to the view in one fell swoop
		}
	
		$this->set('id', $id); //so view knows if it's an insert or update (and can pass it back to us in the submit)
		$this->render('something/edit'); //overridden render method assumes a view file in package single_pages directory
	}

### Basic Database CRUD Operations
Each of the model files (`models/category.php` and `models/widget.php`) contains a class representing a single database table. They extend the "basic_crud_model" class, which handles the INSERT, UPDATE, DELETE queries automatically (as well as SELECTing one record) via the `save()`, `delete()`, and `findById()` methods. All other database interaction (including validation) is handled by custom functions in the two model classes. I prefer this lightweight solution to a full-blown ORM because Concrete5 packages are usually relatively small in scope, so it's not overly complicated to just use SQL statements for the few tricky queries you'll need. The most important thing is that you separate your concerns by putting database access in an isolated place -- how you actually access the database from that isolated place doesn't really matter too much IMHO.

### Validation
If you look at the validate() methods in the two model files (`models/category.php` and `models/widget.php`), you'll see examples of using the validation library. It's generally something like this:

	public function validate($post) {
		Loader::library('kohana_validation', 'boilerplate_crud');
		$v = new KohanaValidation($post);
		
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		//etc...
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}

The included validation library (ported from the Kohana framework, hence the name) provides a much more comprehensive set of validation rules than C5's validation helpers. See https://github.com/jordanlev/c5_kohana_form_validator for more details.
