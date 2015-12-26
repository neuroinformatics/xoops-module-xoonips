/*
 * Javascript Library for the XooNIps item edit
 */

if ( typeof( XooNIpsItemEditJS ) == 'undefined' ) XooNIpsItemEditJS = function() {};

/**
 * form checker class
 * @package XooNIpsItemEditJS
 */

/** 
 * constructor
 *
 * @access public
 * @return object instance
 */
XooNIpsItemEditJS.formChecker = function() {
  this._style = new Array();
  this._style['error'] = new Array();
  this._style['error']['color'] = 'black';
  this._style['error']['background'] = '#FFFFCC';
  return this;
}

XooNIpsItemEditJS.formChecker.prototype = {

  /**
   * initilize style property
   *
   * @access public
   * @param string type style property type
   * @param string name style name
   * @param mixed value style value
   */
  initStyle: function( type, name, value ) {
    if ( type in this._style ) {
      this._style[type][name] = value;
    }
  },

  /**
   * apply style
   *
   * @access public
   * @param object el element
   * @param string type style property type, if null remove style
   */
  applyStyle: function( el, type ) {
    if ( type == null ) {
      el.style['color'] = '';
      el.style['background'] = '';
    } else if ( type in this._style ) {
      for ( var i in this._style[type] ) {
        el.style[i] = this._style[type][i];
      }
    }
  },

  /**
   * check filled data in <input type="text"> or <textarea> field
   *
   * @access public
   * @param object el element
   * @return boolean true if ok
   */
  isFilledInputText: function( el ) {
    var ret, val;
    ret = true;
    el.value = el.value.replace( /^\s+|\s+$/g, '' );
    if ( el.value == '' ) {
      ret = false;
    }
    if ( ret ) {
      this.applyStyle( el, null );
    } else {
      this.applyStyle( el, 'error' );
    }
    return ret;
  },

  /**
   * check decimal data in <input type="text"> field
   *
   * @access public
   * @param object el element
   * @param boolean is_required true if required field
   * @param int min minimum value, if empty not checking
   * @param int max maximum value, if empty not checking
   * @return boolean true if ok
   */
  isDecimalInputText: function( el, is_required, min, max ) {
    var ret, val;
    ret = true;
    el.value = el.value.replace( /^\s+|\s+$/g, '' );
    if ( el.value == '' ) {
      if ( is_required ) {
        ret = false;
      }
    } else {
      if ( ! el.value.match( /^\-?\d+$/ ) ) {
        ret = false;
      } else {
        val = parseInt( el.value );
        if ( ( min != null && min > val ) || ( max != null && max < val ) ) {
          ret = false;
        }
      }
    }
    if ( ret ) {
      this.applyStyle( el, null );
    } else {
      this.applyStyle( el, 'error' );
    }
    return ret;
  },

  /**
   * check extended id data in <input type="text"> field
   *
   * @access public
   * @param object el element
   * @param string name field param name
   * @param string maxlen maximum length
   * @param string pattern acceptable value pattern
   * @return boolean true if ok
   */
  isExtIdInputText: function( el, name, maxlen, pattern ) {
    var ret, val, reg;
    ret = true;
    el.value = el.value.replace( /^\s+|\s+$/g, '' );
    if ( name != '' && el.value != '' ) {
      reg = new RegExp( '^' + pattern + '$' );
      if ( el.value.length > maxlen || ! el.value.match( reg ) ) {
        ret = false;
      }
    }
    if ( ret ) {
      this.applyStyle( el, null );
    } else {
      this.applyStyle( el, 'error' );
    }
    return ret;
  }
}

/**
 * multiple field controller class
 * @package XooNIpsItemEditJS
 */

/** 
 * constructor
 *
 * @access public
 * @param string prefix
 * @param string name_text base name of input text
 * @param string name_id base name of id
 * @param string name_order base name of order
 * @return object instance
 */
XooNIpsItemEditJS.MultipleField = function( prefix, name_text, name_id, name_order ) {
  this.prefix = prefix;
  this.name_text = name_text;
  this.name_id = name_id;
  this.name_order = name_order;
  this.icon_delete = './images/icon_delete.png';
  this.icon_up = './images/icon_up.png';
  this.icon_down = './images/icon_down.png';
  this.icon_blank = './images/icon_blank.png';
  this.label_delete = 'DELETE';
  this.label_up = 'UP';
  this.label_down = 'DOWN';
  this.label_blank = '';
  this.prop_imemode = 'auto';
  return this;
}

XooNIpsItemEditJS.MultipleField.prototype = {

  /**
   * append field
   *
   * @access public
   * @return boolen false fi failure
   */
  appendField: function() {
    var obj = this;
    var el = this._getElement( this.prefix + '_container' );
    if ( el == null ) {
      return false;
    }
    var num = this.getNumOfFields();
    var is_first = ( num == 0 );
    // create new field
    var el_div =  document.createElement( 'DIV' );
    el_div.id = this.prefix + '_' + num;
    el_div.style.whiteSpace = 'nowrap';
    var el_text = document.createElement( 'INPUT' );
    el_text.type = 'text';
    el_text.size = 50;
    el_text.id = this.prefix + '_' + num + '_' + this.name_text;
    el_text.name = this.prefix + '[' + num + '][' + this.name_text + ']';
    el_text.value = '';
    el_text.style.imeMode = this.prop_imemode;
    el_div.appendChild( el_text );
    var el_id = document.createElement( 'INPUT' );
    el_id.type = 'hidden';
    el_id.id = this.prefix + '_' + num + '_' + this.name_id;
    el_id.name = this.prefix + '[' + num + '][' + this.name_id + ']';
    el_id.value = '0';
    el_div.appendChild( el_id );
    var el_order = document.createElement( 'INPUT' );
    el_order.type = 'hidden';
    el_order.id = this.prefix + '_' + num + '_' + this.name_order;
    el_order.name = this.prefix + '[' + num + '][' + this.name_order + ']';
    el_order.value = num.toString();
    el_div.appendChild( el_order );
    el_div.appendChild( document.createTextNode( '\n' ) );
    var el_del_a = document.createElement( 'A' );
    el_del_a.href='';
    el_del_a.onclick = function() { obj.deleteField( num ); return false; };
    var el_del_img = document.createElement( 'IMG' );
    el_del_img.src = this.icon_delete;
    el_del_img.title = this.label_delete;
    el_del_img.alt = this.label_delete;
    el_del_a.appendChild( el_del_img );
    el_div.appendChild( el_del_a );
    el.appendChild( el_div );
    el_div.appendChild( document.createTextNode( '\n' ) );
    var el_up_a = document.createElement( 'A' );
    el_up_a.href = '';
    el_up_a.onclick = function() { obj.upField( num ); return false; };
    var el_up_img = document.createElement( 'IMG' );
    el_up_img.id = this.prefix + '_' + num + '_image_up'; 
    if ( is_first ) {
      el_up_img.src = this.icon_blank;
      el_up_img.title = this.label_blank;
      el_up_img.alt = this.label_blank;
    } else {
      el_up_img.src = this.icon_up;
      el_up_img.title = this.label_up;
      el_up_img.alt = this.label_up;
    }
    el_up_a.appendChild( el_up_img );
    el_div.appendChild( el_up_a );
    el_div.appendChild( document.createTextNode( '\n' ) );
    var el_down_a = document.createElement( 'A' );
    el_down_a.href='';
    el_down_a.onclick = function() { obj.downField( num ); return false; };
    var el_down_img = document.createElement( 'IMG' );
    el_down_img.id = this.prefix + '_' + num + '_image_down'; 
    el_down_img.src = this.icon_blank;
    el_down_img.title = this.label_blank;
    el_down_img.alt = this.label_blank;
    el_down_a.appendChild( el_down_img );
    el_div.appendChild( el_down_a );
    this._setNumOfFields( num + 1 );
    // change down label and icon for upper field
    if ( num != 0 ) {
      var el_img = this._getElement( this.prefix + '_' + (num - 1) + '_image_down' );
      if ( el_img == null ) {
        return false;
      }
      el_img.src = this.icon_down;
      el_img.title = this.label_down;
      el_img.alt = this.label_down;
    }
    return true;
  },

  /**
   * set field value
   *
   * @access public
   * @param int num field number
   * @param string text field value
   * @return boolen false if failure
   */
  setField: function( num, text ) {
    var num_fields = this.getNumOfFields();
    if ( num < 0 ) {
      alert( 'out of range : ' + num );
      return false;
    }
    for ( var i = num_fields - 1; i < num; i++ ) {
      this.appendField();
    }
    var el = this._getElement( this.prefix + '_' + num + '_' + this.name_text );
    if ( el == null ) {
      return false;
    }
    el.value = text;
    return true;
  },

  /**
   * trim empty fields
   *
   * @access public
   * @return boolen false if failure
   */
  trimFields: function() {
    var num_fields = this.getNumOfFields();
    var el = null;
    for ( var i = 0; i < num_fields; i++ ) {
      el = this._getElement( this.prefix + '_' + i + '_' + this.name_text );
      if ( el == null ) {
        return false;
      }
      el.value = el.value.replace( /^\s+|\s+$/g, '' );
      if ( el.value == '' ) {
        this.deleteField( i );
        num_fields--;
        i--;
      }
    }
    return true;
  },

  /**
   * delete current field
   *
   * @access public
   * @param int num field number
   * @return boolen false if failure
   */
  deleteField: function( num ) {
    var num_fields = this.getNumOfFields();
    if ( num < 0 || num >= num_fields ) {
      alert( 'out of range : ' + num );
      return false;
    }
    // swap value with down field
    var last_num = num_fields - 1;
    for ( var i = num; i < last_num; i++ ) {
      this.downField( i );
    }
    var el = this._getElement( this.prefix + '_' + last_num );
    if ( el == null ) {
      return false;
    }
    el.parentNode.removeChild( el );
    this._setNumOfFields( last_num );
    // change blank label and icon for last field
    if ( last_num != 0 ) {
      var el_img = this._getElement( this.prefix + '_' + ( last_num - 1 ) + '_image_down' );
      if ( el_img == null ) {
        return false;
      }
      el_img.src = this.icon_blank;
      el_img.title = this.label_blank;
      el_img.alt = this.label_blank;
    }
    return true;
  },

  /**
   * delete all field
   *
   * @access public
   * @return boolen false if failure
   */
  deleteAllFields: function() {
    var num_fields = this.getNumOfFields();
    for ( var i = num_fields - 1; i >= 0; i-- ) {
      if ( ! this.deleteField( i ) ) {
        return false;
      }
    }
    return true;
  },

  /**
   * swap current value to upper field value
   *
   * @access public
   * @param int num field number
   * @return boolen false if failure
   */
  upField: function( num ) {
    if ( num <= 0 ) {
      return false;
    }
    return this._swapValues( num - 1, num );
  },

  /**
   * swap current value to lower field value
   *
   * @access public
   * @param int num field number
   * @return boolean false if failure
   */
  downField: function( num ) {
    var num_fields = this.getNumOfFields();
    if ( num < 0 || num_fields - 1 <= num ) {
      return false;
    }
    return this._swapValues( num, num + 1 );
  },

  /**
   * get number of fields
   *
   * @access public
   * @return int maximum number of fields
   */
  getNumOfFields: function() {
    var name = this.prefix + '_num';
    var el = this._getElement( name );
    if ( el == null ) {
      return 0;
    }
    if ( el.value == '' ) {
      alert( 'empty value found : ' + name );
      return 0;
    }
    return parseInt( el.value, 10 );
  },

  /**
   * set number of fields
   *
   * @access private
   * @param int num
   * @return boolean false if failed
   */
  _setNumOfFields: function( num ) {
    var el = this._getElement( this.prefix + '_num' );
    if ( el == null ) {
      return false;
    }
    el.value = num;
    return true;
  },

  /**
   * swap field values
   *
   * @access private
   * @param int num1 field number1
   * @param int num2 field number2
   * @return boolean false if failure
   */
  _swapValues: function( num1, num2 ) {
    var el_name1 = this._getElement( this.prefix + '_' + num1 + '_' + this.name_text );
    var el_id1 = this._getElement( this.prefix + '_' + num1 + '_' + this.name_id );
    var el_name2 = this._getElement( this.prefix + '_' + num2 + '_' + this.name_text );
    var el_id2 = this._getElement( this.prefix + '_' + num2 + '_' + this.name_id );
    if ( el_name1 == null || el_id1 == null || el_name2 == null || el_id2 == null ) {
      return false;
    }
    var tmp = el_name1.value;
    el_name1.value = el_name2.value;
    el_name2.value = tmp;
    tmp = el_id1.value;
    el_id1.value = el_id2.value;
    el_id2.value = tmp;
    return true;
  },

  /**
   * get element by id
   *
   * @access private
   * @param string id name
   * @return object
   */
  _getElement: function( id ) {
    var el = document.getElementById( id );
    if ( el == null ) {
      alert( 'element not found : ' + id );
    }
    return el;
  }
}
