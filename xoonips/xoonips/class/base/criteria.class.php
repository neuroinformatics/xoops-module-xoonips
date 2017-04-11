<?php

// $Revision: 1.1.4.2 $
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

/**
 * multiple cascadable Join Criteria Class.
 *
 * this class is based on 'XoopsJoinCriteria' by
 *
 *   @copyright copyright (c) 2004-2006 Kowa.ORG
 *   @author Nobuki Kowa <Nobuki@Kowa.ORG>
 */
class XooNIpsJoinCriteria
{
    /**
   * sub table name.
   *
   * @var string
   */
  public $_subtable_name;

  /**
   * field name of main table.
   *
   * @var string
   */
  public $_main_field;

  /**
   * field name of sub table.
   *
   * @var string
   */
  public $_sub_field;

  /**
   * join type.
   *
   * @var string
   */
  public $_join_type;

  /**
   * array of cascading next join criteria.
   *
   * @var array
   */
  public $_next_join;

  /**
   * assigned alias name of sub table.
   *
   * @var string
   */
  public $_subtable_alias;

  /**
   * constructor.
   *
   * @param string $subtable_name  joining sub table name
   * @param string $main_field     field name of main table
   * @param string $sub_field      field name of sub table
   * @param string $join_type      join type
   * @param bool   $subtable_alias assign alias name for joining sub table
   */
  public function XooNIpsJoinCriteria($subtable_name, $main_field, $sub_field, $join_type = 'LEFT', $subtable_alias = false)
  {
      $this->_subtable_name = $subtable_name;
      $this->_main_field = $main_field;
      $this->_sub_field = $sub_field;
      $this->_join_type = $join_type;
      $this->_next_join = array();
      $this->_subtable_alias = $subtable_alias;
  }

  /**
   * append next JOIN criteria.
   *
   * @param object &$join_criteria object instance of next join criteria
   * @param string joining main table name, false if use default main table
   * @param bool $next_maintable_is_alias flag for next main table name is alias
   *
   * @return string
   **/
  public function cascade(&$join_criteria, $next_maintable_name = false, $next_maintable_is_alias = false)
  {
      $this->_next_join[] = array(
      'join_criteria' => &$join_criteria,
      'main_table' => $next_maintable_name,
      'is_alias' => $next_maintable_is_alias,
    );
  }

  /**
   * Make a sql JOIN clause.
   *
   * @param object &$db                xoops database object instance
   * @param string $maintable_name     main table name
   * @param bool   $maintable_is_alias flag for main table name is alias
   *
   * @return string
   **/
  public function render(&$db, $maintable_name, $maintable_is_alias)
  {
      if ($maintable_is_alias) {
          $my_maintable_name = $maintable_name;
      } else {
          $my_maintable_name = $db->prefix($maintable_name);
      }
      $my_subtable_name = $db->prefix($this->_subtable_name);
      if ($this->_subtable_alias) {
          $my_sub_alias_def = 'AS '.$this->_subtable_alias;
          $my_sub_alias = $this->_subtable_alias;
      } else {
          $my_sub_alias_def = '';
          $my_sub_alias = $my_subtable_name;
      }
      $join_str = sprintf(' %s JOIN `%s` %s ON `%s`.`%s`=`%s`.`%s` ', $this->_join_type, $my_subtable_name, $my_sub_alias_def, $my_maintable_name, $this->_main_field, $my_sub_alias, $this->_sub_field);
      foreach ($this->_next_join as $next_join) {
          $next_join_criteria = &$next_join['join_criteria'];
          $next_maintable_name = $next_join['main_table'];
          $next_maintable_is_alias = $next_join['is_alias'];
          if ($next_maintable_name) {
              $join_str .= $next_join_criteria->render($db, $next_maintable_name, $next_maintable_is_alias);
          } else {
              $join_str .= $next_join_criteria->render($db, $maintable_name, $maintable_is_alias);
          }
      }

      return $join_str;
  }
}

/**
 * a criteria class for full text search.
 */
class XooNIpsFulltextCriteria extends CriteriaElement
{
    /**
   * table prefix(es).
   *
   * @var string
   */
  public $_prefix;

  /**
   * search column(s).
   *
   * @var mixed
   */
  public $_column;

  /**
   * expr part of SQL : MATCH (...) AGAINST ( 'expr' ).
   *
   * @var string
   */
  public $_expr;

  /**
   * flag for 'IN BOOLEAN MODE'.
   *
   * @var bool
   */
  public $_in_boolean_mode = false;

  /**
   * flag for 'WITH QUERY EXPANSION'.
   *
   * @var bool
   */
  public $_with_query_expansion = false;

  /**
   * constructor.
   *
   * @param mixed  $column          full-text search column(s)
   * @param string $expr            full-text search expression
   * @param bool   $in_boolean_mode flag for 'IN BOOLEAN MODE'
   * @param mixed  $prefix          table prefix(es)
   **/
  public function XooNIpsFulltextCriteria($column, $expr, $in_boolean_mode, $prefix = '')
  {
      $this->_column = $column;
      $this->_expr = $expr;
      $this->_in_boolean_mode = (bool) $in_boolean_mode;
      $this->_prefix = $prefix;
  }

  /**
   * set optional flag for 'IN BOOLEAN MODE'.
   *
   * @param bool $flag flag for 'IN BOOLEAN MODE'
   */
  public function setInBooleanMode($flag)
  {
      if ($flag && $this->_with_query_expansion) {
          $this->_with_query_expansion = false;
      }
      $this->_in_boolean_mode = (bool) $flag;
  }

  /**
   * set optional flag for 'WITH QUERY EXPANSION'.
   *
   * @param bool $flag flag for 'WITH QUERY EXPANSION'
   */
  public function setWithQueryExpansion($flag)
  {
      if ($flag && $this->_in_boolean_mode) {
          $this->_in_boolean_mode = false;
      }
      $this->_with_query_expansion = (bool) $flag;
  }

  /**
   * Make a full-text search sql condition string.
   *
   * @return string
   **/
  public function render()
  {
      $columns = is_array($this->_column) ? $this->_column : array($this->_column);
      $clauses = array();
      foreach ($columns as $key => $column) {
          $prefix = is_array($this->_prefix) ? $this->_prefix[$key] : $this->_prefix;
          $clauses[] = empty($prefix) ? sprintf('`%s`', $column) : sprintf('`%s`.`%s`', $prefix, $column);
      }
      $clause = implode(',', $clauses);
      $expr = trim($this->_expr);
      $flag = $this->_in_boolean_mode ? 'IN BOOLEAN MODE ' : ($this->_with_query_expansion ? 'WITH QUERY EXPANSION ' : '');

      return sprintf('MATCH ( %s ) AGAINST ( \'%s\' %s)', $clause, $expr, $flag);
  }

  /**
   * Generate an LDAP filter from criteria.
   *
   * @return string
   *
   * @deprecated
   */
  public function renderLdap()
  {
      error_log('XooNIpsFulltextCriteria::renderLdap() is not supported');
      die();
  }

  /**
   * Make the criteria into a a SQL "WHERE" clause.
   *
   * @return string
   */
  public function renderWhere()
  {
      $ret = $this->render();

      return($ret != '') ? 'WHERE '.$ret : $ret;
  }
}
