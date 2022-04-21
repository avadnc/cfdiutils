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
require_once 'cfdifacture.class.php';
require_once 'cfdiutils.class.php';
require_once 'cfdipayment.class.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
		global $db, $conf, $user, $langs, $form;

		$error = 0; // Error counter
		$resultdata = []; //Data for response
		$confirm = GETPOST('confirm', 'alpha');

		if (in_array($parameters['currentcontext'], ['productcard'])) {
			$cfdiproduct = new Cfdiproduct($db);
			$cfdiproduct->fetch($object->id);
			$cfdiproduct->getFiscal();

			if ($action == "confirm_valid" && $confirm == "yes") {
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

			if ($action == "confirm_delete" && $confirm == "yes") {
				$cfdiproduct->deleteFiscal();
			}
		}


		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], ['thirdpartycomm', 'thirdpartycard'])) {
			$societe = new Cfdisociete($db);
			$societe->fetch($object->id);
			$societe->getFiscal();

			if ($action == "confirm_valid" && $confirm == "yes") {
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
					$societe->idprof1 = dol_strtoupper($rfc);
					$societe->forme_juridique_code = $regimen;
					$result = $societe->createFiscal();
				} else {
					$societe->fiscal_name = $fiscal_name;
					$societe->zip = $zip;
					$societe->idprof1 = dol_strtoupper($rfc);
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

			if ($action == "confirm_delete" && $confirm == "yes") {
				$societe->deleteFiscal();
			}
		}


		/* Stamp Invoice */

		if (in_array($parameters['currentcontext'], ['invoicecard'])) {

			$thirdparty = new Societe($this->db);
			//---------------------
			dol_include_once('/cfdiutils/core/modules/cfdiutils/' . $conf->global->CFDI_FACTURE_ADDON . '.php');
			$model = new $conf->global->CFDI_FACTURE_ADDON($this->db);

			//--------------------
			$invoice = new Cfdifacture($db);
			$invoice->fetch($object->id);
			$invoice->getStamp();

			//TODO Add actions for free lines entry
			if ($action == "confirm_valid_stamp" && $confirm == "yes") {
				if ($user->rights->cfdiutils->stamp) {
					$condicion_pago = GETPOST('condicion_pago');
					$forma_pago = GETPOST('forma_pago');
					$metodo_pago = GETPOST('metodo_pago');
					$exportacion = GETPOST('exportacion');
					$usocfdi = GETPOST('usocfdi');


					if ($condicion_pago < 0 || $forma_pago < 0 || $metodo_pago < 0 || $exportacion < 0 || $usocfdi < 0) {
						setEventMessage('Faltan datos fiscales', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
						exit;
					}

					if ($metodo_pago == "PPD" && $forma_pago != "99") {
						setEventMessage('Si el método de pago es PPD, la forma de pago debe ser Por Definir', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
						exit;
					}
					if ($forma_pago == "99" && $metodo_pago != "PPD") {
						setEventMessage('Si la forma de pago es Por Definir, el método de pago debe ser PPD', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
						exit;
					}

					if (!$invoice->fecha_emision || $invoice->error) {

						// $sql = "UPDATE ref from " . MAIN_DB_PREFIX . "facture set ref = '" . $model->getNextValue($thirdparty, $object) . "' where rowid = " . $object->id;
						// $result = $this->db->query($sql);
						$nextval =	$model->getNextValue($thirdparty, $object);
						$invoice->ref = $nextval;
						$invoice->update($user, 1);

						//if stock module enabled update reference in llx_stock_mouvement
						if ($conf->stock->enabled) {
							//UPDATE llx_stock_mouvement SET label = "Factura F-0004 validada" WHERE label = "Factura REM-0002 validada"
						}
						$invoice->fetch($object->id);
						$invoice->getStamp();

						$fecha_emision = date('Y-m-d H:i:s');
						$fecha_emision = str_replace(" ", "T", $fecha_emision);
						$invoice->usocfdi = $usocfdi;
						$invoice->condicion_pago = $condicion_pago;
						$invoice->forma_pago = $forma_pago;
						$invoice->metodo_pago = $metodo_pago;
						$invoice->exportacion = $exportacion;

						if (!$invoice->error) {
							$invoice->pac = $conf->global->CFDIUTILS_PAC;
						}

						//Note: update fk_mode_reglement

						$oldpath = $conf->facture->multidir_output[$object->entity] . '/' . dol_sanitizeFileName($object->ref);
						$newpath =	$conf->facture->multidir_output[$object->entity] . '/' . $nextval;
						rename($oldpath, $newpath);
						$sql = "SELECT id from " . MAIN_DB_PREFIX . "c_paiement where code ='" . $forma_pago . "'";
						$result = $this->db->query($sql);
						$obj = $this->db->fetch_object($result);
						$sql = "UPDATE " . MAIN_DB_PREFIX . "facture set fk_mode_reglement =" . $obj->id . " WHERE rowid = " . $object->id;
						$result = $this->db->query($sql);

						$invoice->error ?: $invoice->fecha_emision = $fecha_emision; //If error not exists assign fecha_emision
						$invoice->error ? $result =  $invoice->updateStamp() : $result = $invoice->createStamp(); //If error exists update

						if ($result == "InsertSuccess") {

							$header = $invoice->getHeader();
							$emisor = $invoice->getEmisor();
							$receptor =	$invoice->getReceptor();
							$conceptos = $invoice->getLines();

							$cfdiutils = new Cfdiutils($db);
							//Add doc relacionados
							$docrelations = $invoice->getRelations();
							$xml = $cfdiutils->stampXML($header, $emisor, $receptor, $conceptos, $docrelations ? $docrelations : null);
							$filename = dol_sanitizeFileName($nextval);
							$file_xml = fopen($newpath . "/" . $filename . ".xml", "w");
							fwrite($file_xml, utf8_encode($xml));
							fclose($file_xml);
							dol_include_once('/cfdiutils/pac/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '.class.php');
							$classname = ucfirst(dol_strtolower($conf->global->CFDIUTILS_PAC));
							$pactimbrado = new $classname($db);
							$resultdata = $pactimbrado->timbrar($xml);
						}
					} else {

						if ($invoice->pac != $conf->global->CFDIUTILS_PAC) {
							setEventMessage('No se puede timbrar en PAC distinto al original', 'errors');
							header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
							exit;
						}

						$header = $invoice->getHeader();
						$emisor = $invoice->getEmisor();
						$receptor =	$invoice->getReceptor();
						$conceptos = $invoice->getLines();
						$cfdiutils = new Cfdiutils($db);
						$filename = dol_sanitizeFileName($object->ref);
						$filedir = $conf->facture->multidir_output[$object->entity] . '/' . dol_sanitizeFileName($object->ref);

						if (file_exists($filedir . '/' . $filename . '.xml')) {
							$xml = file_get_contents($filedir . '/' . $filename . '.xml');
						} else {

							$cfdiutils = new Cfdiutils($db);
							$docrelations = $invoice->getRelations();
							$xml = $cfdiutils->stampXML($header, $emisor, $receptor, $conceptos, $docrelations ? $docrelations : null);
							$file_xml = fopen($filedir . "/" . $filename . ".xml", "w");
							fwrite($file_xml, utf8_encode($xml));
							fclose($file_xml);
						}

						dol_include_once('/cfdiutils/pac/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '.class.php');
						$classname = ucfirst(dol_strtolower($conf->global->CFDIUTILS_PAC));
						$pactimbrado = new $classname($db);
						$resultdata = $pactimbrado->timbrar($xml);
					}

					if ($resultdata) {
						if ($resultdata['msg'] == '200') {
							$file_xml = fopen($filedir . "/" . $filename . '-' . $resultdata['data']['uuid'] . ".xml", "w");
							fwrite($file_xml, utf8_encode($resultdata['data']['xmlFile']));
							fclose($file_xml);
							$dataXML = $cfdiutils->getData($resultdata['data']['xmlFile']);
							$invoice->uuid = $dataXML['UUID'];
							$invoice->cer_sat = $dataXML['NoCertificadoSAT'];
							$invoice->cer_csd = $dataXML['NoCertificado'];
							$invoice->fecha_timbrado = $dataXML['FechaTimbrado'];
							$result = $invoice->updateStamp();

							if ($result == "InsertSuccess") {
								$this->__createQr($invoice);
								setEventMessage('Factura timbrada correctamente', 'mesgs');
								header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
								exit;
							} else {
								setEventMessage('Error al registrar los datos fiscales de la factura', 'errors');
								header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
								exit;
							}
						}

						if ($resultdata['msg'] == '400') {
							$message = $resultdata['data'];
							$message = explode(' - ', $message);
							$invoice->error = $message[0];
							$invoice->updateStamp();
							setEventMessage($resultdata['data'], 'errors');
							header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
							exit;
						}
					} else {
						setEventMessage('No hubo respuesta del PAC', 'errors');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
						exit;
					}
					//Generate QR Code with xml data

				}
			}

			if ($action == "delete" && $confirm == "yes") {
				if ($invoice->fecha_emision || $invoice->uuid) {
					setEventMessage('Se inició proceso de timbrado o la factura ya está timbrada', 'errors');
					header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
					exit;
				}
			}

			if ($action == "confirm_valid_addrel" && $confirm == "yes") {
				$factura_uuid = GETPOST('factura_uuid');
				$tiporelacion = GETPOST('tiporelacion');

				if ($tiporelacion < 0) {
					setEventMessage('Debe seleccionar el tipo de relación', 'errors');
					header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
					exit;
				}


				if ($factura_uuid < 0) {
					$uuid_ext = GETPOST('uuid_ext');
					$result = $invoice->createRelationship($tiporelacion, null, $uuid_ext);
				} else {
					$result = $invoice->createRelationship($tiporelacion, $factura_uuid);
				}

				if ($result == "InsertSuccess") {
					setEventMessage('Factura relacionada correctamente', 'mesgs');
					header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
					exit;
				}
			}

			if ($action == "deleteRelation" && $confirm == "yes") {
				$id = GETPOST('id');

				if ($id > 0) {
					$result = $invoice->deleteRel($id);
					if ($result > 0) {
						setEventMessage('Relación eliminada con éxito', 'mesgs');
						header('Location: ' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id);
						exit;
					}
				}
			}

			if ($action == "recover_stamp") {
			}

			if ($action == 'confirm_cambio_tercero' && $confirm == 'yes') {

				if (GETPOST('nuevotercero') > 0) {

					$date_facture = dol_mktime(0, 0, 0, GETPOST('fechafacmonth'), GETPOST('fechafacday'), GETPOST('fechafacyear'));

					$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'facture';
					$sql .= " SET fk_soc=" . GETPOST('nuevotercero') . "";
					if (GETPOST('fechafacyear') && $date_facture != $object->date) {
						$sql .= " , datef='" . $db->idate($date_facture) . "'";
						setEventMessages($langs->trans('fechamodificado'), null, 'mesgs');
					}
					$sql .= ' WHERE rowid = ' . $object->id;

					dol_syslog(__METHOD__ . "::nuevotercero sql=" . $sql);
					$resql = $db->query($sql);
					setEventMessages($langs->trans('terceromodificado'), null, 'mesgs');
				} else {
					setEventMessages($langs->trans('No se realizaron cambios'), null, 'mesgs');
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

		/* Payment */
		if (in_array($parameters['currentcontext'], ['paiementcard'])) {


			if ($action == "confirm_validate") {
			}
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
		global $conf, $user, $langs;
		$form = new Form($this->db);

		//Actions for Products
		if (in_array($parameters['currentcontext'], ['productcard'])) {
			if ($action != "create") {
				$cfdiproduct = new Cfdiproduct($this->db);
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

				//Actions

				if ($action == "addfiscal") {

					$umed = $cfdiproduct->getDictionary('umed');
					$claveprodserv = $cfdiproduct->getDictionary('claveprodserv');
					$objetoimp = $cfdiproduct->getDictionary('objetoimp');

					$formquestion = array(

						'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
						['type' => 'select', 'name' => 'umed', 'id' => 'umed', 'label' => 'Unidad de Medida', 'values' => $umed],
						['type' => 'select', 'name' => 'claveprodserv', 'id' => 'claveprodserv', 'label' => 'ClaveProdServ', 'values' => $claveprodserv],
						['type' => 'select', 'name' => 'objetoimp', 'id' => 'objetoimp', 'label' => 'Objeto de Impuesto', 'values' => $objetoimp],


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
					$objetoimp = $cfdiproduct->getDictionary('objetoimp');

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

		//Actions for thirdparties
		if (in_array($parameters['currentcontext'], ['thirdpartycomm', 'thirdpartycard'])) {

			if ($action != "create") {

				// Validate if object is customer
				if ($object->client == 1) {

					$societe = new Cfdisociete($this->db);
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
							['type' => 'text', 'name' => 'fiscal_name', 'id' => 'fiscal_name', 'label' => 'Nombre Fiscal SAT', 'value' => dol_strtoupper($societe->name), 'tdclass' => 'fieldrequired'],
							['type' => 'text', 'name' => 'rfc', 'id' => 'rfc', 'label' => 'RFC', 'value' => dol_strtoupper($societe->idprof1), 'tdclass' => 'fieldrequired'],
							['type' => 'text', 'name' => 'cp', 'id' => 'cp', 'label' => 'Código Postal', 'value' => $societe->zip, 'tdclass' => 'fieldrequired'],
							['type' => 'select', 'name' => 'regimen', 'id' => 'regimen', 'label' => 'Régimen Fiscal', 'values' => $regimen, 'default' => $societe->forme_juridique_code, 'tdclass' => 'fieldrequired'],


						);

						$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('selectProductFiscal'), '', 'confirm_valid', $formquestion, 0, 1, 380, 640);
						print $formconfirm;

						echo '<script>$(document).ready(function(){
						$(".flat").css("width","20rem");
					});</script>';
					}

					//actions
					if ($action == "modifyfiscal") {

						$regimen = $societe->getFormeJuridique();

						//TODO: Add Municipio, CODE Municipio..... etc...
						$formquestion = array(

							'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>',
							['type' => 'text', 'name' => 'fiscal_name', 'id' => 'fiscal_name', 'label' => 'Nombre Fiscal SAT', 'value' => $societe->fiscal_name, 'tdclass' => 'fieldrequired'],
							['type' => 'text', 'name' => 'rfc', 'id' => 'rfc', 'label' => 'RFC', 'value' => dol_strtoupper($societe->idprof1), 'tdclass' => 'fieldrequired'],
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

		//Actions for Invoices
		if (in_array($parameters['currentcontext'], ['invoicecard'])) {

			$invoice = new Cfdifacture($this->db);
			$payterm = new Paytype($this->db);
			$thirdparty = new Societe($this->db);
			$thirdparty->fetch($object->socid);
			$invoice->fetch($object->id);
			$invoice->getStamp();

			dol_include_once('/cfdiutils/pac/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '/' . dol_strtolower($conf->global->CFDIUTILS_PAC) . '.class.php');
			$classname = ucfirst(dol_strtolower($conf->global->CFDIUTILS_PAC));
			$pactimbrado = new $classname($this->db);

			//Show data from Invoice

			if (
				$object->type == Facture::TYPE_STANDARD && $object->status == Facture::STATUS_VALIDATED ||
				$object->type == Facture::TYPE_CREDIT_NOTE && $object->status == Facture::STATUS_VALIDATED ||
				$object->type == Facture::TYPE_STANDARD && $object->status == Facture::STATUS_CLOSED ||
				$object->type == Facture::TYPE_CREDIT_NOTE && $object->status == Facture::STATUS_CLOSED
			) {

				if ($action != "create") {

					$invoice_relations = $invoice->getRelations();

					if (!$invoice->uuid || $invoice->error && !$invoice->uuid) {

						//Invoice Relationship
						print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">' . $langs->trans('dataFiscalRelationship') . '</td></tr>';
						print '<tr><td align="center" colspan="2"><a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=addrelation" class="butAction">Añadir Relación</a></tr>';
						if ($invoice_relations) {
							foreach ($invoice_relations as $invrel) {

								echo $invrel['ref'] ? '<tr><td>' . $invrel['ref'] . '</td><td>' . $invrel['label'] . '<br>' .  $invrel['uuid'] . '&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=deleteRelation&id=' . $invrel['id'] . '"><span class="fa fa-trash"></a></span></td></tr>' : '<tr><td>UUID Externo</td><td>' . $invrel['label'] . '<br>' .  $invrel['uuid'] . '&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=deleteRelation&id=' . $invrel['id'] . '"><span class="fa fa-trash"></span></a></td></tr>';
							}
						}
					} else {

						$emisor = $invoice->getEmisor();
						$receptor = $invoice->getReceptor();

						if ($conf->multicurrency->enabled) {
							$expression = 'id=' . $invoice->uuid . '&re=' . $emisor['Rfc'] . '&rr=' . $receptor['Rfc'] . '&tt=' . $invoice->multicurrency_total_ttc . '&fe=' . substr($invoice->cer_sat, -8);
						} else {
							$expression = 'id=' . $invoice->uuid . '&re=' . $emisor['Rfc'] . '&rr=' . $receptor['Rfc'] . '&tt=' . $invoice->total_ttc . '&fe=' . substr($invoice->cer_sat, -8);
						}

						$data_cbb = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?' . $expression;
						$filedir = $conf->facture->multidir_output[$object->entity] . '/' . dol_sanitizeFileName($object->ref);
						$qr = file_get_contents($filedir . "/" . $object->ref . '-' . $invoice->uuid . ".png");
						$sql = "SELECT label FROM " . MAIN_DB_PREFIX . "c_cfdiutils_usocfdi WHERE code = '" . $invoice->usocfdi . "'";
						$dataset = $this->db->query($sql);
						$formapago = $this->db->fetch_object($dataset);
						// Verify if invoice is stamped
						print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">Información Fiscal</td></tr>';
						print '<tr><td>Uso del CFDI:</td><td>' . $invoice->usocfdi . ' - ' . $formapago->label . '</td></tr>';
						print '<tr><td>Método de Pago:</td><td>' . $invoice->metodo_pago . '</td></tr>';
						print '<tr><td>UUID:</td><td><a href="' . $data_cbb . '" target="__blank">' . $invoice->uuid . '</a></td></tr>';
						print '<tr><td>Fecha de Emision:</td><td>' . $invoice->fecha_emision . '</td></tr>';
						print '<tr><td>Fecha de Timbrado:</td><td>' . $invoice->fecha_timbrado . '</td></tr>';

						$qr ? print '<tr><td>QR</td><td align="left"><img width="100px" height="100px" src="data:image/png;base64,' . base64_encode($qr) . '"></td></tr>' : '';



						print '<script>$(document).ready(function(){
							//$(".butActionDelete ").hide();
						});</script>';


						if ($invoice_relations) {
							print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">' . $langs->trans('dataFiscalRelationship') . '</td></tr>';
							foreach ($invoice_relations as $invrel) {

								echo $invrel['ref'] ? '<tr><td>' . $invrel['ref'] . '</td><td>' . $invrel['label'] . '<br>' .  $invrel['uuid'] . '&nbsp;</td></tr>' : '<tr><td>UUID Externo</td><td>' . $invrel['label'] . '<br>' .  $invrel['uuid'] . '</td></tr>';
							}
						}

						print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">' . $langs->trans('fiscalActions') . '</td></tr>';
						print '<tr><td align="center" colspan="2"><a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=status_stamp" class="butAction" style="background:#00549f;">Consultar Estado CFDI</a>';
						if ($user->rights->cfdiutils->cancel) {
							print '<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=cancel_stamp" class="butAction" style="background:#880000 !important">Solicitar Cancelación</a>';
						}
						if (!file_exists($filedir . "/" . $object->ref . '-' . $invoice->uuid . ".xml")) {
							print '<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=recover_stamp" class="butAction">Recuperar XML</a>';
						}
						print '</td></tr>';
					}

					//Actions

					//Action for stamp CFDI
					if ($action == "stamp") {

						if ($user->rights->cfdiutils->stamp) {

							//TODO: User rights
							//---------------------
							dol_include_once('/cfdiutils/core/modules/cfdiutils/' . $conf->global->CFDI_FACTURE_ADDON . '.php');
							$model = new $conf->global->CFDI_FACTURE_ADDON($this->db);

							//--------------------

							$condicion_pago = $payterm->getDictionary('payment_term', false, "PaymentConditionShort");
							$forma_pago = $payterm->getDictionary('paiement', false, "PaymentType");
							$metodo_pago = $invoice->getDictionary('_metodopago');
							$exportacion = $invoice->getDictionary('_exportacion');
							$usocfdi = $invoice->getDictionary('_usocfdi');

							if (!$invoice->fecha_emision) {

								$formquestion = array(

									'text' => 'Serie y Número que se le asignará a la factura una vez timbrada <b>' . $model->getNextValue($thirdparty, $object) . '</b>',
									// ['type' => 'onecolumn', 'value' => ],
									['type' => 'select', 'name' => 'usocfdi', 'id' => 'usocfdi', 'label' => 'Uso del CFDI', 'values' => $usocfdi, 'default' => $invoice->usocfdi ? $invoice->usocfdi : $invoice->usocfdi, 'tdclass' => 'fieldrequired'],
									['type' => 'select', 'name' => 'condicion_pago', 'id' => 'condicion_pago', 'label' => 'Condiciones de pago', 'values' => $condicion_pago, 'default' => $object->cond_reglement_code ? $object->cond_reglement_code : $invoice->condicion_pago, 'tdclass' => 'fieldrequired'],
									['type' => 'select', 'name' => 'forma_pago', 'id' => 'forma_pago', 'label' => 'Forma de pago', 'values' => $forma_pago, 'default' =>  $object->mode_reglement_code ? $object->mode_reglement_code : $invoice->forma_pago, 'tdclass' => 'fieldrequired'],
									['type' => 'select', 'name' => 'metodo_pago', 'id' => 'metodo_pago', 'label' => 'Método de pago', 'values' => $metodo_pago, 'default' => $invoice->metodo_pago ? $invoice->metodo_pago : $invoice->metodo_pago, 'tdclass' => 'fieldrequired'],
									['type' => 'select', 'name' => 'exportacion', 'id' => 'exportacion', 'label' => 'Exportación', 'values' => $exportacion, 'default' => $invoice->exportacion ? $invoice->exportacion : $invoice->exportacion, 'tdclass' => 'fieldrequired'],
									['type' => 'onecolumn', 'value' => '**Atención al hacer click en SI usted estará timbrando la factura fiscalmente ante el SAT**<br>**No se podrán realizar cambios en el comprobante en el caso de que haya datos erróneos**'],
									// ['type'=> 'onecolumn', 'value' => '<div align="center"><bu	tton class="butAction" style="background:red;">Timbrar</button></div>']
									// ['type' => 'onecolumn', 'value' => '**No se podrán realizar cambios en el comprobante en el caso de que haya datos erróneos**',]

								);
							} else {

								$formquestion = array(

									'text' => '<h2>' . $langs->trans("dataFiscalCFDI") . '</h2>',
									['type' => 'select', 'name' => 'usocfdi', 'id' => 'usocfdi', 'label' => 'Uso del CFDI', 'values' => $usocfdi, 'default' => $invoice->usocfdi ? $invoice->usocfdi : $invoice->usocfdi, 'tdclass' => 'fieldrequired', 'select_disabled' => $invoice->error ? 0 : 1],
									['type' => 'select', 'name' => 'condicion_pago', 'id' => 'condicion_pago', 'label' => 'Condiciones de pago', 'values' => $condicion_pago, 'default' => $object->cond_reglement_code ? $object->cond_reglement_code : $invoice->condicion_pago, 'tdclass' => 'fieldrequired', 'select_disabled' => $invoice->error ? 0 : 1],
									['type' => 'select', 'name' => 'forma_pago', 'id' => 'forma_pago', 'label' => 'Forma de pago', 'values' => $forma_pago, 'default' =>  $object->mode_reglement_code ? $object->mode_reglement_code : $invoice->forma_pago, 'tdclass' => 'fieldrequired', 'select_disabled' => $invoice->error ? 0 : 1],
									['type' => 'select', 'name' => 'metodo_pago', 'id' => 'metodo_pago', 'label' => 'Método de pago', 'values' => $metodo_pago, 'default' => $invoice->metodo_pago ? $invoice->metodo_pago : $invoice->metodo_pago, 'tdclass' => 'fieldrequired', 'select_disabled' => $invoice->error ? 0 : 1],
									['type' => 'select', 'name' => 'exportacion', 'id' => 'exportacion', 'label' => 'Exportación', 'values' => $exportacion, 'default' => $invoice->exportacion ? $invoice->exportacion : $invoice->exportacion, 'tdclass' => 'fieldrequired', 'select_disabled' => $invoice->error ? 0 : 1],
									['type' => 'onecolumn', 'value' => '**Atención al hacer click en SI usted estará timbrando la factura fiscalmente ante el SAT**<br>**No se podrán realizar cambios en el comprobante en el caso de que haya datos erróneos**'],
									// ['type'=> 'onecolumn', 'value' => '<div align="center"><bu	tton class="butAction" style="background:red;">Timbrar</button></div>']
									// ['type' => 'onecolumn', 'value' => '**No se podrán realizar cambios en el comprobante en el caso de que haya datos erróneos**',]
								);
							}


							$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('stampFiscal'), '', 'confirm_valid_stamp', $formquestion, 0, 1, 420, 600);
							print $formconfirm;

							echo '<script>$(document).ready(function(){
							$(".select2-container").css("width","20rem");
							});</script>';
						}
					}

					//request status stamp
					if ($action == "status_stamp") {

						$result = $pactimbrado->consultar($expression);

						// $formquestion = array(
						// 	['type' => 'onecolumn', 'value' => '<span>Estatus:</span><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->CodigoEstatus . '</span>'],
						// 	['type' => 'onecolumn', 'value' => '<span>Es Cancelable:</span><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->EsCancelable . '</span>'],
						// 	['type' => 'onecolumn', 'value' => '<span>Estado:</span><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->Estado . '</span>'],
						// 	$result->ConsultaResult->EstatusCancelacion ? ['type' => 'onecolumn', 'value' => '<span>Estatus Cancelación:</span><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->EstatusCancelacion . '</span>'] : null,
						// 	$result->ConsultaResult->ValidacionEFOS ? ['type' => 'onecolumn', 'value' => '<span>Estatus:</span><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->ValidacionEFOS . '</span>'] : null,
						// );

						// $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('statusFiscalCFDI'), '', '', $formquestion, null, 1, 320, 600);
						// print $formconfirm;

						print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">Estado del Comprobante Fiscal</td></tr>';
						print '<tr><td><span>Código Estatus:</span></td><td><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->CodigoEstatus . '</span></td></tr>';
						print '<tr><td><span>Es Cancelable:</span></td><td><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->EsCancelable . '</span></td></tr>';
						print '<tr><td><span>Estado:</span></td><td><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->Estado . '</span></td></tr>';
						print '<tr><td><span>Estatus Cancelación:</span></td><td><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->EstatusCancelacion . '</span></td></tr>';
						print '<tr><td><span>Validación Estatus:</span></td><td><span class="badge  badge-status1 badge-status">' . $result->ConsultaResult->ValidacionEFOS . '</span></td></tr>';
					}

					//request cancel
					if ($action == "cancel_stamp") {
						if ($user->rights->cfdiutils->cancel) {
							if ($invoice->uuid) {
								$data_rel = $invoice->getDictionary('_tiporelacion');
								$data = $invoice->getRelationship($object->socid);
								if ($data) {

									$formquestion = array(

										'text' => '<h2>' . $langs->trans("stampCancel") . '</h2>',
										['type' => 'select', 'name' => 'tiporelacion', 'id' => 'tiporelacion', 'label' => 'Tipo de relación', 'values' => $data_rel, 'tdclass' => 'fieldrequired'],
										['type' => 'select', 'name' => 'factura_uuid', 'id' => 'factura_uuid', 'label' => 'Factura UUID', 'values' => $data],
										['type' => 'onecolumn', 'value' => '**En caso de que el UUID se generara fuera del sistema, introducirlo a mano',],
										['type' => 'text', 'name' => 'uuid_ext', 'id' => 'uuid_ext', 'label' => 'UUID Externo']
									);

									$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('stampCancel'), '', 'confirm_stampCancel', $formquestion, 0, 1, 320, 600);
									print $formconfirm;

									echo '<script>$(document).ready(function(){
											$(".select2-container").css("width","20rem");
										});</script>';
								}
							}
						}
					}

					if ($action == "addrelation") {
						if ($user->rights->cfdiutils->stamp) {
							if (!$invoice->fecha_emision || $invoice->error) {

								$data_rel = $invoice->getDictionary('_tiporelacion');

								$data = $invoice->getRelationship($object->socid);
								if ($data) {

									$formquestion = array(

										'text' => '<h2>' . $langs->trans("dataFiscalRelationship") . '</h2>',
										['type' => 'select', 'name' => 'tiporelacion', 'id' => 'tiporelacion', 'label' => 'Tipo de relación', 'values' => $data_rel, 'tdclass' => 'fieldrequired'],
										['type' => 'select', 'name' => 'factura_uuid', 'id' => 'factura_uuid', 'label' => 'Factura UUID', 'values' => $data],
										['type' => 'onecolumn', 'value' => '**En caso de que el UUID se generara fuera del sistema, introducirlo a mano',],
										['type' => 'text', 'name' => 'uuid_ext', 'id' => 'uuid_ext', 'label' => 'UUID Externo']

										// ['type'=> 'onecolumn', 'value' => '<div align="center"><bu	tton class="butAction" style="background:red;">Timbrar</button></div>']
										// ['type' => 'onecolumn', 'value' => '**No se podrán realizar cambios en el comprobante en el caso de que haya datos erróneos**',]

									);

									$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('adddataFiscalRelationship'), '', 'confirm_valid_addrel', $formquestion, 0, 1, 320, 600);
									print $formconfirm;

									echo '<script>$(document).ready(function(){
											$(".select2-container").css("width","20rem");
										});</script>';
								}
							} else {
								setEventMessage('No se pueden añadir documentos relacionados<br>Ya se mandó a timbrar ' . $invoice->fecha_emision, 'errors');
							}
						}
					}

					//Actions for module Pedimentos.
					if ($conf->pedimentos->enabled) {
						if ($action == "addline") {
						}
					}
				}
			}
		}

		if (in_array($parameters['currentcontext'], ['paymentcard'])) {

			$payment = new Cfdipayment($this->db);
			$payment->fetch($object->id);
			$payment->getDataPayment();

			// echo '<pre>';var_dump($object);echo '</pre>';

			// $html = '<div class="fichecenter"><div class="fichehalfleft">';
			// $html .= '<div class="div-table-responsive-no-min">';
			// $html .= '<table class="liste formdoc noborder centpercent">';
			// $html .= '<tbody>';
			// $html .= '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">Archivos Vinculados</td></tr>';
			// $html .= '<tr><td align="left">Payment-0001.pdf</td><td>16/06/2022</td><tr>';
			// $html .= '<tr><td align="left">Payment-0001.xml</td><td>16/06/2022</td><tr>';
			// $html .= '<tr><td align="left">Payment-0001.png</td><td>16/06/2022</td><tr>';
			// $html .= '<tr><td align="center" colspan="2">';
			// $html .= '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_addrelation" class="butAction">Regenerar PDF</a>';
			// $html .= '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_stamp" class="butAction">Solicitar XML</a>';
			// $html .= '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_stamp" class="butAction">Enviar E-Mail</a>';
			// $html .= '</td></tr>';
			// $html .= '</tbody>';
			// $html .= '</table>';
			// $html .= '</div>';
			// $html .= '</div>';

			// //TODO: call ajax to create view. Show buttons and files
			// print "<script>
			// 	$(document).ready(function(){
			// 	$('.tabsAction').append('" . $html . "');
			// 	});
			// 	</script>";
			print '<style>
			.tablediv {
				border:1px solid black;
				margin-bottom:2rem;
				display:table;
			}

			.tablediv .row{
				display: table-row;
			}

			.tablediv .theader{
				background-color:red;
				color:white;
				vertical-align:middle;
			}

			.tablediv .theader .cell{
				padding-bottom:1.2rem;
				font-weight:bold;
				font-size: 1.2rem;
				padding-top:1rem;
			}

			.tablediv .row .cell{
				padding-top:0.5rem;
				display: table-cell;
				padding-left: 0.5rem;
				padding-right:0.5rem;
			}
			</style>';

			print '<tr><td>Moneda</td><td>' . $payment->multicurrency_code . '</td></tr>';
			print '<tr><td>Tipo de Cambio</td><td>' . $payment->multicurrency_tx . '</td></tr>';
			print '<tr class="liste_titre"><td class="titlefield" align="center" colspan="2">' . $langs->trans('CFDI') . '</td></tr>';
			print '<tr><td align="center" colspan="2">';
			$htmlmore = "";
			if (!$payment->uuid) {
				$sql = "SELECT pf.fk_facture as rowid, pf.amount, pf.multicurrency_code,pf.multicurrency_tx,pf.multicurrency_amount FROM " . MAIN_DB_PREFIX . "paiement_facture pf";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture f on pf.fk_facture = f.rowid";
				$sql .= " WHERE fk_paiement =" . $object->id . " ORDER BY f.ref ASC";
				$resql = $this->db->query($sql);

				if ($resql) {
					$num = $this->db->num_rows($resql);
					$i = 0;
					$error = 0;
					$error2 = 0;
					$error_stamp = 0;
					while ($i < $num) {

						$obj = $this->db->fetch_object($resql);
						$invoice = new Cfdifacture($this->db);
						$invoice->fetch($obj->rowid);

						$invoice->getStamp();

						if ($invoice->uuid) {
							if ($invoice->metodo_pago == "PUE") {
								$error2++;
							} else {

								$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "paiement_facture where fk_facture =" . $invoice->id;
								$dataset_parcialidad = $this->db->query($sql);
								$result = $this->db->fetch_object($dataset_parcialidad);
								if ($result->nb > 1) {

									$sql = "SELECT p.ref, cp.uuid, pf.amount, pf.multicurrency_code,pf.multicurrency_tx,pf.multicurrency_amount from " . MAIN_DB_PREFIX . "paiement_facture pf";
									$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "paiement p on p.rowid = pf.fk_paiement";
									$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cfdiutils_payment_det pd on pf.fk_facture = pd.fk_paiement_facture";
									$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cfdiutils_payment cp on p.rowid = pd.fk_cfdipay";
									$sql .= " WHERE pf.fk_facture in(" . $invoice->id . ") AND pf.fk_paiement NOT IN(" . $payment->id . ")";
									// $sql .= " AND p.datep < '". dol_print_date($payment->datepaye, '%Y-%m-%d')."'";
									// echo  dol_print_date($payment->datepaye, '%Y-%m-%d');
									echo $sql;
									$dataset_pay =	$this->db->query($sql);
									$numrows_pay = $this->db->num_rows($dataset_pay);
									$j = 0;
									$pay_currency = 0;
									$pay_multicurrency = 0;
									while ($j < $numrows_pay) {
										$obj_pago = $this->db->fetch_object($dataset_pay);

										if (!$obj_pago->uuid) {
											print '<div class="row"><div class="cell"><span class="badge badge-status6 badge-status"> El pago ' . $obj_pago->ref . ' de la factura ' . $invoice->ref . ' no está timbrado</span></div></div>';
											$error_stamp++;
										}
										if ($invoice->multicurrency_code == $conf->currency) {
											$pay_currency += $obj_pago->amount;
										} else {
											if ($obj_pago->multicurrency_code == $conf->currency) {
												$pay_multicurrency += $obj_pago->amount;
											} else {
												$pay_multicurrency += $obj_pago->multicurrency_amount;
											}
										}

										$j++;
									}
								}
								if ($invoice->multicurrency_code == $conf->currency) {
									$saldo_anterior = $invoice->total_ttc - $pay_currency;
									$pago = price2num($obj->amount, "CT");
									$saldo_insoluto = price2num(($obj->amount + $pay_currency) - $invoice->total_ttc, "CT");
									$equivalencia = 1;
								} else {

									//Aclarar pagos si son en pesos que pagan dolares o si son dolares que pagan dolares
									if ($obj->multicurrency_code == $invoice->multicurrency_code) {
										$equivalencia = 1;
									} else {
										$equivalencia = "CONFIGURAR";
									}
									$saldo_anterior = $invoice->multicurrency_total_ttc - $pay_multicurrency;
									$pago = $obj->multicurrency_amount;
									$saldo_insoluto = $invoice->multicurrency_total_ttc - ($obj->multicurrency_amount + $pay_multicurrency);
								}

								// echo '<pre>';var_dump($payment);exit;
								$htmlmore .= '<div class="row">';
								$htmlmore .= '<div class="cell">' . $invoice->ref . '</div>';

								$htmlmore .= $invoice->multicurrency_code ? '<div class="cell">' . $invoice->multicurrency_code . '</div>' : '<div class="cell">' . $conf->currency . '</div>';

								$htmlmore .= '<div class="cell"> ' . $result->nb . ' parcialidad</div>';

								$htmlmore .= '<div class="cell">' . $saldo_anterior . '</div>';

								$htmlmore .= '<div class="cell">' . $pago . '</div>';

								$htmlmore .= '<div class="cell">' . abs($saldo_insoluto) . '</div>';

								$htmlmore .= '<div class="cell">' . $equivalencia . '</div>';
								$htmlmore .= '</div>';
							}
						} else {
							$error++;
						}

						$saldo_anterior = 0;
						$pago = 0;
						$saldo_insoluto = 0;
						$i++;
					}
				}
				print '</div>';
				if ($error2) {
					print '<span class="badge badge-status2 badge-status">No se puede timbrar el pago, la factura ' . $invoice->ref . ' el método de pago es PUE</span>';
				} else if (!$error) {
					print '<div><h2>Documento/s Relacionado</h2></div>';
					print '<div class="tablediv">';
					print '<div class="row theader">';
					print '<div class="cell">Ref</div>';
					print '<div class="cell">Moneda</div>';
					print '<div class="cell">Parcialidad</div>';
					print '<div class="cell">Saldo Anterior</div>';
					print '<div class="cell">Monto Pagado</div>';
					print '<div class="cell">Saldo Insoluto</div>';
					print '<div class="cell">Equivalencia</div>';
					print '</div>';
					print $htmlmore;
					print '</div>';

					if (!$payment->uuid) {
						print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_addrelation" class="butAction">Añadir Relación</a>';
						print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_addoptionaldata" class="butAction">Datos Opcionales</a>';
					}
					if (!$error_stamp) {
						print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=payment_stamp" class="butAction" style="background:#347733 !important">Timbrar Pago</a>';
					}
				} else {
					print '<span class="badge badge-status2 badge-status">No se puede timbrar el pago, la factura ' . $invoice->ref . ' no está timbrada</span>';
				}
			} else {

				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=cancelpayment_stamp" class="butAction" style="background:#880000 !important">Cancelar Pago</a>';

				print '<script>
				$(document).ready(function(){
				$(".tabsAction").append();
				});
				</script>';
			}
			print '</td></tr>';
			//Actions

			if ($action == 'payment_addoptionaldata') {
				$formquestion = array(

					'text' => '<h2>' . $langs->trans("dataPaymentFiscalCFDI") . '</h2>',
					['type' => 'text', 'name' => 'bancoextranjero', 'id' => 'bancoextranjero', 'label' => 'Nombre del banco ordenante (Extranjero)'],
					['type' => 'text', 'name' => 'rfccuentaordenante', 'id' => 'rfccuentaordenante', 'label' => 'RFC emisor cuenta ordenante'],
					['type' => 'text', 'name' => 'cuentaordenante', 'id' => 'cuentaordenante', 'label' => 'Cuenta ordenante'],
					['type' => 'text', 'name' => 'rfccuentabeneficiario', 'id' => 'rfccuentabeneficiario', 'label' => 'RFC emisor cuenta beneficiario'],
					['type' => 'text', 'name' => 'rfccuentabeneficiario', 'id' => 'rfccuentabeneficiario', 'label' => 'Cuenta beneficiario'],

				);
				$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('save_data'), '', 'payment_saveoptionaldata', $formquestion, 0, 1, 350, 600);
				print $formconfirm;
			}
			if($action == "payment_stamp"){



				// $formquestion = array(

				// 	'text' => '<h2>' . $langs->trans("dataFiscal") . '</h2>'

				// );

				// $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('paymentStamp'), '', 'confirm_valid', $formquestion, 0, 1, 300, 600);
				// print $formconfirm;


			}

		}

		if (in_array($parameters['currentcontext'], ['paiementcard'])) {
			// Multicurrency
			print '<tr><td>' . $langs->trans('Multicurrency');
			print ' <em>(' . $langs->trans("InsertMulticurrency") . ')</em>';
			print '</td>';
			print '<td><input name="multicurrency_tx" class="maxwidth300" type="text" value="' . GETPOST('multicurrency_tx', 'multicurrency_tx') . '"></td></tr>';
		}
	}

	public function printObjectLine(&$parameters, &$objp, &$action)
	{
		// var_dump($objp);
		if (in_array($parameters['currentcontext'], ['paiementcard'])) {
			echo '<td>Adios</td><td>Caracola</td>';
		}
	}

	public function addMoreActionsButtons(&$parameters, &$object, &$action)
	{
		global $db, $user;
		if (in_array($parameters['currentcontext'], ['invoicecard'])) {

			$invoice = new Cfdifacture($db);
			$invoice->fetch($object->id);
			$invoice->getStamp();

			if (!$invoice->uuid) {
				print '<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=changethirdparty" class="butAction" style="background:#347733 !important">Cambiar de tercero</a>';
				if ($object->status == Facture::STATUS_VALIDATED || $object->module_source == "takepos") {
					if ($user->rights->cfdiutils->stamp) {
						print '<a href="' . $_SERVER["PHP_SELF"] . '?facid=' . $object->id . '&action=stamp" class="butAction" style="background:#347733 !important">Timbrar SAT</a>';
					}
				}
			}
		}
		// if (in_array($parameters['currentcontext'], ['globalcard'])) {
		// 	echo '<pre>';
		// 	var_dump($object);
		// 	exit;
		// }
		// print '<button class="butAction">Pago CFDI</button>';
	}

	public function formConfirm($parameters, $object, $action)
	{
		global $conf, $langs;

		$form = new Form($this->db);
		if (in_array($parameters['currentcontext'], ['invoicecard'])) {
			if ($action == 'changethirdparty') {

				$selectcontrol = $form->select_thirdparty_list($object->socid, 'nuevotercero', '', $langs->trans("ThirdParty"));
				$fecha = dol_print_date($object->date, 'day');
				$fechavalue = $object->date;

				$formquestion = [
					['type' => 'other', 'name' => 'nuevotercero', 'label' => $langs->trans('NuevoTercero'), 'value' => $selectcontrol],
					['label' => 'Fecha fac.(actual ' . $fecha . ')', 'type' => 'date', 'name' => 'fechafac', 'value' => $fechavalue]
				];

				print $form->formconfirm(
					$_SERVER["PHP_SELF"] . "?facid=" . $object->id,
					$langs->trans("CambiarTercero"),
					$langs->trans("ConfirmChangeTercero"),
					"confirm_cambio_tercero",
					$formquestion,
					'',
					2,
					250,
					750
				);
			}
		}


		if (in_array($parameters['currentcontext'], ['paiementcard'])) {
			if ($action == "add_paiement") {

				// echo '<pre>';var_dump($object);exit;


			}
			// echo '<pre>';var_dump(count($parameters['formConfirm']));exit;

		}
	}

	/* Hook for Lists */

	public function printFieldListSelect($parameters)
	{
		if (in_array($parameters['currentcontext'], ['invoicelist'])) {
			$sql = ",cfdi.uuid, cfdi.fecha_emision,cfdi.fecha_timbrado ";
			return $sql;
		}
	}

	public function printFieldListFrom($parameters, $object)
	{

		if (in_array($parameters['currentcontext'], ['invoicelist'])) {
			$sql = " LEFT JOIN " . MAIN_DB_PREFIX . "cfdiutils_facture cfdi on f.rowid = cfdi.fk_facture ";
			return $sql;
		}
	}

	// public function printFieldListSearchParam ($parameters, $object){
	//     if (in_array($parameters['currentcontext'], ['invoicelist'])) {


	// 	}
	// }

	// Hook for mass action (Massive CFDI STAMP??)
	// public function printFieldPreListTitle( $parameters){
	// 	if (in_array($parameters['currentcontext'], ['invoicelist'])) {

	// 		return '<td>HOla</td>';
	// 	}
	// }

	public function printFieldListOption($parameters)
	{
		if (in_array($parameters['currentcontext'], ['invoicelist'])) {
			return '<td>&nbsp;</td><td>&nbsp;</td>';
		}
	}

	public function printFieldListTitle($parameters)
	{
		if (in_array($parameters['currentcontext'], ['invoicelist'])) {
			return '<th class="wrapcolumntitle liste_titre"><span>Fecha de timbrado</span></th><th class="wrapcolumntitle liste_titre"><span>UUID</span></th>';
		}
	}
	public function printFieldListValue(&$parameters)
	{

		// if (in_array($parameters['currentcontext'], ['paiementcard'])) {
		// 	echo '<td>Hola</td><td>Caracola</td>';
		// }

		if (in_array($parameters['currentcontext'], ['invoicelist'])) {

			// echo '<pre>';var_dump($parameters['obj']);exit;
			if ($parameters['obj']->uuid) {
				echo '<td><span class="badge badge-status4 badge-status">' . $parameters['obj']->fecha_timbrado . '</span></td>';
				echo '<td><span class="badge badge-status4 badge-status">' . dol_strtoupper($parameters['obj']->uuid) . '</span></td>';
			} else if ($parameters['obj']->fecha_emision && $parameters['obj']->fk_statut == Facture::STATUS_VALIDATED) {
				echo '<td align="center"><span class="badge badge-status3 badge-status">E - ' . $parameters['obj']->fecha_emision . '</span></td>';
				echo '<td align="center"><a class="butAction" href="' . DOL_URL_ROOT . '/compta/facture/card.php?facid=' . $parameters['obj']->id . '&action=stamp" target="__blank">Timbrar</td>';
			} else {
				echo '<td align="center"><span class="badge  badge-status3 badge-status">Sin Timbrar</span></td>';
				echo '<td align="center"><a class="butAction" href="' . DOL_URL_ROOT . '/compta/facture/card.php?facid=' . $parameters['obj']->id . '" target="__blank">Ver Factura</td>';
			}
		}
	}

	/* Mail */
	public function getFormMail($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $conf;
		if (in_array($parameters['currentcontext'], ['invoicecard'])) {
			$id = explode('inv', $parameters['trackid']);
			$invoice = new Cfdifacture($db);
			$invoice->fetch($id[1]);
			$invoice->getStamp();
			$filename = dol_sanitizeFileName($invoice->ref);
			$filedir = $conf->facture->multidir_output[$invoice->entity] . '/' . dol_sanitizeFileName($invoice->ref);


			$file2 = $filedir . "/" . $filename . '-' . $invoice->uuid . ".pdf";
			$file3 = $filedir . "/" . $filename . '-' . $invoice->uuid . ".xml";
			if (file_exists($file3)) {
				// $file6 = $fileparams . "/ADDENDA-" . $rs->uuid . ".xml";

				$object->add_attached_files($file2, basename($file2), dol_mimetype($file2));
				$object->add_attached_files($file3, basename($file3), dol_mimetype($file3));
			}
		}
	}

	public function ODTSubstitution($parameters, $object, $action)
	{
		global $conf;
		if (in_array($parameters['currentcontext'], ['invoicecard'])) {
			$facture = new Cfdifacture($this->db);
			$xml = new Cfdiutils($this->db);
			$facture->fetch($parameters['object']->id);
			$facture->getStamp();
			if (!$facture->uuid) {
				return;
			}
			$filename = dol_sanitizeFileName($facture->ref);
			$filedir = $conf->facture->multidir_output[$facture->entity] . '/' . dol_sanitizeFileName($facture->ref);

			$data_xml = $xml->getData(file_get_contents($filedir . "/" . $filename . '-' . $facture->uuid . ".xml"));
			// echo $data_xml['UUID'];exit;
			$parameters['substitutionarray']['UUID'] = $facture->uuid;
			$parameters['substitutionarray']['NoCertificado'] = $data_xml['NoCertificado'];
			$parameters['substitutionarray']['FechaTimbrado'] = $data_xml['FechaTimbrado'];
			$parameters['substitutionarray']['NoCertificadoSAT'] = $data_xml['NoCertificadoSAT'];
			$parameters['substitutionarray']['EmisorRfc'] = $data_xml['EmisorRfc'];
			$parameters['substitutionarray']['ReceptorRfc'] = $data_xml['ReceptorRfc'];
			$parameters['substitutionarray']['Total'] = $data_xml['Total'];
			$parameters['substitutionarray']['SelloCFD'] = $data_xml['SelloCFD'];
		}
	}

	private function __createQr(&$invoice)
	{
		global $conf;
		$emisor = $invoice->getEmisor();
		$receptor = $invoice->getReceptor();
		if ($conf->multicurrency->enabled) {
			$expression = 'id=' . $invoice->uuid . '&re=' . $emisor['Rfc'] . '&rr=' . $receptor['Rfc'] . '&tt=' . $invoice->multicurrency_total_ttc . '&fe=' . substr($invoice->cer_sat, -8);
		} else {
			$expression = 'id=' . $invoice->uuid . '&re=' . $emisor['Rfc'] . '&rr=' . $receptor['Rfc'] . '&tt=' . $invoice->total_ttc . '&fe=' . substr($invoice->cer_sat, -8);
		}

		$data_cbb = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?' . $expression;
		$filename = dol_sanitizeFileName($invoice->ref);
		$filedir = $conf->facture->multidir_output[$invoice->entity] . '/' . dol_sanitizeFileName($invoice->ref);
		$qr = QrCode::create($data_cbb);
		$writer = new PngWriter();
		if (!file_exists($filedir . "/" . $invoice->ref . '-' . $invoice->uuid . ".png")) {
			$writer->write($qr)->saveToFile($filedir . "/" . $filename . '-' . $invoice->uuid . ".png");
		}
	}
}
