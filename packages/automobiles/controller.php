<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class AutomobilesPackage extends Package {
	
	protected $pkgHandle = 'automobiles';
	protected $pkgName = 'Automobiles';
	protected $pkgDescription = 'Manage and display automobile inventory';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '0.1';
	
	public function install() {
		$pkg = parent::install(); //this will automatically install our package-level db.xml schema for us (among other things)
		
		$this->seedData($pkg, 'body_types.sql');
		$this->seedData($pkg, 'colors.sql');
		$this->seedData($pkg, 'manufacturers.sql');
		$this->seedData($pkg, 'cars.sql');
		$this->seedData($pkg, 'car_colors.sql');
		
		$this->installOrUpgrade($pkg);
	}
	
	public function upgrade() {
		$this->installOrUpgrade($this);
		parent::upgrade();
	}
	
	//Put most installation tasks here -- makes development easier
	// (just make sure the actions you perform are "non-destructive",
	//  for example, check if a page exists before adding it).
	private function installOrUpgrade($pkg) {
		
		//Frontend Page:
		$this->getOrAddSinglePage($pkg, '/automobiles', 'Automobiles');
		
		//Dashboard Pages:
		//Install one page for each *controller* (not each view),
		// plus one at the top-level to serve as a placeholder in the dashboard menu
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles', 'Automobiles'); //top-level pleaceholder
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles/cars', 'Car Listing');
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles/misc', 'Misc. Settings'); //this one controller handles colors AND manufacturers
		
		//Special 'config' page (for package-wide settings)
		$config_page = $this->getOrAddSinglePage($pkg, '/dashboard/automobiles/config', 'Configuration');
		$config_page->setAttribute('exclude_nav', 1); //don't show this page in the dashboard menu
		
		//Config settings:
		$this->getOrAddConfig($pkg, 'currency_symbol', '$');
		$this->getOrAddConfig($pkg, 'dummy_example', 'test');
	}
	
	//You might want to remove this in production -- could be dangerous (if package is accidentally removed)!!
	public function uninstall() {
		parent::uninstall();
	
		//Manually remove database tables (C5 doesn't do this automatically)
		$table_prefix = 'automobile_'; //<--make sure this is unique enough to not accidentally drop other tables!
		$db = Loader::db();
		$tables = $db->GetCol("SHOW TABLES LIKE '{$table_prefix}%'");
		$sql = 'DROP TABLE ' . implode(',', $tables);
		$db->Execute($sql);
	}


/*** UTILITY FUNCTIONS ***/
	private function seedData($pkg, $filename) {
		//NOTE that you can only run one query at a time,
		// so each sql statement must be in its own file!
		$db = Loader::db();
		$sql = file_get_contents($pkg->getPackagePath() . '/seed_data/' . $filename);
		$r = $db->execute($sql);
		if (!$r) { 
			throw new Exception(t('Unable to install data: %s', $db->ErrorMsg()));
		}
	}
	
	private function getOrAddSinglePage($pkg, $cPath, $cName = '', $cDescription = '') {
		Loader::model('single_page');
		
		$sp = SinglePage::add($cPath, $pkg);
		
		if (is_null($sp)) {
			//SinglePage::add() returns null if page already exists
			$sp = Page::getByPath($cPath);
		} else {
			//Set page title and/or description...
			$data = array();
			if (!empty($cName)) {
				$data['cName'] = $cName;
			}
			if (!empty($cDescription)) {
				$data['cDescription'] = $cDescription;
			}
			
			if (!empty($data)) {
				$sp->update($data);
			}
		}
		
		return $sp;
	}
	
	public function getOrInstallBlockType($pkg, $btHandle) {
		$bt = BlockType::getByHandle($btHandle);
		if (empty($bt)) {
			BlockType::installBlockTypeFromPackage($btHandle, $pkg);
			$bt = BlockType::getByHandle($btHandle);
		}
		return $bt;
	}
	
	public function getOrAddConfig($pkg, $key, $default_value_if_new = null) {
		$cfg = $pkg->config($key, true); //pass true to retrieve the full object (so we can differentiate between a non-existent config versus an existing config that has value set to null)
		if (is_null($cfg)) {
			$pkg->saveConfig($key, $default_value_if_new);
			return $default_value_if_new;
		} else {
			return $pkg->config($key);
		}
	}

}
