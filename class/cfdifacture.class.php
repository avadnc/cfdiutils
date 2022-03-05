<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2022  Alex Vives <gerencia@vivescloud.com>
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
 * \file        class/cfdifacture.class.php
 * \ingroup     cfdiutils
 * \brief       This file is a CRUD class file for Cfdifacture (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

require_once 'cfdisociete.class.php';
require_once 'cfdiproduct.class.php';

class Cfdifacture extends Facture
{

	public $fk_facture;
	public $usocfdi;
	public $condicion_pago;
	public $forma_pago;
	public $metodo_pago;
	public $exportacion;
	public $fecha_emision;
	public $fecha_timbrado;
	public $cer_csd;
	public $cer_sat;
	public $uuid;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
		'fk_facture' => array('type' => 'integer:Facture:compta/facture/class/facture.class.php', 'label' => 'Facture', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 15),
		'usocfdi' => array('type' => 'varchar(50)', 'label' => 'usocfdi', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 16),
		'condicion_pago' => array('type' => 'varchar(50)', 'label' => 'Condicionpago', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 20),
		'forma_pago' => array('type' => 'varchar(50)', 'label' => 'Formapago', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 25),
		'metodo_pago' => array('type' => 'varchar(50)', 'label' => 'Metodopago', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 30),
		'exportacion' => array('type' => 'varchar(50)', 'label' => 'Exportacion', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 31),
		'fecha_emision' => array('type' => 'varchar(50)', 'label' => 'Fechaemision', 'enabled' => 1, 'visible' => -1, 'position' => 35),
		'fecha_timbrado' => array('type' => 'varchar(50)', 'label' => 'Fechatimbrado', 'enabled' => 1, 'visible' => -1, 'position' => 40),
		'cer_csd' => array('type' => 'varchar(50)', 'label' => 'Cercsd', 'enabled' => 1, 'visible' => -1, 'position' => 45),
		'cer_sat' => array('type' => 'varchar(50)', 'label' => 'Cersat', 'enabled' => 1, 'visible' => -1, 'position' => 50),
		'uuid' => array('type' => 'varchar(50)', 'label' => 'Uuid', 'enabled' => 1, 'visible' => -1, 'position' => 55),
	);


	//CRUD Header
	public function createStamp()
	{
		$error = 0;
		$this->error = [];

		$this->fk_facture = $this->id;

		if (!empty($this->condicion_pago)) {
			$this->condicion_pago = dol_sanitizeFileName(dol_string_nospecial(trim($this->condicion_pago)));
		} else {
			$error++;
			$this->error = 'Failcondicion_pago';
		}
		if (!empty($this->usocfdi)) {
			$this->usocfdi = dol_sanitizeFileName(dol_string_nospecial(trim($this->usocfdi)));
		} else {
			$error++;
			$this->error = 'Failusocfdi';
		}
		if (!empty($this->forma_pago)) {
			$this->forma_pago = dol_sanitizeFileName(dol_string_nospecial(trim($this->forma_pago)));
		} else {
			$error++;
			$this->error = 'Failforma_pago';
		}
		if (!empty($this->metodo_pago)) {
			$this->metodo_pago = dol_sanitizeFileName(dol_string_nospecial(trim($this->metodo_pago)));
		} else {
			$error++;
			$this->error = 'Failmetodo_pago';
		}
		if (!empty($this->fecha_emision)) {
			$this->fecha_emision = trim($this->fecha_emision);
		} else {
			$error++;
			$this->error = 'Failfecha_emision';
		}
		if (!empty($this->exportacion)) {
			$this->exportacion = dol_string_nospecial(trim($this->exportacion));
		} else {
			$error++;
			$this->error = 'Failfecha_emision';
		}

		$this->db->begin();
		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "cfdiutils_facture where fk_facture = " . $this->fk_facture;
		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
			if ($obj->nb == 0) {
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_facture (";
				$sql .= "fk_facture";
				$sql .= ",usocfdi";
				$sql .= ",condicion_pago";
				$sql .= ",forma_pago";
				$sql .= ",metodo_pago";
				$sql .= ",fecha_emision";
				$sql .= ",exportacion";
				$sql .= ") VALUES (";
				$sql .= $this->fk_facture;
				$sql .= ",'" . $this->usocfdi . "'";
				$sql .= ",'" . $this->condicion_pago . "'";
				$sql .= ",'" . $this->forma_pago . "'";
				$sql .= ",'" . $this->metodo_pago . "'";
				$sql .= ",'" . $this->fecha_emision . "'";
				$sql .= ",'" . $this->exportacion . "'";
				$sql .= ")";

				dol_syslog(get_class($this) . "::Create", LOG_DEBUG);

				$result = $this->db->query($sql);
				if (!$result) {
					$error++;
					$this->error = $this->db->lasterror();
				}

				if (!$error) {
					$this->db->commit();
					return 'InsertSuccess';
				} else {
					$this->db->rollback();
					return -$error;
				}
			} else {

				return 'RecordExists';
			}
		}
	}

	public function updateStamp()
	{
	}

	public function getStamp()
	{
		$this->fk_facture = $this->id;
		$sql = "SELECT  usocfdi,condicion_pago,forma_pago,metodo_pago,exportacion,fecha_emision,fecha_timbrado,cer_csd,cer_sat,uuid";
		$sql .= " FROM " . MAIN_DB_PREFIX . "cfdiutils_facture WHERE fk_facture = " . $this->fk_facture;
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num > 0) {

				$obj = $this->db->fetch_object($resql);

				$this->usocfdi = $obj->usocfdi;
				$this->condicion_pago = $obj->condicion_pago;
				$this->forma_pago = $obj->forma_pago;
				$this->metodo_pago = $obj->metodo_pago;
				$this->exportacion = $obj->exportacion;
				$this->fecha_emision = $obj->fecha_emision;
				$this->fecha_timbrado = $obj->fecha_timbrado;
				$this->cer_csd = $obj->cer_csd;
				$this->cer_sat = $obj->cer_sat;
				$this->uuid = $obj->uuid;
			}
		}
	}

	public function getHeader()
	{
		global $conf;

		$this->getStamp();

		if (strpos($this->ref, '-') !==	false) {

			$ref = explode("-", $this->ref);
		} else {
			$ref[0] = '';
			$ref[1] = $this->ref;
		}

		if ($this->type == Facture::TYPE_STANDARD) {
			$tipoComprobante = "I";
		}

		if ($this->type == Facture::TYPE_DEPOSIT) {
			$tipoComprobante = "I";
		}

		if ($this->type == Facture::TYPE_CREDIT_NOTE) {
			$tipoComprobante = "E";
		}


		//TODO add total discounts before VAT

		if (!$conf->multicurrency->enabled) {
			$header = [
				'Serie' => $ref[0],
				'Folio' => $ref[1],
				'Fecha' => $this->fecha_emision,
				'SubTotal' => round($this->total_ht, 2),
				'Total' => round($this->total_ttc, 2),
				'TipoDeComprobante' => $tipoComprobante,
				'LugarExpedicion' => $conf->global->MAIN_INFO_SOCIETE_ZIP,
				'FormaPago' => $this->forma_pago,
				'CondicionesDePago' => $this->condicion_pago,
				'MetodoPago' => $this->metodo_pago,
				'Exportacion' => $this->exportacion,
				'Moneda' => $conf->currency,

			];
		} else {
			$header = [
				'Serie' => $ref[0],
				'Folio' => $ref[1],
				'Fecha' => $this->fecha_emision,
				'SubTotal' => round($this->multicurrency_total_ht, 2),
				'Total' => round($this->multicurrency_total_ttc, 2),
				'TipoDeComprobante' => $tipoComprobante,
				'LugarExpedicion' => $conf->global->MAIN_INFO_SOCIETE_ZIP,
				'FormaPago' => $this->forma_pago,
				'CondicionesDePago' => $this->condicion_pago,
				'MetodoPago' => $this->metodo_pago,
				'Exportacion' => $this->exportacion,
				'Moneda' => $this->multicurrency_code,
			];

			if ($this->multicurrency_code != "MXN") {
				$header['TipoCambio'] = $this->multicurrency_tx;
			}
		}
		return $header;
	}

	public function getLines()
	{
		$conceptos = [];
		$cfdiproduct = new Cfdiproduct($this->db);
		$i = 0;
		//TODO: ADD freelines, multicurrency support
		foreach ($this->lines as $line) {

			if ($line->fk_product) {

				$cfdiproduct->fetch($line->fk_product);
				$cfdiproduct->getFiscal();

				$conceptos[$i] = [
					'ClaveProdServ' => $cfdiproduct->claveprodserv,
					'Cantidad' => $line->qty,
					'ClaveUnidad' => $cfdiproduct->umed,
					'Descripcion' => $line->description ? $cfdiproduct->ref . ' - ' . $line->description : $cfdiproduct->ref . ' - ' . $cfdiproduct->label,
					'ValorUnitario' => round($cfdiproduct->price, 2),
					'Importe' => round($line->total_ht, 2),
					'ObjetoImp' => $cfdiproduct->objetoimp, //Check first if product is exempt VAT, if VAT 0% and Code VAT is EXE

				];
				$conceptos['Traslado'][$i] = [
					'Base' => round($line->total_ht, 2),
					'Impuesto' => $line->vat_src_code,
					'TipoFactor' => isset($line->vat_src_code) ? "Tasa" : "Exento", //Check objetoimp
					'TasaOCuota' => number_format(($line->tva_tx / 100), 6),
					'Importe' => round($line->total_tva, 2),
				];
			} else {
			}
			$i++;
		}

		return $conceptos;
	}

	public function getEmisor()
	{
		global $conf;

		$emisor = [
			'Rfc' => $conf->MAIN_INFO_SIREN,
			'Nombre' => $conf->MAIN_INFO_SOCIETE_NOM,
			'RegimenFiscal' => $conf->MAIN_INFO_SOCIETE_FORME_JURIDIQUE
		];

		return $emisor;
	}

	public function getReceptor()
	{
		$societe = new Cfdisociete($this->db);
		$societe->fetch($this->socid);
		$societe->getFiscal();


		//TODO add CCE Comercio Exterior data

		$receptor = [

			'Rfc' => $societe->idprof1,
			'Nombre' => $societe->fiscal_name,
			'DomicilioFiscalReceptor' => $societe->zip,
			'RegimenFiscalReceptor' => $societe->forme_juridique_code,
			'UsoCFDI' => $this->usocfdi

		];

		if ($societe->country_id != '154') {

			$sql = "SELECT code_iso FROM " . MAIN_DB_PREFIX . "c_country where rowid = " . $societe->country_id;
			$resql = $this->db->query($sql);
			if ($resql) {

				$obj = $this->db->fetch_object($resql);
				$receptor['Rfc'] = 'XEXX010101000';
				$receptor['ResidenciaFiscal'] = $obj->code_iso;
				$receptor['NumRegIdTrib'] = $societe->idprof1;
				$receptor['forme_juridique_code'] = '616';
			} else {
				return 'ErrorCustomerCountry';
			}
		}
		return $receptor;
	}

	//Get Dictionary

	public function getDictionary($table)
	{

		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "c_cfdiutils" . $table . " WHERE active = 1";
		$result = $this->db->query($sql);
		if ($result) {
			$datatable = [];
			$obj = $this->db->fetch_object($result);
			if ($obj->nb > 0) {
				$sql = "SELECT code, label FROM " . MAIN_DB_PREFIX . "c_cfdiutils" . $table . " WHERE active = 1";
				$resql = $this->db->query($sql);
				$num = $this->db->num_rows($resql);
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$datatable[$obj->code] = $obj->label;
					$i++;
				}
			}
		}

		return $datatable;
	}
}

class Cfdifactureline extends FactureLigne
{
	public $claveprodserv;
	public $umed;
}

class Paytype
{

	public $db;
	public $fk_paytype;
	public $code;
	public $label;

	public function __construct($db)
	{

		$this->db = $db;
	}

	public function getDictionary($table, $withid = true, $type = "PaymentType")
	{
		global $langs;
		$langs->loadLangs(array("errors", "bills", "cfdiutils@cfdiutils"));
		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "c_" . $table . " WHERE active = 1";
		$result = $this->db->query($sql);
		if ($result) {
			$datatable = [];
			$obj = $this->db->fetch_object($result);
			if ($obj->nb > 0) {
				if ($withid == true) {
					$sql = "SELECT id, code, libelle FROM " . MAIN_DB_PREFIX . "c_" . $table . " WHERE active = 1";
				} else {
					$sql = "SELECT code, libelle FROM " . MAIN_DB_PREFIX . "c_" . $table . " WHERE active = 1";
				}
				$resql = $this->db->query($sql);
				$num = $this->db->num_rows($resql);
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					if ($withid == true) {
						$datatable[$obj->code] = $obj->id;
					} else {
						$datatable[$obj->code]	= $langs->trans($type . strtoupper($obj->code));
					}
					$i++;
				}
			}
		}

		return $datatable;
	}
}
