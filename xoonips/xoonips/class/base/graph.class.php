<?php
// $Revision: 1.1.4.1.2.6 $
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

if ( ! defined( 'XOOPS_ROOT_PATH' ) ) exit();

if ( ! class_exists( 'XooNIpsGraphLib' ) ) {

include dirname(__FILE__).'/graphlib.class.php';

/**
 * basic data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphData
{
  /**
   * data values
   * @var array
   * @access protected
   */
  var $values = array();

  /**
   * data color
   * @var string
   * @access protected
   */
  var $color = 'black';

  /**
   * legend string
   * @var string
   * @access protected
   */
  var $legend = '';

  /**
   * shadow color
   * @var string
   * @access protected
   */
  var $shadow_color = 'grayCC';

  /**
   * drawing shadow offset
   * @var int
   * @access protected
   */
  var $shadow_offset = 3;

  /**
   * related y axis
   * @var string
   * @access protected
   */
  var $y_axis = 'left';

  /**
   * data type
   * @var string
   * @access protected
   */
  var $data_type = '';

  /**
   * maximum value of data array, it used for caching
   * @var float
   * @access protected
   */
  var $cache_max = null;

  /**
   * minimum value of data array, it used for caching
   * @var float
   * @access protected
   */
  var $cache_min = null;

  /**
   * constructor
   *
   * normally, this is called from child classes only
   * @access public
   */
  function XooNIpsGraphData()
  {
    // nothing to do
  }

  /**
   * set data color
   *
   * @access public
   * @parem string $color data color
   */
  function setColor( $color )
  {
    $this->color = $color;
  }

  /**
   * set legend string
   *
   * @access public
   * @parem string $legend legend string
   */
  function setLegend( $legend )
  {
    $this->legend = $legend;
  }

  /**
   * set shadow color
   *
   * @access public
   * @parem string $color shadow color, 'none' means don't draw.
   */
  function setShadowColor( $color )
  {
    $this->shadow_color = $color;
  }

  /**
   * set drawing shadow offset
   *
   * @access public
   * @parem int $offset drawing shadow offset
   */
  function setShadowOffset( $offset )
  {
    $this->shadow_offset = $offset;
  }

  /**
   * set related y axis
   *
   * @access public
   * @param string $axis related y axis. choose from following variables,
   *   'left','right'
   */
  function setYAxis( $axis )
  {
    $axis_types = array( 'left', 'right' );
    if ( ! in_array( $axis, $axis_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->y_axis = $axis;
  }

  /**
   * calculate maxinum/minimum value in data array
   *
   * @access private
   */
  function _calcRange() {
    $cnt = count( $this->values );
    if ( $cnt == 0 ) return;
    $this->cache_max = $this->cache_min = $this->values[0];
    foreach( $this->values  as $val ) {
      if( $val > $this->cache_max )
        $this->cache_max = $val;
      if( $val < $this->cache_min )
        $this->cache_min = $val;
    }
  }

  /**
   * get maximum value in data array
   *
   * @access public
   * @return float maximum value
   */
  function getMax() {
    if ( is_null( $this->cache_max ) ) {
      $this->_calcRange();
    }
    return $this->cache_max; 
  }

  /**
   * get minimum value in data array
   *
   * @access public
   * @return float minimum value
   */
  function getMin() {
    if ( is_null( $this->cache_min ) ) {
      $this->_calcRange();
    }
    return $this->cache_min; 
  }
}

/**
 * point data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataPoint extends XooNIpsGraphData
{
  /**
   * point shape
   * @var string
   * @access protected
   */
  var $point = 'circle';

  /**
   * point size
   * @var int
   * @access protected
   */
  var $point_size = 4;

  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataPoint( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'none';
  }

  /**
   * set point shape
   *
   * @param string $type point shape, choose from following shapes,
   *   'square', 'square-open', 'circle', 'circle-open', 
   *   'diamond', 'diamond-open', 'triangle', 'triangle-open',
   *   'dot', 'none'. 'none' means don't draw point.
   * @access public
   */
  function setPoint( $type )
  {
    $point_types = array( 'square', 'square-open', 'circle', 'circle-open', 'diamond', 'diamond-open', 'triangle', 'triangle-open', 'dot', 'none' );
    if ( ! in_array( $type, $point_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->point = $type;
  }

  /**
   * set point size
   *
   * @param int $size point size
   * @access public
   */
  function setPointSize( $size )
  {
    $this->point_size = $size;
  }
}

/**
 * line data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataLine extends XooNIpsGraphDataPoint
{
  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataLine( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'line';
    $this->setPoint( 'none' );
  }
}

/**
 * dash line data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataDashLine extends XooNIpsGraphDataLine
{
  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataDashLine( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'dash';
    $this->setPoint( 'none' );
  }
}

/**
 * brush line data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataBrushLine extends XooNIpsGraphDataLine
{
  /**
   * brush shape
   * @var string
   * @access protected
   */
  var $brush = 'circle';

  /**
   * brush size
   * @var int
   * @access protected
   */
  var $brush_size = 4;

  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataBrushLine( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'brush';
    $this->setPoint( 'none' );
  }

  /**
   * set brush shape
   *
   * @param string $type brush shape, choose from following shapes,
   *   'circle', 'square', 'vertical', 'horizontal', 'slash', 
   *   'backslash', 'none'. 'none' means don't draw point.
   * @access public
   */
  function setBrush( $type )
  {
    $brush_types = array( 'circle', 'square', 'vertical', 'horizontal', 'slash', 'backslash', 'none' );
    if ( ! in_array( $type, $brush_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->brush = $type;
  }

  /**
   * set brush size
   *
   * @param int $size brush size
   */
  function setBrushSize( $size )
  {
    $this->brush_size = $size;
  }
}

/**
 * bar data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataBar extends XooNIpsGraphData
{
  /**
   * bar type
   * @var string
   */
  var $bar = 'fill';

  /**
   * bar width
   * @var float
   * @access protected
   */
  var $bar_size = 0.8;

  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataBar( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'bar';
  }

  /**
   * set bar type
   *
   * @param string $type bar type, choose from following types,
   *   'fill', 'open'.
   * @access protected
   */
  function setBar( $type )
  {
    $bar_types = array( 'fill', 'open' );
    if ( ! in_array( $type, $bar_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->bar = $type;
  }

  /**
   * set bar size
   *
   * @param float bar size. 
   *   <1 bars won't touch.
   *    1 is full width.
   *   >1 means bars will overlap.
   * @access protected
   */
  function setBarSize( $size )
  {
    $this->bar_size = $size;
  }
}

/**
 * area data class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphDataArea extends XooNIpsGraphData
{
  /**
   * area type
   * @var string
   * @access protected
   */
  var $area = 'fill';

  /**
   * constructor
   *
   * @param array $values data array
   * @access public
   */
  function XooNIpsGraphDataArea( $values )
  {
    parent::XooNIpsGraphData();
    $this->values =& $values;
    $this->data_type = 'area';
  }

  /**
   * set area type
   *
   * @param string $type area type. choose from following types,
   *   'fill', 'open'.
   * @access public
   */
  function setArea( $type )
  {
    $area_types = array( 'fill', 'open' );
    if ( ! in_array( $type, $area_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->area = $type;
  }
}

/**
 * axis class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphAxis
{
  /**
   * axis label
   * @var string
   * @access protected
   */
  var $label = '';

  /**
   * maximum value of axis
   * @var float
   * @access protected
   */
  var $max_value = 0;

  /**
   * minimum value of axis
   * @var float
   * @access protected
   */
  var $min_value = 0;

  /**
   * number of decimal places for axis text
   * @var int
   * @access protected
   */
  var $decimal = 0;

  /**
   * scaling for rounding of axis maximum value.
   * this variable will used in following scaling algorithm.
   *   $max == 0: $factor = 1;
   *   $max < 0 : $factor = - pow(10,(floor(log10(abs($max)))+$resolution));
   *   $max > 0 : $factor = pow(10,(floor(log10(abs($max))-$resolution));
   *   $max = $factor * @ceil($max/$factor);
   *   $min = $factor * @floor($min/$factor);
   * @var int
   * @access protected
   */
  var $resolution = 3;

  /**
   * ticks interval of axis text
   * @var int
   * @access protected
   */
  var $tick_interval = 1;

  /**
   * constructor
   *
   * @access public
   */
  function XooNIpsGraphAxis()
  {
    // nothing to do
  }

  /**
   * set label string
   *
   * @param string label
   * @access public
   */
  function setLabel( $label )
  {
    $this->label = $label;
  }

  /**
   * set maximum value of axis
   *
   * @param float $max_value maximum value
   * @access public
   */
  function setMax( $max_value )
  {
    $this->max_value = $max_value;
  }

  /**
   * set minimum value of axis
   *
   * @param float $min_value minimum value
   * @access public
   */
  function setMin( $min_value )
  {
    $this->min_value = $min_value;
  }

  /**
   * set number of decimal places for axis text.
   *
   * @param int $decimal number of decimal places
   * @access public
   */
  function setDecimal( $decimal )
  {
    $this->decimal = $decimal;
  }

  /**
   * set scaling for rounding of axis max value.
   *
   * @param int $resolution resolution
   * @access public
   */
  function setResolution( $resolution )
  {
    $this->resolution = $resolution;
  }

  /**
   * set ticks interval
   *
   * @var int
   * @access protected
   */
  function setTickInterval( $interval )
  {
    $this->tick_interval = $interval;
  }
}

/**
 * inner frame class for graph drawing
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraphFrame
{
  /**
   * padding size of inner frame
   * @var int
   * @access private
   */
  var $padding = 6;

  /**
   * border line color
   * @var string
   * @access private
   */
  var $color = 'black';

  /**
   * background color of inner frame
   * @var string
   * @access private
   */
  var $background = 'none';

  /**
   * inner frame border type
   * @var string
   * @access private
   */
  var $type = 'box';

  /**
   * constructor
   *
   * @access public
   */
  function XooNIpsGraphFrame()
  {
    // nothing to do
  }

  /**
   * set padding size
   *
   * @param int $size padding size
   * @access public
   */
  function setPadding( $size )
  {
    $this->padding = $size;
  }

  /**
   * set border color
   *
   * @param string $color border color
   * @access public
   */
  function setColor( $color )
  {
    $this->color = $color;
  }

  /**
   * set background color
   *
   * @param string $color background color
   * @access public
   */
  function setBackgroundColor( $color )
  {
    $this->background = $color;
  }

  /**
   * set inner frame border type
   *
   * @param string $type inner frame type, choose from following types,
   *  'box'     : all four size, 
   *  'axis'    : for x/y axis only,
   *  'y'       : y axis only,
   *  'y-left'  : left y axis only,
   *  'y-right' : right y axis only,
   *  'x'       : x axis only,
   *  'u'       : both left and right y axis and x axis.
   * @access public
   */
  function setType( $type )
  {
    $frame_types = array( 'box', 'axis', 'y', 'y-left', 'y-right', 'x', 'u' );
    if ( ! in_array( $type, $frame_types ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->type = $type;
  }
}

/**
 * graph class
 *
 * @package xoonips_base
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsGraph
{
  /**
   * font file name
   * @var string
   * @access private
   */
  var $font_name = 'default.ttf';

  /**
   * module path for font file detection
   * @var string
   * @access private
   */
  var $module_path;

  /**
   * graph library - instance class XooNIpsGraphLib
   * @var object class
   * @access private
   */
  var $graph;

  /**
   * data array - instance of class XooNIpsGraphData
   * @var array
   * @access private
   */
  var $data = array();

  /**
   * graph axis array - instance of class XooNIpsGraphAxis
   * @var array
   * @access public
   */
  var $axis = array();

  /**
   * inner frame - instance of class XooNIpsGraphFrame
   * @var object
   * @access public
   */
  var $frame;

  /**
   * constructor
   *
   * @param int $width width of graph image
   * @param int $height height of graph image
   * @access public
   */
  function XooNIpsGraph( $width, $height )
  {
    $this->module_path = dirname( dirname( dirname(__FILE__) ) );
    $this->graph = new XooNIpsGraphLib( $width, $height );
    foreach ( array( 'bottom', 'left', 'right' ) as $ax ) {
      $this->axis[$ax] = new XooNIpsGraphAxis();
    }
    $this->frame = new XooNIpsGraphFrame();
    // initialize
    $this->axis['right']->setTickInterval( 0 );
    $this->setAxisAngle( 'x', 0 );
    $this->setXAxisOffset( 0 );
    // font settings
    $langman =& xoonips_getutility( 'languagemanager' );
    $font_path = $langman->font_path( $this->font_name );
    $this->graph->parameter['path_to_fonts'] = str_replace( $this->font_name, '', $font_path );
    $font_fields = array( 'title_font', 'label_font', 'axis_font', 'legend_font' );
    foreach( $font_fields as $field ) {
      $this->graph->parameter[$field] = $this->font_name;
    }
  }

  /**
   * set graph title
   *
   * @param string $title graph title
   * @access public
   */
  function setTitle( $title )
  {
    $this->graph->parameter['title'] = $title;
  }

  /**
   * set graph title size
   *
   * @param int $size graph title size
   * @access public
   */
  function setTitleSize( $size )
  {
    $this->graph->parameter['title_size'] = $size;
  }

  /**
   * set graph title color
   *
   * @param string $color graph title color
   * @access public
   */
  function setTitleColor( $color )
  {
    $this->graph->parameter['title_colour'] = $color;
  }

  /**
   * set axis label size
   *
   * @param int $size axis label size
   * @access public
   */
  function setLabelSize( $size )
  {
    $this->graph->parameter['label_size'] = $size;
  }

  /**
   * set axis label color
   *
   * @param string $color axis label color
   * @access public
   */
  function setLabelColor( $color )
  {
    $this->graph->parameter['label_colour'] = $color;
  }

  /**
   * set x axis offset
   *
   * @param int $offset x axis tick offset from y axis as fraction 
   *   of tick spacing.
   * @access public
   */
  function setXAxisOffset( $offset )
  {
    $this->graph->parameter['x_offset'] = $offset;
  }

  /**
   * set legend position
   *
   * @param string $position legend position, choose from following potisions,
   *   'top-left', 'top-right', 'bottom-left', 'bottom-right', 
   *   'outside-top', 'outside-bottom', 'outside-left', 'outside-right',
   *   'none'. 'none' means don't draw legend.
   * @access public
   */
  function setLegendPosition( $position )
  {
    $positions = array( 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'outside-top', 'outside-bottom', 'outside-left', 'outside-right', 'none' );
    if ( ! in_array( $position, $positions ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->graph->parameter['legend'] = $position;
  }

  /**
   * set legend border color
   *
   * @param string $color legend border color
   * @access public
   */
  function setLegendBorderColor( $color )
  {
    $this->graph->parameter['legend_border'] = $color;
  }

  /**
   * set legend offset
   *
   * @param int $offset offset in pixels from graph or outside border
   * @access public
   */
  function setLegendOffset( $offset )
  {
    $this->graph->parameter['legend_offset'] = $offset;
  }

  /**
   * set axis label angle
   *
   * @param string $xy which axis position 'x' or 'y'.
   * @param int $angle axis label angle
   * @access public
   */
  function setAxisAngle( $xy, $angle )
  {
    $xys = array( 'x', 'y' );
    if ( ! in_array( $xy, $xys ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->graph->parameter[ $xy.'_axis_angle'] = $angle;
  }

  /**
   * set axis grid line type
   *
   * @param string $xy which axis position 'x' or 'y'.
   * @param string $grid grid line type, choose from following types,
   *   'line', 'dash', 'none'. 'nome' means don't draw grid line.
   * @access public
   */
  function setAxisGrid( $xy, $grid )
  {
    $xys = array( 'x', 'y' );
    $grids = array( 'line', 'dash', 'none' );
    if ( ! in_array( $xy, $xys ) || ! in_array( $grid, $grids ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->graph->parameter[ $xy.'_grid'] = $grid;
  }

  /**
   * set number of axis grid lines
   *
   * @param string $xy which axis position 'x' or 'y'.
   * @param int $lines number of axis grid lines
   * @access public
   */
  function setAxisGridLines( $xy, $lines )
  {
    $xys = array( 'x', 'y' );
    if ( ! in_array( $xy, $xys ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->graph->parameter[ $xy.'_axis_gridlines'] = $lines;
  }

  /**
   * set axis ticks color
   *
   * @param string $xy which axis position 'x' or 'y'.
   * @param string $color axis ticks color
   * @access public
   */
  function setAxisTicksColor( $xy, $color )
  {
    $xys = array( 'x', 'y' );
    if ( ! in_array( $xy, $xys ) ) {
      $this->error( __FILE__, __LINE__ );
    }
    $this->graph->parameter[ $xy.'_ticks_colour'] = $color;
  }

  /**
   * set axis ticks length
   *
   * @param string $xy which axis position 'x' or 'y'.
   * @param string $len axis ticks length
   * @access public
   */
  function setAxisTicksLength( $len )
  {
    $this->graph->parameter['tick_length'] = $len;
  }

  /**
   * set zero line color
   *
   * @param string $color zero line color
   * @access public
   */
  function setAxisZeroLineColor( $color )
  {
    $this->graph->parameter['zero_axis'] = $color;
  }

  /**
   * set bar spacing size
   *
   * @param int $size space in pixels between group of bars for each x value.
   * @access public
   */
  function setBarSpacing( $size )
  {
    $this->graph->parameter['bar_spacing'] = $size;
  }

  /**
   * add new color
   *
   * @param string $name color name
   * @param int $r color space of red part. 0-255
   * @param int $g color space of green part. 0-255
   * @param int $b color space of blue part. 0-255
   * @access public
   */
  function addColor( $name, $r, $g, $b )
  {
    $this->graph->colour[$name] = ImageColorAllocate( $this->graph->image, $r, $g, $b );
  }

  /**
   * set x data
   *
   * @param array $data x data, this is string or float array.
   * @access public
   */
  function setXData( &$data )
  {
    $this->graph->x_data =& $data;
  }

  /**
   * add y data
   *
   * @param array $data y data, this is object instance of 
   *   class XooNIpsGraphData.
   * @access public
   */
  function addYData( &$data )
  {
    $this->data[] =& $data;
  }

  /**
   * set prefered y axis min/max range
   *
   * @access public
   */
  function setPreferedYAxisRange() {
    if ( count( $this->data ) == 0 )
      return;
    $axis = array( 'left', 'right' );
    foreach ( $axis as $ax ) {
      $my_max[$ax] = $this->axis[$ax]->max_value;
      $my_min[$ax] = $this->axis[$ax]->min_value;
    }
    // get max/min values
    foreach ( $this->data as $key => $datum ) {
      $d_max = $datum->getMax();
      $d_min = $datum->getMin();
      if ( $my_max[$datum->y_axis] < $d_max ) {
        $my_max[$datum->y_axis] = $d_max;
      }
      if ( $my_min[$datum->y_axis] > $d_min ) {
        $my_min[$datum->y_axis] = $d_min;
      }
    }
    // adjust max/min value
    $gridlines = $this->graph->parameter['y_axis_gridlines'];
    foreach( $axis as $ax ) {
      if ( $my_min[$ax] == $my_max[$ax] ) {
        if ( $my_max[$ax] == 0 ) {
          $factor = 1;
        } else {
          $factor = pow( 10, log10( abs( $my_max[$ax] ) - 1 ) );
        }
        $my_max[$ax] += $factor * ( $gridlines - 1 );
      } else {
        $my_diff = $my_max[$ax] - $my_min[$ax];
        $ten_per = $my_diff / 10;
        $my_max[$ax] = $ten_per * 11;
        if ( $gridlines != 0 ) {
          if ( $this->graph->parameter['y_decimal_'.$ax] == 0 ) {
            $fix = ceil( $my_diff / ( $gridlines - 1 ) );
            $total = $fix * ( $gridlines - 1 );
            $my_min[$ax] = floor( $my_min[$ax] );
            $my_max[$ax] = $total + $my_min[$ax];
          }
        }
      }
    }
    foreach( $axis as $ax ) {
      $this->axis[$ax]->setMax( $my_max[$ax] );
      $this->axis[$ax]->setMin( $my_min[$ax] );
    }
  }

  /**
   * initialize drawing parameters for library class XooNIpsGraphLib
   *
   * @access private
   */
  function _initLibraryParams()
  {
    if ( count( $this->data ) == 0 ) $this->error( __FILE__, __LINE__ );
    if ( !isset( $this->graph->x_data ) ) $this->error( __FILE__, __LINE__ );

    // frame
    $this->graph->parameter['inner_padding'] = $this->frame->padding;
    $this->graph->parameter['inner_border'] = $this->frame->color;
    $this->graph->parameter['inner_background'] = $this->frame->background;
    $this->graph->parameter['inner_border_type'] = $this->frame->type;
    // axis
    $is_numeric_x = true;
    foreach( $this->graph->x_data as $x ) {
      if ( is_string( $x ) ) {
        $is_numeric_x = false;
        break;
      }
    }
    if ( $is_numeric_x ) {
      if ( $this->graph->parameter['x_axis_gridlines'] == 'auto' ) {
        $x_min = $this->axis['bottom']->min_value;
        $x_max = $this->axis['bottom']->max_value;
        $x_range = $this->graph->find_range( $this->graph->x_data, $x_min, $x_max, $this->axis['bottom']->resolution );
        $this->axis['bottom']->min_value = $x_range['min'];
        $this->axis['bottom']->max_value = $x_range['max'];
        $this->graph->parameter['x_axis_gridlines'] = count( $this->graph->x_data );
      }
    } else {
      $this->axis['bottom']->min_value = 0;
      $this->axis['bottom']->max_value = 0;
      $this->graph->parameter['x_axis_gridlines'] = 'auto';
    }
    $this->graph->parameter['x_label'] = $this->axis['bottom']->label;
    $this->graph->parameter['x_min'] = $this->axis['bottom']->min_value;
    $this->graph->parameter['x_max'] = $this->axis['bottom']->max_value;
    $this->graph->parameter['x_decimal'] = $this->axis['bottom']->decimal;
    $this->graph->parameter['x_resolution'] = $this->axis['bottom']->resolution;
    $this->graph->parameter['x_axis_text'] = $this->axis['bottom']->tick_interval;

    $this->graph->parameter['y_label_left'] = $this->axis['left']->label;
    $this->graph->parameter['y_min_left'] = $this->axis['left']->min_value;
    $this->graph->parameter['y_max_left'] = $this->axis['left']->max_value;
    $this->graph->parameter['y_decimal_left'] = $this->axis['left']->decimal;
    $this->graph->parameter['y_resolution_left'] = $this->axis['left']->resolution;
    $this->graph->parameter['y_axis_text_left'] = $this->axis['left']->tick_interval;
    $this->graph->parameter['y_label_right'] = $this->axis['right']->label;
    $this->graph->parameter['y_min_right'] = $this->axis['right']->min_value;
    $this->graph->parameter['y_max_right'] = $this->axis['right']->max_value;
    $this->graph->parameter['y_decimal_right'] = $this->axis['right']->decimal;
    $this->graph->parameter['y_resolution_right'] = $this->axis['right']->resolution;
    $this->graph->parameter['y_axis_text_right'] = $this->axis['right']->tick_interval;
    // values
    $this->graph->y_order = array();
    foreach ( $this->data as $key => $datum ) {
      $key = 'data:'.strval( $key );
      $this->graph->y_order[] = $key;
      $this->graph->y_data[$key] =& $datum->values;
      switch ( $datum->data_type ) {
      case 'point':
        $this->graph->y_format[$key]['line'] = 'none';
        $this->graph->y_format[$key]['point'] = $datum->point;
        $this->graph->y_format[$key]['point_size'] = $datum->point_size;
      case 'line':
        $this->graph->y_format[$key]['line'] = 'line';
        $this->graph->y_format[$key]['point'] = $datum->point;
        $this->graph->y_format[$key]['point_size'] = $datum->point_size;
        break;
      case 'dash':
        $this->graph->y_format[$key]['line'] = 'dash';
        $this->graph->y_format[$key]['point'] = $datum->point;
        $this->graph->y_format[$key]['point_size'] = $datum->point_size;
        break;
      case 'brush':
        $this->graph->y_format[$key]['line'] = 'brush';
        $this->graph->y_format[$key]['point'] = $datum->point;
        $this->graph->y_format[$key]['point_size'] = $datum->point_size;
        $this->graph->y_format[$key]['brush_type'] = $datum->brush;
        $this->graph->y_format[$key]['brush_size'] = $datum->brush_size;
        break;
      case 'bar':
        $this->graph->y_format[$key]['bar'] = $datum->bar;
        $this->graph->y_format[$key]['bar_size'] = $datum->bar_size;
        break;
      case 'area':
        $this->graph->y_format[$key]['area'] = $datum->area;
        break;
      }
      $this->graph->y_format[$key]['y_axis'] = $datum->y_axis;
      $this->graph->y_format[$key]['legend'] = $datum->legend;
      $this->graph->y_format[$key]['colour'] = $datum->color;
      $this->graph->y_format[$key]['shadow'] = $datum->shadow_color;
      $this->graph->y_format[$key]['shadow_offset'] = $datum->shadow_offset;
    }
  }

  /**
   * draw graph
   *
   * @access public
   */
  function draw()
  {
    $this->_initLibraryParams();
    $this->graph->draw();
  }

  /**
   * draw graph with stack bar mode
   *
   * @access public
   */
  function draw_stack()
  {
    $this->_initLibraryParams();
    $this->graph->draw_stack();
  }

  /**
   * fatal error
   *
   * @param string $file file name
   * @param string $line line number
   */
  function error( $file, $line )
  {
    die( 'Fatal Error in '.$file.' line '.$line );
  }
}

}

?>
