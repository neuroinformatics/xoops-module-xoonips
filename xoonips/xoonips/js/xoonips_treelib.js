

var	xoonipsDocTreeView2 = 0;

// get tree-window document
function xoonipsGetDocTreeView2(){
	var ua = navigator.userAgent;
	if ( ua.indexOf("MSIE") >= 0 ||
			ua.indexOf("MSN") >= 0 ){
		xoonipsDocTreeView2 = window.top.document.treeFrame.document;
	}
	else if ( ua.indexOf("Netscape") >= 0 ||
			ua.indexOf("Mozilla") >= 0 ){
		var treeFrames = window.top.document.getElementsByName( 'treeFrame' );
		if ( treeFrames.length != 1 ){
			alert( "no treeFrame" );
			return 0;
		}
		var treeFrame = treeFrames.item(0);
		xoonipsDocTreeView2 = treeFrame.contentDocument;
	}
	if ( xoonipsDocTreeView == null )
		window.alert("cannot get docTreeView");
}

// iframe
var xoonipsOpenState2;
var xoonipsCheckState2;

// save tree state to xoonipsOpenState2, xoonipsCheckState2
function xoonipsSaveTreeState2(doc){
	var nodes = doc.getElementsByTagName( 'div' );
	var len = nodes.length;
	var i;
	
	var opens = new Array();
	var slen = 0;
	for ( i = 0; i < len; i++ ){
		var name = nodes[i].id;
		var index = name.substr(1); // strip 1 heading char. 't123' -> '123'
		if ( nodes[i].style.display == 'block' )
			opens[slen++] = index;
	}
	xoonipsOpenState2 = opens.join( ',' );
	//window.alert( "xoonipsOpenState2=" + xoonipsOpenState2 );
	
	var nodes = doc.getElementsByTagName( 'input' );
	var len = nodes.length;
	var checks = new Array();
	slen = 0;
	for ( i = 0; i < len; i++ ){
		var name = nodes[i].name;
		var index = name.substr(1);
		if ( nodes[i].checked )
			checks[slen++] = index;
	}
	xoonipsCheckState2 = checks.join( ',' );
}
