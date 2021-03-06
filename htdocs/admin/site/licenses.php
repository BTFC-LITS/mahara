<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2012 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitelicenses');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'licenses');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('license.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('sitelicenses', 'admin'));
define('DEFAULTPAGE', 'home');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['license_delete'])) {
        $del = array_shift(array_keys($_POST['license_delete']));
        delete_records('artefact_license', 'name', $del);
        $SESSION->add_ok_msg(get_string('licensedeleted', 'admin'));
    }
}

if (!isset($licenses)) {
    $licenses = get_records_assoc('artefact_license', null, null, 'displayname');
}
$extralicenses = get_column_sql("
    SELECT license FROM artefact WHERE license IS NOT NULL and license <> ''
    EXCEPT
    SELECT name FROM artefact_license
");

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('licenses', $licenses);
$smarty->assign('extralicenses', $extralicenses);
$smarty->assign('enabled', get_config('licensemetadata'));
$smarty->display('admin/site/licenses.tpl');
