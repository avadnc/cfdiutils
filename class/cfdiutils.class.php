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

require '../vendor/autoload.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

class Cfdiutils
{


	public function __construct(DoliDB $db)
	{
		global $conf;
		$this->db = $db;
	}

	/**
	 *	Create xml from invoice
	 *
	 *	@param	int 	$id		ID Object facture
	 */
	public function xmlInvoice($id)
	{
		global $conf;

		$invoice = new Facture($this->db);
		$invoice->fetch($id);

		//Get CSD

		$sql = "SELECT type,value from " . MAIN_DB_PREFIX . "cfdiutils_conf where entity =" . $conf->entity;
		$resql = $this->db->query($sql);
		$num_rows = $this->db->num_rows($resql);
		if ($num_rows > 0) {
			$i = 0;
			while($i < $num_rows){
				$obj = $this->db->fetch_object($resql);

				if($obj->type == "CSD"){
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
		$comprobanteAtributos = [
			'Serie' => 'XXX',
			'Folio' => '0000123456',
			// y otros atributos más...
		];
		$creator = new \CfdiUtils\CfdiCreator40($comprobanteAtributos, $certificado);

		$comprobante = $creator->comprobante();

		// No agrego (aunque puedo) el Rfc y Nombre porque uso los que están establecidos en el certificado
		$comprobante->addEmisor([
			'RegimenFiscal' => '601', // General de Ley Personas Morales
		]);

		$comprobante->addReceptor([/* Atributos del receptor */]);

		$comprobante->addConcepto([
			/* Atributos del concepto */])->addTraslado([
			/* Atributos del impuesto trasladado */]);

		// método de ayuda para establecer las sumas del comprobante e impuestos
		// con base en la suma de los conceptos y la agrupación de sus impuestos
		$creator->addSumasConceptos(null, 2);

		// método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privada)
		$creator->addSello($key,$passkey);

		// método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
		$creator->moveSatDefinitionsToComprobante();

		// método de ayuda para validar usando las validaciones estándar de creación de la librería
		$asserts = $creator->validate();
		$asserts->hasErrors(); // contiene si hay o no errores

		// método de ayuda para generar el xml y retornarlo como un string
		$creator->asXml();
	}
}
