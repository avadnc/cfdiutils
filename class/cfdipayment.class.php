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

require_once DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php';

require_once 'cfdisociete.class.php';
require_once 'cfdifacture.class.php';

class Cfdipayment extends Paiement
{

	public $fk_payment;
	public $usocfdi;
	public $tipodecambio;
	public $fecha_emision;
	public $fecha_timbrado;
	public $cer_csd;
	public $cer_sat;
	public $uuid;
	public $NumOperacion;
	public $RfcEmisorCtaOrd;
	public $NomBancoOrdExt;
	public $CtaOrdenante;
	public $RfcEmisorCtaBen;
	public $CtaBeneficiario;
	public $TipoCadPago;
	public $CertPago;
	public $CadPago;
	public $SelloPago;
	public $pac;
	public $status;
	public $error;


	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
		'fk_payment' => array('type' => 'integer:Paiement:/compta/paiement/class/paiement.class.php', 'label' => 'Fkpayment', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 15),
		'usocfdi' => array('type' => 'varchar(50)', 'label' => 'Usocfdi', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 20),
		'tipodecambio' => array('type' => 'real', 'label' => 'Tipodecambio', 'enabled' => 1, 'visible' => -1, 'position' => 25),
		'fecha_emision' => array('type' => 'varchar(50)', 'label' => 'Fechaemision', 'enabled' => 1, 'visible' => -1, 'position' => 30),
		'fecha_timbrado' => array('type' => 'varchar(50)', 'label' => 'Fechatimbrado', 'enabled' => 1, 'visible' => -1, 'position' => 35),
		'cer_csd' => array('type' => 'varchar(50)', 'label' => 'Cercsd', 'enabled' => 1, 'visible' => -1, 'position' => 40),
		'cer_sat' => array('type' => 'varchar(50)', 'label' => 'Cersat', 'enabled' => 1, 'visible' => -1, 'position' => 45),
		'uuid' => array('type' => 'varchar(50)', 'label' => 'Uuid', 'enabled' => 1, 'visible' => -1, 'position' => 50),
		'NumOperacion' => array('type' => 'varchar(50)', 'label' => 'NumOperacion', 'enabled' => 1, 'visible' => -1, 'position' => 55),
		'RfcEmisorCtaOrd' => array('type' => 'varchar(50)', 'label' => 'RfcEmisorCtaOrd', 'enabled' => 1, 'visible' => -1, 'position' => 60),
		'NomBancoOrdExt' => array('type' => 'varchar(50)', 'label' => 'NomBancoOrdExt', 'enabled' => 1, 'visible' => -1, 'position' => 65),
		'CtaOrdenante' => array('type' => 'varchar(50)', 'label' => 'CtaOrdenante', 'enabled' => 1, 'visible' => -1, 'position' => 70),
		'RfcEmisorCtaBen' => array('type' => 'varchar(50)', 'label' => 'RfcEmisorCtaBen', 'enabled' => 1, 'visible' => -1, 'position' => 75),
		'CtaBeneficiario' => array('type' => 'varchar(50)', 'label' => 'CtaBeneficiario', 'enabled' => 1, 'visible' => -1, 'position' => 80),
		'TipoCadPago' => array('type' => 'varchar(50)', 'label' => 'TipoCadPago', 'enabled' => 1, 'visible' => -1, 'position' => 85),
		'CertPago' => array('type' => 'text', 'label' => 'CertPago', 'enabled' => 1, 'visible' => -1, 'position' => 90),
		'CadPago' => array('type' => 'text', 'label' => 'CadPago', 'enabled' => 1, 'visible' => -1, 'position' => 95),
		'SelloPago' => array('type' => 'text', 'label' => 'SelloPago', 'enabled' => 1, 'visible' => -1, 'position' => 100),
		'pac' => array('type' => 'varchar(255)', 'label' => 'Pac', 'enabled' => 1, 'visible' => -1, 'position' => 105),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => -1, 'position' => 500),
		'error' => array('type' => 'varchar(255)', 'label' => 'Error', 'enabled' => 1, 'visible' => -1, 'position' => 115),
	);

	public function createStamp(){

		$error = 0;
		$this->fk_payment = $this->id;
		$this->usocfdo = 'CP01';

		if (!empty($this->tipodecambio)) {
			$this->tipodecambio = dol_sanitizeFileName(dol_string_nospecial(trim($this->tipodecambio)));
		} else {
            $error++;
            $this->error = 'Failtipodecambio';
        }
		if (!empty($this->fecha_emision)) {
			$this->fecha_emision = trim($this->fecha_emision);
		} else {
			$error++;
			$this->error = 'Failfecha_emision';
		}


		$this->db->begin();
		$sql = "SELECT count() as nb FROM " . MAIN_DB_PREFIX . "cfdiutils_payment WHERE fk_cfdipay = " . $this->fk_payment;
		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
            if ($obj->nb == 0) {

            }
		} else {
			return 'RecordExists';
		}
	}
	public function updateStamp()
	{
	}
	public function getStamp()
	{
	}
	public function cancelStamp()
	{
	}

	public function getcancelStamp()
	{
	}

	public function getDataPayment()
	{
		$sql = "SELECT multicurrency_code, multicurrency_tx FROM ".MAIN_DB_PREFIX."paiement_facture where fk_paiement = ".$this->id." GROUP BY multicurrency_code";
		$resql = $this->db->query($sql);
		$obj = $this->db->fetch_object($resql);
		$this->multicurrency_tx = $obj->multicurrency_tx;
		$this->multicurrency_code = $obj->multicurrency_code;
	}




}

class Cfdipaymentfacture
{

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
		'fk_cfdipay' => array('type' => 'integer:Cfdipayment:cfdipayment.class.php', 'label' => 'Fkcfdipay', 'enabled' => 1, 'visible' => -1, 'position' => 15),
		'fk_paiement_facture' => array('type' => 'integer:Facture:compta/facture/class/facture.class.php', 'label' => 'Fkpaiementfacture', 'enabled' => 1, 'visible' => -1, 'position' => 20),
		'parcialidad' => array('type' => 'integer', 'label' => 'Parcialidad', 'enabled' => 1, 'visible' => -1, 'position' => 25),
		'moneda' => array('type' => 'varchar(50)', 'label' => 'Moneda', 'enabled' => 1, 'visible' => -1, 'position' => 30),
		'saldo_anterior' => array('type' => 'real', 'label' => 'Saldoanterior', 'enabled' => 1, 'visible' => -1, 'position' => 35),
		'pago' => array('type' => 'real', 'label' => 'Pago', 'enabled' => 1, 'visible' => -1, 'position' => 40),
		'saldo_insoluto' => array('type' => 'real', 'label' => 'Saldoinsoluto', 'enabled' => 1, 'visible' => -1, 'position' => 45),
		'equivalencia' => array('type' => 'real', 'label' => 'Equivalencia', 'enabled' => 1, 'visible' => -1, 'position' => 50),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => -1, 'position' => 500),
	);
}
/*
$cerfile = $this->getAmbiente($data->Emisor->Rfc)->cer;
        $keyfile = $this->getAmbiente($data->Emisor->Rfc)->key;

        $certificado = new Certificado($cerfile);
        $creator->putCertificado($certificado, false);
[11:40]
$comprobante->addEmisor([
            'Nombre' => $requestPagos->Emisor->Nombre,
            'Rfc' => $requestPagos->Emisor->Rfc,
            'RegimenFiscal' => $requestPagos->Emisor->RegimenFiscal,
        ]);
        $comprobante->addReceptor([
            'Rfc' => $requestPagos->Receptor->Rfc,
            'Nombre' => $requestPagos->Receptor->Nombre,
            'UsoCFDI' => 'P01',
            'DomicilioFiscalReceptor' =>$requestPagos->Receptor->DomicilioFiscalReceptor,
        ]);
        // The concepto must have this content
        $comprobante->addConcepto([
            'ClaveProdServ' => '84111506',
            'Cantidad' => '1',
            'ClaveUnidad' => 'ACT',
            'Descripcion' => 'Pago',
            'ValorUnitario' => '0',
            'Importe' => '0',
        ]);
[11:40]
// create and populate the "complemento de pagos"
        // @see \CfdiUtils\Elements\Pagos20\Pagos
        $complementoPagos = new Pagos();

        $pagoInfo = $requestPagos->Complemento[0]->Pagos->Pago[0];

        $pago = $complementoPagos->addPago([
            'FechaPago' => $pagoInfo->FechaPago,
            'FormaDePagoP' => $pagoInfo->FormaDePagoP, // transferencia
            'MonedaP' => $pagoInfo->MonedaP,
            'Monto' => $pagoInfo->Monto,
            'NumOperacion' => '963852',
            'RfcEmisorCtaOrd' => 'BMI9704113PA', // Monex
            'CtaOrdenante' => '0001970000',
            'RfcEmisorCtaBen' => 'BBA830831LJ2', // BBVA
            'CtaBeneficiario' => '0198005000',
        ]);

		*/
