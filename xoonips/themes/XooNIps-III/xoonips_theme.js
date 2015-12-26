<!-- 
/*
 * Javascript library for the XooNIps
 *       supported browsers : IE6, FireFox 1.5, Safari 2, Opera 9
 *
 * Copyright (C) 2006-2007 RIKEN Japan All rights reserved.
 */

if ( typeof( XooNIpsThemeJS ) == 'undefined' ) XooNIpsThemeJS = function() {};

/**
 * public functions
 *
 * @package XooNIpsThemeJS
 * @copyright copyright &copy; 2006-2007 RIKEN Japan
 * @author  Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */

/**
 * a trick for the class inheritance
 *
 * @access public
 * @param function child_class
 * @param function parent_class
 */
XooNIpsThemeJS.Inherit = function( child_class, parent_class ) {
  // copy parent methods
  for ( var prop in parent_class.prototype ) {
    child_class.prototype[prop] = parent_class.prototype[prop];
  }
  if ( typeof( parent_class.prototype.__super__ ) == 'function' ) {
    child_class.prototype.__super__ = function() {
      var this_super = this.__super__;
      this.__super__ = parent_class.prototype.__super__;
      var ret = parent_class.apply( this, arguments );
      this.__super__ = this_super;
      return ret;
    }
  } else {
    child_class.prototype.__super__ = function() {
      return parent_class.apply( this, arguments );
    }
  }
}

/**
 * The cookie controller class
 * @package XooNIpsThemeJS
 * @copyright copyright &copy; 2006-2007 RIKEN Japan
 * @author  Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */

/** 
 * constructor
 *
 * @access public
 * @param string domain cookie domain
 * @param string path cookie path
 * @param number expires how many days the cookie expiry
 *               0 : this session only
 *        positive : n days
 *        negative : never
 * @param boolean secure set cookie when secure
 * @return object instance
 */
XooNIpsThemeJS.Cookie = function( domain, path, expires, secure ) {
  // check cookie enabled
  this.cookie_enabled = ( navigator.cookieEnabled ) ? true : false;
  if ( typeof( navigator.cookieEnabled ) == 'undefined' && ! this.cookie_enabled ) {
    // if not ie4+ nor ns6+
    document.cookie = 'testcookie';
    this.cookie_enabled = ( document.cookie.indexOf( 'testcookie' ) != -1 ) ? true : false;
  }
  this.expires_clear = 'Fri, 31-Dec-1999 23:59:59 GMT';
  this.expires_never = 'Tue, 1-Jan-2030 00:00:00 GMT';
  this.cookie_domain = domain;
  this.cookie_path = path;
  this.cookie_expires = expires;
  this.cookie_secure = secure;
  return this;
}

XooNIpsThemeJS.Cookie.prototype = {
  /**
   * set value to cookie
   *
   * @access public
   * @param string key cookie key
   * @param string val cookie value
   * @return boolean false if failure
   */
  setCookie: function( key, val ) {
    if ( ! this.cookie_enabled ) {
      return false;
    }
    var cookie = key + '=' + escape( val ) + ';';
    if ( this.cookie_domain ) {
      cookie += ' domain=' + this.cookie_domain + ';';
    }
    if ( this.cookie_path ) {
      cookie += ' path=' + this.cookie_path + ';';
    }
    if ( this.cookie_expires > 0 ) {
      var today = new Date();
      var expire = new Date();
      expire.setTime( today.getTime() + 3600000 * 24 * this.cookie_expires );
      cookie += ' expires=' + expire.toGMTString() + ';';
    } else if ( this.cookie_expires < 0 ) {
      cookie += ' expires=' + this.expires_never + ';';
    }
    if ( this.cookie_secure ) {
      cookie += ' secure;';
    }
    document.cookie = cookie;
    return true;
  },
  
  /**
   * get value from cookie 
   *
   * @access public
   * @param string key cookie key
   * @return string value
   */
  getCookie: function( key ) {
    if ( ! this.cookie_enabled ) {
      return;
    }
    var cookie = document.cookie + ';';
    var pos = cookie.indexOf( key, 0 );
    if ( pos == -1 ) {
      return ''; // not found
    }
    cookie = cookie.substring( pos, cookie.length );
    var s_pos = cookie.indexOf( '=', 0 );
    var e_pos = cookie.indexOf( ';', s_pos );
    return unescape( cookie.substring( s_pos + 1, e_pos ) );
  },

  /**
   * delete variable from cookie
   *
   * @access public
   * @param string key cookie key
   * @return boolean false if failure
   */
  deleteCookie: function( key ) {
    if ( ! this.cookie_enabled ) {
      return false;
    }
    var cookie = key + '=;';
    if ( this.cookie_domain ) {
      cookie += ' domain=' + this.cookie_domain + ';';
    }
    if ( this.cookie_path ) {
      cookie += ' path=' + this.cookie_path + ';';
    }
    cookie += ' expires=' + this.expires_clear + ';';
    if ( this.cookie_secure ) {
      cookie += ' secure;';
    }
    document.cookie = cookie;
    return true;
  }
}


/**
 * The element controller class
 * @package XooNIpsThemeJS
 * @copyright copyright &copy; 2006-2007 RIKEN Japan
 * @author  Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */

/**
 * constructor
 *
 * @access public
 * @param string id
 * @return object instance
 */
XooNIpsThemeJS.Element = function( id ) {
  this.id = id;
  this.supported = ( document.getElementById );
  this.element = null;
  if ( this.supported && id ) {
    var elem = document.getElementById( id );
    if ( elem ) {
      this.element = elem;
    }
  }
  return this;
}

XooNIpsThemeJS.Element.prototype = {
  /**
   * check element exists
   *
   * @access public
   * @return boolean false if invalid
   */
  exists: function() {
    return ( this.element ) ? true: false;
  },

  /**
   * create new element
   *
   * @access public
   * @param string type element type
   * @param element parent parent element
   * @return boolean false if failure
   */
  createElement: function( type, parent ) {
    if ( this.element ) {
      return false;
    }
    this.element = document.createElement( type );
    this.element.setAttribute( 'id', this.id );
    this.setStyle( 'display', 'none' );
    parent.appendChild( this.element );
    return true;
  },

  /**
   * set inner html
   *
   * @access public
   * @param string html inner html
   * @return boolean false if failure
   */
  setInnerHTML: function( text ) {
    if ( ! this.element ) {
      return false;
    }
    this.element.innerHTML = text;
    return true;
  },

  /**
   * set css style
   *
   * @access public
   * @param string style
   * @param string value
   * @return boolean false if failure
   */
  setStyle: function( style, value ) {
    if ( ! this.element ) {
      return false;
    }
    var camelcase = this._toCamelCase( style );
    switch ( style ) {
    case 'opacity':
      if ( typeof( this.element.style.opacity ) != 'undefined' ) {
        // css3
        this.element.style.opacity = value;
      } else if ( typeof( this.element.style.filter ) != 'undefined' ) {
        // ie
        this.element.style.filter = 'alpha(opacity=' + value * 100 + ')';
      } else if ( typeof( this.element.style.MozOpacity ) != 'undefined' ) {
        // mozilla
        this.element.style.MozOpacity = value;
      } else {
        return false;
      }
      break;
    default:
      this.element.style[camelcase] = value;
      break;
    }
    return true;
  },

  /**
   * get css style
   *
   * @access public
   * @param string style
   * @return string value
   */
  getStyle: function( style ) {
    if ( ! this.element ) {
      return '';
    }
    var camelcase = this._toCamelCase( style );
    var value = this.element.style[camelcase];
    if ( ! value ) {
      if ( document.defaultView ) {
        value = document.defaultView.getComputedStyle( this.element, '' ).getPropertyValue(style);
      } else if ( this.element.currentStyle ) {
        value = this.element.currentStyle[camelcase];
      }
    }
    return value;
  },

  /**
   * get offset
   *
   * @access public
   * @param string direction
   * @return string value
   */
  getOffset: function( direction ) {
    if ( ! this.element ) {
      return '';
    }
    var value = '';
    switch ( direction ) {
    case 'width':
      value = this.element.offsetWidth;
      break;
    case 'height':
      value = this.element.offsetHeight;
      break;
    case 'left':
      value = this.element.offsetLeft;
      break;
    case 'top':
      value = this.element.offsetTop;
      break;
    case 'x':
      value = this.element.offsetX;
      break;
    case 'y':
      value = this.element.offsetY;
      break;
    }
    return this.element.offsetWidth;
  },

  /**
   *  convert a string to a camel cased string of itself
   *
   * @access private
   * @param string str input string
   * @return string converted string
   */
  _toCamelCase: function( str ) {
    var slist = str.split( '-' );
    if ( slist.length == 1 )
      return slist[0];
    var ret = ( str.indexOf('-') == 0 ) ? slist[0].charAt( 0 ).toUpperCase() + slist[0].substring( 1 ) : slist[0];
    for ( var i = 1, len = slist.length; i < len; i++ ) {
      var s = slist[i];
      ret += s.charAt(0).toUpperCase() + s.substring(1);
    }
    return ret;
  },

  /**
   * set event
   *
   * @access public
   * @param string event type
   * @param object object observer object
   * @param function func function
   * @return boolean false if failure
   */
  setEvent: function( type, obj, func ) {
    if ( ! this.element ) {
      return false;
    }
    var new_func = function() { return func.apply( obj, arguments ); }
    switch ( type ) {
    case 'onmouseup':
      this.element.onmouseup = new_func;
      break;
    case 'onmousedown':
      this.element.onmousedown = new_func;
      break;
    case 'onmousemove':
      this.element.onmousemove = new_func;
      break;
    case 'onmouseout':
      this.element.onmouseout = new_func;
      break;
    case 'oncontextmenu':
      this.element.oncontextmenu = new_func;
      break;
    case 'ondblclick':
      this.element.ondblclick = new_func;
      break;
    default:
      return false;
    }
    return true;
  }
}


/**
 * The layer controller class
 * @package XooNIpsThemeJS
 * @copyright copyright &copy; 2006-2007 RIKEN Japan
 * @author  Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */

/**
 * constructor
 *
 * @access public
 * @param string id
 * @return object instance
 */
XooNIpsThemeJS.Layer = function( id ) {
  this.__super__( id );
  return this;
}
XooNIpsThemeJS.Inherit( XooNIpsThemeJS.Layer, XooNIpsThemeJS.Element );

/**
 * create layper object
 *
 * @access public
 * @param number left left position
 * @param number top top position
 * @param number|string width 
 * @param number|string height
 * @return boolean false if failure
 */
XooNIpsThemeJS.Layer.prototype.createLayer = function( left, top, width, height ) {
  if ( ! this.supported ) {
    return false;
  }
  if ( ! this.createElement( 'div', document.body ) ) {
    return false;
  }
  this.setStyle( 'position', 'absolute' );
  if ( typeof( left ) == 'number' || typeof( left ) == 'string' ) {
    this.setStyle( 'left', left );
  }
  if ( typeof( top ) == 'number' || typeof( top ) == 'string' ) {
    this.setStyle( 'top', top );
  }
  if ( typeof( width ) == 'number' || typeof( width ) == 'string' ) {
    this.setStyle( 'width', width );
  }
  if ( typeof( height ) == 'number' || typeof( height ) == 'string' ) {
    this.setStyle( 'height', height );
  }
  return true;
}

/**
 * show layer
 *
 * @access public
 * @return boolean false if failure
 */
XooNIpsThemeJS.Layer.prototype.showLayer = function() {
  return this.setStyle( 'display', 'block' );
}

/**
 * show layer
 *
 * @access public
 * @return boolean false if failure
 */
XooNIpsThemeJS.Layer.prototype.hideLayer = function() {
  return this.setStyle( 'display', 'none' );
}


XooNIpsThemeJS.ResizableColumn = function( name, id_resizable, id_vbar, cookie, axis ) {
  this.enable_debug = false;
  this.size_x = null;
  this.padding_x = null;
  this.mouse_x = null;
  this.bOnDown = false;
  this.id_resizable = id_resizable;
  this.id_vbar = id_vbar;
  this.el_resizable = null;
  this.el_vbar = null;
  this.el_overlay = null;
  this.el_debug = null;
  this.cookie = cookie;
  this.cookie_key = 'XooNIpsThemeJS_ResizableColumn_' + name;
  this.name = name;
  this.axis = axis;
  // register initialize function
  this.addLoadEvent();
  return this;
}

XooNIpsThemeJS.ResizableColumn.prototype.initialize = function() {
  // check supported browser
  if ( ! document.getElementById || ! document.createElement ) {
    return;
  }
  this.el_resizable = new XooNIpsThemeJS.Element( this.id_resizable );
  this.el_vbar = new XooNIpsThemeJS.Element( this.id_vbar );
  if ( ! this.el_resizable.exists() || ! this.el_vbar.exists() ) {
    return;
  }
  // set draggable vertical bar object
  this.el_vbar.setStyle( 'cursor', 'move' );
  this.el_vbar.setEvent( 'onmousedown', this, this.onMouseDown );

  // create overlay object
  this.el_overlay = new XooNIpsThemeJS.Layer( 'XooNIpsThemeJS_ResizableColumn_' + this.name + '_overlay' );
  this.el_overlay.createLayer( '0px', '0px', '100%', null );
  this.el_overlay.setStyle( 'opacity', 0.5 );
  this.el_overlay.setStyle( 'background-color', '#333' );
  this.el_overlay.setStyle( 'z-index', 90 );
  this.el_overlay.setStyle( 'cursor', 'move' );
  this.el_overlay.setEvent( 'onmouseup', this, this.onMouseUp );
  this.el_overlay.setEvent( 'onmouseout', this, this.onMouseOut );
  this.el_overlay.setEvent( 'onmousemove', this, this.onMouseMove );
  if ( this.enable_debug ) {
    // create debug box
    this.el_debug = new XooNIpsThemeJS.Element( 'XooNIpsThemeJS_ColumnResizable_' + name + '_debug' );
    this.el_debug.createElement( 'div', this.el_overlay.element );
    this.el_debug.setStyle( 'border', '1px solid white' );
    this.el_debug.setStyle( 'color', 'white' );
    this.el_debug.setStyle( 'display', 'block' );
    this.el_debug.setInnerHTML( 'Debug' );
  }
  // set initial size
  var size_x = this.cookie.getCookie( this.cookie_key );
  this.padding_x = parseInt( this.el_resizable.getStyle( 'padding-left' ) ) + parseInt( this.el_resizable.getStyle( 'padding-right' ) );
  if ( size_x ) {
    this.el_resizable.setStyle( 'width', size_x + 'px' );
    this.size_x = this.el_resizable.getOffset( 'width' ) - this.padding_x;
  } else {
    this.size_x = this.el_resizable.getOffset( 'width' ) - this.padding_x;
  }
};

XooNIpsThemeJS.ResizableColumn.prototype.debug = function( text ) {
  if ( ! this.bOnDown ){
    return;
  }
  if ( this.enable_debug ) {
    this.el_debug.setInnerHTML( text );
  }
}

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
XooNIpsThemeJS.ResizableColumn.prototype.getPageSize = function() {
  var pageWidth, pageHeight;
  var windowWidth, windowHeight;
  var xScroll, yScroll;
  var arrayPageSize;
  if ( window.innerHeight && window.scrollMaxY ) {  
    xScroll = document.body.scrollWidth;
    yScroll = window.innerHeight + window.scrollMaxY;
  } else if (  document.body.scrollHeight > document.body.offsetHeight ) {
    // all but Explorer Mac
    xScroll = document.body.scrollWidth;
    yScroll = document.body.scrollHeight;
  } else {
    // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
    xScroll = document.body.offsetWidth;
    yScroll = document.body.offsetHeight;
  }
  if ( self.innerHeight ) {
    // all except Explorer
    windowWidth = self.innerWidth;
    windowHeight = self.innerHeight;
  } else if ( document.documentElement && document.documentElement.clientHeight ) {
    // Explorer 6 Strict Mode
    windowWidth = document.documentElement.clientWidth;
    windowHeight = document.documentElement.clientHeight;
  } else if ( document.body ) { // other Explorers
    windowWidth = document.body.clientWidth;
    windowHeight = document.body.clientHeight;
  }       
  // for small pages with total height less then height of the viewport
  if( yScroll < windowHeight ) {
    pageHeight = windowHeight;
  } else { 
    pageHeight = yScroll;
  }
  // for small pages with total width less then width of the viewport
  if( xScroll < windowWidth ) {      
    pageWidth = windowWidth;
  } else {
    pageWidth = xScroll;
  }
  arrayPageSize = new Array( pageWidth, pageHeight, windowWidth, windowHeight );
  return arrayPageSize;
}

XooNIpsThemeJS.ResizableColumn.prototype.getMousePosition = function( e ) {
  var isOpera6 = ( navigator.userAgent.search( "Opera(\ |\/)6" ) != -1 );
  var isIE = ( document.all );
  var isNN = ( document.layers || document.getElementById );
  var mouseXPos = 0;
  var mouseYPos = 0;
  if ( isOpera6 ) {
    mouseXPos = e.clientX;
    mouseYPos = e.clientY;
  } else if ( isIE ) {
    mouseXPos = document.body.scrollLeft + window.event.clientX;
    mouseYPos = document.body.scrollTop + window.event.clientY;
  } else if ( isNN ) {
    mouseXPos = e.pageX;
    mouseYPos = e.pageY;
  }
  arrayPosition = new Array( mouseXPos, mouseYPos );
  return arrayPosition;
}

XooNIpsThemeJS.ResizableColumn.prototype.onMouseDown = function( e ) {
  if ( this.bOnDown ) {
    return false;
  }
  this.bOnDown = true;
  mousePos = this.getMousePosition( e );
  this.mouse_x = mousePos[0];
  arrayPageSize = this.getPageSize();
  // set height of overlay and show
  this.el_overlay.setStyle( 'height', arrayPageSize[1] + 'px' );
  this.el_overlay.showLayer();
//  this.debug( this.el_resizable.getOffset( 'width' ) + ', size_x : ' + this.size_x + ', mouseX : ' + this.mouse_x );
  return false;
};

XooNIpsThemeJS.ResizableColumn.prototype.onMouseOut = function( e ) {
  if ( ! this.bOnDown ){
    return false;
  }
  this.size_x = this.el_resizable.getOffset( 'width' ) - this.padding_x;
  this.cookie.setCookie( this.cookie_key, this.size_x );
  // hide overlay
  this.el_overlay.hideLayer();
  this.bOnDown = false;
  return false;
}

XooNIpsThemeJS.ResizableColumn.prototype.onMouseUp = function( e ) {
  if ( ! this.bOnDown ){
    return false;
  }
  this.size_x = this.el_resizable.getOffset( 'width' ) - this.padding_x;
  this.cookie.setCookie( this.cookie_key, this.size_x );
  // hide overlay
  this.el_overlay.hideLayer();
  this.bOnDown = false;
  return false;
}

XooNIpsThemeJS.ResizableColumn.prototype.onMouseMove = function( e ) {
  if ( ! this.bOnDown ){
    return false;
  }
  mousePos = this.getMousePosition( e );
  var new_x = this.size_x;
  if ( this.axis == 'left' ) {
    new_x += ( mousePos[0] - this.mouse_x );
  } else if ( this.axis == 'right' ) {
    new_x += ( this.mouse_x - mousePos[0] );
  }
//  this.debug( this.el_resizable.getOffset( 'width' ) + ', new_x : ' + new_x + ', size_x : ' + this.size_x + ', mouseX : ' + this.mouse_x );
  if ( new_x > 0 ) {
    if ( this.size_x != new_x ) {
      this.el_resizable.setStyle( 'width', new_x + 'px' );
      this.size_x = new_x;
      this.mouse_x = mousePos[0];
    }
  }
  return false;
}

XooNIpsThemeJS.ResizableColumn.prototype.addLoadEvent = function() {
  var self_copy = this;
  var old_onload = window.onload;
  if ( typeof( window.onload ) != 'function') {
    window.onload = function() {
      self_copy.initialize();
    }
  } else {
    window.onload = function() {
      old_onload();
      self_copy.initialize();
    }
  }
}


//-->

