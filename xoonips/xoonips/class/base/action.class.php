<?php

// $Revision: 1.1.2.9 $
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

require_once __DIR__.'/viewfactory.class.php';
require_once __DIR__.'/logicfactory.class.php';
require_once dirname(__DIR__).'/xoonipsresponse.class.php';

class XooNIpsAction
{
    public $_params = null;
    public $_response = null;
    public $_error = null;
    public $_view_params = null;
    public $_formdata = null;

    public function __construct()
    {
        $this->_params = array();
        $this->_response = new XooNIpsResponse();
        $this->_error = &$this->_response->getError();
        $this->_view_params = array();
        $this->_formdata = &xoonips_getutility('formdata');
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return null;
    }

    public function action()
    {
        $this->preAction();
        $this->doAction();
        $this->postAction();
        $this->render();
    }

    public function preAction()
    {
    }

    public function doAction()
    {
        $factory = &XooNIpsLogicFactory::getInstance();
        $logic = &$factory->create($this->_get_logic_name());
        if (!is_object($logic)) {
            $this->_response->setResult(false);
            $this->_error->add(XNPERR_SERVER_ERROR, "can't create a logic:".$this->_get_logic_name());

            return;
        }
        $logic->execute($this->_params, $this->_response);
    }

    public function postAction()
    {
    }

    public function render()
    {
        if (is_null($this->_get_view_name())) {
            return;
        }

        $factory = &XooNIpsViewFactory::getInstance();
        $view = &$factory->create($this->_get_view_name(), $this->_view_params);
        if (!is_object($view)) {
            die("can't create view:".$this->_get_view_name());
        } else {
            $view->render();
        }
    }
}
