<?php

// $Revision: 1.7.4.1.2.17 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';
require_once 'include/lib.php';
require_once 'include/AL.php';

$xnpsid = $_SESSION['XNPSID'];

xoonips_deny_guest_access();

$op = 'open';

(method_exists(MyTextSanitizer, sGetInstance) and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');

$uid = $formdata->getValue('post', 'uid', 'i', false, UID_GUEST);

if ($uid <= 0) {
    if ($xoopsUser) {
        //Next, try to retrive a UID from a xoopsUser.
        $uid = $xoopsUser->getVar('uid');
    } else {
        redirect_header(XOOPS_URL.'/', 3, _US_SELECTNG);
        exit();
    }
}

//error if argument 'uid' is not equal to own UID
if (xnp_is_moderator($xnpsid, $_SESSION['xoopsUserId']) || $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
} elseif ($uid != $_SESSION['xoopsUserId']) {
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
}

$xoopsOption['template_main'] = 'xoonips_editcvitae.html';
require XOOPS_ROOT_PATH.'/header.php';

$op = $formdata->getValue('both', 'op', 's', false);

$op_delete = $formdata->getValue('post', 'op_delete', 's', false);
$op_modify = $formdata->getValue('post', 'op_modify', 's', false);
if (isset($op_delete)) {
    $op = 'delete';
}
if (isset($op_modify)) {
    $op = 'modify';
}

// function to get ahead (in up) or after one's (in down) cvitae ids ( cvitae_id and cvitae_order )
function xnpGetSwapCvitaeId($pos, $uid)
{
    global $xoopsDB;
    $sql = 'SELECT cvitae_id, cvitae_order FROM '.$xoopsDB->prefix('xoonips_cvitaes').' WHERE uid='.$uid.' ORDER BY cvitae_order ASC';
    $res = $xoopsDB->query($sql);
    $i = 0;
    while ($row = $xoopsDB->fetchArray($res)) {
        if ($pos == $i) {
            $cvitae_id = $row['cvitae_id'];
            $cvitae_order = $row['cvitae_order'];
            $result = array($cvitae_id => $cvitae_order);
            break;
        } else {
            ++$i;
        }
    }
    if (isset($result)) {
        return $result;
    } else {
        return null;
    }
}

// function to get order in array of cvitae_ids
function xnpGetSwapCvitaePos($id)
{
    global $xoopsDB;
    $sql = 'SELECT cvitae_id FROM '.$xoopsDB->prefix('xoonips_cvitaes').' WHERE uid='.$id.' ORDER BY cvitae_order ASC, from_year ASC, from_month ASC';
    $res = $xoopsDB->query($sql);
    $result = $xoopsDB->getRowsNum($res);
    if (isset($result)) {
        return $result;
    } else {
        return null;
    }
}

// operation
if ($op == 'open' || $op == '') {
} elseif ($op == 'register') {
    $cvitae_title = $formdata->getValue('post', 'cvitae_title', 's', false);
    if (empty($cvitae_title)) {
        $ent = 1;
        $xoopsTpl->assign('ent', $ent);
    } else {
        $cvitae_from_date = $formdata->getValueArray('post', 'cvitae_from_date', 's', false);
        $from_month4sql = isset($cvitae_from_date['Date_Month']) ? addslashes($cvitae_from_date['Date_Month']) : '';
        if (strlen(trim($cvitae_from_date['Date_Month'])) == 0) {
            $from_month4sql = 0;
        }
        $from_year4sql = isset($cvitae_from_date['Date_Year']) ? addslashes($cvitae_from_date['Date_Year']) : '';
        if (strlen(trim($cvitae_from_date['Date_Year'])) == 0) {
            $from_year4sql = 0;
        }
        $cvitae_to_date = $formdata->getValueArray('post', 'cvitae_to_date', 's', false);
        $to_month4sql = isset($cvitae_to_date['Date_Month']) ? addslashes($cvitae_to_date['Date_Month']) : '';
        if (strlen(trim($cvitae_to_date['Date_Month'])) == 0) {
            $to_month4sql = 0;
        }
        $to_year4sql = isset($cvitae_to_date['Date_Year']) ? addslashes($cvitae_to_date['Date_Year']) : '';
        if (strlen(trim($cvitae_to_date['Date_Year'])) == 0) {
            $to_year4sql = 0;
        }
        $cvitae_title4sql = addslashes($cvitae_title);
        $besql = 'SELECT cvitae_order FROM '.$xoopsDB->prefix('xoonips_cvitaes').' ORDER BY cvitae_order DESC LIMIT 1';
        $beres = $xoopsDB->query($besql);
        if (!$beres) {
            // $err = "Can't insert data into database.";
            // if No CV data in DB, cvitae_order=1.
            $cvitae_order4sql = 1;
        }
        if ($beres == false) {
            echo "ERROR: SQL=$besql<br />\n error=".$xoopsDB->error()."<br />\n";
        }
        $berow = $xoopsDB->fetchArray($beres);
        $current_order = $berow['cvitae_order'];
        $cvitae_order4sql = ++$current_order;
        $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_cvitaes').' ';
        $sql .= 'SET uid='.$uid.', from_month='.$from_month4sql.', from_year='.$from_year4sql.', to_month='.$to_month4sql.', ';
        $sql .= "to_year='".$to_year4sql."', cvitae_title='".$cvitae_title4sql."', cvitae_order='".$cvitae_order4sql."'";
        $result = $xoopsDB->query($sql);
        redirect_header('editcvitae.php', 1, _MD_XOONIPS_CURRICULUM_VITAE_INSERT);
        if (!$result) {
            $err = "Can't insert data into database.";
        }
        if ($result == false) {
            echo "ERROR: SQL=$sql<br />\n error=".$xoopsDB->error()."<br />\n";
        }
    }
} elseif ($op == 'modify') {
    $check = $formdata->getValueArray('post', 'check', 's', false);
    if (isset($check)) {
        reset($check);
        while ($cvitae_id = array_shift($check)) {
            $ecvitae_id = intval($cvitae_id);

            $from = $formdata->getValueArray('post', $ecvitae_id.'_from', 's', false);
            $from_month4up = isset($from['Date_Month']) ? addslashes($from['Date_Month']) : '';
            if (strlen(trim($from['Date_Month'])) == 0) {
                $from_month4up = 0;
            }
            $from_year4up = isset($from['Date_Year']) ? addslashes($from['Date_Year']) : '';
            if (strlen(trim($from['Date_Year'])) == 0) {
                $from_year4up = 0;
            }
            $to = $formdata->getValueArray('post', $ecvitae_id.'_to', 's', false);
            $to_month4up = isset($to['Date_Month']) ? addslashes($to['Date_Month']) : '';
            if (strlen(trim($to['Date_Month'])) == 0) {
                $to_month4up = 0;
            }
            $to_year4up = isset($to['Date_Year']) ? addslashes($to['Date_Year']) : '';
            if (strlen(trim($to['Date_Year'])) == 0) {
                $to_year4up = 0;
            }
            $cvitae_title4up = addslashes($formdata->getValue('post', 'cvitae_title'.$ecvitae_id, 's', false));
            $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_cvitaes').' ';
            $sql .= 'SET from_month='.$from_month4up.', from_year='.$from_year4up.', to_month='.$to_month4up.', ';
            $sql .= 'to_year='.$to_year4up.", cvitae_title='".$cvitae_title4up."' WHERE cvitae_id=".$ecvitae_id.' AND uid='.$uid.'';
            $result = $xoopsDB->query($sql);
            if (!$result) {
                $err = "Can't update data into database.";
            }
            if ($result == false) {
                echo "ERROR: SQL=$sql<br />\n error=".$xoopsDB->error()."<br />\n";
            }
        }
    }
} elseif ($op == 'delete') {
    $check = $formdata->getValueArray('post', 'check', 's', false);
    if (isset($check)) {
        reset($check);
        while ($cvitae_id = array_shift($check)) {
            $dcvitae_id = intval($cvitae_id);
            $sql = 'DELETE FROM '.$xoopsDB->prefix('xoonips_cvitaes').' WHERE cvitae_id='.$dcvitae_id.' AND uid='.$uid.'';
            $result = $xoopsDB->query($sql);
            if (!$result) {
                $err = "Can't delete data into database.";
            }
            if ($result == false) {
                echo "ERROR: SQL=$sql<br />\n error=".$xoopsDB->error()."<br />\n";
            }
        }
    }
} elseif ($op == 'up' || $op == 'down') {
    $move_id = $formdata->getValue('post', 'updown_cvitae', 'i', true);
    $steps = $formdata->getValueArray('post', 'steps', 'i', true);
    $step = $steps[$move_id];

    if ($op == 'up') {
        $dir = -1;
    } else {
        $dir = 1;
    }

    $cvitaeLen = xnpGetSwapCvitaePos($uid);

    // get position
    $pos = -1;
    // $psql = "SELECT cvitae_id FROM ".$xoopsDB->prefix('xoonips_cvitaes')." WHERE uid=".$uid." ORDER BY cvitae_order ASC, from_year ASC, from_month ASC";
    $psql = 'SELECT cvitae_id, cvitae_order FROM '.$xoopsDB->prefix('xoonips_cvitaes').' WHERE uid='.$uid.' ORDER BY cvitae_order ASC, from_year ASC, from_month ASC';
    $pres = $xoopsDB->query($psql);
    $i = 0;
    while ($prow = $xoopsDB->fetchArray($pres)) {
        $cvitae_id_sql = $prow['cvitae_id'];
        if ($move_id == $cvitae_id_sql) {
            $move_order = $prow['cvitae_order'];
            $pos = $i;
            break;
        } else {
            ++$i;
        }
    }

    if ($pos != -1) {
        // change order ch1:cvitae_order=0 (cvitae_id=move_id)
        //              ch2:cvitae_order=move_id's order (cvitae_id=updown_cvitae2)
        //              ch3:cvitae_order=updown_cvitae2's order (cvitae_id=move_id)
        for ($i = 0; $i < $step; ++$i) {
            $pos += $dir;

            if ($pos < 0 || $cvitaeLen <= $pos) {
                break;
            }

            $nextArray = xnpGetSwapCvitaeId($pos, $uid);
            foreach ($nextArray as $key => $value) {
                $updown_cvitae2 = strval($key);
                $change_orders2 = $value;
            }

            $ch1sql = 'UPDATE '.$xoopsDB->prefix('xoonips_cvitaes').' SET cvitae_order = 0 WHERE cvitae_id='.$move_id.' ';
            $ch1res = $xoopsDB->query($ch1sql);
            if (!$ch1res) {
                $err = "Can't update data into database1.";
            }
            if ($ch1res == false) {
                echo "ERROR: SQL=$ch1sql<br />\n error=".$xoopsDB->error()."<br />1\n";
            }
            $ch2sql = 'UPDATE '.$xoopsDB->prefix('xoonips_cvitaes').' SET cvitae_order = '.$move_order.' WHERE cvitae_id='.$updown_cvitae2.'';
            $ch2res = $xoopsDB->query($ch2sql);
            if (!$ch2res) {
                $err = "Can't update data into database2.";
            }
            if ($ch2res == false) {
                echo "ERROR: SQL=$ch2sql<br />\n error=".$xoopsDB->error()."<br />2\n";
            }
            $ch3sql = 'UPDATE '.$xoopsDB->prefix('xoonips_cvitaes').' SET cvitae_order = '.$change_orders2.' WHERE cvitae_id='.$move_id.'';
            $ch3res = $xoopsDB->query($ch3sql);
            if (!$ch3res) {
                $err = "Can't update data into database3.";
            }
            if ($ch3res == false) {
                echo "ERROR: SQL=$ch3sql<br />\n error=".$xoopsDB->error()."<br />3\n";
            }
            $move_order = $change_orders2;
        }
    }
}

// display confirm form
$sql = 'SELECT * FROM '.$xoopsDB->prefix('xoonips_cvitaes').' ';
$sql .= 'WHERE uid='.$uid.' ORDER BY cvitae_order ASC, from_year ASC, from_month ASC';
$result = $xoopsDB->query($sql);
if (!$result) {
    $err = "Can't select data into database.";
}
if ($result == false) {
    echo "ERROR: SQL=$sql<br />\n error=".$xoopsDB->error()."<br />\n";
}
$rcount = $xoopsDB->getRowsNum($result);
$xoopsTpl->assign('rcount', $rcount);
while ($row = $xoopsDB->fetchArray($result)) {
    $cvdata['cvitae_id'] = $myts->makeTboxData4Show($row['cvitae_id']);
    $cvdata['cvitae_from_date'] = $textutil->html_special_chars(xnpMktime($row['from_year'], $row['from_month'], 0));
    $cvdata['cvitae_to_date'] = $textutil->html_special_chars(xnpMktime($row['to_year'], $row['to_month'], 0));
    $cvdata['cvitae_title'] = $textutil->html_special_chars($row['cvitae_title']);
    $xoopsTpl->append('cv_array', $cvdata);
}

$xoopsTpl->assign('updown_options', array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10));

require XOOPS_ROOT_PATH.'/footer.php';
exit();

function xnpMktime($year, $month, $day)
{
    $int_year = intval($year);
    $int_month = intval($month);
    $int_day = intval($day);
    if ($int_month == 0) {
        $date = sprintf('%04s--%02s', $int_year, $int_day);
    } else {
        $date = sprintf('%04s-%02s-%02s', $int_year, $int_month, $int_day);
    }

    return $date;
}
