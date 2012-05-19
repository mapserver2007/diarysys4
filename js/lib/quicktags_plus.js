// === Quicktags Plus version 1.0.3b
// === Copyright (c) 2006 cie
//
// Library Name: Quicktags Plus
// Library URI: http://blog.livedoor.jp/cie/archives/50417516.html
// Description: Inserts a quicktag toolbar in the blog comment form. And Tag conversion.
// Version: 1.0.3 Beta
// Last Modified: October 22, 2006
// Author: cie
// Author URI: http://blog.livedoor.jp/cie/
//
//
// ** This Library is modified code from Alex King's JS Quicktags. **
// ----------------------------------------------------------------------------------------
// Library Name: JS QuickTags
// Library URI: http://www.alexking.org/index.php?content=software/javascript/content.php
// Description: Inserts a quicktag toolbar on the textarea.
// Version: 1.2
// Author: Alex King
// Author URI: http://www.alexking.org/
//
//
// ** Licensed under the LGPL license **
// http://www.gnu.org/copyleft/lesser.html (English)
// http://www.opensource.jp/lesser/lgpl.ja.html (Japanese)
// ----------------------------------------------------------------------------------------
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ----------------------------------------------------------------------------------------
//
// IE, Mozilla, Firefox, Netscape, Camino, Operaではカーソル位置にタグが挿入されますが
// Safari, OmniWebなどでは一番最後にタグが挿入されるようです。
//
// また、'edCanvas'は必ず<textarea>タグより後ろで定義しなくてはいけません。
// 使用例に関しましては同梱している'test.html'をご覧になってください。
//
// ----------------------------------------------------------------------------------------
// 拡張タグのONOFF機能をつけた。拡張タグ機能が必要ない場合はOFFに。(デフォルトはONになってます。
// もしかすると絵文字機能も統合するかも……はしない方向へ……
// ----------------------------------------------------------------------------------------

var edButtons = new Array();
var edOpenTags = new Array();
// デフォルトでは動かないので、問い合わせごとに要素を取得する方法に変更
var edCanvasId = 'ds_description_form';

// バージョン情報
var LibraryVersion = "1.0.3b";
// 初期ヘルプメッセージ
var HelpMessage = "注意：HTMLタグ・JavaScript等は使用不可";
// 拡張タグのONOFFフラグ 1ならON、0やそれ以外ならOFF
var exTagFlag = 0;

function getCanvasElement(){
	return document.getElementById(edCanvasId);
}

function edButton(id, display, help, tagStart, tagEnd, optStart, optEnd, access, visibility, open) {
	this.id = id;			// used to name the toolbar button
	this.display = display;		// label on button
	this.help = help;		// help message 
	this.tagStart = tagStart; 	// open tag
	this.tagEnd = tagEnd;		// close tag
	this.optStart = optStart; 	// open option
	this.optEnd = optEnd;		// close option
	this.access = access;		// accesskey
	this.visibility = visibility;	// button visibility flag: visible:1 hide:0
	this.open = open;		// set to -1 if tag does not need to be closed
}

//----------------------- 使用するタグ設定ここから
edButtons.push(
	new edButton(
		'ed_fontcolor'
		,'色'
		,'color：文字色を変更します'
		,'[color]'
		,'[/color]'
		,''
		,''
		,'o'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_bold'		// inputのid名
		,'太'			// inputのvalue部
		,'strong：文字を太字にします'	// ヘルプメッセージ
		,'[strong]'		// 開始タグ
		,'[/strong]'		// 終了タグ
		,''			// オプション開始タグ
		,''			// オプション終了タグ
		,'b'			// accesskeyの文字(無くても大丈夫)
		,1			// ボタン表示フラグ(表示：1 非表示：0)
	)
);

edButtons.push(
	new edButton(
		'ed_italic'
		,'斜'
		,'em：文字を斜体にします'
		,'[em]'
		,'[/em]'
		,''
		,''
		,'i'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_under'
		,'下線'
		,'u：文字に下線を引きます'
		,'[u]'
		,'[/u]'
		,''
		,''
		,'u'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_strike'
		,'打消'
		,'del：文字に打ち消し線を引きます'
		,'[del]'
		,'[/del]'
		,''
		,''
		,'d'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_big'
		,'大'
		,'big：文字をひとまわり大きく表示します'
		,'[big]'
		,'[/big]'
		,''
		,''
		,'g'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_small'
		,'小'
		,'small：文字をひとまわり小さく表示します'
		,'[small]'
		,'[/small]'
		,''
		,''
		,'s'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_image'
		,'画像'
		,'image：画像URLと代替テキストを入力してください'
		,'[image]'
		,'[/image]'
		,''
		,''
		,'m'
		,1
		,-1
	)
);

edButtons.push(
	new edButton(
		'ed_anchor'
		,'リンク'
		,'url：リンク先URLとリンクテキストを入力してください'
		,'[url]'
		,'[/url]'
		,''
		,''
		,'a'
		,1
		,-1
	)
);

edButtons.push(
	new edButton(
		'ed_s_anchor'
		,'スナップ'
		,'snap：スナップショット適用リンク先URLを入力してください(リンクの後に実行推奨)'
		,'[snap]'
		,'[/snap]'
		,''
		,''
		,'a'
		,1
		,-1
	)
);

edButtons.push(
	new edButton(
		'ed_block'
		,'引用'
		,'blockquote：引用したい文章を入力します'
		,'[blockquote]'
		,'[/blockquote]'
		,''
		,''
		,'q'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_heading'
		,'見出'
		,'h4：見出しです'
		,'[h4]'
		,'[/h4]'
		,''
		,''
		,'4'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_paragraph'
		,'段'
		,'p：段落です'
		,'[p]'
		,'[/p]'
		,''
		,''
		,'p'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_ulist'
		,'一覧'
		,'ul：リストで使用する項目数を半角数字で入力し、項目の内容を入力して下さい'
		,'[ul]'
		,'[/ul]'
		,'[li]'
		,'[/li]'
		,'l'
		,1
		,-1
	)
);

edButtons.push(
	new edButton(
		'ed_left'
		,'左'
		,'left：左寄せの段落に書きます'
		,'[left]'
		,'[/left]'
		,''
		,''
		,'f'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_center'
		,'中'
		,'center：真ん中寄せの段落に書きます'
		,'[center]'
		,'[/center]'
		,''
		,''
		,'c'
		,1
	)
);
edButtons.push(
	new edButton(
		'ed_right'
		,'右'
		,'right：右寄せの段落に書きます'
		,'[right]'
		,'[/right]'
		,''
		,''
		,'r'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_hr'
		,'水平'
		,'hr：水平線を挿入します'
		,'[hr /]'
		,''
		,''
		,''
		,'h'
		,1
		,-1
	)
);

edButtons.push(
	new edButton(
		'ed_code'
		,'コード'
		,'code：ソースコードを色分けして表示します'
		,'[code]'
		,'[/code]'
		,''
		,''
		,'e'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_amazon'
		,'amazon'
		,'amazon：amazonから書籍を検索します'
		,'[amazon]'
		,'[/amazon]'
		,''
		,''
		,'z'
		,1
	)
);

edButtons.push(
	new edButton(
		'ed_carousel'
		,'carousel'
		,'carousel：ファイルアーカイブスから画像を取得します'
		,'[carousel]'
		,'[/carousel]'
		,''
		,''
		,'j'
		,1
	)
);

//---------------------- 使用するタグ設定ここまで

var extendedStart = edButtons.length;

// below here are the extended buttons
//---------------------- 使用する拡張タグ設定ここから

edButtons.push(
	new edButton(
		'ed_style'
		,'飾'
		,'style：スタイルと装飾したいテキストを入力して下さい'
		,'[style]'
		,'[/style]'
		,''
		,''
		,'y'
		,0
		,-1
	)
);


//---------------------- 使用する拡張タグ設定ここまで

function edShowButton(button, i) {
	if (button.access) {
		var accesskey = ' accesskey = "' + button.access + '"'
	}
	else {
		var accesskey = '';
	}
	// ボタン表示フラグ
	if (button.visibility == 1){
		switch (button.id) {
			case 'ed_image':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertImage(getCanvasElement());" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_anchor':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertLink(getCanvasElement());" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_s_anchor':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertSnapLink(getCanvasElement());" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_style':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertStyle(getCanvasElement());" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_ulist':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertList(getCanvasElement());" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_fontcolor':
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="paletteHideShow();" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				ColorPalette();
				break;
			case 'ed_amazon':
				var division = "amazon";
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="ds.quicktags_plus_extension.start(\'' + division + '\');" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			case 'ed_carousel':
				var division = "carousel";
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="ds.quicktags_plus_extension.start(\'' + division + '\');" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
			default:
				document.write('<input type="button" id="' + button.id + '" ' + accesskey + ' class="ed_button" onclick="edInsertTag(getCanvasElement(), ' + i + ');" value="' + button.display + '" onmouseover="showHelpMsg(' + i + ');" onmouseout="hideHelpMsg();" />');
				break;
		}
	}
}

function edAddTag(button) {
	if (edButtons[button].tagEnd != '') {
		edOpenTags[edOpenTags.length] = button;
		document.getElementById(edButtons[button].id).value = '/' + document.getElementById(edButtons[button].id).value;
		document.getElementById(edButtons[button].id).style.backgroundColor = "#ccc";
	}
}

function edRemoveTag(button) {
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			edOpenTags.splice(i, 1);
			document.getElementById(edButtons[button].id).value = document.getElementById(edButtons[button].id).value.replace('/', '');
			document.getElementById(edButtons[button].id).style.backgroundColor = '';
		}
	}
}

function edCheckOpenTags(button) {
	var tag = 0;
	for (i = 0; i < edOpenTags.length; i++) {
		if (edOpenTags[i] == button) {
			tag++;
		}
	}
	if (tag > 0) {
		return true; // tag found
	}
	else {
		return false; // tag not found
	}
}	

function edCloseAllTags() {
	var count = edOpenTags.length;
	for (o = 0; o < count; o++) {
		edInsertTag(getCanvasElement(), edOpenTags[edOpenTags.length - 1]);
	}
}

function edToolbar() {
	document.write('<div id="ed_toolbar"><span id="ed_stand_bttons">');
	for (var i = 0; i < extendedStart; i++) {
		edShowButton(edButtons[i], i);
	}

	if (exTagFlag == 1) {
		if (edShowExtraCookie()) {
			document.write(
				'<input type="button" id="ed_close" class="ed_button" title="閉じられてないタグを全て閉じる" onclick="edCloseAllTags();" value="タグを閉じる" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
				+ '<input type="button" id="ed_extra_show" class="ed_button" title="拡張タグを開く" onclick="edShowExtra()" value="&raquo;" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
				+ '</span><br />'
				+ '<span id="ed_extra_buttons">'
				+ '<input type="button" id="ed_extra_hide" class="ed_button" title="拡張タグを閉じる" onclick="edHideExtra();" value="&laquo;" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
			);
		}
		else {
			document.write(
				'<input type="button" id="ed_close" class="ed_button" title="閉じられてないタグを全て閉じる" onclick="edCloseAllTags();" value="タグを閉じる" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
				+ '<input type="button" id="ed_extra_show" class="ed_button" title="拡張タグを開く" onclick="edShowExtra()" value="&raquo;" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
				+ '</span><br />'
				+ '<span id="ed_extra_buttons" style="display: none;">'
				+ '<input type="button" id="ed_extra_hide" class="ed_button" title="拡張タグを閉じる" onclick="edHideExtra();" value="&laquo;" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
			);
		}
		for (var i = extendedStart; i < edButtons.length; i++) {
			edShowButton(edButtons[i], i);
		}
		document.write(
//				'&nbsp;<a href="http://blog.livedoor.jp/cie/" title="Quicktags Plus" onmouseover="document.getElementById(\'helpmsg\').value=\'Goto Author Website.\';" onmouseout="hideHelpMsg();">v' + LibraryVersion + '</a>'
				+ '</span>'
				+ '<input type="text" id="helpmsg" name="helpmsg" size="80" readonly="readonly" value="' + HelpMessage + '" />'
		);
	}
	else {
		document.write(''
				+ '<input type="button" id="ed_close" class="ed_button" title="閉じられてないタグを全て閉じる" onclick="edCloseAllTags();" value="タグを閉じる" onmouseover="document.getElementById(\'helpmsg\').value=this.title;" onmouseout="hideHelpMsg();" />'
				+ '</span>'
//				+ '&nbsp;<a href="http://blog.livedoor.jp/cie/" title="Quicktags Plus" onmouseover="document.getElementById(\'helpmsg\').value=\'Goto Author Website.\';" onmouseout="hideHelpMsg();">v' + LibraryVersion + '</a>'
				+ '<input type="text" id="helpmsg" name="helpmsg" size="80" readonly="readonly" value="' + HelpMessage + '" />'
		);
	}
	document.write('</div>');
}

function edShowExtra() {
	document.getElementById('ed_extra_show').style.visibility = 'hidden';
	document.getElementById('ed_extra_buttons').style.display = 'block';
	edSetCookie(
		'js_quicktags_extra'
		, 'show'
		, new Date("December 31, 2100")
	);
}

function edHideExtra() {
	document.getElementById('ed_extra_buttons').style.display = 'none';
	document.getElementById('ed_extra_show').style.visibility = 'visible';
	document.getElementById('color_palette').style.display = 'none';
	edSetCookie(
		'js_quicktags_extra'
		, 'hide'
		, new Date("December 31, 2100")
	);
}

// insertion code

function edInsertTag(myField, i) {
	//IE support
	if (document.selection) {
		myField.focus();
	    	sel = document.selection.createRange();
		if (sel.text.length > 0) {
			sel.text = edButtons[i].tagStart + sel.text + edButtons[i].tagEnd;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				sel.text = edButtons[i].tagStart;
				edAddTag(i);
			}
			else {
				sel.text = edButtons[i].tagEnd;
				edRemoveTag(i);
			}
		}
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = myField.scrollTop;
		if (startPos != endPos) {
			myField.value = myField.value.substring(0, startPos)
			              + edButtons[i].tagStart
			              + myField.value.substring(startPos, endPos) 
			              + edButtons[i].tagEnd
			              + myField.value.substring(endPos, myField.value.length);
			cursorPos += edButtons[i].tagStart.length + edButtons[i].tagEnd.length;
		}
		else {
			if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagStart
				              + myField.value.substring(endPos, myField.value.length);
				edAddTag(i);
				cursorPos = startPos + edButtons[i].tagStart.length;
			}
			else {
				myField.value = myField.value.substring(0, startPos) 
				              + edButtons[i].tagEnd
				              + myField.value.substring(endPos, myField.value.length);
				edRemoveTag(i);
				cursorPos = startPos + edButtons[i].tagEnd.length;
			}
		}
		myField.focus();
		myField.selectionStart = cursorPos;
		myField.selectionEnd = cursorPos;
		myField.scrollTop = scrollTop;
	}
	else {
		if (!edCheckOpenTags(i) || edButtons[i].tagEnd == '') {
			myField.value += edButtons[i].tagStart;
			edAddTag(i);
		}
		else {
			myField.value += edButtons[i].tagEnd;
			edRemoveTag(i);
		}
		myField.focus();
	}
}

function edInsertContent(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		var scrollTop = myField.scrollTop;
		myField.value = myField.value.substring(0, startPos)
		              + myValue 
                      + myField.value.substring(endPos, myField.value.length);
		myField.focus();
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
		myField.scrollTop = scrollTop;
	} else {
		myField.value += myValue;
		myField.focus();
	}
}
// リンク
function edInsertLink(myField) {
	var myURL = prompt('リンクするURLを入力してください' , 'http://');
	var txt;

	if (myURL) {
		var data = myURL.match(/(^http|^ftp|^https):\/\/.+/);
		var data2 = myURL.match(/\s|"|\<|\>/);
		if (!data || data2){
			alert("URLを正しく入力してください。");
		}
		else {
			txt = prompt('リンクテキスト（サイトのタイトルなど）を入力してください。\n入力せずOKを押すとアドレスがそのまま表示されます。','');
			if (txt == null) return;
			else if (!txt) {
				myURL = '\[url\]' + myURL + '\[\/url\]';
				edInsertContent(myField, myURL); 
			}
			else if (txt != null){
				myURL = '\[url\=' + myURL + '\]' + txt + '\[\/url\]';
				edInsertContent(myField, myURL); 
			}
		}
	}
}
// スナップ
function edInsertSnapLink(myField) {
	var myURL = prompt('リンクするURLを入力してください' , 'http://');
	var txt;

	if (myURL) {
		var data = myURL.match(/(^http|^ftp|^https):\/\/.+/);
		var data2 = myURL.match(/\s|"|\<|\>/);
		if (!data || data2){
			alert("URLを正しく入力してください。");
		}
		else {
			myURL = '\[snap\]' + myURL + '\[\/snap\]';
			edInsertContent(myField, myURL); 
		}
	}
}

// 画像
function edInsertImage(myField) {
	var myValue = prompt('画像のURLを入力してください。', 'http://');
	var alt;
	
	if (myValue) {
		var data = myValue.match(/^http:\/\/.+\.(gif|jpe?g|png|bmp)$/);
		var data2 = myValue.match(/\s|"|\<|\>|\[|\]|\{|\}/);
		if (!data || data2){
			alert("画像URLは正しく入力してください。");
		}
		else {
			alt = prompt('代替テキスト（画像の説明など）を入力してください。\n入力せずOKを押すとURLが代替テキストに設定されます。','');
			if (alt == null) return;
			else if (!alt) {
				myValue = '\[image\]' + myValue + '\[\/image\]';
				edInsertContent(myField, myValue);
			}
			else if (alt != null){
				myValue = '\[image\=' + myValue + '\]' + alt + '\[\/image\]';
				edInsertContent(myField, myValue);
			}
		}
	}
}
// スタイル
function edInsertStyle(myField) {
	var myStyle = prompt('スタイルを入力してください。例：color: #c30;' , '');
	var txt;
	
	if(myStyle) {
		var data = myStyle.match(/\;$/);
		var data2 = myStyle.match(/^\W/);
		if (!data || data2) {
			alert("スタイルを正しく記述して下さい。");
		}
		else {
			txt = prompt('装飾したいテキストを入力してください。','');
			if (!txt) {
				alert("テキストを入力して下さい。");
			}
			else if (txt != null){
				myStyle = '\[style\=' + myStyle + ']' + txt + '\[\/style\]';
				edInsertContent(myField, myStyle);
			}
		}
	}
}
// リスト
function edInsertList(myField) {
	var myList;
	var myItem = '';
	var listItem;
	var i = prompt('リストで使用する項目数を半角数字で入力してください。' , '');
	
	if (i) {
		var data = i.match(/\d/);
		var k = i;
		if(!data){
			alert("半角数字で入力してください");
		}
		else{
			for(j = 0; j < i; j++){
				var l = j+1;

				listItem = prompt('' + l + 'つ目の項目の内容を入力してください。残り' + k + 'つの項目。','');
				k--;
				
				if (listItem == null) {
					break;	// 途中でキャンセル
				}
				else{
					myItem = myItem + '[li]' + listItem + '[/li]';
				}
			}
			if (listItem != null) {
				myList = '[ul]' + myItem + '[/ul]';
				edInsertContent(myField, myList); 
			}
		}
	}
}
// 色パレット
function ColorPalette() {

	document.write('<div id=\"color_palette\" style=\"display: none; position: absolute; background-color: #eee; width: 202px; border:1px solid #666;\"><ul>');
	document.write('<li id=\"sample\">Sample Text</li>');
	var Colors = new Array(
			"000","030","060","090","0C0","0F0","300","330","360","390","3C0","3F0","600","630","660","690","6C0","6F0",
			"003","033","063","093","0C3","0F3","303","333","363","393","3C3","3F3","603","633","663","693","6C3","6F3",
			"006","036","066","096","0C6","0F6","306","336","366","396","3C6","3F6","606","636","666","696","6C6","6F6",
			"009","039","069","099","0C9","0F9","309","339","369","399","3C9","3F9","609","639","669","699","6C9","6F9",
			"00C","03C","06C","09C","0CC","0FC","30C","33C","36C","39C","3CC","3FC","60C","63C","66C","69C","6CC","6FC",
			"00F","03F","06F","09F","0CF","0FF","30F","33F","36F","39F","3CF","3FF","60F","63F","66F","69F","6CF","6FF",
			"900","930","960","990","9C0","9F0","C00","C30","C60","C90","CC0","CF0","F00","F30","F60","F90","FC0","FF0",
			"903","933","963","993","9C3","9F3","C03","C33","C63","C93","CC3","CF3","F03","F33","F63","F93","FC3","FF3",
			"906","936","966","996","9C6","9F6","C06","C36","C66","C96","CC6","CF6","F06","F36","F66","F96","FC6","FF6",
			"909","939","969","999","9C9","9F9","C09","C39","C69","C99","CC9","CF9","F09","F39","F69","F99","FC9","FF9",
			"90C","93C","96C","99C","9CC","9FC","C0C","C3C","C6C","C9C","CCC","CFC","F0C","F3C","F6C","F9C","FCC","FFC",
			"90F","93F","96F","99F","9CF","9FF","C0F","C3F","C6F","C9F","CCF","CFF","F0F","F3F","F6F","F9F","FCF","FFF");

	for(var i = 0; i < Colors.length; i++){
		document.write('<li title=\"#' + Colors[i] + '\" style=\"background-color: #' + Colors[i] + '; height: 10px; width:10px; line-height: 10px;margin: 0 0 1px 1px;\" onclick=\"edFontColor(getCanvasElement(), \'' + Colors[i] + '\');\" onmouseover=\"SampleColorChange(\'' + Colors[i] + '\');\">&nbsp;</li>');
	}

	document.write('<li class=\"close\" onclick=\"paletteHideShow();\" onmouseout="this.style.backgroundColor=\'\';  document.getElementById(\'sample\').innerHTML=\'Sample Text\';" onmouseover=\"this.style.backgroundColor=\'#ccc\'; SampleColorChange(\'666\'); document.getElementById(\'sample\').innerHTML=\'パレットを閉じる\';\">close x</li>');
	document.write('</ul></div>');
}
function paletteHideShow() {
	var myPalette = (document.all)? document.all('color_palette') : document.getElementById('color_palette');
	
	if (myPalette.style.display == 'none') myPalette.style.display = 'block';
	else myPalette.style.display = 'none';
}
function SampleColorChange(color) {
	var mySample = (document.all)? document.all('sample'):document.getElementById('sample');

	mySample.style.color = '#' + color;
}
function edFontColor(myField, color) {
	var openColorTag = '\[color\=\#' + color + '\]';
	var closeColorTag = '\[\/color\]';

	myField.focus();

	if (document.selection) {
		sel = document.selection.createRange().text;
		if (!sel) {
			document.selection.createRange().text = openColorTag + 'テキスト' + closeColorTag;
			myField.focus();
			paletteHideShow();
			return;
		}
		document.selection.createRange().text = openColorTag + sel + closeColorTag;
		myField.focus();
		paletteHideShow();
		return;
	}
	else if (myField.selectionEnd && (myField.selectionEnd - myField.selectionStart > 0)) {
		mozWrap(myField, openColorTag, closeColorTag);
		paletteHideShow();
		return;
	}
	else {
		myField.value += openColorTag + 'テキスト' + closeColorTag;
		myField.focus();
		paletteHideShow();
	}
	storeCaret(myField);
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}
// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close) {
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2) {
		selEnd = selLength;
	}
	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	return;
}

// Cookie
function edSetCookie(name, value, expires, path, domain) {
	document.cookie= name + "=" + escape(value) +
		((expires) ? "; expires=" + expires.toGMTString() : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "");
}

function edShowExtraCookie() {
	var cookies = document.cookie.split(';');
	for (var i=0;i < cookies.length; i++) {
		var cookieData = cookies[i];
		while (cookieData.charAt(0) ==' ') {
			cookieData = cookieData.substring(1, cookieData.length);
		}
		if (cookieData.indexOf('js_quicktags_extra') == 0) {
			if (cookieData.substring(19, cookieData.length) == 'show') {
				return true;
			}
			else {
				return false;
			}
		}
	}
	return false;
}

function countInstances(string, substr) {
	var count = string.split(substr);
	return count.length - 1;
}


// ----------------------------------------------------------------------------------------
// Tag変換用スクリプト
// ----------------------------------------------------------------------------------------
//--------Tag変換ココから
function changeCommentTags() {
	// ***********************************************
	// （１）コメント全体のタグの id 属性
	var commentAreaIdName = 'left_container';
	// （２）コメント全体のタグ名
	var commentAreaTag = 'div';
	// （３）各コメントの class 属性
	var commentBodyClassName = 'ds_entry_description_quick'; //BBコードからHTMLタグに変換許可するclass
	// ***********************************************
	var tags = getCommentTags(edButtons);
	var objComArea;
	var classComArea;
	var objComBody = new Array();
	if (commentAreaIdName != '') {
		objComArea = document.getElementById(commentAreaIdName);
	if (!objComArea || !objComArea.innerHTML) return;
		classComArea = objComArea.getElementsByTagName(commentAreaTag);
	} else {
		classComArea = document.getElementsByTagName('*');
	}
	for (var i = 0; i < classComArea.length; i++) {
		if (classComArea[i].className == commentBodyClassName) objComBody[objComBody.length] = classComArea[i];
	}
	for (var i = 0; i < objComBody.length; i++) {
		objComBody[i].innerHTML = getStringCommentTags(tags, objComBody[i].innerHTML);
	}
}

// tagを設定(抽出)する設定
function getCommentTags(buttons){
	var tags = new Array();

	for(var i = 0; i < buttons.length; i++) {
//		if(buttons[i].visibility == 1){
			tags[tags.length] = new Array(strSubstring(buttons[i].tagStart));
			if(buttons[i].tagEnd != ''){
				tags[tags.length] = new Array(strSubstring(buttons[i].tagEnd));
			}
			
			if(buttons[i].optStart != ''){
				tags[tags.length] = new Array(strSubstring(buttons[i].optStart));
				tags[tags.length] = new Array(strSubstring(buttons[i].optEnd));
			}
//		}
	}

	return tags;
}
// 疑似タグからHTMLタグへ変換する関数
function getStringCommentTags(tags, s) {
	var regColor = /\[color\=\#([0-9a-fA-F]{3})\]/g;
	var regImage = /\[image\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))\]/g;
	var regImage2 = /\[image\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))/g;
	var regURL = /\[url\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)\]/g;
	var regURL2 = /\[url\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)/g;
	var regSnapURL = /\[snap\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)/g;
	var regStyle = /\[style\=([a-zA-Z0-9\:\;\ \-\_\'\!\*\(\)\/\.\,\%\#]+\;)\]/g;

	for (var i = 0; i < tags.length; i++) {
		// Color
		if(tags[i] == "color") {
			s = s.replace(regColor, '<span style=\"color: \#$1;\">');
		} else if(tags[i] == "/color") {
			s = s.replace(/\[\/color\]/g, '<\/span>');
		}
		// left center right
		else if(tags[i] == "left" || tags[i] == "center" || tags[i] == "right"){
			s = s.split('[' + tags[i] + ']').join('<p style="text-align: ' + tags[i] + ';">');
		} else if(tags[i] == "/left" || tags[i] == "/center" || tags[i] == "/right"){
			s = s.split('[' + tags[i] + ']').join('</p>');
		}
		// code
		else if(tags[i] == "code"){
			s = s.split('[' + tags[i] + ']').join('<pre class="prettyprint">');
		} else if(tags[i] == "/code"){
			s = s.split('[' + tags[i] + ']').join('</pre>');
			//s = s.replace(/<br\/>/g, '&nbsp;<br\/>');
		}
		// Image
		else if(tags[i] == "image") {
			s = s.replace(regImage, '<a href=\"$1\" rel=\"facebox\"><img src=\"$1\" alt=\"');	//[image=URI]TEXT[/image]
			s = s.replace(regImage2, '<a href=\"$1\" rel=\"facebox\"><img src=\"$1\" alt=\"$1\" title=\"$1');	//[image]URI[/image]
		} else if(tags[i] == "/image") {
			s = s.replace(/\[\/image\]/g, '\" class=\"pic\" \/></a>');
		}
		/*
		else if(tags[i] == "image") {
			s = s.replace(regImage, '<img src=\"$1\" alt=\"');			//[image=URI]TEXT[/image]
			s = s.replace(regImage2, '<img src=\"$1\" alt=\"$1\" title=\"$1');	//[image]URI[/image]
		} else if(tags[i] == "/image") {
			s = s.replace(/\[\/image\]/g, '\" class=\"pic\" \/>');
		}
		*/
		// URL
		else if(tags[i] == "url") {
			s = s.replace(regURL, '<a href=\"$1\">');	//[url=URI]TEXT[/url]
			s = s.replace(regURL2, '<a href=\"$1\">$1');	//[url]URI[/url]
		} else if(tags[i] == "/url") {
			s = s.replace(/\[\/url\]/g, '<\/a>');
		}
		// Snap
		else if(tags[i] == "snap") {
			s = s.replace(regSnapURL, '<a href=\"javascript:void(0);\" style=\"text-decoration: none;\"><span onclick=\"showSnapShot(\'$1\', this);\"><img src=\"images/snap.gif\" border=\"0\">');
		} else if(tags[i] == "/snap") {
			s = s.replace(/\[\/snap\]/g, '<\/span><\/a>');
		}
		// Style
		else if(tags[i] == "style") {
			s = s.replace(regStyle, '<span style=\"$1\">');
		} else if(tags[i] == "/style") {
			s = s.replace(/\[\/style\]/g, '<\/span>');
		}
		// Amazon
		else if(tags[i] == "amazon") {
			try{
				var ary = s.match(/\[amazon\].+?\[\/amazon\]/g);
				var amazon_str = "";
				var amazon_ary = [];
				var len = ary.length || 0;
				for(var k = 0; k < len; k++){
					var str = ary[k].replace(/\[amazon\]/g, '');
					str = str.replace(/\[\/amazon\]/g, '');
					var split_str = str.split(",");
					var amazon_obj = {};
					for(var j = 0; j < split_str.length; j++){
						var elem = split_str[j].split("=");
						if(elem[0] == "url"){
							if(elem.length >= 2){
								var url_join = "";
								for(var n = 1; n < elem.length; n++){url_join += (elem[n] + "=");}
								url_join = url_join.slice(0, -1);
								if(url_join.match(/(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)/g)){
									amazon_obj.url = url_join;
								}
							}
						}else if(elem[0] == "image"){
							if(elem[1].match(/(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))/g)){
								amazon_obj.image = elem[1];
							}
						}else if(elem[0] == "title"){
							amazon_obj.title = elem[1];
						}else if(elem[0] == "author"){
							amazon_obj.author = elem[1];
						}else if(elem[0] == "price"){
							amazon_obj.price = elem[1];
						}
					}
					amazon_ary.push(amazon_obj);
				}
			}catch(err){
				//[amazon] tag not found
			}
		} else if(tags[i] == "/amazon") {
			for(var m = 0; m < amazon_ary.length; m++){
				var html = '<div class="ds_entry_amazon_box">';
				html += '<div style="float:left;width:auto;"><a href="'+amazon_ary[m].url+'" target="_blank"><img class="pic" src="'+amazon_ary[m].image+'" \/><\/a><\/div>';
				html += '<div style="float:left;width:auto;">'
				html += '<p>'+amazon_ary[m].title+'<\/p>';
				html += '<p>'+amazon_ary[m].author+'<\/p>';
				html += '<p>'+amazon_ary[m].price+'<\/p>';
				html += '<\/div><div style="clear:both;" \/>';
				html += '<\/div><\/div>';
				//var html = '<div><img src="'+amazon_ary[m].image+'" \/>'+amazon_ary[m].title+'<\/div>';
				s = s.replace(/\[amazon\].+?\[\/amazon\]/, html);
			}
		}
		// Default & Others
		else{
			s = s.split('[' + tags[i] + ']').join('<' + tags[i] + '>');
		}
	}
	return s;
}

//--------Tag変換ココまで

//----------------- タグ名取出し用関数
function strSubstring(str){
	var end = str.indexOf("]");
	var s = str.substring(1,end);
	
	return s;
}

//----------------- ヘルプメッセージ用関数
function showHelpMsg(i) {
	document.getElementById('helpmsg').value = edButtons[i].help;
}
function hideHelpMsg() {
	document.getElementById('helpmsg').value = HelpMessage;
}

//----------------- 入力内容確認用関数(未実装)
function checkTheValue() {
	var edCanvas = getCanvasElement();
	var myValue = edCanvas.value;
	var tags = getCommentTags(edButtons);
	var X,Y;

	myValue = myValue.split('&').join('&amp;');
	myValue = myValue.split('<').join('&lt;');
	myValue = myValue.split('>').join('&gt;');
	myValue = myValue.split('\'').join('&quot;');
	myValue = myValue.split('\n').join('<br />');
	
	myValue = getStringCommentTags(tags, myValue);
	// livedoor絵文字用 (http://parts.blog.livedoor.jp/js/emoji.js)
	myValue = getStringEmojiTag (myValue);

	var myWin = window.open('','check_value','width=450,height=250,left=X,top=Y,scrollbars=yes');
	myWin.document.open();
	myWin.document.write('<?xml version=\"1.0" encoding="EUC-JP\"?>\n'
				+ '<\!DOCTYPE html PUBLIC \"-\/\/W3C//DTD XHTML 1.0 Transitional\/\/EN\" \"http:\/\/www.w3.org\/TR\/xhtml1\/DTD\/xhtml1-transitional.dtd\">\n'
				+ '<html xmlns=\"http:\/\/www.w3.org\/1999\/xhtml\" xml:lang=\"ja\" lang=\"ja\">\n'
				+ '<head>\n'
				+ '<meta http-equiv=\"Content-Type\" content=\"text\/html; charset=EUC-JP\" \/>\n'
				+ '<title>コメントプレビュー<\/title>\n'
				+ '<\/head>\n'
				+ '<body>\n'
				+ '<h1>コメントプレビュー<\/h1>\n'
				+ '<div>\n');
	myWin.document.write(myValue);
	myWin.document.write('<form><p><input type=\"button\" value=\"ウィンドウを閉じる\" onclick=\"window.close();\" \/><\/p><\/form>\n'
				+ '<\/div>\n'
				+ '<\/body>\n'
				+ '<\/html>\n');
    myWin.document.close();
}
