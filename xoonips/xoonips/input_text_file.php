<?php
// $Revision: 1.11.2.1.2.12 $
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

include 'include/common.inc.php';

// If not a user, redirect
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
if ($uid == UID_GUEST) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');

$name = $formdata->getValue('get', 'name', 's', true);

// name -> get names to display
$ft_handler = &xoonips_getormhandler('xoonips', 'file_type');
$criteria = new Criteria('name', addslashes($name));
$ft_objs = &$ft_handler->getObjects($criteria);
if (count($ft_objs) != 1) {
    die('invalid name');
}
$displayName = $ft_objs[0]->getVar('display_name', 's');
unset($ft_objs);

//var_dump( $_POST );
//var_dump( $_FILES );
$text = false;
$file = $formdata->getFile('file', false);
$errorMessage = false;
if (!is_null($file)) {
    // file was uploaded
    $originalFileName = $file['name'];
    $mimeType = $file['type'];
    $fileName = $file['tmp_name'];
    $error = (int) $file['error'];
    if ($error != 0) {
        if ($error == UPLOAD_ERR_INI_SIZE) {
            $errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_TOO_LARGE;
        } else {
            $errorMessage = _MD_XOONIPS_ITEM_UPLOAD_FILE_FAILED;
        }
        $getTextFromOpener = true;
    } else {
        // check mime type
        if (strstr($mimeType, 'text/plain') === false) {
            $errorMessage = 'unsupported file type : '.$mimeType;
            $getTextFromOpener = true;
        } else {
            $text = file_get_contents($fileName);
            // convert encoding to _CHARSET
            $unicode = &xoonips_getutility('unicode');
            $text = $unicode->convert_encoding($text, _CHARSET, 'h');
            $getTextFromOpener = false;
        }
    }
} else {
    $getTextFromOpener = true;
}

xoops_header(false);

?>
</head>
<body <?php if ($getTextFromOpener) {
    echo 'onload="getTextFromOpener()"';
} ?> >
<form  method="post" action='' enctype="multipart/form-data">
<table class="outer">
<tr>
<td class="head">
  <?php echo $displayName; ?>
</td>
<td class="even">
<input type="hidden" name="name" value="<?php echo $textutil->html_special_chars($name); ?>" id="xoonipsName"/>
<input type="file" name="file"/>
<input class="formButton" type="submit" name="" value="<?php echo _MD_XOONIPS_ITEM_UPLOAD_LABEL; ?>"/>
<br />
<?php
if ($errorMessage) {
    echo '<div style="color:red; font-weight:bold;">';
    echo $textutil->html_special_chars($errorMessage);
    echo '</div>';
}
?>
<br />

<textarea name="text" id="xoonipsText" cols="37" rows="8" style="width:296px;">
<?php echo $textutil->html_special_chars($text); ?>
</textarea>

<br />

<input class="formButton" type="button" value="<?php echo _MD_XOONIPS_ITEM_OK_LABEL; ?>" onclick="clickOK()"/>
<input class="formButton" type="button" value="<?php echo _MD_XOONIPS_ITEM_CANCEL_LABEL; ?>" onclick="clickCancel()" />

</td>
</tr>
</table>
</form>
<script type="text/javascript">
<!--

function headText(str){
	str = str.replace(/\r\n/, "\n");
	str = str.replace(/\r/, "\n");
	return str;
}

function nl2br(str){
	return str.replace(/\n/, " <br /> ");
}

function clickOK(){
	var opener = window.opener;
	var name = document.getElementById('xoonipsName').value;
	
	// textarea -> opener_text
	var textarea = document.getElementById('xoonipsText');
	var text = textarea.value; // IE:OK mozilla:OK safari:OK
	text = text.split("\u00a5").join("\u005c"); // yen sign -> backslash
	
	// set text to opener
	var openerEncText = opener.document.getElementById(name+'EncText');
	var openerShowText = opener.document.getElementById(name+'ShowText');
	openerEncText.value = text;
	
	//openerShowText.firstChild.nodeValue = nl2br(headText(text)); // IE:OK mozilla:OK safari:OK
	var str = text;
	str = str.replace(/\r\n/g, "\n");
	str = str.replace(/\r/g, "\n");
	var ar = str.split( "\n" );
	
	// clear showText
	while ( openerShowText.hasChildNodes() )
		openerShowText.removeChild( openerShowText.lastChild );
	
	// appendto showText
	var i;
	var visibleLines = 3;
	for ( i = 0; i < ar.length && i < visibleLines; i++ ){
		if ( i == visibleLines-1 ){
			if ( ar.length > visibleLines+1 || ar.length == visibleLines+1 && ar[visibleLines] != '' ){
				//window.alert(ar);
				//window.alert(visibleLines);
				ar[i] = ar[i] + ' ...';
			}
		}
		openerShowText.appendChild( opener.document.createTextNode(ar[i]) );
		openerShowText.appendChild( opener.document.createElement('BR'));
	}
	
	window.close();
	return false;
}

function clickCancel(){
	window.close();
	return false;
}

<?php if ($getTextFromOpener) {
    ?>

function resizer(){
<?php 
    /* resize window to prefered height
        see : http://www.quirksmode.org/viewport/compatibility.html
    */
?>
	
	window.resizeTo( 500, 400 );
	
	var windowInnerHeight;
	if (self.innerHeight) // all except Explorer
		windowInnerHeight = self.innerHeight;
	else if (document.documentElement && document.documentElement.clientHeight) // Explorer 6 Strict Mode
		windowInnerHeight = document.documentElement.clientHeight;
	else if (document.body) // other Explorers
		windowInnerHeight = document.body.clientHeight;
	
	var documentHeight;
	var test1 = document.body.scrollHeight;
	var test2 = document.body.offsetHeight
	if (test1 > test2) // all but Explorer Mac
		documentHeight = document.body.scrollHeight;
	else // Explorer Mac;
	     //would also work in Explorer 6 Strict, Mozilla and Safari
		documentHeight = document.body.offsetHeight;
	
	if ( windowInnerHeight && documentHeight ){
		window.resizeBy( 0, documentHeight - windowInnerHeight );
	}
}

function getTextFromOpener(){
	var opener = window.opener;
	var name = document.getElementById('xoonipsName').value;
	
	// opener_text -> textarea
	var encText = opener.document.getElementById(name+'EncText').value; // IE:OK mozilla:OK safari:OK
	var tarea = document.getElementById('xoonipsText');
	if ( encText == '' && name == 'rights' )
		encText = "All rights reserved.\nLicense:\n";
	tarea.value = encText;
	resizer();
}
<?php 
} ?>


//-->
</script>

<?php
//$xoopsTpl->assign('item_htmls', $item_htmls );

//include XOOPS_ROOT_PATH.'/footer.php';
xoops_footer();
