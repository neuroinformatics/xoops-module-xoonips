/*
 * Javascript Library for the XooNIps IndexTree
 */

if ( typeof( XooNIpsTreeJS ) == 'undefined' ) XooNIpsTreeJS = function() {};

/**
 * The cookie controller class
 * @package XooNIpsTreeJS
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
XooNIpsTreeJS.Cookie = function( domain, path, expires, secure ) {
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

XooNIpsTreeJS.Cookie.prototype = {
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
      return '';
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
 * The Scroll Bar Position controller class
 * @package XooNIpsTreeJS
 */

/** 
 * constructor
 *
 * @access public
 * @param object cookie instance of XooNIpsTreeJS.Cookie
 * @param string cookie_key cookie name for scroll bar position saving
 * @return object instance
 */
XooNIpsTreeJS.ScrollPosition = function( cookie, cookie_key ) {
  this.positions = {};
  this.cookie = cookie;
  this.cookie_key = cookie_key;
  return this;
}

XooNIpsTreeJS.ScrollPosition.prototype = {
  /**
   * save scroll bar position to cookie
   *
   * @access public
   * @param string prefix
   */
  saveScrollPosition: function( prefix ) {
    var x_name = 'x' + prefix;
    var y_name = 'y' + prefix;
    if ( window.pageXOffset || window.pageYOffset ){
      this.positions[x_name] = window.pageXOffset;
      this.positions[y_name] = window.pageYOffset;
    } else if ( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      this.positions[x_name] = document.documentElement.scrollLeft;
      this.positions[y_name] = document.documentElement.scrollTop;
    } else if ( document.body ) {
      this.positions[x_name] = document.body.scrollLeft;
      this.positions[y_name] = document.body.scrollTop;
    } else {
      return; // no support to get scroll bar positions
    }

    // save position to cookie
    var ar = new Array();
    for ( var key in this.positions ) {
      ar.push( key + "/" + this.positions[key] );
    }
    this.cookie.setCookie( this.cookie_key, ar.join( ',' ) );
  },

  /**
   * load scroll bar positions from cookie
   *
   * @access public
   * @param string prefix
   */
  loadScrollPosition: function( prefix ) {
    var x_name = 'x' + prefix;
    var y_name = 'y' + prefix;
    var str = this.cookie.getCookie( this.cookie_key );
    if ( str ) {
      // found
      var ar = str.split(',');
      for ( var i = 0; i < ar.length; i++ ) {
        var keyval = ar[i].split('/');
        this.positions[keyval[0]] = keyval[1];
      }
      var x = Number( this.positions[x_name] );
      var y = Number( this.positions[y_name] );
      if ( isNaN( x ) || x < 0 ) { x = 0; }
      if ( isNaN( y ) || y < 0 ) { y = 0; }
      // window.scrollTo( x, y ); // IE fails ( only if y>height/2 ?? )
      setTimeout( 'window.scrollTo(' + x + ', ' + y + ')', 1 );
    }
  }
}


/**
 * The Tree Tab control class
 * @package XooNIpsTreeJS
 */

/**
 * constructor
 *
 * @access public
 * @param object cookie instance of XooNIpsTreeJS.Cookie
 * @param string id_prefix prifix tab element id. 
 *        tab element id must be set 'id_prefix + index id'
 * @param string open_func function name to open tab.
 *        tab open function must be set 'window.top.document.' + open_func
 * @param string cookie_key_tab cookie name for tab saving
 * @param string cookie_key_scroll cookie name for scroll bar position saving
 * @return object instance
 */
XooNIpsTreeJS.TabControl = function( cookie, id_prefix, open_func, cookie_key_tab, cookie_key_scroll ) {
  this.hash = new Array();
  this.tabs = new Array();
  this.selected = null;
  this.cookie = cookie;
  this.id_prefix = id_prefix;
  this.open_func = open_func;
  this.cookie_key = cookie_key_tab;
  this.scroll_position = new XooNIpsTreeJS.ScrollPosition( cookie, cookie_key_scroll );
  return this;
}

XooNIpsTreeJS.TabControl.prototype = {
  /**
   * register page id
   *
   * @access public
   * @param string id element id
   * @param number n element number
   */
  registerTab: function( id, n ) {
    this.hash[n] = id;
    this.tabs.push( n );
  },

  /**
   * set current tab
   *
   * @access public
   * @param number n element number
   */
  selectTab: function( n ) {
    if ( this.tabs.length == 0 || this.selected == n ) {
      return;
    }
    if ( this.selected != null ) {
      this.scroll_position.saveScrollPosition( this.selected );
    }
    var len = this.tabs.length;
    for ( var i = 0; i < len; i++ ) {
       var page = this.tabs[i];
       var id = this.hash[page];
       var elem = document.getElementById( id );
       elem.style.display = ( page == n ) ? 'block' : 'none';
    }
    this.scroll_position.loadScrollPosition( n );
    this.selected = n;
  },

  /**
   * load selected tab
   *   tab selection priority:
   *     $_GET['selected_tab'] > cookie:xoonipsSelectedTab2 > min(xid)
   *
   * @access public
   * @param number n index id
   */
  loadSelectedTab: function( n ) {
    if ( this.tabs.length == 0 ) {
      return;
    }
    if ( ! n ) {
      n = this.cookie.getCookie( this.cookie_key );
    }
    // validate index id
    var xid = Number( n );
    if ( ! ( xid > 0 && window.top.document.getElementById( this.id_prefix + xid ) ) ) {
      xid = this.tabs[0];
    }
    // call tab open function
    var func_name = 'window.top.document.' + this.open_func;
    var open_func_type = eval( 'typeof( ' + func_name + ')' );
    if ( open_func_type == 'function' ) {
      eval( func_name + '( ' + xid + ')' );
    } else {
      this.selectTab( xid );
    }
  },

  /**
   * save selected tab
   *
   * @access public
   */
  saveSelectedTab: function() {
    if ( this.tabs.length == 0 || this.selected == null ) {
      return;
    }
    this.cookie.setCookie( this.cookie_key, this.selected );
    this.scroll_position.saveScrollPosition( this.selected );
  }
}


/**
 * The global attributes class
 * @package XooNIpsTreeJS
 */

/**
 * constructor
 *
 * @access public
 * @return object instance
 */
XooNIpsTreeJS.Attributes = function() {
  this.url = '';
  this.target_url = '';
  this.link_is_checkbox = 0;
  this.selected_tab = null;
  this.onclick_title = '';
  this.image_url = '';
  this.image_compat33 = 1;
  return this;
}

XooNIpsTreeJS.Attributes.prototype = {
  /**
   * set global attribute
   *
   * @access public
   * @param string name attribute name
   * @param mixed value attribute value
   */
  setAttribute: function( name, value ) {
    switch( name ) {
    case 'url':
      this.url = value;
      break;
    case 'target_url':
      this.target_url = value;
      break;
    case 'link_is_checkbox':
      this.link_is_checkbox = Number( value );
      break;
    case 'selected_tab':
      this.selected_tab = value;
      break;
    case 'onclick_title':
      this.onclick_title = value;
      break;
    case 'image_url':
      this.image_url = value;
      break;
    case 'image_compat33':
      this.image_compat33 = Number( value );
      break;
    }
  },

  /**
   * get global attribute
   *
   * @access public
   * @param string name attribute name
   * @return mixed attribute value
   */
  getAttribute: function( name ) {
    var ret = null;
    switch( name ) {
    case 'url':
      ret = this.url;
      break;
    case 'target_url':
      ret = this.target_url;
      break;
    case 'link_is_checkbox':
      ret = this.link_is_checkbox;
      break;
    case 'selected_tab':
      ret = this.selected_tab;
      break;
    case 'onclick_title':
      ret = this.onclick_title;
      break;
    case 'image_url':
      ret = this.image_url;
      break;
    case 'image_compat33':
      ret = this.image_compat33;
      break;
    }
    return ret;
  },

  /**
   * get image url
   *
   * @access public
   * @param string name folder image
   * @return string url
   */
  getImageUrl: function( name ) {
    var url = '';
    if ( this.image_compat33 != 0 ) {
      var name2n = {
        'padding': 9,
        'line_normal': 2,
        'line_empty': 4,
        'root_normal': 7,
        'root_open': 8,
        'root_close': 8,
        'folder_middle_normal': 1,
        'folder_middle_open': 5,
        'folder_middle_close': 5,
        'folder_end_normal': 3,
        'folder_end_close': 6,
        'folder_end_open': 6
      };
      if ( typeof name2n[name] != undefined ) {
        url = this.image_url + '/t' + name2n[name] + '.gif';
      }
    } else {
      url = this.image_url + '/tree_' + name + '.gif';
    }
    return url;
  }
}

/**
 * The tree node data holder class
 * @package XooNIpsTreeJS
 */

/** 
 * constructor
 *
 * @access public
 * @param number xid index id
 * @param array child child index ids
 * @param number open_level open level of index. 1: Public, 2:Group, 3:Private
 * @param number is_last is this last child node?  0 or 1
 * @param string title index title
 * @return object instance
 */
XooNIpsTreeJS.TreeNode = function( xid, child, open_level, is_last, title ) {
  this.xid = xid;
  this.child = child;
  this.open_level = open_level;
  this.is_last = is_last;
  this.title = title;
  this.start_opened = false;
  this.is_opened = false;
  this.once_opened = false;
  this.child_el = null;
  this.checkbox_el = null;
  return this;
}

XooNIpsTreeJS.TreeNode.prototype = {
  /**
   * is this public node?
   *
   * @access public
   * @return bool
   */
  isPublic: function() {
    return ( this.open_level == 1 ); // OL_PUBLIC
  },

  /**
   * is this group node?
   *
   * @access public
   * @return bool
   */
  isGroup: function() {
    return ( this.open_level == 2 ); // OL_GROUP_ONLY
  },

  /**
   * is this private node?
   *
   * @access public
   * @return bool
   */
  isPrivate: function() {
    return ( this.open_level == 3 ); // OL_PRIVATE
  }
}

/**
 * The tree controller class
 * @package XooNIpsTreeJS
 */

/** 
 * constructor
 *
 * @access public
 * @param object cookie instance of XooNIpsTreeJS.Cookie
 * @param object tab instance of XooNIpsTreeJS.TabControl
 * @param object attribs instance of XooNIpsTreeJS.Attributes
 * @param string stub_id element id of xoonips tree stub
 * @param string stub_hidden_checked_id element id of hidden checkbox stub
 * @param string cookie_key_open cookie name for node open state saving
 * @param string cookie_key_checked cookie name for node checkbox state saving
 * @param string stub_checked_name element name for node checkbox state holder
 * @return object instance
 */
XooNIpsTreeJS.Tree = function( cookie, tab, attribs, stub_id, stub_hidden_checked_id, cookie_key_open, cookie_key_checked, stub_checked_name ) {
  this.cookie = cookie;
  this.tab = tab;
  this.attribs = attribs;
  this.stub_id = stub_id;
  this.stub_el = null;
  this.stub_hidden_checked_id = stub_hidden_checked_id;
  this.stub_hidden_checked_el = null;
  this.cookie_key_open = cookie_key_open;
  this.cookie_key_checked = cookie_key_checked;
  this.stub_checked_name = stub_checked_name;
  this.roots = new Array();
  this.nodes = new Array();
  this.xid2n = new Array();
  this.onclick_checkbox_public_callback = new Array();
  this.onclick_checkbox_group_callback = new Array();
  this.onclick_checkbox_private_callback = new Array();
  this.open_start_tick = 0;
  this.open_all_tick = 0;
  this.close_all_tick = 0;
  return this;
}

XooNIpsTreeJS.Tree.prototype = {
  /**
   * register tree node
   *
   * @access public
   */
  registerNode: function( xid, child, open_level, is_last, title ) {
    var node = new XooNIpsTreeJS.TreeNode( xid, child, open_level, is_last, title );
    this.xid2n['x' + xid] = this.nodes.length;
    this.nodes.push( node );
  },

  /**
   * register root index id
   *
   * @access public
   * @param number xid index id
   */
  registerRoot: function( xid ) {
    this.roots.push( xid );
  },

  /**
   * register onclick checkbox callback
   *
   * @access public
   * @param number xid index id
   */
  registerCheckboxCallback: function( type, func ) {
    switch( type ) {
    case 'public':
      this.onclick_checkbox_public_callback.push( func );
      break;
    case 'group':
     this.onclick_checkbox_group_callback.push( func );
      break;
    case 'private':
      this.onclick_checkbox_private_callback.push( func );
      break;
    }
  },

  /**
   * initilize tree
   *
   * @access public
   */
  initialize: function() {
    var obj = this;
    this.stub_el = document.getElementById( this.stub_id );
    this.stub_hidden_checked_el = document.getElementById( this.stub_hidden_checked_id );

    // register index tree control functions. these functions will be called 
    // from tree block on top window.
    this.stub_el.openAll = function(){ obj._openAll.apply( obj, arguments ); };
    this.stub_el.closeAll = function(){ obj._closeAll.apply( obj, arguments ); };
    this.stub_el.clearCheck = function(){ obj._clearCheck.apply( obj, arguments ); };
    this.stub_el.selectTab = function(){ obj._selectTab.apply( obj, arguments ); };

    // create root (public, groups, private) nodes
    this._create_roots();

    // restore checkbox checked status
    var stub_checked_els = window.top.document.getElementsByName( this.stub_checked_name );
    var checked_state = '';
    if ( stub_checked_els.length != 0 ) {
      checked_state = stub_checked_els.item( 0 ).value;
    } else {
      checked_state = this.cookie.getCookie( this.cookie_key_checked );
    }
    var checked_xids = checked_state.split( ',' );
    this._set_checked_nodes( checked_xids );

    // restore tree opened status
    var open_state = this.cookie.getCookie( this.cookie_key_open );
    var open_xids = open_state.split( ',' );
    this._set_open_nodes( open_xids );

    // load selected tab
    this.tab.loadSelectedTab( this.attribs.getAttribute( 'selected_tab' ) );
  },

  /**
   * finalize tree
   *
   * @access public
   */
  finalize: function() {
    // save open and checked node status into cookie
    var len = this.nodes.length;
    var open_state = new Array();
    var checked_state = new Array();
    for ( var i = 0; i < len; i++ ) {
      var node = this.nodes[i];
      if ( node.is_opened ) {
        open_state.push( node.xid );
      }
      if ( node.checkbox_el && node.checkbox_el.checked ) {
        checked_state.push( node.xid );
      }
    }
    this.cookie.setCookie( this.cookie_key_open, open_state.join( ',' ) );
    this.cookie.setCookie( this.cookie_key_checked, checked_state.join( ',' ) );
    // save selected tab
    this.tab.saveSelectedTab();
  },

  /**
   * get node object by xid
   *
   * @access private
   * @param number xid index id
   * @return object instance of XooNIpsTreeJS.TreeNode
   */
  _get_node_by_xid: function( xid ) {
    var n = this.xid2n['x' + xid];
    if ( n == undefined ) {
      return null;
    }
    return this.nodes[n];
  },

  /**
   * create closed node element
   *
   * @access public
   * @param object node tree node
   * @param string lastinfo ancestor of creating element. ex. '001010'.
   * @param boolean is_last is this last child node?
   * @param object parent parent element
   */
  _create_closed_element: function( node, lastinfo, is_last, parent ) {
    node.is_last = is_last;
    node.lastinfo = lastinfo + ( is_last ? '1' : '0' );
    var obj = this;
    var text = document.createTextNode( node.title );
    var div = document.createElement( 'DIV' );
    parent.appendChild( div );
    div.style.height = '19px';
    div.style.whiteSpace = 'nowrap';
    var len = node.lastinfo.length - 1;
    // create line images
    if ( 0 < len ){
      for( var i = 0; i < len; i++ ){
        if ( i == 0 && this.attribs.getAttribute( 'image_compat33' ) == 0 ) {
          div.style.paddingLeft = '5px';
          continue;
        }
        var img = document.createElement( 'IMG' );
        div.appendChild( img );
        img.height = 19;
        if ( i == 0 ) {
          img.width = 3;
          img.src = this.attribs.getImageUrl( 'padding' );
        } else if ( node.lastinfo.charAt( i ) == '1' ) {
          img.width = 10;
          img.src = this.attribs.getImageUrl( 'line_empty' );
        } else {
          img.width = 10;
          img.src = this.attribs.getImageUrl( 'line_normal' );
        }
      }
    }
    // create folder image
    var img = document.createElement( 'IMG' );
    div.appendChild( img );
    if ( node.child == null ){
      if ( len == 0 ) {
        img.src = this.attribs.getImageUrl( 'root_normal' );
      } else if ( node.lastinfo.charAt( len ) == '1' ) {
        img.src = this.attribs.getImageUrl( 'folder_end_normal' );
      } else {
        img.src = this.attribs.getImageUrl( 'folder_middle_normal' );
      }
    } else {
      if ( len == 0 ) {
        img.src = this.attribs.getImageUrl( 'root_close' );
      } else if ( node.lastinfo.charAt( len ) == '1' ) {
        img.src = this.attribs.getImageUrl( 'folder_end_close' );
      } else {
        img.src = this.attribs.getImageUrl( 'folder_middle_close' );
      }
      img.onclick = function(){ return obj._toggle_folder.call( obj, node ); };
    }
    node.folder_el = img;
    // create title label element
    if ( this.attribs.getAttribute( 'link_is_checkbox' ) ) {
      // create checkbox element if required
      var checkbox = document.createElement( 'INPUT' );
      checkbox.type = 'checkbox';
      checkbox.name = 'c' + node.xid;
      checkbox.onclick = function() { return obj._onclick_checkbox.call( obj, node ); };
      div.appendChild( checkbox );
      var hidden_checkbox = node.checkbox_el;
      if ( hidden_checkbox ){
        checkbox.checked = hidden_checkbox.checked;
        if ( hidden_checkbox.parentNode )
          hidden_checkbox.parentNode.removeChild( hidden_checkbox );
      }
      node.checkbox_el = checkbox;
      var a = document.createElement( 'A' );
      div.appendChild( a );
      a.className = 'tree';
      a.href = '';
      a.onclick = function() { return obj._toggle_checkbox.call( obj, node ); };
      a.target = '_top';
      a.title = node.title;
      a.appendChild( text );
    } else if ( this.attribs.getAttribute( 'target_url' ) || this.attribs.getAttribute( 'onclick_title' ) ) {
      var a = document.createElement( 'A' );
      div.appendChild( a );
      a.className = 'tree';
      if ( this.attribs.getAttribute( 'target_url' ) ) {
        a.href = this.attribs.getAttribute( 'target_url' ) + '?index_id=' + node.xid;
      } else {
        a.href = 'javascript:void(0);';
      }
      if ( this.attribs.getAttribute( 'onclick_title' ) != '' ) {
        a.onclick = function() { return obj._onclick_title.call( obj, node ); };
      }
      a.target = '_top';
      a.title = node.title;
      a.appendChild( text );
    } else {
      var span = document.createElement( 'SPAN' );
      div.appendChild( span );
      span.className = 'tree';
      span.appendChild( text );
    }
    // create child node block
    var div = document.createElement( 'DIV' );
    parent.appendChild( div );
    div.style.display = 'none';
    node.child_el = div;
  },

  /**
   * set open nodes before tree creation
   *
   * @access private
   * @param array xids index ids
   */
  _set_open_nodes: function( xids ) {
    var len = xids.length;
    while ( this.open_start_tick < len ) {
      var xid = xids[this.open_start_tick];
      this.open_start_tick++;
      if ( ! xid.match( /^[0-9]+$/ ) ) {
        continue; // xid is not number
      }
      var node = this._get_node_by_xid( xid );
      if ( node == undefined ) {
        continue; // not registered node
      }
      this._set_node_open( node, true );
      if ( this.open_start_tick % 5 == 0 ) {
        var obj = this;
        setTimeout( function() { obj._set_open_nodes.call( obj, xids ); }, 1 );
        return;
      }
    }
    this.open_start_tick = 0;
  },

  /**
   * set checked nodes before tree creation
   *
   * @access private
   * @param array xids index ids
   */
  _set_checked_nodes: function( xids ) {
    if ( this.attribs.getAttribute( 'link_is_checkbox' ) == 0 ) {
      return;
    }
    for ( var i = 0; i < xids.length; i++ ) {
      var xid = xids[i];
      if ( ! xid.match( /^[0-9]+$/ ) ) {
        continue; // xid is not number
      }
      var node = this._get_node_by_xid( xid );
      if ( node == undefined ) {
        continue; // not registered node
      }
      // create hidden checkbox element if checkbox element is not available
      // and is_checked == true
      if ( ! node.checkbox_el ) {
        var checkbox = document.createElement( 'INPUT' );
        checkbox.type = 'checkbox';
        checkbox.name = 'c' + node.xid;
        this.stub_hidden_checked_el.appendChild( checkbox );
        node.checkbox_el = checkbox;
      }
      node.checkbox_el.checked = true;
      this._onclick_checkbox( node );
    }
  },

  /**
   * create root nodes
   *
   * @access private
   */
  _create_roots: function() {
    this.stub_el = document.getElementById( this.stub_id );
    for ( var i = 0; i < this.roots.length; i++ ) {
      var xid = this.roots[i];
      var node = this._get_node_by_xid( xid );
      var div = document.createElement( 'DIV' );
      this.stub_el.appendChild( div );
      var id = 'xoonips_tree_root' + xid;
      div.id = id;
      div.style.display = 'none';
      this._create_closed_element( node, '', true, div );
      // always opened for root nodes
      this._set_node_open( node, true );
      this.tab.registerTab( id, xid );
    }
  },

  /**
   * set node open state
   *
   * @access public
   * @param object node tree node
   * @param is_opened new opened flag
   */
  _set_node_open: function( node, is_opened ) {
    if ( node.is_opened == is_opened ) {
      return;
    }
    if ( node.child == null ) {
      return; // node has not child nodes
    }
    if ( node.child_el == null ) {
      if ( is_opened ) {
         node.start_opened = true;
      }
      return;
    }
    // show/hide child node stub
    node.child_el.style.display = ( is_opened ? 'block' : 'none' );
    if ( node.child != null ){
      var len = node.lastinfo.length - 1;
      if ( len == 0 ) {
        if ( is_opened ) {
          node.folder_el.src = this.attribs.getImageUrl( 'root_open' );
        } else {
          node.folder_el.src = this.attribs.getImageUrl( 'root_close' );
        }
      } else if ( node.lastinfo.charAt( len ) == '1' ) {
        if ( is_opened ) {
          node.folder_el.src = this.attribs.getImageUrl( 'folder_end_open' );
        } else {
          node.folder_el.src = this.attribs.getImageUrl( 'folder_end_close' );
        }
      } else {
        if ( is_opened ) {
          node.folder_el.src = this.attribs.getImageUrl( 'folder_middle_open' );
        } else {
          node.folder_el.src = this.attribs.getImageUrl( 'folder_middle_close' );
        }
      }
    }
    // create child nodes if not available
    if ( ! node.once_opened && is_opened && node.child_el ) {
      var len = node.child.length;
      for ( var i = 0; i < len; i++ ) {
        var is_last = ( i == len - 1 ) || ( node.lastinfo == '' );
        var child_node = this._get_node_by_xid( node.child[i] );
        this._create_closed_element( child_node, node.lastinfo, is_last, node.child_el );
        if ( child_node.start_opened ) {
          this._set_node_open( child_node, true );
        }
      }
      node.once_opened = true;
    }
    node.is_opened = is_opened;
  },

  /**
   * set node checkbox state
   *
   * @access private
   * @param object node tree node
   * @param is_checked new checked flag
   */
  _set_node_checked: function( node, is_checked ) {
    if ( node.checkbox_el ) {
      if ( node.checkbox_el.checked != is_checked ) {
        this._toggle_checkbox( node );
      }
    }
  },

  /**
   * onclick event handler for 'INPUT' elements of checkbox
   * @access private
   * @param object node
   */
  _onclick_checkbox: function( node ) {
    if ( node.checkbox_el ) {
      var is_checked = node.checkbox_el.checked;
      if ( node.isPublic() ) {
        var len = this.onclick_checkbox_public_callback.length;
        for ( var i = 0; i < len; i++ ) {
          if ( typeof( this.onclick_checkbox_public_callback[i] ) == 'function' ) {
            this.onclick_checkbox_public_callback[i].call( node, is_checked );
          }
        }
      } else if ( node.isGroup() ) {
        var len = this.onclick_checkbox_group_callback.length;
        for ( var i = 0; i < len; i++ ) {
          if ( typeof( this.onclick_checkbox_group_callback[i] ) == 'function' ) {
            this.onclick_checkbox_group_callback[i].call( node, is_checked );
          }
        }
      } else if ( node.isPrivate() ) {
        var len = this.onclick_checkbox_private_callback.length;
        for ( var i = 0; i < len; i++ ) {
          if ( typeof( this.onclick_checkbox_private_callback[i] ) == 'function' ) {
            this.onclick_checkbox_private_callback[i].call( node, is_checked );
          }
        }
      }
    }
    return true;
  },

  /**
   * onclick event handler for 'A' elements of title label
   * @access private
   * @param object node
   */
  _onclick_title: function( node ) {
    var onclick = this.attribs.getAttribute( 'onclick_title' );
    if ( onclick == '' ) {
      return false;
    }
    eval( 'var ret = ( typeof( window.parent.' + onclick + ') == "undefined" ) ? false : window.parent.' + onclick + '(' + node.xid + ');' );
    return ret;
  },

  /**
   * onclick event handler for 'A' elements of checkbox
   * @access private
   * @param object node
   */
  _toggle_checkbox: function( node ) {
    var b = ! node.checkbox_el.checked;
    node.checkbox_el.checked = b;
    if ( node.checkbox_el.onclick ) {
      node.checkbox_el.onclick();
    }
    return false;
  },

  /**
   * onclick event handler for 'IMG' element of index folder image
   * @access private
   * @param object node
   */
  _toggle_folder: function( node ) {
    this._set_node_open( node, ! node.is_opened );
    return false;
  },

  /**
   * open all tree node
   *
   * @access private
   */
  _openAll: function() {
    var len = this.nodes.length;
    while ( this.open_all_tick < len ) {
      this._set_node_open( this.nodes[this.open_all_tick], true );
      this.open_all_tick++;
      if ( this.open_all_tick % 50 == 0 ) {
        var obj = this;
        setTimeout( function() { obj._openAll.apply( obj, arguments ); }, 1 );
        return;
      }
    }
    this.open_all_tick = 0;
  },

  /**
   * close all tree node
   *
   * @access private
   */
  _closeAll: function() {
    var len = this.nodes.length;
    while ( this.close_all_tick < len ) {
      this._set_node_open( this.nodes[this.close_all_tick], false );
      this.close_all_tick++;
      if ( this.close_all_tick % 200 == 0 ) {
        var obj = this;
        setTimeout( function() { obj._closeAll.apply( obj, arguments ); }, 1 );
        return;
      }
    }
    this.close_all_tick = 0;
  },

  /**
   * clear all checkboxes
   *
   * @access private
   */
  _clearCheck: function() {
    if ( this.attribs.getAttribute( 'link_is_checkbox' ) == 0 ) {
      return;
    }
    var len = this.nodes.length;
    for ( var i = 0; i < len; i++ ) {
      this._set_node_checked( this.nodes[i], false );
    }
  },

  /**
   * switch tree tab
   *
   * @access private
   * @param number xid index id
   */
  _selectTab: function( xid ) {
    this.tab.selectTab( xid );
  }

}
