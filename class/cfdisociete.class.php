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
 * \file        class/cfdisociete.class.php
 * \ingroup     cfdiutils
 * \brief       This file is a CRUD class file for Cfdisociete (Create/Read/Update/Delete)
 */
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

class Cfdisociete extends Societe
{
	public $fiscal_name;
	public $municipio;
	public $cod_municipio;
	public $localidad;
	public $cod_localidad;
	public $colonia;
	public $cod_colonia;

	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 10),
		'fk_soc' => array('type' => 'integer:Societe:societe/class/societe.class.php', 'label' => 'ThirdParty', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 15),
		'fiscal_name' => array('type' => 'varchar(255)', 'label' => 'Fiscalname', 'enabled' => 1, 'visible' => -1, 'notnull' => 1, 'position' => 20),
		'municipio' => array('type' => 'varchar(100)', 'label' => 'Municipio', 'enabled' => 1, 'visible' => -1, 'position' => 25),
		'cod_municipio' => array('type' => 'varchar(5)', 'label' => 'Codmunicipio', 'enabled' => 1, 'visible' => -1, 'position' => 30),
		'localidad' => array('type' => 'varchar(100)', 'label' => 'Localidad', 'enabled' => 1, 'visible' => -1, 'position' => 35),
		'cod_localidad' => array('type' => 'varchar(5)', 'label' => 'Codlocalidad', 'enabled' => 1, 'visible' => -1, 'position' => 40),
		'colonia' => array('type' => 'varchar(100)', 'label' => 'Colonia', 'enabled' => 1, 'visible' => -1, 'position' => 45),
		'cod_colonia' => array('type' => 'varchar(5)', 'label' => 'Codcolonia', 'enabled' => 1, 'visible' => -1, 'position' => 50),
	);


	public function createFiscal()
	{
		$error = 0;
		if (!empty($this->fiscal_name)) {
			$this->fiscal_name =trim($this->fiscal_name);
		} else {
			$error++;
			$this->error = 'FailFiscalName';
		}

		$this->fk_soc = $this->id;

		$this->municipio = trim($this->municipio);
		$this->cod_municipio = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_municipio)));
		$this->localidad = trim($this->localidad);
		$this->cod_localidad = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_localidad)));
		$this->colonia = trim($this->colonia);
		$this->cod_colonia = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_colonia)));

		$this->db->begin();
		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "cfdiutils_societe where fk_soc = " . $this->fk_soc;

		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
			if ($obj->nb == 0) {
				//No existe registro fiscal del producto
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_societe (";
				$sql .= "fk_soc";
				$sql .= ",fiscal_name";
				$sql .= $this->municipio ? ",municipio": '';
				$sql .= $this->cod_municipio ? ",cod_municipio": '';
				$sql .= $this->localidad ? ",localidad": '';
				$sql .= $this->cod_localidad ? ",cod_localidad": '';
				$sql .= $this->colonia ? ",colonia": '';
				$sql .= $this->cod_colonia ? ",cod_colonia": '';
				$sql .= ") VALUES (";
				$sql .= $this->id;
				$sql .= ",'" . strtoupper($this->fiscal_name) . "'";
				$sql .= $this->municipio ? ",'" . strtoupper($this->municipio) . "'" : '';
				$sql .= $this->cod_municipio ? ",'" . $this->cod_municipio . "'" : ' ';
				$sql .= $this->localidad ? ",'" . strtoupper($this->localidad) . "'" : '';
				$sql .= $this->cod_localidad ? ",'" . $this->cod_localidad . "'" : '';
				$sql .= $this->colonia ? ",'" . strtoupper($this->colonia) . "'" : '';
				$sql .= $this->cod_colonia ? ",'" . $this->cod_colonia . "'" : '';
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

	public function updateFiscal()
	{
		$error = 0;
		if (!empty($this->fiscal_name)) {
			$this->fiscal_name =trim($this->fiscal_name);
		} else {
			$error++;
			$this->error = 'FailUmed';
		}

		$this->fk_soc = $this->id;

		$this->municipio = trim($this->municipio);
		$this->cod_municipio = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_municipio)));
		$this->localidad =trim($this->localidad);
		$this->cod_localidad = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_localidad)));
		$this->colonia = trim($this->colonia);
		$this->cod_colonia = dol_sanitizeFileName(dol_string_nospecial(trim($this->cod_colonia)));


		$this->db->begin();

		$sql = "UPDATE " . MAIN_DB_PREFIX . "cfdiutils_societe ";
		$sql .= "SET fiscal_name = '" . $this->fiscal_name . "'";
		$sql .= $this->municipio?", municipio = '" . $this->municipio . "'":'';
		$sql .= $this->cod_municipio?", cod_municipio = '" . $this->cod_municipio . "'":'';
		$sql .= $this->localidad?", localidad = '" . $this->localidad . "'":'';
		$sql .= $this->cod_localidad?", cod_localidad = '" . $this->cod_localidad . "'":'';
		$sql .= $this->colonia?", colonia = '" . $this->colonia . "'":'';
		$sql .= $this->cod_colonia?", cod_colonia = '" . $this->cod_colonia . "'":'';
		$sql .= " WHERE fk_soc = " . $this->fk_soc;

		dol_syslog(get_class($this) . "::update", LOG_DEBUG);

		$result = $this->db->query($sql);
		if (!$error) {
			$this->db->commit();
			return $result;
		} else {
			$this->db->rollback();
			return -$error;
		}
	}

	public function deleteFiscal()
	{
		$this->fk_soc = $this->id;
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "cfdiutils_societe where fk_soc =" . $this->fk_soc;
		$result = $this->db->query($sql);
		return $result;
	}

	public function getFiscal()
	{
		$sql = "SELECT fiscal_name, municipio,cod_municipio,localidad,cod_localidad,colonia,cod_colonia FROM " . MAIN_DB_PREFIX . "cfdiutils_societe";
		$sql .= " WHERE fk_soc = " . $this->id;

		$resql = $this->db->query($sql);
		$num_rows = $this->db->num_rows($resql);

		if ($num_rows > 0) {
			$obj = $this->db->fetch_object($resql);

			$this->fiscal_name = $obj->fiscal_name;
			$this->municipio = $obj->municipio;
			$this->cod_municipio = $obj->cod_municipio;
			$this->localidad = $obj->localidad;
			$this->cod_localidad = $obj->cod_localidad;
			$this->colonia = $obj->colonia;
			$this->cod_colonia = $obj->cod_colonia;
		}
	}

	public function getFormeJuridique()
	{
		$sql = "SELECT count(*) as nb FROM " . MAIN_DB_PREFIX . "c_forme_juridique WHERE fk_pays = 154 AND active = 1";
		$result = $this->db->query($sql);
		if ($result) {
			$datatable = [];
			$obj = $this->db->fetch_object($result);
			if ($obj->nb > 0) {
				$sql = "SELECT code, libelle as label FROM " . MAIN_DB_PREFIX . "c_forme_juridique WHERE fk_pays = 154 AND active = 1";
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
