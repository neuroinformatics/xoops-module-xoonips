<?php

// $Revision: 1.1.2.7 $
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

require_once dirname(__DIR__).'/base/logic.class.php';
require_once __DIR__.'/transfer.class.php';

class XooNIpsLogicTransferUserReject extends XooNIpsLogicTransfer
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * user rejected transfer request.
     *
     * @param[in]  $vars[0] array of item_id
     * @param[out] XooNIpsError error
     *
     * @return bool true if succeeded
     */
    public function execute_without_transaction(&$vars, &$error)
    {
        $item_ids = $vars[0];
        if (!is_array($item_ids)) {
            return true;
        }
        foreach ($item_ids as $item_id) {
            $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
            if (!$eventlog_handler->recordRejectTransferItemEvent($item_id)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');

                return false;
            }

            if (false == $this->remove_item_from_transfer_request($error, $item_id)) {
                return false;
            }

            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            if (false == $item_lock_handler->unlock($item_id)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot unlock item');

                return false;
            }
        }

        return true;
    }
}
