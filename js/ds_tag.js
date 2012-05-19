(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.tag = {
		/**
		 * パラメータ初期化
		 */
		init : function(){
			this.json_cache = "";　//JSONをキャッシュする
			this.list_open = false; //タグ一覧の開閉フラグ
			this.new_open  = false; //タグ新規登録フォームの開閉フラグ
			this.tag_num = 5; //一度に登録できるタグ数
			this.url = "./php/ajax.php"; //問い合わせURL
			this.defcomment = "タグを登録してください";
			this.load_img = "./image/ajax-loader.gif"; //ローディング画像のパス
			this.tag_list = this.listInit(); //登録するタグのリストの初期化
			this.event(); //イベントのセット
			this.load({mode : "select"}); //タグ一覧取得
			this.list_disp = "manage"; //リストの表示方法(manage, widget)
		},
		/**
		 * タグ一覧を開閉する
		 */
		showTags : function(){
			//タグ一覧がクローズのとき
			if(!this.list_open){
				//タグキャッシュがない場合は問い合わせる
				if (this.json_cache == "") {
					//タグ一覧を取得する
					//this.load({mode : "select"});
				}
				//キャッシュがある場合はそれを表示する
				else {
					$("#tag_list").css({"padding" : "4px"})
						.append($("<div>")
						.attr("id", "tags")
						.append(this.cache2elem()));
				}
				this.list_open = true;
			}
			//タグ一覧がオープンのとき
			else{
				$("#tag_list").css({"padding" : "0px"});
				$("#tags").remove();
				this.list_open = false;
			}
		},
		/**
		 * タグを新規作成する
		 */
		createTag : function(){
			var tag_name = $("#tag_new_text")[0].value;
			if(tag_name){
				//タグを作成して登録
				this.load({mode : "insert", tag : tag_name}, "on");
			}
			else{
				//空欄ならアラート
				alert("タグ名を入力してください");
			}
		},
		/**
		 * タグを新規作成するフォームを作る
		 */
		createTagForm : function(){
			//新規登録フォームがクローズのとき
			if(!this.new_open){
				$("#tag_new_input").css({"display" : "block"});
				this.new_open = true;
			}
			//新規登録フォームがオープンのとき
			else{
				$("#tag_new_input").css({"display" : "none"});
				this.new_open = false;
			}
		},
		/**
		 * タグをサーバに問い合わせる
		 * @param {Object} param
		 * @param {Object} disp
		 */
		load : function(param, disp){
			var _this = this;
			//JSONP開始
			var url = this.url + "?" + "callback=?";
			//ローディング開始
			this.loading('on');
			$.getJSON(url, param, function(json){
				//エラー処理
				if(json.error){
					$("#tag_new_progress").html(json.error);
					return;
				}
				//タグ検索用にキャッシュする
				_this.cache(json);
				//dispがONのときは結果を表示する
				if(disp == "on")
					$("#tag_list").css({"padding" : "4px"})
						.append($("<div>").attr("id", "tags").append(this.cache2elem()));
				//ローディング終了
				_this.loading('off');
			});
		},
		/**
		 * タグ処理で使用するイベントを設定する
		 */
		event : function(){
			var _this = this;
			//タグ一覧を表示
			$("#tag_show").click(function(e){
				_this.showTags();
			});
			//タグ新規作成フォームを表示
			$("#tag_create").click(function(e){
				_this.createTagForm();
			})
			//タグを新規作成
			$("#tag_new_submit").click(function(e){
				_this.createTag();
			})		
			//タグ検索：ひと文字入力するたびに実行
			$("#tag_grep").keyup(function(e){
				_this.search();
			});
			//タグ検索：フォームにフォーカスするたびに実行
			$("#tag_grep").focus(function(e){
				_this.search();
			});
		},
		/**
		 * タグの検索をする
		 */
		search : function(){
			try{
				//フォームの語を取得する
				if (this.list_open) {
					$("#tags").remove();
					$("#tag_list").css({"padding" : "4px"})
						.append($("<div>").attr("id", "tags").css({"text-align": "justify"})
						.append(this.cache2elem($("#tag_grep")[0].value)));
				}
			}catch(err){}
		},
		/**
		 * タグを追加(削除も)する
		 * @param {Object} id
		 */
		append : function(id){
			var result = this.push(id);
			if(result.exist){
				this.pop(id);
				this.addTags();
			}
			else if(result.fully){
				alert("タグがいっぱい");
			}
			else {
				this.addTags();
			}	
			for(var i = 0; i < this.tag_num; i++){
				$("#debug").get(0).innerHTML += this.tag_list[i] + " ";
			}
		},
		/**
		 * タグを登録する
		 */
		addTags : function(){
			var _this = this;
			//表示を初期化する
			$("#tag_register").html("");
			//タグがあるかどうかチェックするフラグ
			var no_tag = false;
			//タグIDからタグ名を取得する
			var get_tag_name = function(tid, json_cache){
				for(var i = 0; i < json_cache.length; i++){
					if(json_cache[i].id == tid){
						return json_cache[i].name;
					}
				}
			};
			for (var i = 0; i < this.tag_num; i++) {
				//pushされたタグIDだけ取り出す
				if (this.tag_list[i] != 0) {
					var tag_id = this.tag_list[i];
					var tag_name = get_tag_name(tag_id, this.json_cache);
					//タグ要素
					var parent = $(document.createElement("div")).attr("id", "reg_tag_" + tag_id).css({
						"padding": "1px"
					});
					//タグ内要素		
					var child1 = $(document.createElement("span")).css({
						"margin-right": "2px"
					}).html(tag_name);
					//削除ボタン
					var child2 = $(document.createElement("input"))
						.attr("id", "bt_" + tag_id)
						.attr("type", "button").attr("value", "削除")
						.click(function(e){
							var id = e.target ? e.target.id : e.srcElement.id;
							id = id.split("_")[1];
							_this.pop(id);
							_this.addTags();
						});
					//タグID
					var child3 = $(document.createElement("input"))
						.attr("type", "hidden").attr("name", "ds_tagid[]")
						.attr("value", tag_id);
					//タグ名
					var child4 = $(document.createElement("input"))
						.attr("type", "hidden").attr("name", "ds_tagname[]")
						.attr("value", tag_name);
					//参照回数
					var child5 = $(document.createElement("input"))
						.attr("type", "hidden").attr("name", "ds_tagref[]")
						.attr("value", tag_name);			
						
					parent.append(child1);
					parent.append(child2);
					parent.append(child3);
					parent.append(child4);
					parent.appendTo("#tag_register");
					no_tag = true;
				}
			}
			//タグがゼロになったら初期状態にする
			if(!no_tag)
				$("#tag_register").html(this.defcomment);	
		},
		/**
		 * タグリストを初期化する
		 */
		listInit : function(){
			var ary = [];
			//すべての要素を0で初期化
			for(var i = 0; i < this.tag_num; i++){
				ary[i] = 0;
			}
			return ary;
		},
		/**
		 * タグリストにタグIDを格納する
		 * @param {Object} id
		 */
		push : function(id){
			var regflg = {exist : false, fully : false};
			//同じ要素が既に登録されていないか
			for (var i = 0; i < this.tag_num; i++) {
				if (this.tag_list[i] == id) {
					regflg.exist = true;
					return regflg;
				}
			}
			//登録タグが既に一杯でないか
			var empty = 0;
			for (var j = 0; j < this.tag_num; j++) {
				if (this.tag_list[j] != 0)
					empty++;
			}
			if(empty == this.tag_num){
				regflg.fully = true;
				return regflg;
			}
			//登録タグに重複がなく、空きがある
			for (var k = 0; k < this.tag_num; k++) {
				if(this.tag_list[k] == 0){
					this.tag_list[k] = id;
					break;
				}
			}
			return regflg;
		},
		/**
		 * タグリストからタグIDを除去する
		 * @param {Object} id
		 */
		pop : function(id){
			//削除する
			for (var i = 0; i < this.tag_num; i++) {
				if (this.tag_list[i] == id) {
					this.tag_list[i] = 0;
				}
			}
			//登録が古い順にソート
			var tmp = this.listInit();
			var idx = 0;
			for (var j = 0; j < this.tag_num; j++) {
				if(this.tag_list[j] != 0){
					tmp[idx++] = this.tag_list[j];
				}
			}
			//コピー
			this.tag_list = tmp;
			//参照を外す
			tmp = null;
		},
		/**
		 * jsonデータをキャッシュする
		 * @param {Object} data
		 */
		cache : function(data){
			this.json_cache = data;
		},
		/**
		 * キャッシュをelementに変換して返す
		 * @param {Object} grep
		 * @return {Object} ul
		 */
		cache2elem : function(grep){
			var _this = this;
			//実態参照文字に変換
			var to_entity = function(str){
				str = str.split('&').join('&amp;');
				str = str.split('<').join('&lt;');
				str = str.split('>').join('&gt;');
				str = str.split('\'').join('&quot;');
				str = str.split('\n').join('<br />');
				return str;
			};
			if(grep){grep = to_entity(grep);}
			if($("#tags")){$("#tags").remove();}
			//var parent = $("<div>").attr("id", "tags");
			var ul = $("<ul>");
			
			//タグ参照回数からclass属性を決定
			var tagref = function(ref){
				var formated_ref = Math.ceil(ref / 10);
				return formated_ref > 10 ? "tagcloud10" : "tagcloud" + formated_ref;
			};
			//管理画面での表示
			if(this.list_disp == "manage"){
				for (var i = 0; i < this.json_cache.length; i++) {
					//検索処理なら文字をハイライトする
					var tagname = grep ? this.json_cache[i].name.replace(grep, "<span class='tag_hilite'>" + grep + "</span>") : this.json_cache[i].name;
					//表示する要素
					$("<li>").attr("id", "tag" + this.json_cache[i].id)
						.addClass(tagref(this.json_cache[i].ref))
						.click(function(e){
							var elem = e.target ? e.target : e.srcElement;
							var id = elem.id ? elem.id : elem.parentNode.id;
							_this.append(parseInt(id.replace("tag", "")));
						}).html(tagname + " ").appendTo(ul);
				}
			}
			//Widgetでの表示
			else if(this.list_disp == "widget"){
				for (var i = 0; i < this.json_cache.length; i++) {
					//検索処理なら文字をハイライトする
					var tagname = grep ? this.json_cache[i].name.replace(grep, "<span class='tag_hilite'>" + grep + "</span>") : this.json_cache[i].name;
					var html = "<a class=\"" + tagref(this.json_cache[i].ref) + "\" href=\"?tag=" + this.json_cache[i].id + "\">" + tagname + " " + "</a>";
					html += "<sup>" + this.json_cache[i].ref + "</sup>"
					$("<li>").html(html).appendTo(ul);
				}
				//$("#" + api.id).append(tag_list.append(tags.append(ul)));
			}
			return ul;
		},
		/**
		 * ローディング画像表示
		 * @param {Object} m
		 */
		loading : function(m){
			if(m == "on"){
				$("#tag_new_progress").html("");
				$(document.createElement("img"))
					.attr("src", this.load_img)
					.appendTo("#tag_new_progress");
			}else if(m == "off"){
				$("#tag_new_progress").html("");
			}		
		}
	};
})(ds_common);
