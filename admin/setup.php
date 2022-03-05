<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 SuperAdmin
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
 * \file    cfdiutils/admin/setup.php
 * \ingroup cfdiutils
 * \brief   Cfdiutils setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/cfdiutils.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';


// Translations
$langs->loadLangs(array("admin", "cfdiutils@cfdiutils"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$passkey = GETPOST('passkey', 'alpha');


$error = [];
$setupnotempty = 0;

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);


/*
 * Actions
 */

if ((float) DOL_VERSION >= 6) {
	include DOL_DOCUMENT_ROOT . '/core/actions_setmoduleoptions.inc.php';
}

if ($action == "save") {

	if ($_FILES['csd']['type'] != "application/x-x509-ca-cert") {
		$error['csd'] = "El archivo CSD no corresponde a un CSD";
	}
	if ($_FILES['key']['type'] != "application/octet-stream") {
		$error['key'] = "El archivo KEY no es válido";
	}

	if ($error['key'] == NULL && $error['csd'] == NULL) {
		$filename_csd = $_FILES['csd']['name'];
		$path_csd = $csd = $conf->cfdiutils->dir_output . '/' . $filename_csd;

		move_uploaded_file($_FILES['csd']['tmp_name'], $path_csd . $filename);

		$sql = "SELECT rowid from " . MAIN_DB_PREFIX . "cfdiutils_conf where type = 'CSD' AND entity = " . $conf->entity;
		$resql = $db->query($sql);
		$num_rows = $db->num_rows($resql);

		if ($num_rows > 0) {

			$obj = $db->fetch_object($resql);
			$sql = "UPDATE " . MAIN_DB_PREFIX . "cfdiutils_conf SET value ='" . $path_csd . "' where rowid = " . $obj->rowid;
			$result = $db->query($sql);
			$db->free();
		} else {

			$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_conf (type,value,entity) ";
			$sql .= "VALUES ('CSD','" . $path_csd . "'," . $conf->entity . ")";
			$result = $db->query($sql);
			$db->free();
		}

		$filename_key = $_FILES['key']['name'];
		$path_key = $key = $conf->cfdiutils->dir_output . '/' . $filename_key;
		move_uploaded_file($_FILES['key']['tmp_name'], $path_key . $filename);

		$sql = "SELECT rowid from " . MAIN_DB_PREFIX . "cfdiutils_conf where type = 'KEY' AND entity = " . $conf->entity;
		$resql = $db->query($sql);
		$num_rows = $db->num_rows($resql);

		if ($num_rows > 0) {

			$obj = $db->fetch_object($resql);
			$sql = "UPDATE " . MAIN_DB_PREFIX . "cfdiutils_conf SET value ='" . $path_key . "' where rowid = " . $obj->rowid;
			$result += $db->query($sql);
			$db->free();
		} else {

			$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_conf (type,value,entity) ";
			$sql .= "VALUES ('KEY','" . $path_key . "'," . $conf->entity . ")";
			$result += $db->query($sql);
			$db->free();
		}

		if ($passkey > 0) {

			$sql = "SELECT rowid from " . MAIN_DB_PREFIX . "cfdiutils_conf where type = 'PASSKEY' AND entity = " . $conf->entity;
			$resql = $db->query($sql);
			$num_rows = $db->num_rows($resql);

			if ($num_rows > 0) {

				$obj = $db->fetch_object($resql);
				$sql = "UPDATE " . MAIN_DB_PREFIX . "cfdiutils_conf SET value ='" . $passkey . "' where rowid = " . $obj->rowid;
				$result += $db->query($sql);
				$db->free();
			} else {

				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "cfdiutils_conf (type,value,entity) ";
				$sql .= "VALUES ('PASSKEY','" . $passkey . "'," . $conf->entity . ")";
				$result += $db->query($sql);
				$db->free();
			}
		}


		if ($result <= 0) {
			$error['key'] = "Hubo un error al guardar la configuración";
			return;
		}
	}
} else {

	$sql = "SELECT rowid,type,value from " . MAIN_DB_PREFIX . "cfdiutils_conf where entity = " . $conf->entity;
	$resql = $db->query($sql);
	$num_rows = $db->num_rows($resql);
	if ($num_rows > 0) {
		$i = 0;
		while ($i < $num_rows) {
			$obj = $db->fetch_object($resql);

			if ($obj->type == "CSD") {
				$csd = $obj->value;
			}
			if ($obj->type == "KEY") {
				$key = $obj->value;
			}

			$i++;
		}
	}
}


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "CfdiutilsSetup";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = cfdiutilsAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "cfdiutils@cfdiutils");

// Setup page goes here
echo '<span class="opacitymedium">' . $langs->trans("CfdiutilsSetupPage") . '</span><br><br>';

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="save">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';
print '<tr><td>Archivo CSD CER</td><td><input type="file" name="csd" id="csd"></td></tr>';
if ($csd) {
	print '<tr><td>Ruta CSD CER</td><td>' . $csd . '</td></tr>';
}
print '<tr><td>Archivo CSD KEY</td><td><input type="file" name="key" id="key"></td></tr>';
if ($key) {
	print '<tr><td>Ruta CSD KEY</td><td>' . $key . '</td></tr>';
}
print '<tr><td>Contraseña de los certificados</td><td><input type="text" name="passkey" id="passkey"></td></tr>';
print '</table>';
print '<br><div class="center">';
print '<input class="button button-save" type="submit" value="' . $langs->trans("Save") . '">';
print '</div>';
print '</form>';
print '<br>';

if ($error['csd'] != NULL || $error['key'] != NULL) {
	print '<div class=""><span>';
	foreach ($error as $err) {
		print '<label class="badge badge-status1">' . $err . '</label><br>';
	}
	print '<span></div>';
}



// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
