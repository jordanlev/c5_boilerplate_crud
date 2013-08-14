<?php defined('C5_EXECUTE') or die(_("Access Denied."));

// In C5 you can extend the "Model" class to get an ActiveRecord-style object
// (see http://phplens.com/lens/adodb/docs-active-record.htm),
// but ADODB's ActiveRecord implementation is woefully incomplete and buggy
// (for example, it doesn't do anything useful for many-to-many relationships).
//
//This, on the other hand, is a very lightweight model that doesn't try to do too much.
//It provides very basic "CRUD" (insert/update, delete, and 'get one') functionality.

class BasicCRUDModel {
	
	protected $table = '';
	protected $pkid = 'id'; //primary key id field name
	protected $fields = array(); //all other field names (including foreign keys)
	protected $db = null;
	
	public function __construct() {
		if (empty($this->table)) {
			throw new Exception('BasicCRUDModel class error: table not set!');
		}
		
		$this->db = Loader::db();
		
		//Auto-populate field names if not explicitly set already
		if (empty($this->fields)) {
			$cols = $this->db->MetaColumns($this->table);
			foreach ($cols as $col) {
				if ($col->name != $this->pkid) {
					$this->fields[] = $col->name;
				}
			}
		}
	}
	
	public function getAll() {
		//You should always override this (so there's a sort order,
		// and possibly to include extra related data not from the primary record).
		$sql = "SELECT * FROM {$this->table}";
		return $this->db->GetArray($sql);
	}
	
	public function getById($id) {
		$sql = "SELECT * FROM {$this->table} WHERE {$this->pkid} = ? LIMIT 1";
		$vals = array($id);
		return $this->db->GetRow($sql, $vals);
	}
	
	public function exists($id) {
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$this->pkid} = ?";
		$vals = array(intval($id));
		return (bool)$this->db->GetOne($sql, $vals);
	}
	
	public function delete($id) {
		$sql = "DELETE FROM {$this->table} WHERE {$this->pkid} = ?";
		$vals = array($id);
		$this->db->Execute($sql, $vals);
	}
	
	public function validate(&$post) {
		//This method should always be over-ridden.
		//Note that we don't bother calling add_standard_rules() here
		// because we don't know what the labels should be or which fields to ignore.
		return Loader::helper('validation/error');
	}
	
	//Saves a record in the database using data from the provided $post array.
	// The $post array must have our "pkid" field name as an item key
	// (if its value is empty we INSERT, otherwise we UPDATE the record having that id).
	//Returns the id of the inserted/updated record.
	public function save($post) {
		$record = $this->recordFromPost($post);
		
		if ($this->isNewRecord($post)) {
			$this->db->AutoExecute($this->table, $record, 'INSERT');
			return $this->db->Insert_ID();
		} else {
			$id = intval($post[$this->pkid]);
			$this->db->AutoExecute($this->table, $record, 'UPDATE', "{$this->pkid}={$id}");
			return $id;
		}
	}
	
	protected function isNewRecord($post) {
		$id = isset($post[$this->pkid]) ? intval($post[$this->pkid]) : 0;
		return ($id == 0);
	}
	
	private function recordFromPost($post) {
		$record = array();
		foreach ($this->fields as $field) {
			$val = array_key_exists($field, $post) ? $post[$field] : null;
			$val = ($val === '') ? null : $val; //don't just check for empty() because then a '0' would erroneously become null!
			$record[$field] = $val;
		}
		return $record;
	}
	
	public static function selectOptionsFromArray($arr, $keyField, $valField, $headerItem = array()) {
		$options = $headerItem; //e.g. array(0 => 'Choose One')
		foreach ($arr as $item) {
			$options[$item[$keyField]] = htmlentities($item[$valField], ENT_QUOTES, APP_CHARSET);
		}
		return $options;
	}
	
	//Calls add_rule() on the given KohanaValidation object for a variety of "standard" rules.
	//We will only add rules for fields that exist in the given $fields_and_labels array,
	// which should have keys of field names and values of human-readable labels (for error messages).
	//
	//The following rules are added (depending on field's definition in db.xml):
	// -'required' rule is added to any fields having <NOTNULL/>
	// -'length[0,x]' rule is added to varchar fields ("x" is field size)
	// -'numeric' rule is added to float fields
	// -'digit' rule is added to integer fields
	// -'atleast[0]' rule is added to non-required unsigned integer fields
	// -'atleast[1]' rule is added to required unsigned integer fields
	protected function add_standard_rules(KohanaValidation &$v, $fields_and_labels) {
		$cols = $this->db->MetaColumns($this->table);
		foreach ($cols as $col) {
			if (array_key_exists($col->name, $fields_and_labels)) {
				$field = $col->name;
				$label = $fields_and_labels[$field];
				$type = $col->type;
				
				if ($col->not_null) {
					$v->add_rule($field, 'required', "{$label} is required.");
				}
				
				if ($type == 'varchar') {
					$v->add_rule($field, "length[0,{$col->max_length}]", "{$label} cannot exceed {$col->max_length} characters in length.");
				}
				
				if ($type == 'float') {
					$v->add_rule($field, 'numeric', "{$label} must be a number.");
				}
				
				if ($type == 'int') {
					$v->add_rule($field, 'digit', "{$label} must be a whole number.");
					if ($col->unsigned) {
						if ($col->not_null) {
							$v->add_rule($field, 'atleast[1]', "You must choose a {$label}."); //Assumes required unsigned ints are foreign key id's, and hence have a dropdown list for selections
						} else {
							$v->add_rule($field, 'atleast[0]', "{$label} must be a positive number");
						}
					}
				}
			}
		}
	}

}

//Extends the basic crud model with functionality for a display order field.
class SortableCRUDModel extends BasicCRUDModel {
	
	protected $order = 'display_order'; //display order field name (must be an INT)
	protected $segment = ''; //optional field name of a foreign key that we'll segment display orders by
	
	public function save($post) {
		if ($this->isNewRecord($post)) {
			//Add new records at the end of the display order
			$segment_id = $this->segment ? $post[$this->segment] : null;
			$post[$this->order] = $this->maxDisplayOrder($segment_id) + 1;
		} else if (empty($post[$this->order])) {
			//Remove the display order field from the fields list,
			// so existing value doesn't get null'ed by recordFromPost().
			$this->fields = array_diff($this->fields, array($this->order));
		}
		
		return parent::save($post);
	}
	
	private function maxDisplayOrder($segment_id = null) {
		$sql = "SELECT MAX({$this->order}) FROM {$this->table}";
		$sql .= $segment_id ? " WHERE {$this->segment} = " . intval($segment_id) : '';
		$max = $this->db->GetOne($sql);
		return intval($max);
	}
	
	public function getAll($segment_id = null) {
		$sql = "SELECT * FROM {$this->table}";
		$sql .= $segment_id ? " WHERE {$this->segment} = " . intval($segment_id) : '';
		$sql .= " ORDER BY {$this->order}";
		return $this->db->GetArray($sql);
	}
	
	//Pass in an array of id's, in the order you want those records to be.
	//Optionally pass in the "segment id" (if sorting only a subset of the table).
	//The given $ids array should contain ALL id's for the table (or segment)
	// -- records whose id's are not in the array will be moved to the end
	// of the display order for that table (or segment).
	public function setDisplayOrder($ids, $segment_id = null) {
		$sql = "UPDATE {$this->table} SET {$this->order} = 0";
		$sql .= $segment_id ? " WHERE {$this->segment} = " . intval($segment_id) : '';
		$this->db->Execute($sql);
		
		$next_display_order = $this->setPartialDisplayOrder($ids, 1, $segment_id);
		
		//Now move all the ones we didn't have an id for to the end
		$sql = "SELECT {$this->pkid} FROM {$this->table} WHERE {$this->order} = 0";
		$sql .= $segment_id ? " AND {$this->segment} = " . intval($segment_id) : '';
		$sql .= " ORDER BY {$this->pkid}";
		$ids = $this->db->GetCol($sql);
		$this->setPartialDisplayOrder($ids, $next_display_order);
	}
		//Helper function for setDisplayOrder()...
		private function setPartialDisplayOrder($ids, $starting_display_order, $segment_id = null) {
			$current_display_order = $starting_display_order;
			$sql = "UPDATE {$this->table} SET {$this->order} = ? WHERE {$this->pkid} = ?";
			$stmt = $this->db->Prepare($sql);
			foreach ($ids as $id) {
				$vals = array($current_display_order, intval($id));
				$this->db->Execute($stmt, $vals);
				$current_display_order++;
			}
			return $current_display_order;
		}
	//END setDisplayOrder()
}
