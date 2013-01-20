<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class AutomobilesPackage extends Package {
	
	protected $pkgHandle = 'automobiles';
	protected $pkgName = 'Automobiles';
	protected $pkgDescription = 'Manage and Display Automobiles';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '0.1';
	
	public function install() {
		$pkg = parent::install(); //this will automatically install our package-level db.xml schema for us (among other things)
		
		$this->seedData($pkg, 'colors.sql');
		$this->seedData($pkg, 'manufacturers.sql');
		$this->seedData($pkg, 'cars.sql');
		
		$this->installAndUpgrade($pkg);
	}
	
	public function upgrade() {
		$pkg = Package::getByHandle($this->pkgHandle);
		$this->installAndUpgrade($pkg);
		parent::upgrade();
	}
	
	//Put most installation tasks here -- makes development easier
	// (just make sure the actions you perform are "non-destructive",
	//  for example, check if a page exists before adding it).
	private function installAndUpgrade($pkg) {
		//Install one page for each *controller* (not each view),
		// plus one at the top-level to serve as a placeholder in the dashboard menu
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles', 'Automobiles'); //top-level pleaceholder
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles/cars', 'Car Listing');
		$this->getOrAddSinglePage($pkg, '/dashboard/automobiles/misc', 'Misc. Settings'); //this one controller handles colors AND manufacturers
	}
	
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
	
	//You might want to remove this in production -- could be dangerous (if package is accidentally removed)!!
	public function uninstall() {
		parent::uninstall();
	
		//Manually remove database tables (C5 doesn't do this automatically)
		$db = Loader::db();
		$sql = 'DROP TABLE AutomobileCars, AutomobileColors, AutomobileManufacturers';
		$db->Execute($sql);
	}
}
