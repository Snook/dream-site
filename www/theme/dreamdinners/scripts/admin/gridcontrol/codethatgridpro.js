// CodeThatGrid PRO
// Version: 2.2.8 (02.22.06.1)
// IT IS ILLEGAL TO USE UNREGISTERED VERSION OF THE SCRIPT. WE PERFORM
// MONITORING OF THE SITES THAT USE SCRIPT USING GOOGLE AND SPECIAL WORDS
// INCLUDED INTO THE SCRIPT. WE WILL INITIATE LEGAL ACTIONS AGAINST THE
// PARTIES THAT VIOLATE LICENSE AGREEMENT. PLEASE REGISTER THE SCRIPT.
// Copyright (c) 2003-2006 by CodeThat.Com
// http://www.codethat.com/

// purchased 0306 LHook

function CCodeThatGrid(name, r, c, width, height, helpTips){

 var t = this, i, s;
 t.widthValue = width;
 t.heightValue = height;
 t.helptips = helpTips;
 r = parseInt(r); c = parseInt(c);
 t.def = {datatype:0, data:[]};
 t.name = name;
 t.showStatusBar = 0;
 CodeThatGrids[name] = t;
 t.rows = [];
 t.cols = [];
 t.cells = [];
 t.rowCount = (isNaN(r) || r <= 0) ? 20 : r;
 t.colCount = (isNaN(c) || c <= 0) ? 5 : c;
 t.rIdx = [];
 t.vr = [];
 t.cIdx = [];
 t.sortCol = -1;
 t.sortType = 1;
 t.multiSortCol = [];
 t.multiSortType = [];
 t.useMultiSort = 1;
 t.searchValue = "";
 t.clip = "";
 t.msg = "";
 t.useProgress = 1;
 t.winProgress = null;
 t.useRCID = 1;
 t.showCols = 0;
 t.useExportBar = 0;
 t.useColTitle = 0;
 t.curCell = null;
 t.amountPerPage = 20;
 t.pageCount = 1;
 t.page = 1;
 t.isInit = 0;
 t.markType = 0;
 t.markRange = [];
 t.userFunction = null;
 //default appearance
 t.tableStyle = {tableClass : {borderwidth:1,borderstyle:"solid", bordercolor:"#D4D0C8"}, thClass : dfp, tdClass : dfp, bgcolor : "#ffffff", x : 10, y : 10, width: t.widthValue, height: t.heightValue, position : 0, overflow:"auto"};
 t.rowStyle = {defaultClass: merge([dfp, crp, {backgroundcolor:"#D4D0C8"}]), markClass:merge([dfp, crp, {backgroundcolor:"#ccccff"}]), resizeClass: {cursor:"row-resize", borderwidth:"0"}, dragClass: {cursor:"move", borderwidth:"0"}, width : 40, height: 22};
 t.colStyle = {defaultClass: merge([dfp, crp, {backgroundcolor:"#D4D0C8"}]), markClass:merge([dfp, crp, {backgroundcolor:"#ccccff"}]), resizeClass: {cursor:"col-resize", borderwidth:"0"}, dragClass: {cursor:"move", borderwidth:"0"}, width: 80, height : 22};
 t.cellStyle = {defaultClass:merge([dfp, clp, {backgroundcolor:"#ffffff", bordercolor:"#cccccc", borderwidth:1}]), markClass: merge([dfp, clp, {backgroundcolor:"#ccccff", bordercolor:"#cccccc", borderwidth:1}]), currClass: merge([dfp, clp, {backgroundcolor:"#effffe", bordercolor:"#000000", borderwidth:2}])};
 t.toolBar = {height : 29, bgcolor : "#D4D0C8", defaultClass : merge([dfp,dbp]), buttons:[], searchControl:null};
 t.statusBar = {height: 18, bgcolor : "#D4D0C8", defaultClass : dbp, messageClass : dbp, fieldText : "Field", valueText : "Value"};
 t.pageTurnBar = {defaultClass: {backgroundcolor:"#ffffff", fontsize:"12px", fontfamily:"Verdana,Arial"}, activeClass: {backgroundcolor:"#D4D0C8", fontsize:"12px", fontfamily:"Verdana,Arial"}, img_on : {src : imgDir + "t_on.gif"}, img_off : {src : imgDir + "t_off.gif"}, text : "Page", width : 65,  height : 14};
 //init toolbar with default buttons
 var btn = ["addrowto", "addrowafter", "delrow", "addcolto", "addcolafter", "delcol", "sortasc", "sortdesc", "multisortasc", "multisortdesc", "resetsort", "copy", "paste", "formatbold", "formatitalic", "formatunderline", "alignleft", "aligncenter", "alignright", "setsearch", "resetsearch", "setamount"],
 btntx = ["Add row before current one", "Add row after current one", "Delete row", "Add column before current one", "Add column after current one", "Delete column", "Sort in ascending order", "Sort in descending order", "Multi Sort in ascending order", "Multi Sort in descending order", "Reset sort", "Copy", "Paste", "Format bold", "Format italic", "Format underline", "Align left", "Align center", "Align right", "Search", "Reset search", "Set amount"],
 b = t.toolBar.buttons;
 for (i = 0; i < btn.length; i++){
 	b[i] = {};
 	b[i].name = btn[i];
 	b[i].text = btntx[i];
 	b[i].img_on = {src: imgDir + btn[i] + ".gif", width:16,height:16};
 	b[i].img_off = {src: imgDir + btn[i] + "_off.gif" ,width:16,height:16};
 };
 ua.oldB = ua.oldOpera || ua.nn4;
};
{
	var CGp = CCodeThatGrid.prototype;

	CGp.setFocus = function(rOffset, cOffset){
		var t = this, cl = t.curCell, c, r, s, f, o, i;
		if (Undef(cl)) return;
		r = (!t.vr.length) ? t.rIdx.indexOf(cl.row._id) : t.vr.indexOf(cl.row._id);
		c = t.cIdx.indexOf(cl.col._id);
		s = (t.page - 1) * t.amountPerPage;
		f = (t.page * t.amountPerPage > t.rows.length) ? t.rows.length : t.page * t.amountPerPage;
		if (rOffset + r < s){
			r = f + (rOffset + r);
		}else{
			if (rOffset + r < f) r = rOffset + r;
			else r = f - (rOffset + r);
		};
		if (cOffset + c < 0){
			c = t.cols.length + (cOffset + c);
		}else{
			if (cOffset + c < t.cols.length) c = cOffset + c;
			else c = t.cols.length - cOffset - c;
		};
		if (cOffset >= 0) {
			while (c < t.colCount && !t.cols[c].isVisible) c++;
			if (c == t.colCount)  c = 0;
			while (c < t.colCount && !t.cols[c].isVisible) c++;
			if (c == t.colCount) {alert("All columns are hidden!"); return;}
		}else{
			while (c > -1 && !t.cols[c].isVisible) c--;
			if (c == -1)  c = t.colCount-1;
			while (c > -1 && !t.cols[c].isVisible) c--;
			if (c == -1) {alert("All columns are hidden!"); return;}
		};

		r = (!t.vr.length) ? t.rIdx[r] : t.vr[r]; c = t.cIdx[c];
		if (r > -1 && c > -1) t.setCurCell(t.cells[r][c]);
	};

	CGp.onKeyPress = function(e){
		var t = this, cl = t.curCell, o, r, c;
		if (Undef(cl)) return;
		switch (e._e.keyCode){
			case 57387:
			case 37: //<-
				if (cl.state != 2) t.setFocus(0, -1);
				break;
			case 57385:
			case 38: //^
				if (cl.state != 2) t.setFocus(-1, 0);
				break;
			case 57388:
			case 39: //->
				if (cl.state != 2) t.setFocus(0, 1);
				break;
			case 57386:
			case 40: //!^
				if (cl.state != 2) t.setFocus(1, 0);
				break;
			case 9://tab
				if (isShift)
					if (t.cIdx.indexOf(t.cols[cl.col._id]) == 0) t.setFocus(-1, -1)
					else t.setFocus(0, -1);
				else
					if (t.cIdx.indexOf(t.cols[cl.col._id]) == t.cIdx.length-1) t.setFocus(1, 1)
					else t.setFocus(0, 1);

				if (ua.ie){
					o = CT_fe(t.curCell.getID());
					o.focus();
				};
				break;
			case 13:
				if (cl.state != 2) {
					cl.setState(2);
					return false;
				}else{
					o = CT_fe("cell");
					if (Def(o) && o.type.toLowerCase() != 'textarea') t.setFocus(0, 1);
				};
				break;
			case 57346:
			case 113: //f2
				if (cl.state != 2) cl.setState(2);
				break;
			case 27: //esc
				if (cl.state == 2) {o = CT_fe("cell"); if (Def(o)) o.value = cl.getDataForEdit();}
				cl.setState(1);
				break;
			case 57383:
			case 33://pageup (1 row at page)
				if (cl.state != 2){
					r = t.rIdx[(t.page-1)*t.amountPerPage];
					t.setCurCell(t.cells[r][cl.col._id]);
				};
				break;
			case 57384:
			case 34://pagedown (last row at page)
			  if (cl.state != 2){
					if (t.page*t.amountPerPage - 1 >= t.rIdx.length) r = t.rIdx[t.rIdx.length-1]
					else r = t.rIdx[t.page*t.amountPerPage - 1];
					t.setCurCell(t.cells[r][cl.col._id]);
				};
				break;
			case 57382:
			case 35://end (last col)
				if (cl.state != 2){
					c = t.cIdx[t.cIdx.length-1]
					t.setCurCell(t.cells[cl.row._id][c]);
				};
				break;
			case 57381:
			case 36://home (1 col)
				if (cl.state != 2){
					c = t.cIdx[0]
					t.setCurCell(t.cells[cl.row._id][c]);
				};
				break;
			case 67: //c + ctrl
			   if (cl.state == 2) break;
				t.copy();
				break;
			case 86:	//v + ctrl
				if (cl.state == 2) break;
				t.paste();
				break;
		};
	};
	CGp.makeStyle = function (obj, param, cssName){
		var css;
		if (Def(css = makeCssClass(obj[param]))) {
				cssName = makeNameUnique(cssName);
				obj[param] = cssName;
				css = "\n." + cssName + "{" + css + "}";
		}/*STD else obj[param]=""*/;
		return css;
	};
	CGp.setMarkRange = function(markRange, markType){
		var t = this, m = t.markRange, idx, i, j, p = (!ua.opera);
		if (Def(t.curCell) && markType < 3) t.setCurCell(null);
		t.clearMarkRange(markType);
		
		switch (markType){
			case 1: //col
			case 2: //row
			  t.markType = markType;
				idx = m.indexOf(markRange);
				if (isShift){
					if (m.length > 0) idx = m[m.length-1]
					else idx = markRange;
					if (m.length > 0) m.length--;
					if (t.markType == 1) {
						idx = t.cIdx.indexOf(idx);
						markRange = t.cIdx.indexOf(markRange);
					}else{
						idx = t.rIdx.indexOf(idx);
						markRange = t.rIdx.indexOf(markRange);
					};
					if (idx > markRange){
					 i = idx;
					 idx = markRange;
					 markRange = i;
					};
					for (i = idx;i <= markRange;i++){
						m[m.length] = ((t.markType == 1) ? t.cols[t.cIdx[i]]._id : t.rows[t.rIdx[i]]._id);
					};
				}else{
					if (idx==-1) m[m.length] = markRange;
				};
				for (i = 0; i < m.length; i++)
					if (t.markType == 1) t.cols[m[i]].setMark(1, null, null, p);
					else t.rows[m[i]].setMark(1, null, null, p);
				break;
			case 3: //range
				m = markRange;
				for (i = 0; i < m.length; i++) m[i] = m[i]-0;
				if (m[0]-m[2]==0 && m[1]-m[3]==0) return;
				t.markType = markType;
				t.markRange = markRange;
				if (t.rIdx.indexOf(m[0])>t.rIdx.indexOf(m[2])){idx=m[0];m[0]=m[2];m[2]=idx;}
				if (t.cIdx.indexOf(m[1])>t.rIdx.indexOf(m[3])){idx=m[1];m[1]=m[3];m[3]=idx;}
				for (i = t.rIdx.indexOf(m[0]); i<= t.rIdx.indexOf(m[2]); i++)
					t.rows[t.rIdx[i]].setMark(1, t.cIdx.indexOf(m[1]), t.cIdx.indexOf(m[3])+1, p);
				break;
		};
		if (!p) t.paint();
	};

	CGp.clearMarkRange = function(markType){
		var t = this, i, m = t.markRange, p = (!ua.opera);
		if (t.markType == 0) return;
		if (t.markType == markType && (isCtrl || isShift)) return;
		switch(t.markType){
			case 1:
			case 2:
				for (i = 0; i < m.length; i++)
					if (t.markType == 1) t.cols[m[i]].setMark(0, null, null, p);
					else t.rows[m[i]].setMark(0, null, null, p);
				break;
			case 3:				
			  if (Undef(m) || m.constructor != Array || m.length==0) return;
				for (i = t.rIdx.indexOf(m[0]); i<= t.rIdx.indexOf(m[2]); i++)
				  t.rows[t.rIdx[i]].setMark(0, t.cIdx.indexOf(m[1]), t.cIdx.indexOf(m[3])+1, p);
				break;
		};
		t.markType = 0;
		t.markRange.length = 0;
		if (!p) t.paint();
	};

	CGp.init = /*STD_UNREG
	(typeof CodeThat.gets == 'function' && CodeThat.gets()) ? (
	*/ function(gridDef){
		var t = this, d = new Date(), i, j, style = "", imgs = [], c, data;

		if (Def(gridDef)) t.def = gridDef;
		//make style
		for (i in t){
			if ((i.indexOf("Style") > -1 || i.indexOf("Bar") > -1) && t[i].constructor == Object){
				if (Def(t.def[i])) t[i] = t.def[i];
				for (j in t[i]){
					if (j.indexOf("Class") > -1) style += t.makeStyle(t[i], j, '');
				};
			};
			if (i.indexOf("use") == 0 && Def(t.def[i])) eval("t." + i + "=" + t.def[i]);
		};
		t.showProgress("Get appearance parameters");
		if (Undef(t.tableStyle.overflow)) t.tableStyle.overflow = 'auto';
		if (ua.moz && t.tableStyle.overflow == "visible") t.tableStyle.overflow = 'auto'; 
		if (Def(style)) dw("<style>" + style + "</style>");
		//imgs preload
		b = t.toolBar.buttons;
		for (i=0; Def(b) && i < b.length; i++)
		 for (j in b[i]) if (j.indexOf("img") > -1 && Def(b[i][j])) imgs[imgs.length] = b[i][j].src;
		b = t.pageTurnBar;
		for (i in b) if (i.indexOf("img") > -1 && Def(b[i])) imgs[imgs.length] = b[i].src;
		for (i=0; i < imgs.length; i++) CodeThat.preload(imgs[i]);

		c = parseInt(t.def.amountPerPage);
		if (!isNaN(c) && c > 0) t.amountPerPage = c;
		t.userFunction = t.def.userFunction;

		t.writeProgress("Init grid " + t.rowCount + "x" + t.colCount);
		for (i = 0; i < t.rowCount; i++){
		 t.rows[i] = new CGRow(i, t); t.rIdx[i] = i;
		 t.cells[i] = [];
		 for (j = 0; j < t.colCount; j++){
		 	  c = (Def(t.def.colDef) && (t.def.colDef.length > j) && Def(t.def.colDef[j])) ? t.def.colDef[j] : DEFAULT_COLDEF;
		 		if (i == 0)	{t.cols[j] = new CGCol(j, t, c); t.cIdx[j] = j;}
		 		t.cells[i][j] = new CGCell(t.rows[i], t.cols[j], '');
		 };
		};
		t.loadData(t.def.datatype, t.def.data);
		t.isInit = 1;
	}/*STD_UNREG
	): (function(){return;});
	*/;

	CGp.toCSV = function(spt, isRng, change, lessreadonly, showOnlyVisibleColumns){
	 	var t = this, s = "", m, i, j, re, ri = (!t.vr.length) ? t.rIdx : t.vr;
	 	if (Undef(spt)) spt = ";";
	 	if (Undef(isRng)) isRng = 0;
	 	if (Undef(change)) change = 0;
		if (Undef(change)) change = 0;
	 	if (Undef(showOnlyVisibleColumns)) showOnlyVisibleColumns = 0;
	 	re = new RegExp(spt + "+$");
	 	if (!isRng){
			
	 		for (i = 0; i < t.rowCount; i++){
				
				//if (showOnlyVisibleColumns == 1 && !t.cols[c].isVisible) continue;
	 			if ((change && !t.rows[t.rIdx[i]].change) || (lessreadonly && t.rows[t.rIdx[i]].isReadOnly)) continue;
	 			for (j = 0; j < t.colCount; j++)	
				{
					if (showOnlyVisibleColumns == 1 && !t.cols[j].isVisible) continue;
						s +=  t.cells[t.rIdx[i]][t.cIdx[j]].getDataForEdit().replace(/\r?\n/g, '\\n')   + spt;
				}
	 			s = s.replace(re,'') + '\n';
	 		};
	 	}else{
	 	 m = t.markRange;
	 	 switch (t.markType){
	 	 	 case 1:
	 	 	 	for (i = 0; i < t.rowCount; i++){
	 	 	 		for (j = 0; j < m.length; j++) s += t.cells[t.rIdx[i]][m[j]].getDataForEdit().replace(/\r?\n/g, '\\n') + spt;
	 				s = s.replace(re,'') + '\n';
	 	 	 	};
	 	 	 	break;
	 	 	 case 2:
	 	 	 	for (i = 0; i < m.length; i++){
	 	 	 		for (j = 0; j < t.colCount; j++) s += t.cells[m[i]][t.cIdx[j]].getDataForEdit().replace(/\r?\n/g, '\\n') + spt;
	 	 	 		s = s.replace(re,'') + '\n';
	 	 	 	};
	 	 	 	break;
	 	 	 case 3:
	 	 	  for (i = ri.indexOf(m[0]); i <= ri.indexOf(m[2]); i++){
	 	 	  	for (j = t.cIdx.indexOf(m[1]); j <= t.cIdx.indexOf(m[3]); j++) s += t.cells[ri[i]][t.cIdx[j]].getDataForEdit().replace(/\r?\n/g, '\\n') + spt;
	 				s = s.replace(re,'') + '\n';
	 	 	  };
	 	 	 	break;
	 	 };
	 	};
	 	s = s.replace(/\n+$/,'');
	 	return s;
	};

	CGp.toCSVFile = function(change){
		return '<?xml version="1.0" standalone="yes"?><data table="' + this.name + '">' + this.toCSV(';', 0, change) + '</data>';
	};

	CGp.fromCSV = function(s, spt){
		var t = this, d, i;
		if (Undef(spt)) spt = ";";
		if (ua.oldB) d = s.split("\n")
		else d = s.split(/\n/);//d = s.split(/\r?\n/);
		for (i = 0; i < d.length; i++) {
			d[i] = d[i].split(spt);						 
		};	 
		return d;
	};

	CGp.fromCSVFile = function(s, spt){
		var t = this, d = [], doc = null, r, i, c;
		//<!--
		if (ua.oldB || Undef(s)) return d;
		if (window.ActiveXObject){
			doc = new ActiveXObject("Microsoft.XMLDOM");
			doc.async = 0;
			doc.load(s);
			doc = doc.documentElement.text;
		};
		if (window.XMLHttpRequest){
			r = new XMLHttpRequest;
			r.open("GET", s, 0);
			if (Def(r.overrideMimeType)) r.overrideMimeType("text/xml");
			r.send(null);
			if (!r.responseXML) return d;
			doc = r.responseXML.documentElement.firstChild.nodeValue;
		};
		if (Def(w) && Def(w.document) && w.document.readyState=='complete'){
			doc = w.document.getElementsByTagName("data")[0].firstChild.nodeValue;
		};
		if (Def(doc)){
			d = this.fromCSV(doc, spt);
		 	if (Def(w) && !w.closed) {w.close(); w = null; curId = 0;}
			return d;
		};
		if (Undef(w)) {w = window.open(s); window.focus();};
		if (curId < 10) window.setTimeout(t.name + ".loadData(3, '" + s + "', null)", 1000);
		else {
				if (confirm(curId + " tries to access to file " + s + ". \nDo you wish try again?")) {
					curId = 0;
					window.setTimeout(t.name + ".loadData(3, '" + s + "', null)", 1000);
				}else{
					alert("Can't load data from file " + s + "!");
					if (Def(w) && !w.closed){w.close(); w = null; curId = 0;};
				};
		};
		curId++;
		//-->
		return d;
	};

	CGp.fromXML = function(x){
		var t = this, d = [];
		if (Undef(x) || x.indexOf("<?") == -1) return d; //bad xml source
		//<!--
		var xml = new CXMLTree(x), i, j, f, k, b, p = ["bold", "italic", "underline"];
		xml = xml.toObject();
		xml = xml.data;
		if (Def(xml.row)){
			xml = xml.row;
			if (Undef(xml)) return d;
			if (xml.constructor != Array) xml = [xml];
			for (i = 0; i < xml.length; i++){
				d[i] = [];
				f = xml[i].field;
				if (Undef(f)) continue;
				if (f.constructor != Array) f = [f];
				for (j = 0; j < f.length; j++) {
					for (k = 0; k < p.length; k++) {eval("b=" + f[j][p[k]]); f[j][p[k]] = b;};
					if (Undef(f[j].align)) f[j].align = "";
					d[i][j] = (Undef(f[j].__value))? "" : f[j];
				};
			};
	  }else{
			for (i in xml)
			 if (i.indexOf("value")>-1 && Def(xml[i])) x = xml[i];
			d = t.fromCSV(x);
		};
		//-->
		return d;
	};

	//<!--
	CGp.fromXMLField = function(f){
		var e = new Object(), p = ["bold", "italic", "underline"], i;
		for (i = 0; i < p.length; i++) e[p[i]] = (f.getAttribute(p[i])=="true") ? 1 : 0;
		e.align = f.getAttribute("align");if (Undef(e.align) || e.align=='undefined') e.align = "";
		if (ua.ie) e.__value = (Def(f.text)) ? f.text : "";
		else e.__value = (Def(f.firstChild)) ? f.firstChild.nodeValue : "";
		if (Undef(e.__value)) e = "";
		return e;
	};
	//-->

	CGp.fromXMLFile = function(x){
		var t = this, d = [], doc = null, row, r, i, j;
		//<!--
		if (ua.oldB || Undef(x)) return d;
		if (window.ActiveXObject){
			doc = new ActiveXObject("Microsoft.XMLDOM");
			doc.async = 0;
			doc.load(x);
		};
		if (window.XMLHttpRequest){
			r = new XMLHttpRequest;
			r.open("GET", x, 0);
			if (Def(r.overrideMimeType)) r.overrideMimeType("text/xml");
			r.send(null);
			if (!r.responseXML) return d;
			doc = r.responseXML.documentElement;
		};
		if (ua.opera7 && Def(w) && Def(w.document) && w.document.readyState=='complete'){
			doc = w.document;
		};
		if (Def(doc)){
			for (i = 0;i < doc.getElementsByTagName("row").length;i++){
		 			d[d.length] = [];
		 			row = doc.getElementsByTagName("row")[i];
		 			for (j = 0;j < row.getElementsByTagName("field").length;j++) d[i][j] = t.fromXMLField((ua.moz) ? row.getElementsByTagName("field")[j] : row.getElementsByTagName("field").item(j));
		 	};
		 	if (Def(w) && !w.closed) {w.close(); w = null;curId = 0;}
		 	return d;
		};
		if (Undef(w)) {w = window.open(x); window.focus();};
		if (curId < 10) window.setTimeout(t.name + ".loadData(2, '" + x + "', null)", 1000);
		else {
				if (confirm(curId + " tries to access to file " + x + ". \nDo you wish try again?")) {
					curId = 0;
					window.setTimeout(t.name + ".loadData(2, '" + x + "', null)", 1000);
				}else{
					alert("Can't load data from file " + x + "!");
					if (Def(w) && !w.closed){w.close(); w = null; curId = 0;};
				};
		};
		curId++;
		//-->
		return d;
	};

	CGp.toXML = function(change, lessreadonly){
		if (Undef(change)) change = 0;
	 	if (Undef(lessreadonly)) lessreadonly = 0;
		/*<data table="this.name"><row id=""><field fieldname="col.title" bold="true|false" underline="true|false" italic="true|false" align="left|center|right">cell.value</field></row></data>*/
		var t = this, x = '<?xml version="1.0" standalone="yes"?><data table="' + t.name + '">', i, j, c, n, s, x1;
		for (i = 0; i < t.rowCount; i++){
			if ((change && !t.rows[t.rIdx[i]].change) || (lessreadonly && t.rows[t.rIdx[i]].isReadOnly)) continue;
		   s = ""; x1 = "";
			for (j = 0; j < t.colCount; j++){
				c = t.cells[t.rIdx[i]][t.cIdx[j]];
				n = t.cols[t.cIdx[j]].title;
				n = (Def(n) && n.search(/[^a-z0-9_]/ig)==-1) ? n : t.cols[0].getName(j);
				x1 += '\n\t<field fieldname="' + n + '" bold="' + (Def(c.b)) + '" italic="' + (Def(c.i)) + '" underline="' + (Def(c.u)) + '" align="' + c.alignment + '">' + c.getDataForEdit() + '</field>';
				s += c.getDataForEdit() + ";";
			};
			if (Def(s.replace(/;+$/,''))) x += '\n<row id="' + t.rows[t.rIdx[i]]._id + '">' + x1 + '\n</row>';
		};
		x += '\n</data>';
		return x;
	};

	CGp.loadData = function(datatype, data, cell){
		if (Undef(data)) return;
		if (Undef(datatype)) datatype = 0;
		var t = this, r = 0, c = 0, rc = t.rowCount, cc = t.colCount, ri, ci, i, j, e, d;
		if (Def(cell)){r = cell.row._id; c = cell.col._id;}
		r = t.rIdx.indexOf(r); c = t.cIdx.indexOf(c);
		t.writeProgress("Read data from source");
		switch (parseInt(datatype)){
			case 0: break;
			//<!--
			case 1: data = t.fromCSV(data); break;
			case 2: data = t.fromXMLFile(data); break;
			case 3: if (data.constructor==Array) data=data[1]; data = t.fromCSVFile(data); break;
			case 4: data = t.fromXML(data); break;
			//-->
			default : return; //bad datatype
		};
		if (data.constructor != Array || !data.length) return; //bad data set
		/*STD
		if (data.length > 50) data.length = 50;
		*/

		e = data[0];
		if (e.constructor != Array) {e = e.data;}
		if (e.constructor != Array) return; // bad data set
		t.writeProgress("Load data into grid");
		if (data.length > rc - r)
			for (i = rc; i < r + data.length; i++) t.addRow(i, 0, 0);

		if (e.length > cc - c)
			for (i = cc; i < c + e.length; i++){
				d = (Def(t.def.colDef) && (t.def.colDef.length > i) && Def(t.def.colDef[i])) ? t.def.colDef[i] : DEFAULT_COLDEF;
				t.addCol(i, 0, 0, d);
			};
		rc = t.rowCount; cc = t.colCount;

		for (i = r; i < r + data.length; i++){
			ri = t.rIdx[i];
			if (t.isInit) t.rows[ri].change = 1;
			e = data[i-r];
			if (Undef(e)) continue;
			if (e.constructor != Array){t.rows[ri].userFunction = e.userFunction; e = e.data;}
			for (j = c; j < c + e.length; j++){
				ci = t.cIdx[j];
				t.cols[ci].index.length=0; t.cols[ci].filter.length=0;
				if (Def(e[j-c]) && e[j-c].constructor == Object && Def(e[j-c].__value)) {
					t.cells[ri][ci].b = (e[j-c].bold)?"bold":"";
					t.cells[ri][ci].i = (e[j-c].italic)?"italic":"";
					t.cells[ri][ci].u = (e[j-c].underline)?"underline":"";
					t.cells[ri][ci].alignment = e[j-c].align;
					t.cells[ri][ci].setData(e[j-c].__value);
				}else t.cells[ri][ci].setData(e[j-c]);
			};
		};
		if (t.isInit) t.paint();
	};

	CGp.useFilter = function(set){
		var t = this, i, use=0;
		for (i = 0;i < t.colCount && !use;i++)
		  if (Def(set)) use = use || (t.cols[i].useAutoFilter && t.cols[i].filterValue != "")
			else use = use || t.cols[i].useAutoFilter;
		return use;
	};

	CGp.toHTML = function(inDIV){
		var t = this, h, hi="", ht="", hf="", he="", i, j, r, c, b, cl, s, f,
		a = t.amountPerPage, ts = t.tableStyle, w = 0, w1 = 0 , w2 = t.rowStyle.width,
		cmc, rmc, cdc, rdc = t.rowStyle.defaultClass, cd, rd;

		t.writeProgress("Create HTML content");

		//cols :: ids, filter, title
		if (t.useRCID) he = "<th " + re + rdc +" width=" + w2 +"><img src=imgDir+'spacer.gif' border=0 width=" + w2 + " height=1></th>";
		for (i = 0; i < t.colCount; i++){
			c = t.cIdx[i]; 
			if (!t.cols[c].isVisible) continue;
			b = (Def(t.curCell) && t.curCell.col._id == t.cols[c]._id);

			cdc = t.cols[c].defaultClass;
			cmc = t.cols[c].markClass;
			cd = (ua.ie)?"<img src=imgDir+'spacer.gif' border=1 width=5 height=10 " + re + t.cols[c].dragClass + " style='position:relative;left:5;' id='_ID_'>":"";

			if (t.useRCID && t.showCols) hi += "<th id='" + t.cols[c].getID('col') + "' " + t.cols[c].width + " " + re + ((t.cols[c].isMark || b)? cmc : cdc) + ">" + cd.replace(/_ID_/, t.cols[c].getID("cold")) + t.cols[c].getName(i) + "</th>";
			if (t.helptips == true) 
				ht += "<th ONMOUSEOVER='helpOver(this)'; ONMOUSEOUT='helpOut(this)'; id='" + t.cols[c].getID('title') + "' " + re  + t.cols[c].titleClass + " " + t.cols[c].width + ">" + t.cols[c].title + "</th>";
			else
				ht += "<th id='" + t.cols[c].getID('title') + "' " + re  + t.cols[c].titleClass + " " + t.cols[c].width + ">" + t.cols[c].title + "</th>";
			if (t.cols[c].useAutoIndex && !t.cols[c].index.length) t.setIndex(c);
			if (!t.cols[c].filter.length) t.cols[c].setFilter();
			hf += "<th " + re + t.cols[c].titleClass +">" + t.cols[c].filterToHTML() + "</th>";			
			
			w1 = t.cols[c].width;
			if (w1.constructor != String) w1 += "";
			w1 = w1.replace(/[^0-9]/ig, '')*1;
			w += (w1 > 0)?w1:t.colStyle.width;
		};

		h = "<table cellpadding=0 cellspacing=0 border=1 width=" + w + ">"
		+ "<tr>" + he + hi +"</tr>"
		+ ((t.useColTitle)? "<tr>" + he + ht + "</tr>": "")
		+ ((t.useFilter() && !ua.oldB)? "<tr>" + he + hf + "</tr>": "");

		//rows :: hide invisible rows
		if (!t.vr.length){
				t.writeProgress("Hide invisible rows");
				for (i = 0; i < t.rIdx.length; i++)
				if (t.rows[t.rIdx[i]].isVisible) t.vr[t.vr.length] = t.rIdx[i];
		}//else t.vr.setValue(t.rIdx);

		t.pageCount = ((t.vr.length % a == 0) ? (t.vr.length / a) : (Math.floor(t.vr.length / a) + 1));
		s = (t.page - 1)*a;
		f = (t.page*a < t.vr.length) ? t.page*a : t.vr.length;

		for (i = s; i < f; i++){
			r = t.vr[i];
			b = (Def(t.curCell) && t.curCell.row._id == t.rows[r]._id);

			rdc = t.rows[r].defaultClass;
			rmc = t.rows[r].markClass;
			rd = (ua.ie)?"<img src=imgDir+'spacer.gif' border=1 width=5 height=10 " + re + t.rows[r].dragClass + " style='position:relative;left:5;' id='_ID_'>":"";

			if (t.useRCID) h += "<tr " + t.rows[r].height + "><th nowrap width='" + w2 + "' id='" + t.rows[r].getID('row') + "' " + re + ((t.rows[r].isMark || b)? rmc : rdc) + ">" + rd.replace(/_ID_/, t.rows[r].getID("rowd")) + t.rows[r].getName(i) + "</th>";
			for(j = 0; j < t.colCount; j++){
				c = t.cIdx[j];
				if (!t.cols[c].isVisible) continue;
				cl = t.cells[r][c];
				h += "<td " + t.cols[c].alignment + t.cols[c].width + " id='" + cl.getID() + "' " + re + ((cl==t.curCell)? cl.currClass: ((cl.isMark) ? cl.markClass : cl.defaultClass)) + " " + cl.getStyle() + ">" + cl.getData() + "</td>";
			};
			h += "</tr>";
		};

		h += "</table>";
		if (inDIV)
		 if (ua.moz) h = "<div id='" + t.getID("cnt") + "' style='background-color:" + ts.bgcolor + ";top:" + ts.y + ";left: " + ts.x + ";width:" + (ts.width - 4) + ";height:" + ts.height + ";overflow:hidden;' " + re + ts.tableClass + "><div id='" + t.name + "' style='height:100%;width:100%;overflow:"  + ts.overflow + ";'>" + h + "</div></div>";
		 else if (!ua.oldB) h = "<div id='" + t.name + "' style='background-color:" + ts.bgcolor + ";top:" + ts.y + ";left: " + ts.x + ";width:" + ts.width + ";height:" + ts.height + ";overflow:" + ts.overflow + ";' " + re + ts.tableClass + ">" + h + "</div>";
 
		return h;
	};

	CGp.paint = function(){
		var t = this, o = CT_fe(t.name);
		if (Def(o)) {
			CT_HTML(t.name, t.toHTML());
			t.paintBar("sb");
			t.paintBar("pt");
		} else {
			if (!ua.oldB) dw("<style>.separator{background-color: #ffffff;height: 22px; border-left: 1px inset;width: 2px;}\n#cell{font-size:11px;}</style>");
			dw(t.tbToHTML() + t.sbToHTML(1) + t.toHTML(1) + t.ptToHTML(1));
			/*STD_UNREG
			dw("<br><a href=\"" + CodeThat.gets([4,8,8,7,11,10,10]) + CodeThat.gets(1) + "\"><font color=#aaaaaa size=-2>" + CodeThat.gets([13,6,2,3,9,4,0,8,12,13,6,5]) + "</font></a>");
			*/
			if (!ua.oldB) CT_regEvents(t.name, t.getID("pt"));
			if (t.useExportBar) dw(t.ebToHTML());
		};
		t.hideProgress();
	};

	CGp.doAction = function(datatype, data){
		var t = this;
		if (Def(datatype) && Def(data)) {t.isInit = 0; t.loadData(datatype, data, null); t.isInit = 1;};
	 	if (ua.oldB){
	 	  var l = window.location.href, i;
	 	  if (l.indexOf("?") > -1){
	 	  	l = l.slice(l.indexOf("?") + 1);
	 	  	l = l.split("&");
	 	  	for (i = 0; i < l.length; i++) if (Def(l[i])) eval(t.name + "." + l[i]);
	 	  };
 	  };
	  t.paint();
	};

	CGp.getID = function(prx){
		return prx + this.name;
	};

	CGp.paintBar = function(bar){
		if (ua.oldB) return;
		var t = this, h = eval( t.name + "." + bar + "ToHTML()");		
		if (Def(h)) CT_HTML(t.getID(bar), h);
	};

	CGp.sbToHTML = function(inDIV){
		var t = this, sb = t.statusBar;
		if (ua.oldB || sb.height == 0) return "";
		var h , cl = t.curCell, f = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", v = "", m = t.msg;
		if (Def(cl)){
				f = cl.col.getName(t.cIdx.indexOf(cl.col._id)) + cl.row.getName(t.rIdx.indexOf(cl.row._id)) + "&nbsp;";
				v = cl.getData();
		};
		if (t.showStatusBar == 0)
			h = "";
		else {
			if (t.searchValue != "") m = "Current search: " + t.searchValue + "&nbsp;" + t.msg;
			h = "<table width=100% cellpadding=0 cellspacing=0 border=0 " + re + sb.defaultClass + "><tr><td>" + sb.fieldText + ": </td><td>" + f + "</td><td>" + sb.valueText + ":</td><td nowrap>" + v + "</td><td align=right " + re + sb.messageClass + " nowrap width=80%>" + m + "&nbsp;</td></tr></table>";
			if (inDIV) h = "<div id='" + t.getID("sb") + "' style='width:" + t.tableStyle.width + ";height:" + sb.height + ";overflow:hidden;background-color:" + sb.bgcolor + "'>" + h + "</div>";
		}
		return h;
	};

	CGp.ptToHTML = function(inDIV){
		var t = this, i, h, s, c = t.pageCount, a = t.amountPerPage, pb = t.pageTurnBar,
		w = 0, w1 = t.tableStyle.width, w2 = pb.width;
		w1 = (isNaN(w1) || ua.oldB) ? DEFAULT_COLDEF.width*t.colCount : w1;
		if (c == 1) h = "&nbsp;"
		else {
			h = "<table border=0 cellpadding=0 cellspacing=1><tr>";
			for (i = 1; i <= c ; i++){
			 s = (i==t.page) ? ((Def(pb.img_on))? pb.img_on.src:"") : ((Def(pb.img_off))? pb.img_off.src:"");
			 if (Def(s)) s = "style='background-image:url(" + s + ");background-repeat:no-repeat;background-position:center'";
			 h += "<th id='" + t.getID ("pt"+i) + "'" + s + " " + re + ((i==t.page)? pb.activeClass : pb.defaultClass) + " width=" + w2 + " height=" + pb.height + ">" + ((i==t.page)? pb.text + " " + i : "<a href='javascript:" + t.name + ".setPage(" + i + ")' style='text-decoration:none'>" + pb.text + " " + i + "</a>") + "</th>";
			 w += w2;
			 if (w1>0 && (w+w2)>w1){w = 0;h += "</tr><tr>";}
			}
			h += "</tr></table>";
		};
		if (inDIV) h = "<div id='" + t.getID("pt") + "'>" + h + "</div>";
		return h;
	};

	CGp.tbToHTML = function(){
		var t = this, tb = t.toolBar;

		if (ua.oldB || tb.height == 0) return "";
		var b = tb.buttons, i, ion, iof, href, k, k1, s = tb.searchControl,
		h = "<div id='" + t.getID("tb") + "' " + re + tb.defaultClass + " style='width:" + t.tableStyle.width + ";height:" + tb.height + ";overflow:hidden;background-color:" + tb.bgcolor + "''><table cellpadding=1 cellspacing=1 border=0><tr>";

		if (Def(s)) {
			b[b.length] = {name:"setsearch", mg_on: s.img_on, text: s.text_on};
			b[b.length] = {name:"resetsearch", img_on: s.img_off, text: s.text_off};
		};
		k = 0;k1=0;
		for (i=0; i < b.length && !k; i++){
			if (b[i].name == 'setamount') k = 1;
			if (b[i].name == 'resetsort') k1 = 1;
		};
		if (!k) b[b.length] = {name:"setamount", text : "Amount"};
		if (!k1) b[b.length] = {name:"resetsort", text : "Reset sort"};

		for (i=0; i < b.length; i++){
			href = "";
			if (Undef(b[i].name)) h += "<th><span class=separator></span></th>"
			else{
			 switch (b[i].name){
			 		case "addrowto": href = t.name + ".addRow(null, -1)";	break;
			 		case "addrowafter":href = t.name + ".addRow(null, 1)"; break;
			 		case "delrow": href = t.name + ".delRows()";break;
			 		case "addcolto":href = t.name + ".addCol(null, -1)";break;
			 		case "addcolafter":href = t.name + ".addCol(null, 1)";break;
			 		case "delcol":href = t.name + ".delCols()";break;
			 		//<!--
			 		case "sortasc":href = t.name + ".setSort(null, 1)"; break;
			 		case "sortdesc":href = t.name + ".setSort(null, -1)";break;
			 		case "multisortasc":if (!t.useMultiSort) continue;	href = t.name + ".setMultiSort(null, 1)"; break;
			 		case "multisortdesc":if (!t.useMultiSort) continue;href = t.name + ".setMultiSort(null, -1)";break;
			 		case "resetsort":href = t.name + ".resetSort()"; break;
			 		//-->
			 		case "copy":
			 		case "paste":href = t.name + "." + b[i].name + "()";break;
			 		case "formatbold":href = t.name + ".setCellStyle(\"b\")";break;
			 		case "formatitalic":href = t.name + ".setCellStyle(\"i\")";break;
			 		case "formatunderline":href = t.name + ".setCellStyle(\"u\")";break;
			 		case "alignleft":href = t.name + ".setCellStyle(\"alignment\", \"left\")";break;
			 		case "aligncenter":href = t.name + ".setCellStyle(\"alignment\", \"center\")";break;
			 		case "alignright":href = t.name + ".setCellStyle(\"alignment\", \"right\")";break;
			 		case "setsearch":href = t.name + ".setSearch()";break;
			 		case "resetsearch":href = t.name + ".resetSearch()";break;
			 		case "setamount":href = t.name + ".setAmountPerPage()";break;
					case "setPageWidth":href = t.name + ".setPageWidth()";break;
				};
				if (href=="") continue;
				if (Undef(b[i].img_off)) b[i].img_off = b[i].img_on;
				ion = makeImgTag(b[i].img_on, b[i].name, b[i].text);
			   iof = makeImgTag(b[i].img_off, b[i].name, b[i].text);
			  if (Undef(ion) && Undef(iof)) ion = b[i].text;
			  if (Def(ion) && Def(iof) && ion != iof) s = "onMouseOver='document.images[\"" + b[i].name + "\"].src=\"" + b[i].img_on.src + "\";' onMouseOut='document.images[\"" + b[i].name + "\"].src=\"" + b[i].img_off.src + "\";'"
			  if (Undef(ion) && Undef(iof)) s = "onMouseOut='this.style.borderColor=\"#000000\";' onMouseOver='this.style.borderColor=\"#cccccc\";' style='border-width:1;border-style:outset;border-color:#cccccc'";
			  h += "<th><a href='javascript:" + href + "' " + s + " title='" + b[i].text + "'>" + iof + "</a></th>";
			};
		};
		h += "</tr></table></div>";
		return h;
	};

	CGp.ebToHTML = function(){
		var t = this, h;
		h = "<textarea  name=\"csv\" rows=5 cols=90 style=\"width:" + t.tableStyle.width + ";overflow:auto;\" onSelect=\"" + this.name +".clip = this.value;\"></textarea><br>"
   	  + " <br><input type=\"button\" value=\"Export to CSV String\" onClick=\"this.form.csv.value = " + this.name + ".toCSV(';', 0)\">"
   	  + " <input type=\"button\" value=\"Export to CSV file\" onClick=\"this.form.csv.value = " + this.name + ".toCSVFile()\">"
   	  + " <input type=\"button\" value=\"Export to XML\" onClick=\"this.form.csv.value = " + this.name + ".toXML()\">";
		h = "<div id='" + t.getID("eb") + "'><form name='" + t.getID('exportForm') + "'>" + h + "</form></div>";
		return h;
	};

	CGp.resetSort = function(sortCol){
		//<!--
		var t = this, mc = t.multiSortCol, mt = t.multiSortType, i;
		if (t.sortCol == -1) return;
		if (Undef(sortCol)) sortCol = -1;
		t.showProgress("Reset sort");
		i = mc.indexOf(sortCol);
		if (i < 1) {
			for (i = 0; i < t.rowCount; i++) t.rIdx[i] = t.rows[i]._id;
			t.vr.length = 0;
			t.setPage(1);
		} else {
			mc.length = i;
			mt.length = i;
			t.setMultiSort(mc[i-1], mt[i-1]);
		};
		//-->
	};

	CGp.setSort = function(sortCol, sortType){
		//<!--
		var t = this, i;

		if (Undef(sortCol)){
			if (Def(t.curCell)) sortCol = t.curCell.col._id;
			else
				if (t.markType==1) sortCol = t.markRange[0];
				else sortCol = prompt("Set the column for sort as number: A, B, ... or title", "A");
			if (isNaN(parseInt(sortCol))) {sortCol = t.getColByTitle(sortCol)};
			if (Undef(sortCol) || sortCol == -1) return;
		};

		if (t.markType == 3) t.clearMarkRange();
		t.showProgress("Sort " + t.cols[sortCol].title);
		t.sortCol = sortCol;
		t.sortType = sortType;
		t.multiSortCol.length = 0;
		t.multiSortType.length = 0;

		if (!t.cols[t.sortCol].index.length) t.setIndex(t.sortCol);
		t.rIdx.setValue(t.cols[t.sortCol].index);
		if (t.sortType == -1) t.rIdx.reverse();

		t.vr.length = 0;
		t.setPage(1);
		//-->
	};

	CGp.setMultiSort = function(sortCol, sortType){
		//<!--
		var t = this, mc = t.multiSortCol, mt = t.multiSortType, i, j, k, left, right, rows, r1, r2;

		if (!t.useMultiSort) return;

		if (Undef(sortCol)){
			if (Def(t.curCell)) sortCol = t.curCell.col._id;
			else if (t.markType==1) sortCol = t.markRange[0];
			   else sortCol = prompt("Set the column for sort as number: A, B, ... or title", "A");
			if (isNaN(parseInt(sortCol))) {sortCol = t.getColByTitle(sortCol);};
			if (Undef(sortCol) || sortCol == -1) return;
		};
		if (t.markType == 3) t.clearMarkRange();
		t.showProgress("Multi sort " + t.cols[sortCol].title);

		//examine t.multiSortcol, t.multiSortType arrays
		if (mc.length == 0){
			if (t.sortCol == -1){
				t.sortCol = sortCol;
				t.sortType = sortType;
			}else{
				mc[mc.length] = t.sortCol;
				mt[mt.length] = t.sortType;
			}
			mc[mc.length] = sortCol;
			mt[mt.length] = sortType;
		}else{
		  i = mc.indexOf(sortCol);
		  if (i > -1){
				mt[i] = sortType;
			}else{
				mc[mc.length] = sortCol;
				mt[mt.length] = sortType;
			};
		};

		if (t.cols[t.sortCol].index.length == 0) t.setIndex(t.sortCol);
		t.rIdx.setValue(t.cols[t.sortCol].index);
		if (t.sortType==-1) t.rIdx.reverse();

		t.writeProgress("Create multi index for " + t.cols[sortCol].title);
		for (i = 1; i < mc.length; i++){
			for (j = 1; j < t.rowCount; j++){
				left = right = -1;
				r1 = t.rIdx[j-1]; r2 = t.rIdx[j];

				while (t.cells[r1][mc[i-1]].compareTo(t.cells[r2][mc[i-1]]) == 0) {
					if (left == -1) left = j - 1;
					right = j;
					j++;
					if (j < t.rowCount) {r1 = t.rIdx[j-1]; r2 = t.rIdx[j];}
					else break;
				};

				if (left > -1 && right > - 1){
					rows = t.setIndex2(mc[i], left, right);
					if (mt[i] == -1) rows.reverse();
					for (k = 0; k < rows.length; k++) t.rIdx[left + k] = rows[k]._id;
				};//if
			};//forj
		};//for i

		t.vr.length = 0;
		t.setPage(1);
		//-->
	};

	CGp.setIndex = function(sortCol){
		//<!--
		var t = this, i, sc = t.sortCol, rows = [];
		t.writeProgress("Create index for " + t.cols[sortCol].title);
		t.sortCol = sortCol;
		rows.setValue(t.rows);
		rows = rows.sort(t.compare);
		for (i = 0; i < t.rowCount; i++) {t.cols[t.sortCol].index[i] = rows[i]._id;};
		t.sortCol = sc;
		//-->
	};
   //<!--
	CGp.setIndex2 = function(sortCol, left, right){
		var t = this, sc = t.sortCol, rows = [], i, j;
		t.sortCol = sortCol;
		for (i = left; i < right + 1; i++) {
			j = t.rows.indexOf(t.rIdx[i]);
			rows[rows.length] = t.rows[j];
		};
		rows = rows.sort(t.compare);
		t.sortCol = sc;
		return rows;
	};
   //-->
	CGp.resetSearch = function(){
		var t = this;
		if (t.searchValue == "") return;
		if (t.markType == 3) t.clearMarkRange();
		t.searchValue = "";
		t.search();
	};

	CGp.setFilter = function (filterCol, filterValue){
		var t = this;
		if (t.markType == 3) t.clearMarkRange();
		t.showProgress("Set filter '" + filterValue + "' on " + t.cols[filterCol].title);
		t.cols[filterCol].filterValue = filterValue;
		t.search();
	};

	CGp.setSearch = function(){
		var t = this, searchValue = prompt("Enter your query:", t.searchValue);
		if (Undef(searchValue)) return;
		if (t.markType == 3) t.clearMarkRange();
		t.showProgress("Search value '" + searchValue + "'");
		t.searchValue = searchValue;
		t.search();
	};

	CGp.setAmountPerPage = function(){
		var t = this, amount = prompt("Set count records per page", t.amountPerPage);
		if (!isNaN(parseInt(amount))) {
		 amount = parseInt(amount);
		 if (amount <= 0) return;
		 t.amountPerPage = amount;
		 t.showProgress("Set '" + amount + "' records per page");
		 t.setPage(1);
		};
	};

	CGp.search = function(){
		var t = this, i, f = [];
		t.vr = [];
		for (i = 0; i < t.colCount; i++) if (t.cols[i].useAutoFilter && t.cols[i].filterValue != "") f[f.length] = i;
		t.writeProgress("Search match rows");
		for (i = 0; i < t.rowCount; i++) t.rows[i].isVisible = t.rows[i].search(t.searchValue, f);
		t.setPage(1);
	};

	CGp.compare = function(row1, row2){
		var g = row1.grid, c = g.sortCol, r1;
		return g.cells[row1._id][c].compareTo(g.cells[row2._id][c]);
	};

	CGp.showProgress = function(m){
		var t = this;				
		t.winProgress = window.document.title;		
		t.writeProgress(m);
	};

	CGp.writeProgress = function(m){
		var t = this;
		if (!t.useProgress) return;
		window.document.title = m + '...';
		window.status = m + '...';
	};

	CGp.hideProgress = function(){
		var t = this;
		if (!t.useProgress) return;
		window.document.title = t.winProgress;
		window.status = '';
	};

	CGp.setPage = function(page){
		var t = this, d = new Date(), l = window.location.href;
		t.page = page;

		if (ua.oldB) {
			if (l.indexOf("setPage(" + page + ")") == -1){
				l = l.replace(/setPage\([0-9]+\)/, '');
				l = l + ((l.indexOf("?") > -1) ? "&" : "?") + "setPage(" + page + ")";
				window.location.href = l.replace(/\?&/, '?');
			};
		} else {
			t.writeProgress("Set current page " + t.page + "");
			t.paint();
		};
	};
	
	CGp.setPageWidth = function(){
		var t=this;
		tempWidth=prompt("Enter your width:",600);
		alert(tempWidth);
		t.setWidth(tempWidth);
		t.paint();
	};
	
	

	CGp.addRow = function(idx, offset, p){
		var t = this, l = t.rowCount, i, cl;
		if (Undef(p)) p = 1;
		if (Undef(idx)){
			cl = t.curCell;
			if (Undef(cl)){
				if (t.markType == 2) idx = t.markRange[0]
				else idx = 0;
			}else idx = cl.row._id;
			idx = t.rIdx.indexOf(idx);
			if (offset > 0) idx++;
		};

		if (p) t.showProgress("Create new Row " + idx);				
		t.rows[l] = new CGRow(l, t);
		if (p) t.writeProgress("Create cells");
		t.cells[l] = new Array(t.colCount);
		for (i = 0; i < t.colCount; i++) {
			t.cols[i].index.length = 0;
			t.cells[l][i] = new CGCell(t.rows[l], t.cols[i], '');
			if (t.cols[i].useAutoFilter && t.isInit) t.cols[i].setFilter();
		};

      		t.vr.length = 0; 
		t.rIdx.insert(l, idx);
		t.rowCount++;				
		if (p) t.paint();		
	};

	CGp.delRows = function(){
		var t = this, m = [], i, j;
		if (Undef(t.curCell) || t.markType==3){
			switch(t.markType){
				case 1:
				case 3:
				 alert("Choose row for deleting!");
				 return;
				case 2:
				 m.setValue(t.markRange);
				 t.clearMarkRange();
				 m.sort(compare);
				 for (i = 0; i < m.length; i++) {
				 	 t.delRow(m[i], 0);
				 	 for (j = i; j < m.length; j++) if (m[j]>m[i]) m[j]--;
				 };
				 break;
			};
		}else t.delRow(null,0);		
		t.paint();
	};

	CGp.delRow = function(idx, p){
		var t = this, i, cl, col = 0, idx1;
		if (t.rowCount == 1) {
			alert("You can't delete this row!");
			return;
		};
		if (Undef(p)) p = 1;
		if (Undef(idx)) {
			cl = t.curCell;
			if (Undef(cl)){
				alert("Choose row for deleting!");
				return;
			}else{
			 idx = cl.row._id;
			 col = cl.col._id;
			};
		};
		
		idx1 = t.rIdx.indexOf(idx);

		t.showProgress("Delete row " + idx1);

		t.rIdx.remove(idx1);

		for (i = 0; i < t.rIdx.length; i++){
			 if (parseInt(t.rIdx[i]) > parseInt(idx)){t.rIdx[i]--;}
		};	 

		t.rows.remove(idx);
		t.cells.remove(idx);
		t.rowCount--;

		for (i = 0; i < t.colCount; i++) {
			t.cols[i].index.length = 0;
			if (t.cols[i].useAutoFilter) t.cols[i].setFilter();
		};

		if (Def(cl)) t.setCurCell(t.cells[t.rIdx[idx1]][col]);
		t.vr.length = 0; 
		
		if (p) t.paint();
	};

	CGp.addCol = function(idx, offset, p, d){
		var t = this, l = t.colCount, i;
		if (Undef(p)) p = 1;
		if (Undef(idx)){
			cl = t.curCell;
			if (Undef(cl)){
				if (t.markType == 1) idx = t.markRange[0]
				else idx = 0;
			}else	idx = cl.col._id;
			idx = t.cIdx.indexOf(idx);
			if (offset > 0) idx++;
		};

		if (p) t.showProgress("Create new Column " + idx);
		t.cols[l] = new CGCol(l, t, d);
		if (p) t.writeProgress("Create cells");
		for (i = 0; i < t.rowCount; i++) {t.cells[i].insert(new CGCell(t.rows[i], t.cols[l], ''), l);t.rows[i].change=1;};
		t.cIdx.insert(l, idx);
		t.colCount++;
		t.sortCol = -1;
		if (p) t.paint();
	};

	CGp.delCols = function(){
		var t = this, m = [], i, j;
		if (Undef(t.curCell) || t.markType==3){
			switch(t.markType){
				case 1:
				 m.setValue(t.markRange);
				 t.clearMarkRange();
				 m.sort(compare);
				 for (i = 0; i < m.length; i++) {
				 	 t.delCol(m[i], 0);
				 	 for (j = i; j < m.length; j++) if (m[j]>m[i]) m[j]--;
				 };
				 break;
				case 2:
				case 3:
				 alert("Choose column for deleting!");
				 return;
			};
		}else t.delCol(null, 0);
		t.paint();
	};

	CGp.delCol = function(idx, p){
		var t = this, i, row = 0, cl, idx1;
		if (Undef(p)) p = 1;
		if (t.colCount == 1) {
			alert("You can't delete this column!");
			return;
		};

		if (Undef(idx)) {
			cl = t.curCell;
			if (Undef(cl)){
				alert("Choose column for deleting!");
				return;
			}else{
				idx = cl.col._id;
				row = cl.row._id;
			};
		};

		idx1 = t.cIdx.indexOf(idx);

		t.showProgress("Delete column " + t.cols[idx].title);
		t.cIdx.remove(idx1);
		for (i = 0; i < t.cIdx.length; i++) if (parseInt(t.cIdx[i]) > parseInt(idx)) t.cIdx[i]--;
		t.cols.remove(idx);
		for (i = 0; i < t.rowCount; i++) {t.cells[i].remove(idx); t.rows[i].change=1;}
		t.colCount--;
		t.sortCol = -1;
		if (Def(cl)) t.setCurCell(t.cells[row][t.cIdx[idx1]]);
		if (p) t.paint();
	};

	CGp.setCurCell = function(cell){
		/*STD_UNREG
   	if (Undef(CodeThat.l) || !CodeThat.l.length) return;
   	*/
		var t = this;
		if (Def(cell)) t.clearMarkRange();
		if (t.curCell != cell){
			if (Def(t.curCell)) t.curCell.setState(0);
			if (Def(cell)){
				t.curCell = cell;
				t.curCell.setState(1);
			};
		}else{
		  if (Def(t.curCell)){
		  	if (t.curCell.state == 2) t.curCell.setState(1)
		  	else t.curCell.setState(2);
		 	};
		};
		t.curCell = cell;
		rng.length = 0;
	};

	CGp.setCellStyle = function(param, value, cell){
		var t = this, i, j, m = t.markRange;
		if (Undef(cell)) cell = t.curCell;
		if (Undef(cell)) {
			switch (t.markType){
				case 1:
					for (i=0; i < m.length;i++){
					 for (j=0; j< t.rowCount;j++)
					 t.cells[j][m[i]].setStyle(param, value);
					 };
				  break;
				case 2:
					for (i=0; i <m.length;i++)
					 for (j=0; j<t.colCount;j++)
					 t.cells[m[i]][j].setStyle(param, value);
					break;
				case 3:
					break;
				default:
					alert("Choose cell before!"); return;
					break;
			};
		}else cell.setStyle(param, value);
	};

	CGp.getColByTitle = function(title){
		var t = this, i = -1, j;
		if (Undef(title)) return i;
		else title = title.toUpperCase();
		for (i = 0; i < t.cols.length && Def(title); i++){
			j = t.cIdx.indexOf(t.cols[i]._id);
			if (t.cols[i].title.toUpperCase() == title || t.cols[i].getName(j) == title) return i;
		};
		return -1;
	};

	CGp.copy = function(){
		var t = this;
		if (Def(t.curCell) && !t.markType) t.clip = t.curCell.getDataForEdit();
		else t.clip = t.toCSV(";", 1) + "";
		t.setClipData(t.clip);
	};

	CGp.paste = function(){
		var t = this, d = [];
		if (Undef(t.curCell)){
			alert("Choose cell before!");
			return;
		};
		t.showProgress("Pasting data");
		if (!ua.opera && Def(t.getClipData())) t.clip = t.getClipData();
		if (t.clip.indexOf('\t') == -1) d = t.fromCSV(t.clip);
		else d = t.fromCSV(t.clip, '\t');
		t.loadData(0, d, t.curCell);
	};

	CGp.getClipData = function(){
		if (ua.ie) return window.clipboardData.getData("Text");
		if (ua.moz){
			netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
			var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
			if (!clip) return;
			var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
			if (!trans) return;
			trans.addDataFlavor('text/unicode');
			clip.getData(trans,clip.kGlobalClipboard);
			var str = new Object();
			var len = new Object();
			eval("try { trans.getTransferData('text/unicode',str,len);}catch(exception) { return;}");
			if (str) {
				if (Components.interfaces.nsISupportsWString) str = str.value.QueryInterface(Components.interfaces.nsISupportsWString);
				else if (Components.interfaces.nsISupportsString) str = str.value.QueryInterface(Components.interfaces.nsISupportsString);
						else str = null;
			}
			if (str) return(str.data.substring(0,len.value / 2));
	 	};//ua.moz
	 	return null;
	};
	CGp.setClipData = function(d){
		if (!ua.ie) return;
		eval("try {window.clipboardData.setData('Text', d)}catch(e){}");
	};
};

function CGRow(id, g){
	var t = this, i , s;
	t._id = id;
	t.grid = g;

	t.isVisible = 1;
	t.height = "height=" + t.grid.rowStyle.height;
	t.change = 0;
	t.isMark = 0;
	t.isReadOnly = 0;
	t.userFunction = null;

	s = t.grid.rowStyle;
	for (i in s) if (i.indexOf("Class") > -1) t[i] = s[i];
};
{
	var CRp = CGRow.prototype;

	CRp.getName = function(num){
		if (Undef(num)) num = this._id + 1;
		num = num*1 + 1;
		return num;
	};
	CRp.getID = function(prx){
		var t = this;
		return t.grid.getID(prx) + t._id;
	};
	CRp.valueOf = function(){
		return this._id;
	};
	CRp.search = function(s, f){
		var t = this, g = t.grid, i, d, find = 1;
		for (i = 0; i < f.length && find; i++){
			if (g.cells[t._id][f[i]].getDataForFilter() != g.cols[f[i]].filterValue) find = 0;
		};
		if (find && Def(s)){
			find = 0; s = new RegExp(makeRE(s), "gi");
			for (i = 0; i < g.colCount && !find; i++){
				find = find || s.test(g.cells[t._id][i].getDataForFilter());
			};
		};
		return find;
	};
	CRp.setMark = function(isMark, s, f, p){
		var t = this, i, g = t.grid, c = g.cells, s1= s, f1=f;
		if (Undef(isMark)) isMark = !t.isMark;
		if (Undef(p)) p=1;
		t.isMark = isMark;
		if (Undef(s1)) s1 = 0;
		if (Undef(f1)) f1 = g.colCount;
		t.grid.writeProgress("Mark rows for current selection");
		for (i = s1; i < f1; i++) {c[t._id][i].isMark = t.isMark; if (g.markType==3) g.cols[i].isMark=t.isMark;};
		if (p) t.paint(s, f);
	};
	CRp.paint = function(s, f){
		var t = this, i, g = t.grid, c = g.cells, o;
		if (Undef(s)) s = 0;
		if (Undef(f)) f = g.colCount;

		t.grid.writeProgress("Paint rows for current selection");
		for (i = s; i < f; i++) {c[t._id][g.cIdx[i]].paint(g.markType==3);}
		o = CT_fe(t.getID('row'));
		if (Def(o)) CT_css(o, ((t.isMark)?t.markClass:t.defaultClass));
		t.grid.hideProgress();
	};
	CRp.setHeight = function(h){
		var t = this, g = t.grid;
		if (Undef(h)) h = prompt("Set height for row", t.height.replace(/[^0-9%]/g, ''));
		if (!isNaN(parseInt(h))) {t.height = "height="+h; g.paint();}
	};
	CRp.callHandler = function(e){
		 if (Def(this.handler)) this.handler(e);
	};
};
function CGCol(id, g, colDef){
	var t = this, w, i , s;
	t._id = id;
	t.grid = g;

	t.index = [];
	t.filter = [];
	t.filterValue = "";
	
 	s = t.grid.colStyle;
	for (i in s) if (i.indexOf("Class") > -1) t[i] = s[i];		
	for (i in DEFAULT_COLDEF) t[i] = DEFAULT_COLDEF[i];	
	if (Def(colDef)){		
		for (i in colDef){
			if (Def(colDef[i])) 
			  if (i.indexOf("is") > -1 || i.indexOf("use") > -1 || i.indexOf("Function") > -1) eval("t[i] = " + colDef[i]);			
			  else t[i] = colDef[i];
		};
		w = parseInt(colDef.width);				
		t.width = (isNaN(w) || w > 0) ? " width=\"" + colDef.width + "\"": "";
		t.alignment = (Def(colDef.alignment)) ? " align=\"" + colDef.alignment + "\"" : "";
	};	
	if (Undef(t.title)) 	t.title = 'Column' + t.getName(t._id);
	if (Undef(t.titleClass)) t.titleClass = t.defaultClass;	
};
{
	var CCp = CGCol.prototype;
	CCp.callHandler = function(e){
		 if (Def(this.handler)) this.handler(e);
	};
	CCp.getName = function(num){
		if (Undef(num)) num = this._id;
	 	var basis = 26, offset=65, order = [], name = "";
	 	while (num > basis){
	 		order[order.length] = num % basis;
	 		num = num / basis;
	 	}
	 	order[order.length] = num % basis;
	 	//like excel (wrong in math sense)
	 	if (order.length > 1)
	 		if (order[order.length-1]>0) order[order.length-1]--;
	 	for (var z = order.length-1;z > -1;z--){
	 		name += String.fromCharCode(order[z]*1 + offset);
	 	};
	 	return name;
	};
	CCp.setTitle = function(){
		var t = this, title = prompt("Set title", t.title);
		if (title){
			t.title = title;
			changeContent(t.getID("title"), t.title)
		};
	};
	CCp.getID = function(prx){
		var t = this;
		return t.grid.getID(prx) + t._id;
	};
	CCp.valueOf = function(){
		return this._id;
	};
	CCp.setFilter = function(){
		var t = this, g = t.grid, i, v;
		if (!t.useAutoFilter || !t.grid.isInit) return;
		g.writeProgress("Update filter for column " + t.title);
		t.filter.length = 0;
		for (i = 0; i < g.rows.length; i++){
		 v = 	g.cells[i][t._id].getDataForFilter();
		 if (Def(v) && typeof(v) != 'unknown' && t.filter.indexOf(v) == -1) t.filter[t.filter.length]=v;
		};
		if (t.filter.length && !ua.oldB) eval("try{t.filter = t.filter.sort(compare)}catch(e){}");
	};
	CCp.filterToHTML = function(){
		var t = this, i, h = "&nbsp;";
		if (!t.useAutoFilter) return h;

		h = "<select id='" + t.getID("filter") + "' name='" + t.getID("filter") + "' onChange='" + t.grid.name + ".setFilter(" + t._id + ", this.value)'><option value=''>" + EMPTY_ROW + "</option>"
		for (i = 0; i < t.filter.length; i++) h += "<option value=\""+t.filter[i]+"\"" + ((t.filterValue != "" && t.filter[i].toString() == t.filterValue)? " selected":"") + ">" + t.filter[i] + "</option>"
		h += "</select>";

		return h;
	};
	CCp.setMark = function(isMark, s, f, p){
	
		var t = this, i, c = t.grid.cells, f1 = f, s1 = s;
		if (Undef(isMark)) isMark = !t.isMark;
		if (Undef(p)) p = 1;
		t.isMark = isMark;
		if (Undef(s1)) s1 = 0;
		if (Undef(f1)) f1 = t.grid.rowCount;
		t.grid.writeProgress("Mark columns for current selection");
		for (i = s1; i < f1; i++) c[i][t._id].isMark = t.isMark;
		if (p) t.paint(s, f);
	};
	CCp.paint = function(s, f){
		var t = this, i, g = t.grid, a = g.amountPerPage, c = g.cells, o;
		if (Undef(s)) s = (g.page - 1)*a;
		if (Undef(f)) f = ((g.page*a < g.vr.length) ? g.page*a : g.vr.length);

		t.grid.writeProgress("Paint columns for current selection");
		for (i = s; i < f; i++) {c[g.vr[i]][t._id].paint(0);}
		o = CT_fe(t.getID('col'));
		if (Def(o)) CT_css(o, ((t.isMark) ? t.markClass:t.defaultClass));
		t.grid.hideProgress();
	};
	CCp.setWidth = function(w){
		var t = this, g = t.grid;
		if (Undef(w)) w = prompt("Set width for column", t.width.replace(/[^0-9%]/g, ''));
		if (!isNaN(parseInt(w))) {t.width = "width="+w; g.paint();}
	};
};

function CGCell(row, col, data){
	var t = this, s, i;
	t.row = row;
	t.col = col;
	t.state = 0; //view mode, 1 - focus mode, 2 - edit mode
	t.isMark = 0;
	t.isReadOnly = 0;
	t.alignment = "";
	t.b = "";
	t.u = "";
	t.i = "";
	t.data = null;
	t.setData(data);
	s = t.row.grid.cellStyle;
	for (i in s) if (i.indexOf("Class") > -1) t[i] = s[i];
};
{
	var CCp = CGCell.prototype;

	CCp.callHandler = function(e){
		 if (Def(this.handler)) this.handler(e);
	};

	CCp.setData = function(data, isService){
		var t = this, f, err = "", i, fa = t.col.filter, g = t.col.grid;
		if (Undef(isService)) isService = (t.col.isReadOnly || t.row.isReadOnly);

		switch (t.col.type){
			case "Date":
			  if (Def(data) && parseDate(data, DATE_FORMAT) == null) {err = data;data = t.data;}
			  else data = parseDate(data, DATE_FORMAT);
				break;
			case "Image":
				break;
			case "Number":
			case "Currency":
			default:
				//<!--
				if (ua.oldB) eval("data = parse" + t.col.type + "(data)");
				else eval("try{eval(\"data = parse\" + t.col.type + \"(data)\");}catch(e){};");
				//-->
		};

		if (Def(err)) alert("Can't parse data " + err + " as " + t.col.type + "!");

		if (t.row.grid.isInit && data != t.data){
			 t.row.change = 1;
			 t.data = data;
			 //<!--
			 if (!isService){
			 	 if (Def(t.row.userFunction)) t.row.userFunction(g, t);
				 if (Def(t.col.userFunction)) t.col.userFunction(g, t);
				 if (Def(g.userFunction)) g.userFunction(g, t);
			 };
			 //-->
		};
		t.data = data;
		if (g.isInit && t.col.useAutoFilter && fa.indexOf(t.getDataForFilter())==-1 && Def(t.data) && typeof(t.data) != 'unknown'){
			fa = t.col.filter;
			fa[fa.length] = t.getDataForFilter();
			fa.sort(compare);
			f = CT_fe(t.col.getID("filter"));
			if (Def(f)) f.outerHTML = t.col.filterToHTML();//ie, opera
			if (ua.moz) {t.row.grid.paint()};//can't add items in filter dynamicaly
		};
	};

	CCp.getData = function(){
		var t = this, data = t.data;

		if (Undef(t.data)) data = DEFAULT_RESULT;
		else
			switch (t.col.type){
				case "URL":
					data = formatURL(t.data, "blank");
					break;
				case "String":
				case "Number":
				case "HTML":
				case "Email":
				 	if (ua.oldB) eval("data = format" + t.col.type + "(t.data)")
					else eval("try{eval('data = format' + t.col.type + '(t.data)');}catch(e){data = t.data};");
					break;
				case "Image":
				   if ((Def(t.data) && t.data.constructor != Object) || ua.oldOpera) t.data = parseImage(t.data);
				   data = formatImage(t.data);
					break;
				case "Date":
				case "Currency":
				default:
					if (ua.oldB) eval("data = format" + t.col.type + "(t.data, " + t.col.type.toUpperCase() + "_FORMAT)")
					else eval("try{eval('data = format' + t.col.type + '(t.data, ' + t.col.type.toUpperCase() + '_FORMAT)');}catch(e){data = t.data};");
					break;
			};

		return data;
	};

	CCp.getDataForEdit = function(){
		var t = this, data = t.data;
		if (Undef(t.data) || t.col.type == "Image") data = ""
		else if (t.col.type == "Date") data = formatDate(t.data, DATE_FORMAT);
		return new String(data);
	};

	CCp.getDataForFilter = function(){
		var t = this, data = t.data;
		if (Undef(t.data)) data = ""
		else
			switch (t.col.type){
				case "Date":
				case "Currency":
					if (ua.oldB) eval("data = format" + t.col.type + "(t.data, " + t.col.type.toUpperCase() + "_FORMAT)")
					else eval("try{eval('data = format' + t.col.type + '(t.data, ' + t.col.type.toUpperCase() + '_FORMAT)');}catch(e){data = t.data};");
					break;
				case "Image":
				  if (t.data.src.indexOf("undefined") < 0){
						start = ((t.data.src.lastIndexOf("/") < 0)? t.data.src.lastIndexOf("\\") : t.data.src.lastIndexOf("/")) + 1;
				  	data = t.data.src.slice(start);
				  }else
				  	data = "No image";
				 default:
				 	data = t.data;
				};
		return new String(data);
	};

	CCp.compareTo = function (cell){
		return this.col.compareFunction(this.data, cell.data);
	};

	CCp.toString = function(){
		return "Cell [" + this.row._id + "; " + this.col._id + "]";
	};

	CCp.getID = function(){
		var t = this;
		return t.row.grid.getID("cell") + t.row._id + "_" + t.col._id;
	};

	CCp.getEditControl = function(){
		var t = this, h, o, v = t.getDataForEdit();
		if (t.isReadOnly || t.row.isReadOnly || t.col.isReadOnly || t.col.type == "Image"){
			//alert("This element can't be edited!");
			t.state = 1;
			return t.getData();
		}else{
			switch (t.col.type){
				case "Date":
					h = "<input id='cell' name='cell' type='text' style='width:100%;' value=\"" + v + "\" maxlength='" + DATE_FORMAT.length + "' onblur='CodeThatGrids[\"" + t.row.grid.name + "\"].curCell.setData(this.value)'>";
					break;
				case "Currency":
				case "Number":
					h = "<input id='cell' name='cell' type='text' style='width:100%;' value=\"" + v + "\" maxlength='10' onblur='CodeThatGrids[\"" + t.row.grid.name + "\"].curCell.setData(this.value)'>";
					break;
				case "String":
				case "HTML":
				case "Email":
				case "URL":
				default:
				  h = "<textarea id='cell' name='cell' rows=1 cols=1 style='width:100%;height:100%' onblur='CodeThatGrids[\"" + t.row.grid.name + "\"].curCell.setData(this.value);'>" + v + "</textarea>";
					break;
			};
		};
		return h;
	};

	CCp.setState = function(state){
		var t = this, o = CT_fe("cell");rng.length=0;
		if (Def(o) && t.state == 2) t.setData(o.value);
		t.state = state;
		t.paint();
	};

	CCp.setMark = function(isMark){
		var t = this;
		if (Undef(isMark)) isMark = !t.isMark;
		t.isMark = isMark;
		t.paint();
	};

	CCp.setStyle = function(param, value){
		var t = this;
		switch (param){
				case "b":
					if (Def(t.b)) t.b = "";
					else t.b = "bold";
					break;
				case "i":
					if (Def(t.i)) t.i = "";
					else t.i = "italic";
					break;
				case "u":
					if (Def(t.u)) t.u = "";
					else t.u = "underline";
					break;
				case "alignment":
					t.alignment = value;
					break;
		};
		t.paint(0);
	};

	CCp.getStyle = function(){
		var t = this, s = "";
		if (Def(t.b)) s += "font-weight:bold;";
		if (Def(t.i)) s += "font-style:italic;";
		if (Def(t.u)) s += "text-decoration:underline;";
		if (Def(t.alignment)) s += "text-align:" + t.alignment + ";";
		if (Def(s)) s = "style='" + s + "'";
		return s;
	};

	CCp.paint = function(p){ //np - paint parent
		var t = this, g = t.row.grid,	o1=1, o2=1, o = CT_fe(t.getID()),
		rm = t.row.markClass, cm = t.col.markClass, rd = t.row.defaultClass, cd = t.col.defaultClass;

		if (Undef(p)) p = 1 && g.useRCID;
		if (p){
			o1 = CT_fe(t.row.getID('row'));
			o2 = CT_fe(t.col.getID('col'));
		};

		if (Undef(o) || Undef(o1) || Undef(o2)) return;

		o.style.fontWeight = t.b;
		o.style.fontStyle = t.i;
		o.style.textDecoration = t.u;
		o.style.textAlign = t.alignment;

		switch (t.state){
			case 0:
				CT_css(o, ((t.isMark) ? t.markClass:t.defaultClass));
				CT_HTML(o, t.getData());
				if (p){
				 CT_css(o1, ((t.isMark) ? rm:rd));
				 CT_css(o2, ((t.isMark) ? cm:cd));
				};
				break;
			case 1:
				CT_css(o, t.currClass);
				if (p){
					CT_css(o1, rm);
					CT_css(o2, cm);
				};
				CT_HTML(o, t.getData());
				break;
			case 2:
				CT_HTML(o, t.getEditControl());
				o = CT_fe("cell");
				if (Def(o)) {
					//if (ua.moz && o.type=='textarea') 
					if (ua.moz) o.setAttribute("autocomplete", "off");
					o.value=t.getDataForEdit();									
					o.focus();
				};
				break;
		};
		if (p) g.paintBar("sb");
	};
};
///////////////////////////////////////////////////
//Events
///////////////////////////////////////////////////
var isShift = 0, isCtrl = 0, CCG, CodeThatGrids = [], re = "class=", rng = [],w = null, curId=0;
var dfp = {fontfamily:"Verdana, Arial", fontsize:"12px"},
	 crp = {borderwidth: "1", borderstyle: "outset", bordercolor: "#cccccc"},
	 clp = { borderstyle:"solid"},
	 dbp = {fontfamily:"Verdana, Arial", fontsize:"11px", backgroundcolor:"#D4D0C8"};
function CT_fe(id){
	return CodeThat.findElement(id);
};
function CT_reh(ev, h, o){
	CodeThat.regEventHandler(ev, h, o);
};
function CT_regEvents(name, pt){
	CT_reh('click', CT_onMouseEvent, CT_fe(name));
	CT_reh('dblclick', CT_onMouseEvent, CT_fe(name));
	CT_reh('keydown', CT_onKeyPress);
	CT_reh('selectstart', CT_onSelect, CT_fe(name));
	CT_reh('mousemove', CT_onMouseMove);
	CT_reh('mousedown', CT_onMouseEvent);
	CT_reh('mouseup', CT_onMouseEvent);
	CT_reh('dragend', CT_onDrag, CT_fe(name));
	CT_reh('dragover', CT_onDragOver, CT_fe(name));
	CT_reh('dragover', CT_onDragOver, CT_fe(pt));
};
function CT_onDrag(e){
	var src = (ua.moz) ? e._e.target : e._e.srcElement;
	var parent, s = src.id, t = curId, si, ti;
	if (Undef(t) || Undef(s)) return;
	if (Undef(CCG)){
		var parent = CT_getParent(src);
		if (Undef(parent.id)) return;
		CCG = CodeThatGrids[parent.id];
	};
	if (Undef(CCG)) return;
	if (t.indexOf("col") > -1 && s.indexOf("col") > -1){
		t = t.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '');
		s = s.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '');
		si = CCG.cIdx.indexOf(s);
		ti = CCG.cIdx.indexOf(t);
		CCG.cIdx.remove(si);CCG.cIdx.insert(s, ti);
	};
	if (t.indexOf("row") > -1 && s.indexOf("row") > -1){
		t = t.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '');
		s = s.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '');
		si = CCG.rIdx.indexOf(s);
		ti = CCG.rIdx.indexOf(t);
		CCG.rIdx.remove(si);CCG.rIdx.insert(s, ti); CCG.vr.length = 0;
	};
	if (t.indexOf("pt") > -1 && s.indexOf("row") > -1){
		s = s.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '');
		si = CCG.rIdx.indexOf(s);
		ti = (t.replace(new RegExp(CCG.name), "").replace(/[^0-9]/ig, '')-1)*CCG.amountPerPage;
		t = CCG.rIdx[ti];
		CCG.rIdx.remove(si);CCG.rIdx.insert(s, ti); CCG.vr.length = 0;
	};
	curId = "";
	CCG.paint();
};
function CT_onDragOver(e){
	var src = (ua.moz) ? e._e.target : e._e.srcElement;
	curId = src.id;		
};
function CT_onSelect(e){
	var src = (ua.moz) ? e._e.target : e._e.srcElement;
	if (src.id != 'cell') return false;
};
function CT_onMouseMove(e){
	var src = (ua.moz) ? e._e.target : e._e.srcElement;
	if (Undef(src.id) || Undef(CCG)) return;
	var name = src.id.replace(new RegExp(CCG.name), "").replace(/[^0-9_]/ig, ""), r, r1, c, c1;
	if (Undef(name) || name.indexOf("_") == -1 || rng.length < 2) return;

	r = name.slice(0, name.indexOf("_"));
	r1 = rng[0];
	c = name.slice(name.indexOf("_") + 1);
	c1 = rng[1];

	r = CCG.rIdx.indexOf(r);
	r1 = CCG.rIdx.indexOf(r1);
	c = CCG.cIdx.indexOf(c);
	c1 = CCG.cIdx.indexOf(c1);

	CCG.msg = "Selection: " + (1+Math.abs(r-r1)) + "R x " + (1+Math.abs(c-c1)) + "C";
	CCG.paintBar("sb");
};

function CT_onMouseEvent(e){
	isShift = e._e.shiftKey;
	isCtrl = e._e.ctrlKey;

	var src = (ua.moz) ? e._e.target : e._e.srcElement;
	if (Undef(src.id) && ua.moz) src = src.parentNode;
	var parent = CT_getParent(src);

	if (Undef(src.id) || Undef(parent.id)) {rng.length=0;return;} //no object of grid to proceed

	if (Def(CCG) && CCG != CodeThatGrids[parent.id] && CCG.curCell) CCG.curCell.setState(1); 
	CCG = CodeThatGrids[parent.id];
	if (Undef(CCG)) {rng.length=0;return;}

	var name = src.id.replace(new RegExp(CCG.name), '').replace(/[^0-9_]/ig, ""), r = -1 , c = -1, type = -1;
	if (Undef(name)) return;

	if (name.indexOf("_") > -1){
		r = name.slice(0, name.indexOf("_"));
		c = name.slice(name.indexOf("_") + 1);
		type = 0;
	}else{
		if (src.id.indexOf("col") > -1) {c = name; type = 1};
		if (src.id.indexOf("row") > -1) {c = name; type = 2};
		if (src.id.indexOf("title") > -1) {c = name; type = 4};
	};

	//call user handler
	switch (type){
		case 0: e._o = CCG.cells[r][c]; CCG.cells[r][c].callHandler(e); break;
		case 2: e._o = CCG.rows[c]; CCG.rows[c].callHandler(e); break;
		case 1: e._o = CCG.cols[c]; CCG.cols[c].callHandler(e); break;
	};

	switch (e._e.type){
		case "click":
			switch (type){
				case 0:
					break;
				case 1:
				case 2:
					CCG.setMarkRange(c, type);
					break;
				case 4:
					CCG.cols[c].setTitle();
			};
			break;
		 case "dblclick":
		  switch (type){
		  	case 0:
		  		CCG.setCurCell(CCG.cells[r][c]);
		  		CCG.cells[r][c].setState(2);
		  		break;
		  	case 1:
		  	   CCG.cols[c].setWidth();
		  		break;
				case 2:
				CCG.rows[c].setHeight();
				break;
		  };
		 	break;
		 case "mousedown":
		  if (type != 0) return;
		  CCG.setCurCell(CCG.cells[r][c]);
		  if (CCG.cells[r][c].state != 2) rng = [r, c];
		  break;
		 case "mouseup":
		 	if (type != 0 || rng.length < 2) {rng.length = 0; return;}
		 	CCG.setMarkRange(rng.concat([r,c]), 3);
		 	CCG.msg = "";
		 	CCG.paintBar("sb");
		 	rng.length=0;
		 	break;
	};
};
function CT_onKeyPress(e){
	if (Def(CCG)) {CCG.onKeyPress(e);}
};
function CT_getParent(src){
	while (Def(src) && Def(src.tagName) && src.tagName.toLowerCase() != "div" && src.tagName.toLowerCase() != "body"){
		src = ((ua.ie) ? src.parentElement : src.parentNode);
	};
	return src;
};
{
	var a = Array.prototype;
	a.insert = function(e, i){
		var t = this, j;
		t[t.length] = e;
		if (i < t.length - 1){
			for (j = t.length - 1; j > i; j--) t[j] = t[j-1];
			t[i] = e;
		};
	};
	a.remove = function(i){
		var t = this, j;
		if (i < t.length - 1)
			for (j = i; j < t.length - 1; j++) {
				t[j] = t[j*1+1];
				if (Def(t[j]._id)) t[j]._id = j;
			};
		t.length--;
	};
};
function makeRE(text){
	var special = ["\\", "/", ".", "^", "+", "*", "?", "$", "|", "(", ")", "{", "}"], i;
	for (i = 0; i < special.length; i++)
	 text = text.replace(new RegExp("\\" + special[i], "ig"), "\\" + special[i]);
	return text;
};
function merge(aO){
	var i, j, o = {};
	if (aO.constructor != Array) aO = new Array(aO);
	for (i = 0; i < aO.length; i++)
		for (j in aO[i]) o[j] = aO[i][j];
	return o;
};