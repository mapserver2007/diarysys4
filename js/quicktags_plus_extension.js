(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.quicktags_plus_extension = {
		/**
		 * QuickTagsPlus拡張開始
		 * @param {Object} id
		 */
		start : function(id){
			if(id == "amazon")
				amazon.start();
			else if(id == "carousel")
				carousel.start();
		}
	};

	var amazon = {
		/**
		 * パラメータの定義
		 */
		init : function(){
			this.result = "ds_entry_amazon";
			this.result_form = "ds_quicktags_plus_amazon";
			this.result_box = "ds_quicktags_plus_amazon_result";
			this.func_by_state = {insert : "this.insert()", remove : "this.remove()"};
			this.use_thumbnale = "medium"; //日記に表示する書籍サムネイルのサイズ
			this.use_price_format = "formatted"; //日記に表示する書籍の価格フォーマット
			this.load_img = "./image/ajax-loader.gif"; //ローディング画像のパス
			this.url = "./pl/amazon.pl";
			this.tmp = {}; //一時データ保存
			this.tmp.idx = 0;
		},
		/**
		 * 「amazon」ボタンが押されたら開始
		 */
		start: function(){
			this.init();
			eval(this.getAmazonBoxState());
		},
		/**
		 * アマゾン検索フォームを挿入する
		 */
		insert : function(){
			var _this = this;
			this.setHTML("base", this.result, []);
			this.loading();
			$("#ds_amazon_search").click(function(e){ _this.search(); });
		},
		/**
		 * アマゾン検索フォームを削除する
		 */
		remove : function(){
			$("#" + this.result).get(0).innerHTML = "";
			this.tmp = null;
		},
		/**
		 * 検索を開始する
		 */
		search : function(){
			var _this = this;
			//フォームの値を取得する
			var keyword = $("#ds_amazon_form")[0].value;
			//値チェック
			if(keyword == ""){
				alert("フォームに検索語を入力してください");
				return;
			}
			//要素を消す
			if(!$("#append_check")[0].checked)
				$("." + _this.result_box).get(0).innerHTML = "";
			//Loading開始
			this.loading("on");
			//JSONP開始
			var url = this.url + "?" + "callback=?";
			var param = {keyword : keyword};
			$.getJSON(url, param, function(json){
				//結果を表示する
				_this.setHTML("books", _this.result_box, json);
				//Loading終了
				_this.loading("off");
			});
		},
		/**
		 * テキストエリアにBBコードを追加する
		 * @param {Object} json
		 */
		tag : function(idx){
			var json = this.tmp.json[idx];
			//表示内容を変更できる要素の処理
			var size = function(type, image){
				var use = "";
				if(type == "small")
					use = image.small.url;
				else if(type == "medium")
					use = image.medium.url;
				else if(type == "large")
					use = image.large.url;
				return use;
			};
			var price = function(type, item){
				var item_price = "";
				if(type == "formatted")
					item_price = item.FormattedPrice.replace(",", "");
				else if(type == "unformatted")
					item_price = item.Amount;
				return item_price;
			};

			//BBコード用文字列を生成
			var amazon_image = "image\=" + size(this.use_thumbnale, json.Image) + ",";
			var amazon_price = "price\=" + price(this.use_price_format, json.Price) + ",";
			var amazon_author = json.Author ? "author\=" + json.Author + "," : "author\=(著者名不明),";
			var amazon_title = "title\=" + json.Title + ",";
			var amazon_link = "url\=" + json.Detail;

			//文字列を連結
			var amazon_str = "\[amazon\]" + amazon_title + amazon_author + amazon_image
				+ amazon_price + amazon_link + "\[\/amazon\]";

			//処理をQuickTagsPlusに移行させる
			edInsertContent(getCanvasElement(), amazon_str);
		},
		/**
		 * modeごとにidの子要素にobjを追加する
		 * @param {Object} mode
		 * @param {Object} id
		 * @param {Object} obj
		 */
		setHTML : function(mode, id, obj){
			this.tmp.json = obj;
			var _this = this;
			try{
				//背景のHTML
				var func = mode == "base" ? function(id){
					var html = "<div class='" + _this.result_form + "'>";
					html += "<input type='text' id='ds_amazon_form' class='ds_quicktags_plus_amazon_text' />";
					html += "<input type='button' id='ds_amazon_search' value='検索' /><br />"
					html += "<input type='checkbox' id='append_check' name='aws' checked='checked' />結果を追加する";
					html += "</div>";
					$("#" + id).get(0).innerHTML = html;
					//結果表示領域も作る
					$("#" + id).get(0).innerHTML += "<div class='" + _this.result_box + "' />";
				} : (
				//書籍のHTML
				mode == "books" ? function(id, idx){
					var json = _this.tmp.json;
					if(json.length == 0){
						$("." + _this.result_box).html("NOT FOUND");
					}else{
						//書籍データ表示DOM
						var ul = _this.c("ul");
						ul.className = "hoverbox";
						for (var i = 0; i < json.length; i++) {
							//リスト
							var li = _this.c("li");
							//リンク(ダミー)
							var link = _this.c("a");
							link.id = "amazon_id_" + (i + idx) + "_" + i;
							link.href = "javascript:void(0)";
							//サムネイル(小)
							var thmb_s = _this.c("img");
							thmb_s.src = json[i].Image.small.url;
							thmb_s.style.width = json[i].Image.small.width + "px";
							thmb_s.style.height = json[i].Image.small.height + "px";
							//サムネイル(大)
							var thmb_m = _this.c("img");
							thmb_m.id = "amazon_id_" + (i + idx) + "_" + i;
							thmb_m.className = "preview";
							thmb_m.src = json[i].Image.medium.url;
							thmb_m.style.width = json[i].Image.medium.width + "px";
							thmb_m.style.height = json[i].Image.medium.height + "px";
							thmb_m.title = json[i].Title;
							//結合する
							link.appendChild(thmb_s);
							link.appendChild(thmb_m);
							li.appendChild(link);
							ul.appendChild(li);
						}
						//要素を追加する
						$("." + id).append(ul);

						//クリアフィックス
						$("#clear_fix").remove();
						$(_this.c("div")).attr("id", "clear_fix").css({"clear" : "both"}).appendTo("." + id);

						//書籍画像をクリックしたときのイベント
						for (var j = 0; j < json.length; j++) {
							$("#amazon_id_" + (j + idx) + "_" + j).click(function(e){
								var id = e.target ? e.target.id : e.srcElement.id;
								_this.tag(id.split("_")[3]);
							});
						}
					}
				} : function(id){ $("#" + id).get(0).innerHTML = "HTMLの構成に失敗しました";});
			}catch(e){}
			func(id, this.tmp.idx);
			this.tmp.idx += obj.length;
		},
		/**
		 * アマゾンの検索BOXが表示されているかどうかをチェック
		 * @return function
		 */
		getAmazonBoxState : function(){
			return $("#" + this.result).get(0).innerHTML == "" ? this.func_by_state.insert : this.func_by_state.remove;
		},
		/**
		 * ローディングエフェクト
		 * @param {Object} m
		 */
		loading : function(m){
			var _this = this;
			if(m == "on"){
				$("." + this.result_box).css({
					"background-image": "url(\"" + _this.load_img + "\")", "padding" : "10px"
				});
			}else if(m == "off"){
				$("." + this.result_box).css({
					"background-image": "url(\"\")"
				});
			}
		},
		/**
		 * 名前が長いのでエイリアス
		 * @param {Object} name
		 */
		c : function(name){
			return document.createElement(name);
		}
	};

	var carousel = {
		/**
		 * パラメータの定義
		 */
		init : function(){
			this.result = "ds_entry_carousel";
			this.func_by_state = {insert : "this.insert()", remove : "this.remove()"};
			this.upload_dir = "./upload/";
			this.thumbnail_dir = "./upload/thumbnail/";
			this.load_img = "./image/ajax-loader.gif"; //ローディング画像のパス
			//this.url = "./pl/filearchives.pl"; //旧プログラム
			this.url = "./pl/carousel.pl";
		},
		/**
		 * 「carousel」ボタンが押されたら開始
		 */
		start: function(){
			this.init();
			eval(this.getCarouselBoxState());
		},
		/**
		 * Carouselを表示
		 */
		insert : function(){
			var _this = this;
			var url = this.url + "?" + "callback=?";
			//Loading開始
			this.loading("on");
			$.getJSON(url, function(json){
				var res = json.length - 1;
				//結果を表示する
				var ul = $("<ul>").attr("id", "ds_carousel").addClass("jcarousel-skin-tango");
				for(var i = 0; i < json.length - 1; i++){
					var li = $("<li>");
					//画像ラッパー
					var div = $("<div>").attr("id", "archive_file_id_" + json[i].id);
					//画像
					//2009.09.22 追加
					var tbl = "<table border=0><body><tr><td class='thumbnail_bg'>" +
								"<img src='" + _this.thumbnail_dir + json[i].filename + "' name='" + json[i].filename + "' style='cursor:pointer;'/>" +
									"</td></tr></body></table>";
					ul.append(li.append(div.html(tbl)));
				}
				//要素を追加する
				$("#" + _this.result).append(ul);
				//jcsrouselを開始
				$("#ds_carousel").jcarousel();
				//Loading終了
				_this.loading("off");
					//アーカイブ画像をクリックしたときのイベント
				for (var j = 0; j < json.length - 1; j++) {
					$("#archive_file_id_" + json[j].id).click(function(e){
						var filename = e.target ? e.target.name : e.srcElement.name;
						_this.tag(filename);
					});
				}
			});
		},
		/**
		 * テキストエリアにBBコードを追加する
		 * @param {Object} filename
		 */
		tag : function(filename){
			//パスを再生成
			var abs_path = function(path){
				var e = document.createElement('span');
				e.innerHTML = '<a href="' + path + '" />';
				return e.firstChild.href;
			};

			//BBコードを生成
			var img_str = "[image]";
			img_str += abs_path(this.upload_dir + filename);
			img_str += "[/image]";
			//処理をQuickTagsPlusに移行させる
			edInsertContent(getCanvasElement(), img_str);
		},
		/**
		 * Carouselを消す
		 */
		remove : function(){
			$("#" + this.result).get(0).innerHTML = "";
		},
		/**
		 * Carouselの表示されているかどうかをチェック
		 * @return function
		 */
		getCarouselBoxState : function(){
			return $("#" + this.result).get(0).innerHTML == "" ? this.func_by_state.insert : this.func_by_state.remove;
		},
		/**
		 * ローディングエフェクト
		 * @param {Object} m
		 */
		loading : function(m){
			var _this = this;
			if(m == "on"){
				$("#" + this.result).css({
					"background-image": "url(\"" + _this.load_img + "\")", "background-color" : "#ffffcc", "padding" : "10px"
				});
			}else if(m == "off"){
				$("#" + this.result).css({
					"background-image": "url(\"\")", "background-color" : "#ffffff", "padding" : "0px"
				});
			}
		},
		/**
		 * 名前が長いのでエイリアス
		 * @param {Object} name
		 */
		c : function(name){
			return document.createElement(name);
		}
	};
})(ds_common);
