<?php

// $Revision:$
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

require '../../../include/cp_header.php';

$title = _AM_XNPPAPER_TITLE;

$mid = $xoopsModule->getVar('mid');
if (defined('XOOPS_CUBE_LEGACY')) {
    // for XOOPS Cube 2.1 Legacy
    $pref_url = XOOPS_URL.'/modules/legacy/admin/index.php?action=PreferenceEdit&confmod_id='.$mid;
} else {
    // for XOOPS 2.0
    $pref_url = XOOPS_URL.'/modules/system/admin.php?fct=preferences&op=showmod&mod='.$mid;
}
$pref_title = _PREFERENCES;
$pref_url = htmlspecialchars($pref_url, ENT_QUOTES);

xoops_cp_header();

echo '<h3>'.$title.'</h3>';
echo '<table width="100%" border="0" cellspacing="1" class="outer">';
echo '<tr class="odd"><td>';
echo '<ul style="margin: 5px;">';
echo '<li style="padding: 5px;">';
echo '<a href="'.$pref_url.'">'.$pref_title.'</a>'."\n";
echo '</li>';
echo '</ul>';
echo '</td></tr>';
echo '</table>';

xoops_cp_footer();
