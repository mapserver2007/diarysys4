(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.newpost = {
		/**
		 * パラメータ初期化
		 */
		init: function(prev){
			//プレビューを表示する領域のID
			this.prev = prev.to;
			//プレビューを取得する領域のID
			this.form = prev.from;
			//プレビューに表示する初期文字列
			this.def_comment = "本文のプレビューを表示します";
			//必須チェックのエラーメッセージ
			this.error_msg = {
				title: "タイトルを入力してください",
				description: "本文を入力してください",
				tag: "タグを登録してください"
			};
			//エスケープフラグ
			this.escape = !this.escape ? "on" : this.escape;
		},
		/**
		 * 日記記述プレビュー処理
		 * @param {Object} mode
		 */
		preview: function(prev, mode){
			var _this = this;
			this.init(prev);
			//previd, formidが不正なら終了
			if (!this.validate()){
				return;
			}
			var description = $("#" + this.prev).html();
			
			//初期状態の処理
			if (mode == "init") {
				if (!description){
					$("#" + this.prev).html(this.def_comment);
				}
				else{ 
					$("#" + this.prev).html(description.replace(/\x0D\x0A|\x0D|\x0A/g, '<br />'));
				}
			}
			//初期状態以外の処理
			else {
				var content = $("#" + this.form)[0].value != undefined ? $("#" + this.form)[0].value : $("#" + this.form)[0].innerHTML;
				var str = this.escape == "on" ? this.convert(content) : this.convert(content, ['\n']);
				//QuickTagsPlusと連携
				var tags = getCommentTags(edButtons);
				str = getStringCommentTags(tags, str);

				$("#" + this.prev).get(0).innerHTML = str == "" ? this.def_comment : str;
			}
		},
		/**
		 * テキストエリアのレイヤから要素を取得する
		 * @param {Object} e
		 * @param {Object} frame
		 */
		getComment: function(e, frame){
			if (document.layers) {
				var f = frame ? frame : self;
				var layers = f.document.layers;
				if (layers[e]){ return layers[e]; }
				for (var i = 0; i < layers.length; i++){ 
					return this.getComment(e, layers[i]);
				}
			}
			else if (document.all) {
				return document.all[e];
			}
			else {
				return document.getElementById(e);
			}
		},
		/**
		 * エスケープ処理のフラグを返す
		 * @param {Object} flg
		 */
		setEscape: function(flg){
			if (!this.escape){ this.escape = flg == "off" ? "off" : "on"; }
		},
		/**
		 * ブラウザ表示用にエスケープする
		 * @param {Object} str
		 */
		convert: function(str, cond){
			//繰り返し回数
			var idx = cond == null ? 1 : cond.length;
			for (var i = 0; i < idx; i++) {
				//&amp;
				if (cond == null || cond[i] == "&") 
					str = str.split('&').join('&amp;');
				//&lt;
				if (cond == null || cond[i] == "<") 
					str = str.split('<').join('&lt;');
				//&gt;
				if (cond == null || cond[i] == "\'") 
					str = str.split('>').join('&gt;');
				//&quot;
				if (cond == null || cond[i] == "\n") 
					str = str.split('\'').join('&quot;');
				//<br />
				if (cond == null || cond[i] == "\n")
					str = str.split('\n').join('<br />');
			}
			
			return str;
		},
		/**
		 * IDが正しく設定されているかチェックする
		 */
		validate: function(){
			if (!document.getElementById(this.prev) || !document.getElementById(this.form)) {
				alert("Can't Description Preview");
				return false;
			}
			else {
				return true;
			}
		},
		/**
		 * エントリの必須チェック
		 */
		confirm: function(){
			var _this = this;
			//取得するIDの定義
			var confirm_count = 0;
			//タイトル
			if (!$("#ds_title_form")[0].value) {
				$("#ds_new_title_error_msg").html(this.error_msg.title);
				$("#ds_title_form").css({
					"background-color": "#FFCCCC"
				});
				confirm_count++;
			}
			else {
				$("#ds_new_title_error_msg").html("");
				$("#ds_title_form").css({
					"background-color": "#FFFFFF"
				});
			}
			//本文
			if (!$("#ds_description_form")[0].value) {
				$("#ds_new_description_error_msg").html(this.error_msg.description);
				$("#ds_description_form").css({
					"background-color": "#FFCCCC"
				});
				confirm_count++;
			}
			else {
				$("#ds_new_description_error_msg").html("");
				$("#ds_description_form").css({
					"background-color": "#FFFFFF"
				});
			}
			//タグ
			var tag_value_sum = function(){
				var tag = ds.tag.tag_list;
				var sum = 0;
				for (var i = 0; i < tag.length; i++) {
					sum += tag[i];
				}
				if (sum == 0) {
					$("#ds_new_tag_error_msg").html(_this.error_msg.tag);
					return 1;
				}
				else {
					$("#ds_new_tag_error_msg").html("");
					return 0;
				}
			};
			confirm_count += tag_value_sum();
			
			//必須チェックOK
			if (confirm_count == 0) {
				return true;
			}
			//必須チェックNG
			else {
				return false;
			}
		}
	};
})(ds_common);
