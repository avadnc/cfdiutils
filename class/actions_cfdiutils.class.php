<?php
/* Copyright (C) 2022 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    cfdiutils/class/actions_cfdiutils.class.php
 * \ingroup cfdiutils
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */
require_once 'cfdiproduct.class.php';
require_once 'cfdisociete.class.php';
/**
 * Class ActionsCfdiutils
 */
class ActionsCfdiutils
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $conf, $user, $langs;

		$error = 0; // Error counter

		/* Detectar si el producto es fiscal */

		if (in_array($parameters['currentcontext'], ['productcard'])) {


			$cfdiproduct = new Cfdiproduct($db);
			$cfdiproduct->fetch($object->id);
			$cfdiproduct->getFiscal();

			if ($action == "confirm_valid") {

				$umed = GETPOST('umed', 'alpha');
				$claveprodserv = GETPOST('claveprodserv', 'alpha');
				$objetoimp = GETPOST('objetoimp', 'alpha');

				if (
					$cfdiproduct->umed == null ||
					$cfdiproduct->claveprodserv == null ||
					$cfdiproduct->objetoimp == null
				) {
					$cfdiproduct->objetoimp = $objetoimp;
					$cfdiproduct->claveprodserv = $claveprodserv;
					$cfdiproduct->umed = $umed;
					$result = $cfdiproduct->createFiscal();
					if ($result == "InsertSuccess") {
						setEventMessage('Datos Fiscales Añadidos', 'mesgs');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
						exit;
					} else {
						setEventMessage('Error al añadir datos', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
						exit;
					}
				} else {

					$cfdiproduct->objetoimp = $objetoimp;
					$cfdiproduct->claveprodserv = $claveprodserv;
					$cfdiproduct->umed = $umed;
					$result = $cfdiproduct->updateFiscal();
					if ($result == 1) {
						setEventMessage('Datos Fiscales Actualizados', 'mesgs');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
						exit;
					} else {
						setEventMessage('Error al actualizar datos', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
						exit;
					}
				}
			}

			if ($action == "confirm_delete") {
				$cfdiproduct->deleteFiscal();
			}
		}


		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], ['thirdpartycomm', 'thirdpartycard'])) {

			$societe = new Cfdisociete($db);
			$societe->fetch($object->id);
			$societe->getFiscal();

			if ($action == "confirm_valid") {
				$fiscal_name = GETPOST('fiscal_name');
				$zip = GETPOST('cp', 'int');
				$rfc = GETPOST('rfc', 'alpha');
				$regimen = GETPOST('regimen', 'int');

				if (
					$societe->fiscal_name == null ||
					$societe->zip == null ||
					$societe->idprof1 == null ||
					$societe->forme_juridique_code == null
				) {

					$societe->fiscal_name = $fiscal_name;
					$societe->zip = $zip;
					$societe->idprof1 = $rfc;
					$societe->forme_juridique_code = $regimen;
					$result = $societe->createFiscal();
				} else {
					$societe->fiscal_name = $fiscal_name;
					$societe->zip = $zip;
					$societe->idprof1 = $rfc;
					$societe->forme_juridique_code = $regimen;
					$result = $societe->updateFiscal();
				}

				if ($result == "InsertSuccess" || $result == 1) {
					$societe->update($object->id, $user, 0);
					setEventMessage('Datos Fiscales Actualizados', 'mesgs');
					header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
					exit;
				} else {
					setEventMessage('Error al actualizar datos', 'errors');
					header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $object->id);
					exit;
				}
			}

			if ($action == "confirm_delete") {
				$societe->deleteFiscal();
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("CfdiutilsMassAction") . '</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("cfdiutils@cfdiutils");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'cfdiutils') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("Cfdiutils");
			$this->results['picto'] = 'cfdiutils@cfdiutils';
		}

		$head[$h][0] = 'customreports.php?objecttype=' . $parameters['objecttype'] . (empty($parameters['tabfamily']) ? '' : '&tabfamily=' . $parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->cfdiutils->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             <0 if KO,
	 *                                          =0 if OK but we want to process standard actions too,
	 *                                          >0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// utilisé si on veut faire disparaitre des onglets.
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('cfdiutils@cfdiutils');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/cfdiutils/cfdiutils_tab.php', 1) . '?id=' . $id . '&amp;module=' . $element;
				$parameters['head'][$counter][1] = $langs->trans('CfdiutilsTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'cfdiutilsemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		}
	}

	/* Add here any other hooked methods... */

	public function formObjectOptions(&$parameters, &$object, &$action)
	{
		global $db, $langs;
		$form = new Form($db);

		if (in_array($parameters['currentcontext'], ['productcard'])) {
			if ($action != "create") {
				$cfdiproduct = new Cfdiproduct($db);
				$cfdiproduct->fetch($object->id);
				$cfdiproduct->getFiscal();
				if (
					$cfdiproduct->umed == null ||
					$cfdiproduct->claveprodserv == null ||
					$cfdiproduct->objetoimp == null
				) {
					echo '<tr><td>Datos Fiscales</td>';
					echo '<td><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=addfiscal">Añadir</a></td></tr>';
				} else {
					echo '<tr><td align="left">Datos Fiscales</td>';
					echo '<td><table class="border centpercent">';
					echo '<tr><td colspan="2" align="center"><h3>SAT</h3></td></tr>';
					echo '<tr><td><strong>UMED</strong></td><td>' . $cfdiproduct->umed . '</td></tr>';
					echo '<tr><td><strong>ClaveProdServ</strong></td><td>' . $cfdiproduct->claveprodserv . '</td></tr>';
					echo '<tr><td><strong>Objeto de Impuesto</strong></td><td>' . $cfdiproduct->objetoimp . '</td></tr>';
					echo '<tr><td colspan="2" align="center"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=modifyfiscal">Modificar</a></td></tr>';
					echo '</table></td></tr>';
				}

				if ($action == "addfiscal") {

					$umed = $cfdiproduct->getDictionary('umed');
					$claveprodserv = $cfdiproduct->getDictionary('claveprodserv');
					$objetoimp = $cfdiproduct->getDictionary('objetoimp');

					$formquestion = array(

						'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
						['type' => 'select', 'name' => 'umed', 'id' => 'umed', 'label' => 'Unidad de Medida', 'values' => $umed],
						['type' => 'select', 'name' => 'claveprodserv', 'id' => 'claveprodserv', 'label' => 'ClaveProdServ', 'values' => $claveprodserv],
						['type' => 'select', 'name' => 'objetoimp', 'id' => 'objetoimp', 'label' => 'Objeto de Impuesto', 'values' => $objetoimp],
						['other' => '<a href="#">asd</a>']

					);
					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('selectProductFiscal'), '', 'confirm_valid', $formquestion, 0, 1, 300, 600);
					print $formconfirm;

					echo '<script>$(document).ready(function(){
						$(".select2-container").css("width","20rem");
					});</script>';
				}

				if ($action == "modifyfiscal") {
					$umed = $cfdiproduct->getDictionary('umed');
					$claveprodserv = $cfdiproduct->getDictionary('claveprodserv');
					$objetoimp = [
						"01" => "01 - No objeto de impuesto.",
						"02" => "02 - Sí objeto de impuesto.",
						"03" => "03 - Sí objeto del impuesto y no obligado al desglose.",
					];

					$formquestion = array(

						'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
						['type' => 'select', 'name' => 'umed', 'id' => 'umed', 'label' => 'Unidad de Medida', 'values' => $umed, 'default' => $cfdiproduct->umed],
						['type' => 'select', 'name' => 'claveprodserv', 'id' => 'claveprodserv', 'label' => 'ClaveProdServ', 'values' => $claveprodserv, 'default' => $cfdiproduct->claveprodserv],
						['type' => 'select', 'name' => 'objetoimp', 'id' => 'objetoimp', 'label' => 'Objeto de Impuesto', 'values' => $objetoimp, 'default' => $cfdiproduct->objetoimp],

					);
					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('selectProductFiscal'), '', 'confirm_valid', $formquestion, 0, 1, 300, 600);
					print $formconfirm;

					echo '<script>$(document).ready(function(){
						$(".select2-container").css("width","20rem");
					});</script>';
				}
			}
		}

		if (in_array($parameters['currentcontext'], ['paymentcard', 'paiementcard'])) {
			echo '<tr><td>Hola</td><td>Caracola</td></tr>';
		}

		if (in_array($parameters['currentcontext'], ['thirdpartycomm', 'thirdpartycard'])) {

			// Validate if object is customer
			if ($object->client == 1) {

				$societe = new Cfdisociete($db);
				$societe->fetch($object->id);
				$societe->getFiscal();
				if (
					$societe->fiscal_name == null ||
					$societe->zip == null ||
					$societe->idprof1 == null ||
					$societe->forme_juridique_code == null
				) {
					echo '<tr><td>Cédula Fiscal</td><td><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=addfiscal">Añadir</a></td></tr>';
				} else {

					echo '<tr><td class="left">Nombre Fiscal SAT<a class="editfielda" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=modifyfiscal">' . img_edit($langs->transnoentitiesnoconv('Edit'), 1) . '</a></td>';
					echo '<td class="left"><span class="fas fa-building" style=" color: #6c6aa8;padding-right:0.5rem;"></span>' . $societe->fiscal_name . '</td></tr>';
				}

				if ($action == "addfiscal") {

					$regimen = $societe->getFormeJuridique();

					//TODO: Add Municipio, CODE Municipio..... etc...
					$formquestion = array(

						'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
						['type' => 'text', 'name' => 'fiscal_name', 'id' => 'fiscal_name', 'label' => 'Nombre Fiscal SAT', 'value' => strtoupper($societe->name), 'tdclass' => 'fieldrequired'],
						['type' => 'text', 'name' => 'rfc', 'id' => 'rfc', 'label' => 'RFC', 'value' => strtoupper($societe->idprof1), 'tdclass' => 'fieldrequired'],
						['type' => 'text', 'name' => 'cp', 'id' => 'cp', 'label' => 'Código Postal', 'value' => $societe->zip, 'tdclass' => 'fieldrequired'],
						['type' => 'select', 'name' => 'regimen', 'id' => 'regimen', 'label' => 'Régimen Fiscal', 'values' => $regimen, 'default' => $societe->forme_juridique_code, 'tdclass' => 'fieldrequired'],


					);

					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('selectProductFiscal'), '', 'confirm_valid', $formquestion, 0, 1, 380, 640);
					print $formconfirm;

					echo '<script>$(document).ready(function(){
						$(".flat").css("width","20rem");
					});</script>';
				}

				if ($action == "modifyfiscal") {

					$regimen = $societe->getFormeJuridique();

					//TODO: Add Municipio, CODE Municipio..... etc...
					$formquestion = array(

						'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
						['type' => 'text', 'name' => 'fiscal_name', 'id' => 'fiscal_name', 'label' => 'Nombre Fiscal SAT', 'value' => $societe->fiscal_name, 'tdclass' => 'fieldrequired'],
						['type' => 'text', 'name' => 'rfc', 'id' => 'rfc', 'label' => 'RFC', 'value' => strtoupper($societe->idprof1), 'tdclass' => 'fieldrequired'],
						['type' => 'text', 'name' => 'cp', 'id' => 'cp', 'label' => 'Código Postal', 'value' => $societe->zip, 'tdclass' => 'fieldrequired'],
						['type' => 'select', 'name' => 'regimen', 'id' => 'regimen', 'label' => 'Régimen Fiscal', 'values' => $regimen, 'default' => $societe->forme_juridique_code, 'tdclass' => 'fieldrequired'],


					);

					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('selectProductFiscal'), '', 'confirm_valid', $formquestion, 0, 1, 380, 640);
					print $formconfirm;

					echo '<script>$(document).ready(function(){
						$(".flat").css("width","20rem");
					});</script>';
				}
			}
		}
	}

	public function printFieldListValue(&$parameters, &$objp, &$action)
	{

		// if (in_array($parameters['currentcontext'], ['paiementcard'])) {
		// 	echo '<td>Hola</td><td>Caracola</td>';
		// }
	}

	public function printObjectLine(&$parameters, &$objp, &$action)
	{
		var_dump($objp);
		if (in_array($parameters['currentcontext'], ['paiementcard'])) {
			echo '<td>Adios</td><td>Caracola</td>';
		}
	}

	public function addMoreActionsButtons(&$parameters, &$object, &$action)
	{
		// if (in_array($parameters['currentcontext'], ['globalcard'])) {
		// 	echo '<pre>';
		// 	var_dump($object);
		// 	exit;
		// }
		// print '<button class="butAction">Pago CFDI</button>';
	}
}
