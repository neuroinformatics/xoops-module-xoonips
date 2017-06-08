<?php
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

require 'include/common.inc.php';

require_once 'include/lib.php';
require_once 'include/AL.php';

// If not a user, redirect
if (!$xoopsUser) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

xoops_header(false);

?>
</head>
<body>
<table border="0" cellspacing="5" cellpadding="0">
 <tr>
  <td id="leftcolumn">              
<?php
$xoopsConfig['nocommon'] = '';
require XOOPS_ROOT_PATH.'/header.php';

require_once '../../class/template.php';
require_once 'blocks/xoonips_blocks.php';

$xoopsModule = &XoopsModule::getByDirname('xoonips');
//print_r($xoopsModule);

$mod_blocks = &XoopsBlock::getByModule($xoopsModule->getVar('mid'));
//print_r($blocks);
$blocks = array();
//copy only necessary block from mod_block to blocks.
foreach ($mod_blocks as $b) {
    if ($b->getVar('mid') == $xoopsModule->getVar('mid')) {
        if ($b->getVar('show_func') == 'b_xoonips_quick_search_show'
            || $b->getVar('show_func') == 'b_xoonips_tree_show'
        ) {
            $blocks[$b->getVar('show_func')] = $b;
        }
    }
}

?>
   <div class="blockTitle"><?php echo $blocks['b_xoonips_tree_show']->getVar('title'); ?></div>
   <div class="blockContent">
   <table cellspacing="0">
    <tr>
     <td>
<?php

$tpl = new XoopsTpl();
$block = $blocks['b_xoonips_tree_show']->buildBlock();
$block['query'] = 'url=related_to_subwin.php';
$tpl->assign('block', $block);
echo $tpl->fetch('db:xoonips_block_tree.html');
?>
     </td>
    </tr>
   </table>
   </div>

   <div class="blockTitle"><?php echo $blocks['b_xoonips_quick_search_show']->getVar('title'); ?></div>
   <div class="blockContent">
   <table cellspacing="0">
    <tr>
     <td>
<?php
$tpl = new XoopsTpl();
$bl = $blocks['b_xoonips_quick_search_show']->buildBlock();
$bl['submit_url'] = XOOPS_URL.'/modules/xoonips/related_to_subwin.php';
$bl['advanced_search_enable'] = false;
$bl['search_itemtypes'] = array('all' => _MD_XOONIPS_SEARCH_ALL,
                                 'basic' => _MD_XOONIPS_SEARCH_TITLE_AND_KEYWORD, );
$itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
foreach ($itemtype_handler->getObjects(new Criteria('item_type_id', ITID_INDEX, '!=')) as $itemtype) {
    if ($itemtype->getVar('item_type_id', 'n') != ITID_INDEX) {
        $bl['search_itemtypes'][$itemtype->getVar('name', 's')] = $itemtype->getVar('display_name', 's');
    }
}
$tpl->assign('block', $bl);
echo $tpl->fetch('db:xoonips_block_quick_search.html');
?>
     </td>
    </tr>
   </table>
   </div>
  </td>
  <td id='centercolumn'>
<?php

$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('post', 'op', 'n', false);
if (!isset($op) || empty($op)) {
    $formdata->set('post', 'op', 'related_to_from_index');
}
$formdata->set('post', 'index_id', $formdata->getValue('both', 'index_id', 'i', false));

require 'include/itemselect.inc.php';
$xoopsTpl->display('db:xoonips_related_to_itemselect.html');

?>
  </td>
 </tr>
</table>

<?php
xoops_footer();
