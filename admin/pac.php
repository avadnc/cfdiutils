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
 * \file    cfdiutils/admin/about.php
 * \ingroup cfdiutils
 * \brief   About page of module Cfdiutils.
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

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once '../lib/cfdiutils.lib.php';

// Translations
// $langs->loadLangs(array('bills', 'companies', 'compta', 'products', 'banks', 'main', 'withdrawals'));
$langs->loadLangs(array("errors", "bills", "cfdiutils@cfdiutils"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */
if ($action == "savepac") {
	if (dolibarr_set_const($db, 'CFDIUTILS_PAC', GETPOST('pac'), 'chaine', 1, 'PAC SELECCIONADO', $conf->entity) >= 0) {

        if (file_exists('../pac/' . $conf->global->CFDIUTILS_PAC . '/conf.php')) {
            // setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
            include '../pac/'.$conf->global->CFDIUTILS_PAC.'/conf.php';

			//TODO: execute first init configuration from conf.php

        }
	}
}

// None


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
print dol_get_fiche_head($head, 'pac', $langs->trans($page_name), -1, 'cfdiutils@cfdiutils');

//Page Body

print '<div>';

print '<span class="opacitymedium">' . $langs->trans('SelectPac') . '</span><br><br>';
print '<form id="savepac" method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="savepac">';
// print '<table class="noborder centpercent">'; //Init Table
// print '<tr class="liste_titre"><td class="titlefield">Forma de Pago</td><td>Código SAT</td></tr>'; //Title Table

print '<select name="pac" id="pac">';
$dir = scandir('../pac/', 1);
foreach ($dir as $pac) {

	if ($pac === '..' || $pac === '.') {
		continue;
	} else {
		if ($conf->global->CFDIUTILS_PAC == strtoupper($pac)) {
			print '<option value="' . strtoupper($pac) . '" selected>' . strtoupper($pac) . '</option>';
		} else {
			print '<option value="' . strtoupper($pac) . '">' . strtoupper($pac) . '</option>';
		}
	}
}
print '</select>';
print '<input type="submit" class="butAction" value="' . $langs->trans('SavePac') . '">';
print '</form>';

print '</div>';
if (file_exists('../pac/' . $conf->global->CFDIUTILS_PAC . '/conf.php')) {
	// setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
	include '../pac/' . $conf->global->CFDIUTILS_PAC . '/setup.php';

}


// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
