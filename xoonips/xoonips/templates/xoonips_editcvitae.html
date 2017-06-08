<{* Page of Edit Curriculum Vitae *}>

<p>
<a href="showusers.php"><{$smarty.const._MD_XOONIPS_SHOW_USER_TITLE}></a>
<{$smarty.const._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR}>
<{$smarty.const._MI_XOONIPS_ITEM_PANKUZU_EDIT_CURRICULUM_VITAE}>
</p>


<{if $xid != 1 }>
<h4><{$smarty.const._MD_XOONIPS_ADD_CURRICULUM_VITAE_LABEL}></h4>
<{if $ent == 1}>
<span style="color: red;"><{$smarty.const._MD_XOONIPS_ADD_CURRICULUM_VITAE_WARNING}></span>
<{/if}>
<form action="editcvitae.php?op=register" method="post"<{$accept_charset}>>
<table width="100%" border="0" cellspacing="5">
  <tr valign="top" class="head">
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_FROM}></th>
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_TO}></th>
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_TITLE}></th>
  </tr>
  <tr valign="top">
    <td class="odd"><{html_select_date display_days=false start_year="-50" end_year="+20" month_format="%b" field_array="cvitae_from_date" time="2006-04-01" month_empty="none" year_empty="none"}></td>
    <td class="odd"><{html_select_date display_days=false start_year="-50" end_year="+20" month_format="%b" field_array="cvitae_to_date" time="2006-03-31" month_empty="none" year_empty="none"}></td>
    <td class="odd"><input type="text" name="cvitae_title" size="70"/>&nbsp;<input class="formButton" type="submit" value="<{$smarty.const._MD_XOONIPS_ADD_CURRICULUM_VITAE}>"/></td>
  </tr>
</table> 
</form>

<hr />
<{/if}>

<h4><{$smarty.const._MD_XOONIPS_MODIFY_CURRICULUM_VITAE_LABEL}></h4>
<{if $rcount == 0 }>
 &nbsp;&nbsp;<{$smarty.const._MD_XOONIPS_NO_CURRICULUM_VITAE_DATA}>
<{else}>
<script type="text/javascript">
<!--
// If checkbox is on and EditCvitae is empty, it returns error.
function canEditCvitae(submit){
    var form = submit.form;
    var elements = form.elements;
    var elementsLen = elements.length;
    var i;
    var error = false;
    
    for ( i = 0; i < elementsLen; i++ ){
        var element = elements.item(i);
        if ( element.id.substring(0,13) == 'xoonipsCheck' && element.checked ){
            var xid = element.id.substring(13);
            var rename = document.getElementById('cvitaeEdit' + xid);
            if ( rename != undefined ){
                if ( rename.value == "" ){
                    error = true;
                }
            }
        }
    }
    
    if ( error ){
        window.alert("<{$smarty.const._MD_XOONIPS_INDEX_NAME_CANNOT_BE_EMPTY}>");
        return false;
    }
    return true;
}

function cvitaeUpDown( op, updown_cvitae ) {
	document.getElementById('xoonips_op').value = op;
	document.getElementById('xoonips_updown_cvitae').value = updown_cvitae;
	document.getElementById('xoonips_modify_cvitae_form').submit();
	return false;
}

//-->
</script>
<form action="editcvitae.php" method="post" id="xoonips_modify_cvitae_form"<{$accept_charset}>>
<input type="hidden" name="uid" value="<{$uid}>"/>
<input type="hidden" name="updown_cvitae" value="" id="xoonips_updown_cvitae"/>
<input type="hidden" name="op" value="" id="xoonips_op"/>
<table>
  <tr valign="top">
    <th></th>
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_FROM}></th>
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_TO}></th>
    <th><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_TITLE}></th>
    <th><{$smarty.const._MD_XOONIPS_INDEX_LABEL_UPDOWN}></th>
  </tr>
<{foreach from=$cv_array item=cvdata}>
  <tr valign="top" class="<{cycle values="even,odd"}>">
    <td><input type="checkbox" name="check[]" value="<{$cvdata.cvitae_id}>" id="xoonipsCheck<{$cvdata.cvitae_id}>"/></td>
    <{if $cvdata.cvitae_from_date !== null }>
     <td><{html_select_date start_year="-50" end_year="+20" display_days=false month_format="%b" field_array=$cvdata.cvitae_id|cat:"_from" time=$cvdata.cvitae_from_date month_empty="none" year_empty="none"}></td>
    <{else}>
     <td><{html_select_date start_year="-50" end_year="+20" display_days=false month_format="%b" field_array=$cvdata.cvitae_id|cat:"_from" month_empty="none" year_empty="none"}></td>
    <{/if}>
    <{if $cvdata.cvitae_to_date !== null }>
     <td><{html_select_date start_year="-50" end_year="+20" display_days=false month_format="%b" field_array=$cvdata.cvitae_id|cat:"_to" time=$cvdata.cvitae_to_date month_empty="none" year_empty="none"}></td>
    <{else}>
     <td><{html_select_date start_year="-50" end_year="+20" display_days=false month_format="%b" field_array=$cvdata.cvitae_id|cat:"_to" month_empty="none" year_empty="none"}></td>
    <{/if}>
    <td><input type="text" name="cvitae_title<{$cvdata.cvitae_id}>" size="70" value="<{$cvdata.cvitae_title}>"/></td>
    <td>
      <select name="steps[<{$cvdata.cvitae_id}>]">
      <{html_options options=$updown_options selected=1}>
      </select>
      <a href="#" onclick="return cvitaeUpDown('up',<{$cvdata.cvitae_id}>)"><{$smarty.const._MD_XOONIPS_INDEX_LABEL_UP}></a>
    / <a href="#" onclick="return cvitaeUpDown('down',<{$cvdata.cvitae_id}>)"><{$smarty.const._MD_XOONIPS_INDEX_LABEL_DOWN}></a></td>
  </tr>
<{/foreach}>
  <tr>
    <td><img src="images/arrow_ltr.png" alt="arrow"/></td>
    <td colspan=2>
      <table><tr>
      <td>
        <{$smarty.const._MD_XOONIPS_INDEX_LABEL_WITH_SELECTED}>
      </td>
      <td>
        <input class="formButton" type="submit" value="<{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_BUTTON_EDIT}>" name="op_modify" onclick="return canEditCvitae(this)"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
      <td>
        <input class="formButton" type="submit" value="<{$smarty.const._MD_XOONIPS_INDEX_BUTTON_DELETE}>" name="op_delete"/> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
      </tr></table>
    </td>
  </tr>
</table>
</form>
<{/if}>

<div align="right"><a href="showusers.php"><{$smarty.const._MD_XOONIPS_CURRICULUM_VITAE_CHECK_INFO}></a></div>

