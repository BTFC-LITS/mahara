<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
define('NOCHECKPASSWORDCHANGE', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('activity.php');

if (param_integer('login_submitted', 0)) {
    redirect(get_config('wwwroot'));
}

if (param_integer('restore', 0)) {
    $id = $USER->restore_identity();
    redirect(get_config('wwwroot') . 'admin/users/edit.php?id=' . $id);
}

/**
 * Notify user (if configured), do the masquerading and emit event. Called when
 * no (further) interaction with the admin is needed before the loginas.
 *
 * @param string $why The masquerading reason (if given) or null.
 */
function do_masquerade($why = null) {
    global $USER, $SESSION;
    $id = param_integer('id');
    $who = display_name($USER, $id);
    $when = format_date(time());
    if (get_config('masqueradingnotified')) {
        $msg = (object) array(
            'subject'   => get_string('masqueradenotificationsubject', 'admin'),
            'message'   => $why === null ?
                get_string('masqueradenotificationnoreason', 'admin',
                    $who, $when
                ) :
                get_string('masqueradenotificationreason', 'admin',
                    $who, $when, $why
                ),
            'users'     => array($id),
            'url'       => profile_url($USER, false),
            'urltext'   => $who,
        );
        activity_occurred('maharamessage', $msg);
        $SESSION->add_info_msg(get_string('masqueradenotificationdone', 'admin'));
    }
    $USER->change_identity_to($id);  // Permissions checking is done in here
    handle_event('loginas', array(
        'who' => $who,
        'when' => $when,
        'reason' => $why,
    ));
    redirect(get_config('wwwroot'));
}

if (!get_config('masqueradingreasonrequired')) {
    do_masquerade();
}

require_once('pieforms/pieform.php');

$form = array(
    'name'       => 'masqueradereason',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'core',
    'elements'   => array(
        'reason' => array(
            'type'         => 'textarea',
            'title'        => get_string('masqueradereason', 'admin'),
            'description'  => (get_config('masqueradingnotified') ?  get_string('masqueradenotifiedreasondescription', 'admin') : get_string('masqueradereasondescription', 'admin')),
            'defaultvalue' => '',
            'rows'         => 3,
            'cols'         => 30,
            'rules'        => array(
                'required'     => true,
            ),
            'help'         => true,
        ),
        'id' => array(
            'type'         => 'hidden',
            'value'        => param_integer('id'),
        ),
        'submit' => array(
            'type'         => 'submit',
            'value'        => get_string('masquerade', 'admin')
        ),
    ),
);
$form = pieform($form);

function masqueradereason_submit(Pieform $form, $values) {
    do_masquerade($values['reason']);
}

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->display('admin/users/changeuser.tpl');
