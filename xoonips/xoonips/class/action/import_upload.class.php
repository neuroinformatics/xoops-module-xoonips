<?php

// $Revision: 1.1.2.21 $
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

require_once dirname(__DIR__).'/base/action.class.php';
require_once dirname(__DIR__).'/base/logicfactory.class.php';
require_once dirname(__DIR__).'/base/gtickets.php';

class XooNIpsActionImportUpload extends XooNIpsAction
{
    public $_view_name = null;

    public function XooNIpsActionImportUpload()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return $this->_view_name;
    }

    public function preAction()
    {
        global $xoopsUser;
        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        $filetype = $this->_formdata->getValue('post', 'filetype', 's', false);
        xoonips_validate_request('localfile' == $filetype || 'remotefile' == $filetype && $xoopsUser && $xoopsUser->isAdmin());

        xoonips_validate_request($this->_is_importable_index_id($this->_get_xoonips_checked_index_ids($this->_formdata->getValue('post', 'xoonipsCheckedXID', 's', false))));
    }

    public function doAction()
    {
        global $xoopsUser;

        include_once dirname(dirname(__DIR__)).'/include/imexport.php';

        $filetype = $this->_formdata->getValue('post', 'filetype', 's', false);
        $remotefile = $this->_formdata->getValue('post', 'remotefile', 's', false);
        $zipfile = $this->_formdata->getFile('zipfile', false);
        if ($filetype == 'localfile' && (empty($zipfile['name']) || $zipfile['size'] == 0) || $filetype == 'remotefile' && empty($remotefile)) {
            redirect_header('import.php?action=default', 3, _MD_XOONIPS_IMPORT_FILE_NOT_SPECIFIED);
            exit();
        }

        //set path of import file
        if ($filetype == 'localfile') {
            $uploadfile = $this->_move_upload_file($zipfile['tmp_name']);
        } else {
            $uploadfile = $remotefile;
        }

        if (!file_exists($uploadfile)) {
            redirect_header('import.php?action=default', 3, _MD_XOONIPS_IMPORT_FILE_NOT_FOUND);
            exit();
        }

        if ($this->_is_index_xml_in_import_file($uploadfile)) {
            $this->_read_index_tree($uploadfile, $this->_formdata->getValue('post', 'error_check_only', 's', false), $this->_get_xoonips_checked_index_ids($this->_formdata->getValue('post', 'xoonipsCheckedXID', 's', false)));

            return;
        }

        $this->_params[] = $uploadfile;
        $this->_params[] = $this->_get_xoonips_checked_index_ids($this->_formdata->getValue('post', 'xoonipsCheckedXID', 's', false));
        $factory = &XooNIpsLogicFactory::getInstance();
        $logic = &$factory->create('importReadFile');
        $logic->execute($this->_params, $this->_response);

        @unlink($uploadfile);

        $success = &$this->_response->getSuccess();
        if (!$this->_response->getResult() || $this->_import_item_have_errors($success['import_items'])) {
            $this->_view_params['result'] = false;
            $success = $this->_response->getSuccess();
            $this->_view_params['import_items'] = $success['import_items'];
            $this->_view_params['uname'] = $xoopsUser->getVar('uname');
            $this->_view_params['filename'] = $filetype == 'localfile' ? $zipfile['name'] : $remotefile;
            $this->_view_params['errors'] = array();
            foreach ($success['import_items'] as $item) {
                foreach (array_unique($item->getErrorCodes()) as $code) {
                    $this->_view_params['errors'][] = array('code' => $code, 'extra' => $item->getPseudoId());
                }
            }
            $this->_view_name = 'import_log';

            return;
        }

        $handler = &xoonips_gethandler('xoonips', 'import_item');
        $handler->setCertifyAutoOption($success['import_items'], !is_null($this->_formdata->getValue('post', 'certify_auto', 's', false)));
        $collection = new XooNIpsImportItemCollection();

        //
        // check conflict below
        //
        $this->_params = array($success['import_items']);
        $factory = &XooNIpsLogicFactory::getInstance();
        $logic = &$factory->create('importCheckConflict');
        $logic->execute($this->_params, $this->_response);

        $success = &$this->_response->getSuccess();
        $this->_set_errors_to_import_items($success['import_items']);
        if (!is_null($this->_formdata->getValue('post', 'error_check_only', 's', false))
            || $this->_import_item_have_errors($success['import_items'])
            || !$this->_response->getResult()
        ) {
            $this->_view_params['result'] = $this->_response->getResult() && !$this->_import_item_have_errors($success['import_items']);
            $this->_view_params['uname'] = $xoopsUser->getVar('uname');
            $this->_view_params['filename'] = $filetype == 'localfile' ? $zipfile['name'] : $remotefile;
            $this->_view_params['import_items'] = $success['import_items'];
            $this->_view_params['errors'] = array();
            foreach ($success['import_items'] as $item) {
                foreach (array_unique($item->getErrorCodes()) as $code) {
                    $this->_view_params['errors'][] = array('code' => $code, 'extra' => $item->getPseudoId());
                }
            }
            $this->_view_name = 'import_log';

            return;
        } elseif ($success['is_conflict']) {
            $this->_view_params['import_items'] = $success['import_items'];
            $this->_view_name = 'import_conflict';
        } else {
            // importCheckImport logic
            $this->_params = array($success['import_items'],
                                      $xoopsUser->getVar('uid'), false, );
            $factory = &XooNIpsLogicFactory::getInstance();
            $logic = &$factory->create('importCheckImport');
            $logic->execute($this->_params, $this->_response);

            $success = &$this->_response->getSuccess();
            if ($success['private_item_number_limit_over']
                || $success['private_item_storage_limit_over']
            ) {
                if ($success['private_item_number_limit_over']) {
                    $collection->addError('Private item number limit exceeds.');
                } elseif ($success['private_item_storage_limit_over']) {
                    $collection->addError('Too large file of item to import.');
                }
                $this->_view_params['result'] = false;
                $this->_view_params['uname'] = $xoopsUser->getVar('uname');
                $this->_view_params['filename'] = $filetype == 'localfile' ? $zipfile['name'] : $remotefile;
                $this->_view_params['errors'] = array();
                $this->_view_params['import_items'] = $success['import_items'];
                foreach ($success['import_items'] as $item) {
                    foreach (array_unique($item->getErrorCodes()) as $code) {
                        $this->_view_params['errors'][] = array('code' => $code, 'extra' => $item->getPseudoId());
                    }
                }
                foreach ($collection->getErrors() as $err) {
                    $this->_view_params['errors'][] = array('extra' => $err);
                }

                $this->_view_name = 'import_log';
            } else {
                $this->_view_params['ticket_html'] = $GLOBALS['xoopsGTicket']->getTicketHtml(__LINE__, 600, 'import');
                $this->_view_name = 'import_confirm';
            }
        }

        foreach ($success['import_items'] as $i) {
            $collection->addItem($i);
        }
        $collection->setLoggingOption(!is_null($this->_formdata->getValue('post', 'logging', 's', false)));
        if ($filetype == 'localfile') {
            $collection->setImportFileName($zipfile['name']);
        } else {
            $collection->setImportFileName($remotefile);
        }

        $sess_handler = &xoonips_getormhandler('xoonips', 'session');
        $sess = &$sess_handler->get(session_id());
        $session = unserialize($sess->get('sess_data'));
        $session['xoonips_import_items'] = base64_encode(gzcompress(serialize($collection)));
        $sess->set('sess_data', serialize($session));
        $sess_handler->insert($sess);
    }

    /**
     * move uploaded file($src) to temporary file.
     *
     * @param $src string uploaded file path
     *
     * @return string temporary file path string
     */
    public function _move_upload_file($src)
    {
        $info = pathinfo($src);
        $result = tempnam($info['dirname'], 'XNP');
        unlink($result);

        if (!move_uploaded_file($src, $result)) {
            die("Possible file upload attack!\n");
        }

        return $result;
    }

    public function _get_xoonips_checked_index_ids($index_id_csv)
    {
        $result = array();
        foreach (explode(',', $index_id_csv) as $id) {
            if (is_numeric($id)) {
                $result[] = (int) $id;
            }
        }

        return $result;
    }

    /**
     * return true if least one private index is in $index_ids.
     *
     * @param array integer value of index id
     */
    public function _is_private_index($index_ids)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_ids as $id) {
            $index = $index_handler->get((int) $id);
            if (!$index) {
                continue;
            }
            if (OL_PRIVATE == $index->get('open_level')) {
                return true;
            }
        }

        return false;
    }

    /**
     * return true if import items has some errors.
     */
    public function _import_item_have_errors($items)
    {
        if ($this->_is_doi_conflict($items)) {
            return true;
        }

        foreach ($items as $item) {
            if (count($item->getErrors()) > 0) {
                return true;
            }
        }

        return false;
    }

    public function _set_errors_to_import_items(&$import_items)
    {
        foreach (array_keys($import_items) as $key) {
            if ($import_items[$key]->getDoiConflictFlag()) {
                $import_items[$key]->setErrors(E_XOONIPS_DOI_CONFLICT, 'doi conflict with following items in exitsing item.');
            }
        }
    }

    /**
     * @param $uploadfile string import file path
     * @pram $error_check_only string 'on' or else
     */
    public function _read_index_tree($uploadfile, $error_check_only, $import_index_ids = array())
    {
        global $xoopsDB, $xoopsConfig, $xoopsUser,$xoopsLogger, $xoopsUserIsAdmin;
        //
        // Import index tree if uploaded file has index.xml.
        // In this case, other files are ignored.
        //
        $unzip = &xoonips_getutility('unzip');
        if (!$unzip->open($uploadfile)) {
            redirect_header('import.php?action=default', 3, _MD_XOONIPS_IMPORT_FILE_NOT_FOUND);
            exit();
        }
        $fnames = $unzip->get_file_list();
        foreach ($fnames as $fname) {
            if (strtolower($fname) != 'index.xml') {
                continue;
            }

            //
            // start transaction
            //
            $xoopsDB->query('START TRANSACTION');

            //
            // check index tree structures
            // and show the structures
            //
            $xml = $unzip->get_data($fname);
            $indexes = array();
            xnpImportIndexCheck($xml, $indexes);

            // To construct tree structure from given indexes by $indexes
            $c2p = array(); //associative array (child ID -> parent ID)
            $p2c = array(); //associative array (parent ID -> array of child ID)
            $index_by_id = array(); // $index_by_id[ index_id ] => index array;
            foreach ($indexes as $i) {
                if (empty($i)) {
                    continue;
                }
                $c2p[$i['index_id']] = $i['parent_id'];
                if (!isset($p2c[$i['parent_id']])) {
                    $p2c[$i['parent_id']] = array();
                }
                $p2c[$i['parent_id']][] = $i['index_id'];
                $index_by_id[$i['index_id']] = $i;
            }

            $str_indexes = '';
            $error = false; //true if cyclic reference
            foreach ($index_by_id as $index_id => $index) {
                if ($index['index_id'] == $index['parent_id']) {
                    //
                    // cyclic reference detected( refers itself )
                    //
                    $error = true;
                    continue;
                }
                if (isset($p2c[$index['index_id']])) {
                    continue;
                }

                //index_id already visited(to detect cyclic reference)
                $visited = array($index['index_id']);

                // $index is not parent of all indexes
                $path = array();
                array_push($path, $index['titles'][0]);
                while (isset($index_by_id[$index['parent_id']])) {
                    $parent = $index_by_id[$index['parent_id']];
                    if (in_array($parent['index_id'], $visited)) {
                        // cyclic reference detecetd( $index refers
                        // already visited index )
                        //
                        $error = true;
                        break;
                    }
                    $visited[] = $parent['index_id'];
                    $unicode = &xoonips_getutility('unicode');
                    array_push($path, $unicode->decode_utf8($parent['titles'][0], xoonips_get_server_charset(), 'h'));
                    $index = $parent;
                }
                $str_indexes .= htmlspecialchars(implode('/', array_reverse($path)), ENT_QUOTES)
                    ."<br />\n";
            }

            $unzip->close();

            include XOOPS_ROOT_PATH.'/header.php';
            if ($error) {
                $submit = _MD_XOONIPS_ITEM_BACK_BUTTON_LABEL;
                $message = _MD_XOONIPS_IMPORT_CIRCULAR_INDEX;
                echo <<<EOT
                    <p>
                    $message <br />
                    </p>
                    <form id='form_submit_back' action='import.php?action=default'
                     method='post'>
                    <input class="formButton" id='submit_back' type='submit'
                    value='$submit'/>
                    </form>
EOT;
                unlink($uploadfile);
            } elseif ($error_check_only == 'on') {
                $submit = _MD_XOONIPS_ITEM_BACK_BUTTON_LABEL;
                $message = _MD_XOONIPS_IMPORT_ERROR_CKECK_DONE."<br />\n"
                    ._MD_XOONIPS_IMPORT_FOLLOWING_INDEX_TEST;
                echo <<<EOT
                    <p>
                    $message
                    </p>
                    <p>
                    $str_indexes
                    </p>
                    <form id='form_submit_back' action='import.php?action=default'
                    method='post'>
                    <input class="formButton" id='submit_back' type='submit' 
                    value='$submit'/>
                    </form>
EOT;
                unlink($uploadfile);
            } else {
                $_SESSION['xoonips_import_file_path'] = $uploadfile;
                $_SESSION['xoonips_import_index_ids'] = serialize($import_index_ids);

                $submit = _MD_XOONIPS_IMPORT_UPLOAD_SUBMIT;
                $message = _MD_XOONIPS_IMPORT_FOLLOWING_INDEX;
                $xoonipsCheckedXID = implode(',', $import_index_ids);
                echo <<<EOT
                    <p>
                    $message
                    </p>
                    <p>
                    $str_indexes
                    </p>
                    <form id='form_import_index'
                    action='import.php?action=import_index_tree'
                    method='post'>
                    <input class="formButton" id='submit_import_index'
                    type='submit' value='$submit'/>
                    </form>
EOT;
            }
            include XOOPS_ROOT_PATH.'/footer.php';

            exit();
        }
    }

    /**
     * is index import file ?
     *
     * @param string $zfile uploaded zip file
     *
     * @return bool true if index import file
     */
    public function _is_index_xml_in_import_file($zfile)
    {
        $unzip = &xoonips_getutility('unzip');
        if (!$unzip->open($zfile)) {
            return false;
        }
        $fnames = array_map('strtolower', $unzip->get_file_list());
        $res = in_array('index.xml', $fnames);
        $unzip->close();

        return $res;
    }

    /**
     * @param $import_items array of XooNIpsImportItem
     *
     * @return bool true(doi conflict) or false(no doi conflicts)
     */
    public function _is_doi_conflict($import_items)
    {
        foreach ($import_items as $i) {
            if (count($i->getErrors()) > 0 || $i->getDoiConflictFlag()) {
                return true;
            }
        }

        return false;
    }

    public function _is_importable_index_id($index_ids)
    {
        global $xoopsUser;
        if (!$xoopsUser) {
            return false;
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $session_handler = &xoonips_getormhandler('xoonips', 'session');

        $su_users = &$session_handler->getObjects(new Criteria('su_uid', $xoopsUser->getVar('uid')));

        foreach ($index_ids as $id) {
            $index = &$index_handler->get((int) $id);
            if (!$index) {
                return false;
            }

            if ($xoopsUser->isAdmin() && OL_PUBLIC == $index->get('open_level')) {
                continue;
            }
            if ($su_users && OL_PUBLIC == $index->get('open_level')) {
                continue;
            }

            if (OL_PRIVATE == $index->get('open_level') && $index->get('uid') == $xoopsUser->getVar('uid')) {
                continue;
            }

            return false;
        }

        return true;
    }
}
