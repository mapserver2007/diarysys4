/**
 * @author summer-lights
 */
(function(_$){
	/**
	 * Widget::LivedoorClip
	 */
	var livedoorclip = function(api){
		var url = api.url + "?" + "callback=?";
		var param = api.param;
		//JSONP開始
		$.getJSON(url, param, function(json){
			var ul = $(document.createElement("ul"));
			for(var i = 0; i < json.clips.length; i++){
				var html = "<a href=\"" + json.clips[i].link + "\">";
				html += json.clips[i].title + "</a>";
				$(document.createElement("li"))
					.html(html)
					.appendTo(ul);
			}
			$("#" + api.id).append(ul);
			//ローディング終了
			ds.common.loading(api.id, "off");
		});
	};
	/**
	 * Widget::TMAP
	 */
	var tmap = function(api){
		//サイドバーの横幅を取得し横幅を調整にする
		var width = document.getElementById("right_container").clientWidth - 20;
		var height = 200;
		//TMAPのインクルード
		$(document.createElement("script"))
			.attr("src", api.url)
			.appendTo(document.getElementsByTagName("head").item(0));
		//TMAPの呼び出し
		var cnt = 0;
		var max_cnt = 10;
		var call = function(){
			//無限ループを回避する
			if(cnt++ > max_cnt){
				new TMAP()
					.setOutFrame(width, height)
					//.setAddress("東京都江東区新木場1-18-7")
					.setLngLat(139.69398813,35.7573271929)
					.setBlogeo({"popup": 1})
					.setScale(6)
					.setMapFrame(api.id)
					.setSlider()
					.load();
					ds.common.loading(api.id, "off");
				
				return;
			}
			else{
				setTimeout(arguments.callee, 100);
			}
		};
		//triggerのインクルードより先にTMAPがコールされるため、遅延ロードさせる
		call();
		
		return true;
	};
	/**
	 * Widget::Yahoo!Topics
	 */
	var yahootopics = function(api){
		var url = api.url + "?" + "callback=?";
		var param = api.param;
		//JSONP開始
		$.getJSON(url, param, function(json){
			var ul = $(document.createElement("ul"));
			for(var i = 0; i < json.length; i++){
				var html = "<a href=\"" + json[i].url + "\">";
				html += json[i].title + "</a>";
				$(document.createElement("li"))
					.html(html)
					.appendTo(ul);
			}
			$("#" + api.id).append(ul);
			//ローディング終了
			ds.common.loading(api.id, "off");
		});
	};
	/**
	 * Widget::TagCloud
	 */
	var tagcloud = function(api){
		var url = api.url + "?" + "callback=?";
		var param = api.param;
		//JSONP開始
		$.getJSON(url, param, function(json){
			//タグ検索フォーム作成
			var input = $("<input>").attr("id", "tag_grep").css({"margin" : "4px"});
			$("#" + api.id).append(input);
			//取得したタグリストをキャッシュする
			ds.tag.cache(json);
			//イベントをセットする
			ds.tag.event();
			//常にタグリストがオープンになっているようにフラグを立てる
			ds.tag.list_open = true;
			
			ds.tag.list_disp = "widget";
			$("#" + api.id).append(
				$("<div>").attr("id", "tag_list").css({"padding" : "4px"}).append(
					$("<div>").attr("id", "tags").css({"text-align": "justify"})
						.append(ds.tag.cache2elem())
				)
			);
			//ローディング終了
			ds.common.loading(api.id, "off");
		});		
	};
	/**
	 * Widget::Archives
	 */
	var archives = function(api){
		var url = api.url + "?" + "callback=?";
		var param = api.param;
		//JSONP開始
		$.getJSON(url, param, function(json){
			var ul = $("<ul>");
			for(var i = 0; i < json.length; i++){
				
				$("<li>").append(
					$("<a>").attr("href", "?month=" + json[i]["year"] + "-" + json[i]["month"])
					.html(json[i]["year"] + "年" + json[i]["month"] + "月")
				)
				.append(
					$("<sup>").html(json[i]["entry"])
				)
				.appendTo(ul);
			}
			$("#" + api.id).append(ul);
			//ローディング終了
			ds.common.loading(api.id, "off");
		});
	};
	
	_$.prototype.widget = {
		/**
		 * パラメータ初期化
		 */
		init : function(){
			//Widget処理のパス
			this.widget_conf_url = "./pl/widget.pl";
			//格納する親要素
			this.parent = "ds_sidebar";
			//各Widgetのパラメータ
			this.widget = {
				livedoorclip  : {url: "./pl/ds_widget.pl", param: {wid: "livedoorclip", uid: "mapserver2007"}, callback: livedoorclip},
				yahootopics   : {url: "./pl/ds_widget.pl", param: {wid: "yahootopics"}, callback: yahootopics},
				tagcloud      : {url: "./pl/ds_widget.pl", param: {wid: "tagcloud"}, callback: tagcloud},
				archives      : {url: "./pl/ds_widget.pl", param: {wid: "archives"}, callback: archives},
				tmap          : {url: "http://summer-lights.dyndns.ws/tmap/tmap3/js/trigger.js", callback: tmap}
			};
		},
		/**
		 * 呼び出し開始
		 */
		start : function(){
			this.init();
			this.getConfig();
		},
		/**
		 * 読み込み開始
		 */
		read : function(widget_data){
			var _this = this;
			var widget = this.widget[widget_data.id];
			$(document.createElement("div"))
				.attr("id", widget_data.wid)
				.addClass("widget_box")
				.appendTo("#" + _this.parent);
			//ウィジェットヘッダ作成
			this.widget_header(widget_data.title, widget_data.url, widget_data.wid);
			//ローディング開始
			ds.common.loading(widget_data.wid, "on");
			//ウィジェット固有処理開始
			widget.callback({
				"id"   : widget_data.wid,
				"url"  : widget.url,
				"param": widget.param
			});
		},
		/**
		 * ウィジェット設定を読み込み、表示状態をチェックする
		 */
		getConfig: function(){
			var _this = this;
			//ウィジェット設定を取得する
			$.getJSON(this.widget_conf_url, function(widget_data){
				var len = widget_data.length - 1;
				if(len == undefined){
					this.error();
					return;	
				}
				for(var i = 0; i < len; i++){
					if(widget_data[i].state == 1){
						_this.read({
							id    : widget_data[i].pid,                 //ウィジェットID
							wid   : "widget_" + widget_data[i].pid,     //ウィジェット表示領域ID
							title : widget_data[i].title,               //ウィジェット名(日本語)
							url   : widget_data[i].url                  //ウィジェット掲載先URL
						});
					}
				}
			});
		},
		/**
		 * ウィジェット読み込み失敗処理
		 */
		error : function(){
			$("#" + this.parent).html("Widget Read Error!");
		},
		/**
		 * ウィジェットのタイトルヘッダを生成
		 * @param {Object} str
		 * @param {Object} append_id
		 */
		widget_header : function(title, url, append_id){
			var head_str = url ? "<a href=\"" + url + "\" target=\"_blank\">" + title + "</a>" : title;
			$(document.createElement("p"))
				.addClass("widget_header")
				.html(head_str)
				.appendTo("#" + append_id);
		}
	};
})(ds_common);
