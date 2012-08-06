<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class BoilerplateCrudPackage extends Package {
	
	protected $pkgHandle = 'boilerplate_crud';
	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '0.1';
	
	public function getPackageName() {
		return t('Boilerplate CRUD');
	}
	
	public function getPackageDescription() {
		return t('Custom Dashboard Management - Boilerplate CRUD Operations');
	}
	
	public function install() {
		$pkg = parent::install(); //this will automatically install our package-level db.xml schema for us (among other things)
		
		//Install seed data
		$this->install_data($pkg->getPackagePath() . '/seed_data/categories.sql');
		$this->install_data($pkg->getPackagePath() . '/seed_data/widgets.sql');
		
		//Install dashboard pages
		Loader::model('single_page');
		$sp = SinglePage::add('/dashboard/boilerplate_crud', $pkg);
		$sp->update(array('cName' => 'Boilerplate CRUD', 'cDescription' => 'Manage Sample Data for Boilerplate CRUD'));
		
		//NOTE: Do not install single_pages for the various sub-pages of the main controller,
		// because we will implement those as actions on the main controller instead
		// (and if we installed separate single_pages, C5 would then look for separate controller
		// files for each one -- instead of looking for the action method name in our one top-level controller).
	}
	
	private function install_data($sql_file) {
		$db = Loader::db();
		$sql = file_get_contents($sql_file);
		$r = $db->execute($sql);
		if (!$r) { 
			throw new Exception(t('Unable to install seed data: %s', $db->ErrorMsg()));
		}
	}
	
	public function uninstall() {
		parent::uninstall();
		
		//Manually remove database tables (C5 unfortunately doesn't do this automatically via db.xml)
		$db = Loader::db();
		$sql = 'DROP TABLE BoilerplateCrudWidgets, BoilerplateCrudCategories';
		$db->Execute($sql);
	}
	
}
