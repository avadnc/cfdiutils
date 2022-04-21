<?php


/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * \file        class/facture.class.php
 * \ingroup     cfdiutils
 * \brief       This file is a XML class file for generate CFDI Facture
 */

require_once DOL_DOCUMENT_ROOT . '/custom/cfdiutils/vendor/autoload.php';

use PhpCfdi\Credentials\PrivateKey;

class Cfdiutils
{
	public $pac;
	public $webservice_prod;

	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 *	Create xml from invoice
	 *
	 *	@param	int 	$id		ID Object facture
	 */
	public function stampXML($header, $emisor, $receptor, $conceptos, $relacionados = null)
	{
		global $conf;

		//test

		$sql = "SELECT type,value from " . MAIN_DB_PREFIX . "cfdiutils_conf where entity =" . $conf->entity;
		$resql = $this->db->query($sql);
		$num_rows = $this->db->num_rows($resql);
		if ($num_rows > 0) {
			$i = 0;
			while ($i < $num_rows) {
				$obj = $this->db->fetch_object($resql);

				if ($obj->type == "CSD") {
					$csd = $obj->value;
				}
				if ($obj->type == "KEY") {
					$key = $obj->value;
				}
				if ($obj->type == "PASSKEY") {
					$passkey = $obj->value;
				}


				$i++;
			}
		} else {

			return -1;
		}

		$certificado = new \CfdiUtils\Certificado\Certificado($csd);

		$creator = new \CfdiUtils\CfdiCreator40($header, $certificado);

		$comprobante = $creator->comprobante();

		// No agrego (aunque puedo) el Rfc y Nombre porque uso los que están establecidos en el certificado
		$comprobante->addEmisor($emisor);

		$comprobante->addReceptor($receptor);

		$num = count($conceptos) - 1;
		if ($num > 0) {
			for ($i = 0; $i < $num; $i++) {
				$comprobante->addConcepto($conceptos[$i])->addTraslado($conceptos['Traslado'][$i]);
			}
		} else {
			$comprobante->addConcepto($conceptos[0])->addTraslado($conceptos['Traslado'][0]);
		}

		//Add cfdi relacionados
		if (is_array($relacionados)) {
			$code = null;
			$group = null;
			foreach ($relacionados as $rel) {

				if (!$code || $code != $rel['code']) {
					$group = $comprobante->addCfdiRelacionados(['TipoRelacion' => $rel['code']]);
					$group->addCfdiRelacionado(['UUID' => $rel['uuid']]);
				} else {
					$group->addCfdiRelacionado(['UUID' => $rel['uuid']]);
				}
				$code = $rel['code'];
			}
		}
		// método de ayuda para establecer las sumas del comprobante e impuestos
		// con base en la suma de los conceptos y la agrupación de sus impuestos
		$creator->addSumasConceptos(null, 2);
		// $creator->pago
		$pemPrivateKeyContents = PrivateKey::convertDerToPem(file_get_contents('file://' . $key), $passkey !== '');
		$creator->addSello($pemPrivateKeyContents, $passkey);
		// método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privada)


		// método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
		$creator->moveSatDefinitionsToComprobante();

		// método de ayuda para validar usando las validaciones estándar de creación de la librería
		$asserts = $creator->validate();
		$asserts->hasErrors(); // contiene si hay o no errores

		// método de ayuda para generar el xml y retornarlo como un string
		return $creator->asXml();
	}

	public function getData($xml)
	{
		// clean cfdi

		$cfdi = \CfdiUtils\Cfdi::newFromString($xml);
		$cfdi->getVersion(); // (string) 3.3
		$cfdi->getDocument(); // clon del objeto DOMDocument
		$cfdi->getSource(); // (string) <cfdi:Comprobante...
		$comprobante = $cfdi->getNode(); // Nodo de trabajo del nodo cfdi:Comprobante
		$tfd = $comprobante->searchNode('cfdi:Complemento', 'tfd:TimbreFiscalDigital');
		$emisor = $comprobante->searchNode('cfdi:Emisor');
		$receptor = $comprobante->searchNode('cfdi:Receptor');


		return [
			'SelloCFD'	=> $tfd['SelloCFD'],
			'NoCertificado'	=> $comprobante['NoCertificado'],
			'FechaTimbrado' => $tfd['FechaTimbrado'],
			'UUID' => $tfd['UUID'],
			'NoCertificadoSAT' => $tfd['NoCertificadoSAT'],
			'EmisorRfc' =>  $emisor['Rfc'],
			'ReceptorRfc' =>  $receptor['Rfc'],
			'Total' => $comprobante['Total'],


		];
	}
}
