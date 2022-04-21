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
	public $error;
	public $pac;

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
		'error' => array('type' => 'varchar(255)', 'label' => 'error', 'enabled' => 1, 'visible' => -1, 'position' => 60),
		'pac' => array('type' => 'varchar(50)', 'label' => 'pac', 'enabled' => 1, 'visible' => -1, 'position' => 65),
	);


	//Create fiscal data from invoice
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
			$this->error = 'Failexportacion';
		}

		if (!empty($this->pac)) {
			$this->pac = dol_string_nospecial(trim($this->pac));
		} else {
			$error++;
			$this->error = 'Failpac';
		}
		$this->forma_pago = $this->__getFormaPago($this->forma_pago);


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
				$sql .= ",pac";
				$sql .= ") VALUES (";
				$sql .= $this->fk_facture;
				$sql .= ",'" . $this->usocfdi . "'";
				$sql .= ",'" . $this->condicion_pago . "'";
				$sql .= ",'" . $this->forma_pago . "'";
				$sql .= ",'" . $this->metodo_pago . "'";
				$sql .= ",'" . $this->fecha_emision . "'";
				$sql .= ",'" . $this->exportacion . "'";
				$sql .= ",'" . $this->pac . "'";
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

	//Update fiscal data from invoice
	public function updateStamp()
	{
		$error = 0;
		$this->fk_facture = $this->id;
		$this->forma_pago = $this->__getFormaPago($this->forma_pago);

		$sql = "UPDATE " . MAIN_DB_PREFIX . "cfdiutils_facture ";
		$sql .= "SET";
		if ($this->uuid) {
			$sql .= " uuid = '" . $this->uuid . "'";
		} else {
			$sql .=  " error = '" . $this->error . "'";
		}


		$sql .= $this->fecha_timbrado ? ",fecha_timbrado = '" . $this->fecha_timbrado . "'" : ' ';
		$sql .= $this->cer_csd ? ",cer_csd = '" . $this->cer_csd . "'" : ' ';
		$sql .= $this->cer_sat ? ",cer_sat = '" . $this->cer_sat . "'" : ' ';
		$sql .= $this->usocfdi ? ",usocfdi = '" . $this->usocfdi . "'" : '';
		$sql .= $this->condicion_pago ? ",condicion_pago = '" . $this->condicion_pago . "'" : '';
		$sql .= $this->forma_pago ? ",forma_pago = '" . $this->forma_pago . "'" : '';
		$sql .= $this->metodo_pago ? ",metodo_pago = '" . $this->metodo_pago . "'" : '';
		$sql .= $this->exportacion ? ",exportacion = '" . $this->exportacion . "'" : '';
		$sql .= $this->pac ? ",pac = '" . $this->pac . "'" : '';
		$sql .= " Where fk_facture = " . $this->fk_facture;

		$this->db->begin();

		$result = $this->db->query($sql);
		if (!$result) {
			$error++;
			$this->error = $this->db->lasterror();
		}

		if (!$error) {

			$this->db->commit();
			$this->db->free();
			return 'InsertSuccess';

		} else {
			$this->db->rollback();
			return -$error;
		}
	}

	//Get fiscal data from invoice
	public function getStamp()
	{
		$this->fk_facture = $this->id;
		$sql = "SELECT  usocfdi,condicion_pago,forma_pago,metodo_pago,exportacion,fecha_emision,fecha_timbrado,cer_csd,cer_sat,uuid,error,pac";
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
				$this->error = $obj->error;
				$this->pac = $obj->pac;
			}
		}
	}

	//Methods for XML

	//Get Header
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

		//Get SAT CODE
		$this->forma_pago = $this->__getFormaPago($this->forma_pago);

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

	//Get lines
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
					'ValorUnitario' => abs(round($line->subprice, 2)),
					'Importe' => abs(round($line->total_ht, 2)),
					'ObjetoImp' => $cfdiproduct->objetoimp, //Check first if product is exempt VAT, if VAT 0% and Code VAT is EXE

				];
				if (!$line->vat_src_code) {
					$line->vat_src_code = '002';
				}
				$conceptos['Traslado'][$i] = [
					'Base' => abs(round($line->total_ht, 2)),
					'Impuesto' => $line->vat_src_code,
					'TipoFactor' => isset($line->vat_src_code) ? "Tasa" : "Exento", //Check objetoimp
					'TasaOCuota' => number_format(($line->tva_tx / 100), 6),
					'Importe' => abs(round($line->total_tva, 2)),
				];
			} else {
			}
			$i++;
		}

		return $conceptos;
	}

	//get emisor
	public function getEmisor()
	{
		global $conf;

		$emisor = [
			'Rfc' => $conf->global->MAIN_INFO_SIREN,
			'Nombre' => $conf->global->MAIN_INFO_SOCIETE_NOM,
			'RegimenFiscal' => $conf->global->MAIN_INFO_SOCIETE_FORME_JURIDIQUE
		];

		return $emisor;
	}

	//get receptor
	public function getReceptor()
	{
		global $conf;

		$societe = new Cfdisociete($this->db);
		$societe->fetch($this->socid);
		$societe->getFiscal();


		//TODO add CCE Comercio Exterior data

		if ($societe->idprof1 == "XAXX010101000") {
			$societe->zip = $conf->global->MAIN_INFO_SOCIETE_ZIP;
		}

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

	//Crud Relationship
	public function getRelationship($socid)
	{
		$sql = "SELECT f.rowid as id,cfdi.uuid as uuid, concat(f.ref, ' - ', cfdi.uuid) as ref FROM " . MAIN_DB_PREFIX . "cfdiutils_facture cfdi";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture f on f.rowid = cfdi.fk_facture";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe s on s.rowid = f.fk_soc";
		$sql .= " WHERE s.rowid = " . $socid . " AND cfdi.uuid IS NOT NULL  AND cfdi.uuid <> ''";

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$data = [];
			if ($num > 0) {
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$data[$obj->id] = $obj->ref;

					$i++;
				}
				return $data;
			}
		}
		return 0;
	}

	public function createRelationship($fk_tipo_rel, $fk_facture_rel = null, $extuuid = null)
	{

		$this->fk_facture = $this->id;
		$error = 0;
		if ($fk_facture_rel) {
			$extuuid = null;
		}
		if ($extuuid) {
			$fk_facture_rel = null;
		}


		$this->db->begin();

		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_facture_rel (";
		$sql .= "fk_facture";
		$sql .= ",fk_tipo_rel";
		$sql .= $fk_facture_rel ? ",fk_facture_rel" : '';
		$sql .= $extuuid ? ",extuuid" : '';
		$sql .= ") VALUES(";
		$sql .= $this->fk_facture;
		$sql .= "," . $fk_tipo_rel;
		$sql .= $fk_facture_rel ? "," . $fk_facture_rel . "" : '';
		$sql .= $extuuid ? ", '" . $extuuid . "'" : '';
		$sql .= ")";

		dol_syslog(get_class($this) . "::Create Facture Relationship", LOG_DEBUG);

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
	}

	public function getRelations()
	{
		$this->fk_facture = $this->id;
		$datatable = [];

		$sql = "SELECT fr.rowid as id, fr.extuuid as uuid, fr.fk_facture_rel, tr.label as label,tr.code as code FROM " . MAIN_DB_PREFIX . "cfdiutils_facture_rel fr";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "c_cfdiutils_tiporelacion tr ON fr.fk_tipo_rel = tr.rowid";
		$sql .= " WHERE fr.fk_facture = " . $this->fk_facture;

		$resql = $this->db->query($sql);
		$num = $this->db->num_rows($resql);

		if ($num > 0) {
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				if ($obj->uuid) {

					$datatable[$i] = [
						'id' => $obj->id,
						'ref' => null,
						'uuid' => $obj->uuid,
						'label' => $obj->label,
						'code' => $obj->code,
					];
				} else {

					$facture = new Cfdifacture($this->db);
					$facture->fetch($obj->fk_facture_rel);
					$facture->getStamp();
					$datatable[$i] = [
						'id' => $obj->id,
						'ref' => $facture->ref,
						'uuid' => $facture->uuid,
						'label' => $obj->label,
						'code' => $obj->code,
					];
				}

				$i++;
			}
			return $datatable;
		} else {
			return 0;
		}
	}

	public function deleteRel($id)
	{
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "cfdiutils_facture_rel WHERE rowid = " . $id;
		$result = $this->db->query($sql);
		return $result;
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

	private function __getFormaPago($code)
	{

		$sql = "SELECT fp.code from " . MAIN_DB_PREFIX . "c_cfdiutils_formapago fp";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_paiement cp on cp.id = fp.fk_code";
		$sql .= " WHERE cp.code ='" . $code . "'";

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num > 0) {
				$obj = $this->db->fetch_object($resql);
				return $obj->code;
			} else {
				return '99';
			}
		}
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
