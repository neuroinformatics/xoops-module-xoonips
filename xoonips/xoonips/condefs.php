<?php

// $Revision: 1.17.2.1.2.16 $
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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// debug mode
define('XOONIPS_DEBUG_MODE', false);

define('XOONIPS_URL', XOOPS_URL.'/modules/xoonips');
define('XOONIPS_PATH', XOOPS_ROOT_PATH.'/modules/xoonips');

// reserved item_id value
define('IID_ROOT', 1);
define('IID_PUBLIC', 3);

// reserved item'_'type'_'id value
define('ITID_INDEX', 1);

//IDs of item operation
define('OP_READ', 1);
define('OP_MODIFY', 2);
define('OP_DELETE', 3);

define('DATETIME_FORMAT', 'M j, Y H:i:s');
define('DATE_FORMAT', 'M j, Y');
define('YEAR_MONTH_FORMAT', 'M, Y');
define('YEAR_FORMAT', 'Y');

define('NOT_CERTIFIED', 0);
define('CERTIFY_REQUIRED', 1);
define('CERTIFIED', 2);

define('OL_PUBLIC', 1);
define('OL_GROUP_ONLY', 2);
define('OL_PRIVATE', 3);

define('OP_REGISTER', 1);
define('OP_UNREGISTER', 2);

// Response of PHP Abstract Layer functions
define('RES_OK', 0);
define('RES_ERROR', 1);
define('RES_DB_NOT_INITIALIZED', 2);
define('RES_LOGIN_FAILURE', 3);
define('RES_NO_SUCH_SESSION', 4);
define('RES_NO_SUCH_USER', 5);
define('RES_NO_SUCH_GROUP', 6);
define('RES_DB_QUERY_ERROR', 7);
define('RES_DB_CONNECT_ERROR', 8);
define('RES_DB_INITIALIZE_ERROR', 9);
define('RES_NO_SUCH_ITEM', 10);
define('RES_NO_WRITE_ACCESS_RIGHT', 11);
define('RES_NO_READ_ACCESS_RIGHT', 12);
define('RES_GROUPNAME_ALREADY_EXISTS', 13);
define('RES_PHP_NONREF', 1000);

define('UID_GUEST', 0); // xoonips user id for guest
define('GID_DEFAULT', 1);
define('SID_GUEST', '0'); // session id for guest

define('ETID_LOGIN_FAILURE', 1);
define('ETID_LOGIN_SUCCESS', 2);
define('ETID_LOGOUT', 3);
define('ETID_INSERT_ITEM', 4);
define('ETID_UPDATE_ITEM', 5);
define('ETID_DELETE_ITEM', 6);
define('ETID_VIEW_ITEM', 7);
define('ETID_DOWNLOAD_FILE', 8);
define('ETID_REQUEST_CERTIFY_ITEM', 9);
define('ETID_INSERT_INDEX', 10);
define('ETID_UPDATE_INDEX', 11);
define('ETID_DELETE_INDEX', 12);
define('ETID_CERTIFY_ITEM', 13);
define('ETID_REJECT_ITEM', 14);
define('ETID_REQUEST_INSERT_ACCOUNT', 15);
define('ETID_CERTIFY_ACCOUNT', 16);
define('ETID_INSERT_GROUP', 17);
define('ETID_UPDATE_GROUP', 18);
define('ETID_DELETE_GROUP', 19);
define('ETID_INSERT_GROUP_MEMBER', 20);
define('ETID_DELETE_GROUP_MEMBER', 21);
define('ETID_VIEW_TOP_PAGE', 22);
define('ETID_QUICK_SEARCH', 23);
define('ETID_ADVANCED_SEARCH', 24);
define('ETID_START_SU', 25);
define('ETID_END_SU', 26);
define('ETID_REQUEST_TRANSFER_ITEM', 27);
define('ETID_TRANSFER_ITEM', 28);
define('ETID_REJECT_TRANSFER_ITEM', 29);
define('ETID_CERTIFY_GROUP_INDEX', 30);
define('ETID_REJECT_GROUP_INDEX', 31);
define('ETID_GROUP_INDEX_TO_PUBLIC', 32);
define('ETID_DELETE_ACCOUNT', 33);
define('ETID_UNCERTIFY_ACCOUNT', 34);
define('ETID_MAX', 35);

//metadata event id
define('ME_CREATED', 1);
define('ME_MODIFIED', 2);
define('ME_DELETED', 3);

define('DESC', 1);
define('ASC', 0);

// OAI-PMH
define('REPOSITORY_RESPONSE_LIMIT_ROW', 100); //result rows per a request
define('REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM', 1000); //term which in resumptionToken is available[Sec]

// XooNIps Configurations
define('XNP_CONFIG_CERTIFY_ITEM_KEY', 'certify_item');
define('XNP_CONFIG_CERTIFY_ITEM_AUTO', 'auto');
define('XNP_CONFIG_CERTIFY_ITEM_ON', 'on');
define('XNP_CONFIG_CERTIFY_ITEM_OFF', 'off');
define('XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY', 'public_item_target_user');
define('XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_PLATFORM', 'platform');
define('XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL', 'all');
define('XNP_CONFIG_REPOSITORY_NIJC_CODE', 'repository_nijc_code');
define('XNP_CONFIG_CERTIFY_USER_KEY', 'certify_user');
define('XNP_CONFIG_CERTIFY_USER_AUTO', 'auto');
define('XNP_CONFIG_CERTIFY_USER_ON', 'on');

define('XNP_PRIVATE_INDEX_TITLE', 'Private');

define('XOONIPS_WINDOW_SIZE', 2); // using in generating search_text
define('XOONIPS_SEARCH_TEXT_ENCODING', 'UTF-8'); //encoding of search_text column

define('DEFAULT_INDEX_TITLE_OFFSET', 0); //Define a integer number to specify a index title which to be used for displaying index tree(see xoonips_item_title.title_id)
define('DEFAULT_ORDER_TITLE_OFFSET', 0); //Define a integer number to specify a title which to be used for sorting for listing item(see xoonips_item_title.title_id)

define('XNP_CONFIG_DOI_FIELD_PARAM_NAME', 'id');
define('XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN', 35);
define('XNP_CONFIG_DOI_FIELD_PARAM_PATTERN', '[\-_0-9A-Za-z]+');
// Add Parameter Keio University 20080825
define('XNP_CONFIG_DOWNLOAD_DOI_FIELD_SEPARATE_PARAM', '/');
define('XNP_CONFIG_DOWNLOAD_FILE_TYPE_LIMIT', 'application/pdf,image/jpeg');

// Index Tree
define('XOONIPS_TREE_SWAP_IMAGE_DIR', 'xoonips_images');

define('XOONIPS_LISTINDEX_MODE_ALL', 0);
define('XOONIPS_LISTINDEX_PUBLICONLY', 1);
define('XOONIPS_LISTINDEX_PRIVATEONLY', 2);

// Import
define('E_XOONIPS_SUCCESS', 'E0000');
define('E_XOONIPS_ERROR', 'E0001');
define('E_XOONIPS_PARSER', 'E0002');
define('E_XOONIPS_USER_NOT_FOUND', 'E0003');
define('E_XOONIPS_DB_QUERY', 'E0004');
define('E_XOONIPS_ATTR_REDUNDANT', 'E0005'); //redundant attributes
define('E_XOONIPS_ATTR_NOT_FOUND', 'E0006'); //required attributes is not declared
define('E_XOONIPS_ATTR_INVALID_VALUE', 'E0007'); //attribute value is invalid
define('E_XOONIPS_DATA_TOO_LONG', 'E0008');
define('E_XOONIPS_INDEX_NOT_FOUND', 'E0009');
define('E_XOONIPS_OPEN_FILE', 'E0010');
define('E_XOONIPS_RELATED_ITEM_IS_NOT_FOUND', 'E0011'); //related item is not found
define('E_XOONIPS_FILE_SYSTEM', 'E0012'); //error in file system
define('E_XOONIPS_IMPORT', 'E0013'); //error in importing
define('E_XOONIPS_INVALID_VALUE', 'E0014'); //CDATA is in valid(invalid format, unknown values ...)
define('E_XOONIPS_VALUE_IS_REQUIRED', 'E0015'); //required value is not given
define('E_XOONIPS_NO_PRIVATE_INDEX', 'E0016'); //error if importing item is not registered any private indexes.
define('E_XOONIPS_ATTACHMENT_HAS_REDUNDANT', 'E0017'); //more than two attachments are given for an item that requires only one attachment.
define('E_XOONIPS_NOT_PERMITTED_ACCESS', 'E0018'); //not permitted access for index, item, ...
define('E_XOONIPS_TAG_NOT_FOUND', 'E0019'); // required tag is omitted
define('E_XOONIPS_INVALID_VERSION', 'E0020'); // unsupported version, format of version is invalid , ...
define('E_XOONIPS_PSEUDO_ID_CONFLICT', 'E0021'); // pseudo_id conflicts
define('E_XOONIPS_RELATED_TO_CONFLICTING_ITEM', 'E0022'); // an item relates to conflicting item
define('E_XOONIPS_TAG_REDUNDANT', 'E0023'); //redundant element tags
define('E_XOONIPS_DOI_CONFLICT', 'E0024'); // doi conflict other items
define('E_XOONIPS_UPDATE_CERTIFY_REQUEST_LOCKED', 'E0025'); // error if updating locked(certify request lock) item
