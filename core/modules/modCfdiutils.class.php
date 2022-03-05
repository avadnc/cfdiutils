<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2022 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   cfdiutils     Module Cfdiutils
 *  \brief      Cfdiutils module descriptor.
 *
 *  \file       htdocs/cfdiutils/core/modules/modCfdiutils.class.php
 *  \ingroup    cfdiutils
 *  \brief      Description and activation file for module Cfdiutils
 */
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Cfdiutils
 */
class modCfdiutils extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 500000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'cfdiutils';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleCfdiutilsName' not found (Cfdiutils is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleCfdiutilsDesc' not found (Cfdiutils is name of module).
		$this->description = "CfdiutilsDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "CfdiutilsDescription";

		// Author
		$this->editor_name = 'Editor name';
		$this->editor_url = 'https://www.example.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where CFDIUTILS is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'generic';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 1,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/cfdiutils/css/cfdiutils.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/cfdiutils/js/cfdiutils.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				'productcard',
				'paymentcard', // !ficha pago realizado
				'paiementcard', // !ficha cuando se realiza el pago
				'thirdpartycomm',
				'thirdpartycard',
				'invoicecard',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/cfdiutils/temp","/cfdiutils/subdir");
		$this->dirs = array("/cfdiutils/temp");

		// Config pages. Put here list of php page, stored into cfdiutils/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@cfdiutils");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

		// The language file dedicated to your module
		$this->langfiles = array("cfdiutils@cfdiutils");

		// Prerequisites
		$this->phpmin = array(5, 6); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'CfdiutilsWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('CFDIUTILS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('CFDIUTILS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->cfdiutils) || !isset($conf->cfdiutils->enabled)) {
			$conf->cfdiutils = new stdClass();
			$conf->cfdiutils->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		$this->tabs[] = [
			//ejemplo de añadir pestaña
			// 'data' => 'product:+productfiscal:Datos Fiscales:cfdiutils@cfdiutils:true:/cfdiutils/product/fiscal.php?id=__ID__'
		];
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@cfdiutils:$user->rights->cfdiutils->read:/cfdiutils/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@cfdiutils:$user->rights->othermodule->read:/cfdiutils/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries = [
			'langs' => 'cfdiutils@cfdiutils',
			'tabname' => [
				MAIN_DB_PREFIX . 'c_cfdiutils_umed',				//CFDI - Unidad de medida
				MAIN_DB_PREFIX . 'c_cfdiutils_claveprodserv',		//CFDI - Claves de producto y/o servicio
				MAIN_DB_PREFIX . 'c_cfdiutils_usocfdi',				//CFDI - Uso del CFDI
				MAIN_DB_PREFIX . 'c_cfdiutils_metodopago',			//CFDI - Método de pago
				MAIN_DB_PREFIX . 'c_cfdiutils_tiporelacion',		//CFDI - Tipo de relación
				MAIN_DB_PREFIX . 'c_cfdiutils_objetoimp',			//CFDI - Objeto de Impuesto
				MAIN_DB_PREFIX . 'c_cfdiutils_exportacion',			//CFDI - Exportacion
			],
			'tablib' => [
				'CFDI - Unidad de medida',					//CFDI - Unidad de medida
				'CFDI - Claves de producto y/o servicio',	//CFDI - Claves de producto y/o servicio
				'CFDI - Uso del CFDI',						//CFDI - Uso del CFDI
				'CFDI - Método de Pago',					//CFDI - Método de pago
				'CFDI - Tipo de relación',					//CFDI - Tipo de relación
				'CFDI - Objeto de Impuesto',				//CFDI - Objeto de Impuesto
				'CFDI - Exportación',						//CFDI - Exportacion
			],
			'tabsql' => [
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_umed as f', 				//CFDI - Unidad de medida
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_claveprodserv as f', 	//CFDI - Claves de producto y/o servicio
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_usocfdi as f', 			//CFDI - Uso del CFDI
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_metodopago as f', 		//CFDI - Método de pago
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_tiporelacion as f',		//CFDI - Tipo de relación
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_objetoimp as f',			//CFDI - Objeto de Impuesto
				'SELECT f.rowid as rowid, f.code, f.label, f.active FROM ' . MAIN_DB_PREFIX . 'c_cfdiutils_exportacion as f',		//CFDI - Exportacion
			],
			'tabsqlsort' => [
				"label ASC", 	//CFDI - Unidad de medida
				"label ASC", 	//CFDI - Claves de producto y/o servicio
				"label ASC",	//CFDI - Uso del CFDI
				"label ASC",	//CFDI - Método de pago
				"label ASC",	//CFDI - Tipo de relación
				"label ASC", 	//CFDI - Objeto de Impuesto
				"label ASC", 	//CFDI - Exportacion
			],
			'tabfield' => [
				"code,label",	//CFDI - Unidad de medida
				"code,label",	//CFDI - Claves de producto y/o servicio
				"code,label",	//CFDI - Uso del CFDI
				"code,label",	//CFDI - Método de pago
				"code,label",	//CFDI - Tipo de relación
				"code,label", 	//CFDI - Objeto de Impuesto
				"code,label", 	//CFDI - Exportacion
			],
			'tabfieldvalue' => [
				"code,label", //CFDI - Unidad de medida
				"code,label", //CFDI - Claves de producto y/o servicio
				"code,label", //CFDI - Uso del CFDI
				"code,label", //CFDI - Método de pago
				"code,label", //CFDI - Tipo de relación
				"code,label", 	//CFDI - Objeto de Impuesto
				"code,label", //CFDI - Exportacion
			],
			'tabfieldinsert' => [
				"code,label",	//CFDI - Unidad de medida
				"code,label",	//CFDI - Claves de producto y/o servicio
				"code,label",	//CFDI - Uso del CFDI
				"code,label",	//CFDI - Método de pago
				"code,label",	//CFDI - Tipo de relación
				"code,label", 	//CFDI - Objeto de Impuesto
				"code,label", 	 //CFDI - Exportacion
			],
			'tabrowid' => [
				"rowid", //CFDI - Unidad de medida
				"rowid", //CFDI - Claves de producto y/o servicio
				"rowid", //CFDI - Uso del CFDI
				"rowid", //CFDI - Método de pago
				"rowid", //CFDI - Tipo de relación
				"rowid", 	//CFDI - Objeto de Impuesto
				"rowid", 	//CFDI - Exportacion
			],
			'tabcond' => [
				$conf->cfdiutils->enabled, //CFDI - Unidad de medida
				$conf->cfdiutils->enabled, //CFDI - Claves de producto y/o servicio
				$conf->cfdiutils->enabled, //CFDI - Uso del CFDI
				$conf->cfdiutils->enabled, //CFDI - Método de pago
				$conf->cfdiutils->enabled, //CFDI - Tipo de relación
				$conf->cfdiutils->enabled, //CFDI - Objeto de Impuesto
				$conf->cfdiutils->enabled, //CFDI - Exportacion
			]
		];
		/* Example:
		$this->dictionaries=array(
			'langs'=>'cfdiutils@cfdiutils',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."table1", MAIN_DB_PREFIX."table2", MAIN_DB_PREFIX."table3"),
			// Label of tables
			'tablib'=>array("Table1", "Table2", "Table3"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
			// Sort order
			'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->cfdiutils->enabled, $conf->cfdiutils->enabled, $conf->cfdiutils->enabled)
		);
		*/

		// Boxes/Widgets
		// Add here list of php file(s) stored in cfdiutils/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'cfdiutilswidget1.php@cfdiutils',
			//      'note' => 'Widget provided by Cfdiutils',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/cfdiutils/class/facture.class.php',
			//      'objectname' => 'Facture',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->cfdiutils->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->cfdiutils->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->cfdiutils->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'CfdiutilsStamp'; // Permission label
		$this->rights[$r][4] = 'stamp';

		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
		$this->rights[$r][1] = 'CfdiutilsCancel'; // Permission label
		$this->rights[$r][4] = 'cancel';

		$r++;
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		// $this->menu[$r++] = array(
		// 	'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		// 	'type'=>'top', // This is a Top menu entry
		// 	'titre'=>'ModuleCfdiutilsName',
		// 	'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		// 	'mainmenu'=>'cfdiutils',
		// 	'leftmenu'=>'',
		// 	'url'=>'/cfdiutils/cfdiutilsindex.php',
		// 	'langs'=>'cfdiutils@cfdiutils', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		// 	'position'=>1000 + $r,
		// 	'enabled'=>'$conf->cfdiutils->enabled', // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled.
		// 	'perms'=>'1', // Use 'perms'=>'$user->rights->cfdiutils->facture->read' if you want your menu with a permission rules
		// 	'target'=>'',
		// 	'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		// );
		/* END MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU FACTURE
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=cfdiutils',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'Facture',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'cfdiutils',
			'leftmenu'=>'facture',
			'url'=>'/cfdiutils/cfdiutilsindex.php',
			'langs'=>'cfdiutils@cfdiutils',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->cfdiutils->enabled',  // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->cfdiutils->facture->read',			                // Use 'perms'=>'$user->rights->cfdiutils->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=cfdiutils,fk_leftmenu=facture',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'List_Facture',
			'mainmenu'=>'cfdiutils',
			'leftmenu'=>'cfdiutils_facture_list',
			'url'=>'/cfdiutils/facture_list.php',
			'langs'=>'cfdiutils@cfdiutils',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->cfdiutils->enabled',  // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->cfdiutils->facture->read',			                // Use 'perms'=>'$user->rights->cfdiutils->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=cfdiutils,fk_leftmenu=facture',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'New_Facture',
			'mainmenu'=>'cfdiutils',
			'leftmenu'=>'cfdiutils_facture_new',
			'url'=>'/cfdiutils/facture_card.php?action=create',
			'langs'=>'cfdiutils@cfdiutils',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->cfdiutils->enabled',  // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->cfdiutils->facture->write',			                // Use 'perms'=>'$user->rights->cfdiutils->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		*/

		// $this->menu[$r++]=array(
		//     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		//     'fk_menu'=>'fk_mainmenu=cfdiutils',
		//     // This is a Left menu entry
		//     'type'=>'left',
		//     'titre'=>'List Facture',
		//     'mainmenu'=>'cfdiutils',
		//     'leftmenu'=>'cfdiutils_facture',
		//     'url'=>'/cfdiutils/facture_list.php',
		//     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		//     'langs'=>'cfdiutils@cfdiutils',
		//     'position'=>1100+$r,
		//     // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
		//     'enabled'=>'$conf->cfdiutils->enabled',
		//     // Use 'perms'=>'$user->rights->cfdiutils->level1->level2' if you want your menu with a permission rules
		//     'perms'=>'1',
		//     'target'=>'',
		//     // 0=Menu for internal users, 1=external users, 2=both
		//     'user'=>2,
		// );
		// $this->menu[$r++]=array(
		//     // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		//     'fk_menu'=>'fk_mainmenu=cfdiutils,fk_leftmenu=cfdiutils_facture',
		//     // This is a Left menu entry
		//     'type'=>'left',
		//     'titre'=>'New Facture',
		//     'mainmenu'=>'cfdiutils',
		//     'leftmenu'=>'cfdiutils_facture',
		//     'url'=>'/cfdiutils/facture_card.php?action=create',
		//     // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		//     'langs'=>'cfdiutils@cfdiutils',
		//     'position'=>1100+$r,
		//     // Define condition to show or hide menu entry. Use '$conf->cfdiutils->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
		//     'enabled'=>'$conf->cfdiutils->enabled',
		//     // Use 'perms'=>'$user->rights->cfdiutils->level1->level2' if you want your menu with a permission rules
		//     'perms'=>'1',
		//     'target'=>'',
		//     // 0=Menu for internal users, 1=external users, 2=both
		//     'user'=>2
		// );

		/* END MODULEBUILDER LEFTMENU FACTURE */
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT FACTURE */
		/*
		$langs->load("cfdiutils@cfdiutils");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='FactureLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='facture@cfdiutils';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Facture'; $keyforclassfile='/cfdiutils/class/facture.class.php'; $keyforelement='facture@cfdiutils';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'FactureLine'; $keyforclassfile='/cfdiutils/class/facture.class.php'; $keyforelement='factureline@cfdiutils'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='facture'; $keyforaliasextra='extra'; $keyforelement='facture@cfdiutils';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='factureline'; $keyforaliasextra='extraline'; $keyforelement='factureline@cfdiutils';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('factureline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'facture as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'facture_line as tl ON tl.fk_facture = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('facture').')';
		$r++; */
		/* END MODULEBUILDER EXPORT FACTURE */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT FACTURE */

		$langs->load("cfdiutils@cfdiutils");
		$this->export_code[$r] = $this->rights_class . '_' . $r;
		$this->export_label[$r] = 'CfdiproductImport';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r] = 'cfdiproduct@cfdiutils';
		$keyforclass = 'Cfdiproduct';
		$keyforclassfile = '/cfdiutils/class/cfdiproduct.class.php';
		$keyforelement = 'cfdiproduct@cfdiutils';
		include DOL_DOCUMENT_ROOT . '/core/commonfieldsinexport.inc.php';
		$keyforselect = 'cfdiproduct';
		$keyforaliasextra = 'extra';
		$keyforelement = 'cfdiproduct@cfdiutils';
		//  include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//  $this->export_dependencies_array[$r]=array(''=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		$this->export_sql_start[$r] = 'SELECT DISTINCT ';
		$this->export_sql_end[$r]  = ' FROM ' . MAIN_DB_PREFIX . 'facture as t';
		$this->export_sql_end[$r] .= ' WHERE 1 = 1';
		$this->export_sql_end[$r] .= ' AND t.entity IN (' . getEntity('facture') . ')';
		$r++;
		/* END MODULEBUILDER IMPORT FACTURE */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/cfdiutils/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		// Permissions
		$this->remove($options);

		$sql = array();
		$regimen = [

			"601" => "General de Ley Personas Morales",
			"603" => "Personas Morales con Fines no Lucrativos",
			"605" => "Sueldos y Salarios e Ingresos Asimilados a Salarios",
			"606" => "Arrendamiento",
			"607" => "Régimen de Enajenación o Adquisición de Bienes",
			"608" => "Demás ingresos",
			"610" => "Residentes en el Extranjero sin Establecimiento Permanente en México",
			"611" => "Ingresos por Dividendos (socios y accionistas)",
			"612" => "Personas Físicas con Actividades Empresariales y Profesionales",
			"614" => "Ingresos por intereses",
			"615" => "Régimen de los ingresos por obtención de premios",
			"616" => "Sin obligaciones fiscales",
			"620" => "Sociedades Cooperativas de Producción que optan por diferir sus ingresos",
			"621" => "Incorporación Fiscal",
			"622" => "Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras",
			"623" => "Opcional para Grupos de Sociedades",
			"624" => "Coordinados",
			"625" => "Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas",
			"626" => "Régimen Simplificado de Confianza",
		];

		//Delete previous data from México
		$sqlreg = "DELETE FROM " . MAIN_DB_PREFIX . "c_forme_juridique ";
		$sqlreg = " WHERE fk_pays = 154, ";
		$this->db->query($sqlreg);

		foreach ($regimen as $cod => $val) {

			$sqlreg = "SELECT count(*) as nb from " . MAIN_DB_PREFIX . "c_forme_juridique where code = " . $cod;
			$result = $this->db->query($sqlreg);
			if ($result) {

				$obj = $this->db->fetch_object($result);
				if ($obj->nb == 0) {

					//Insert New data
					$sqlreg = "INSERT INTO " . MAIN_DB_PREFIX . "c_forme_juridique ";
					$sqlreg .= "(code,fk_pays,libelle,active)";
					$sqlreg .= " VALUES (" . $cod . ",154,'" . $val . "',1)";
					$this->db->query($sqlreg);
				} else {

					//if code exists, update to México
					$sqlreg = "UPDATE " . MAIN_DB_PREFIX . "c_forme_juridique ";
					$sqlreg .= "SET fk_pays = 154, ";
					$sqlreg .= " libelle = '" . $val . "' where code = " . $cod;
					$this->db->query($sqlreg);
				}
			}
		}

		// Document templates
		// $moduledir = 'cfdiutils';
		// $myTmpObjects = array();
		// $myTmpObjects['Facture'] = array('includerefgeneration' => 0, 'includedocgeneration' => 0);

		// foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
		// 	if ($myTmpObjectKey == 'Facture') {
		// 		continue;
		// 	}
		// 	if ($myTmpObjectArray['includerefgeneration']) {
		// 		$src = DOL_DOCUMENT_ROOT . '/install/doctemplates/cfdiutils/template_factures.odt';
		// 		$dirodt = DOL_DATA_ROOT . '/doctemplates/cfdiutils';
		// 		$dest = $dirodt . '/template_factures.odt';

		// 		if (file_exists($src) && !file_exists($dest)) {
		// 			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
		// 			dol_mkdir($dirodt);
		// 			$result = dol_copy($src, $dest, 0, 0);
		// 			if ($result < 0) {
		// 				$langs->load("errors");
		// 				$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
		// 				return 0;
		// 			}
		// 		}

		// 		$sql = array_merge($sql, array(
		// 			"DELETE FROM " . MAIN_DB_PREFIX . "document_model WHERE nom = 'standard_" . strtolower($myTmpObjectKey) . "' AND type = '" . strtolower($myTmpObjectKey) . "' AND entity = " . $conf->entity,
		// 			"INSERT INTO " . MAIN_DB_PREFIX . "document_model (nom, type, entity) VALUES('standard_" . strtolower($myTmpObjectKey) . "','" . strtolower($myTmpObjectKey) . "'," . $conf->entity . ")",
		// 			"DELETE FROM " . MAIN_DB_PREFIX . "document_model WHERE nom = 'generic_" . strtolower($myTmpObjectKey) . "_odt' AND type = '" . strtolower($myTmpObjectKey) . "' AND entity = " . $conf->entity,
		// 			"INSERT INTO " . MAIN_DB_PREFIX . "document_model (nom, type, entity) VALUES('generic_" . strtolower($myTmpObjectKey) . "_odt', '" . strtolower($myTmpObjectKey) . "', " . $conf->entity . ")"
		// 		));
		// 	}
		// }

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
