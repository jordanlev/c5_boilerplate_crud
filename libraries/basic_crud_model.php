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
	
	public function findById($id) {
		$sql = "SELECT * FROM {$this->table} WHERE {$this->pkid} = ? LIMIT 1";
		$vals = array($id);
		return $this->db->query($sql, $vals)->fetchRow();
	}
	
	public function delete($id) {
		$sql = "DELETE FROM {$this->table} WHERE {$this->pkid} = ?";
		$vals = array($id);
		$this->db->Execute($sql, $vals);
	}
	
	//Saves a record in the database using data from the provided $post array.
	// The $post array must have our "pkid" field name as an item key
	// (if its value is empty we INSERT, otherwise we UPDATE the record having that id).
	//Returns the id of the inserted/updated record.
	public function save($post) {
		$record = $this->recordFromPost($post);
		$id = isset($post[$this->pkid]) ? intval($post[$this->pkid]) : null;
		
		if (empty($id)) {
			$this->db->AutoExecute($this->table, $record, 'INSERT');
			return $this->db->Insert_ID();
		} else {
			$this->db->AutoExecute($this->table, $record, 'UPDATE', "{$this->pkid}={$id}"); //NOTE that we already intval()'ed the $id.
			return $id;
		}
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
	
}
