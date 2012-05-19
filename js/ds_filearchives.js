(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.filearchives = {
		/**
		 * パラメータ初期化
		 */
		init: function(){
			//ファイルアップロード・削除のURL
			this.filearchives_delete_url = "./pl/upload.pl";
			//ファイルアーカイブスリストAPIのURL
			this.filearchives_list_url = "./pl/filearchives.pl";
			//アップロードファイルのディレクトリ名
			this.dirname = "upload";
			//ローディング画像のパス
			this.load_img = "./image/ajax-loader.gif";	
		},
		/**
		 * アップロード後の処理開始
		 * @param {Object} json
		 */
		start : function(json){
			this.init();
			this.read(json);
		},
		/**
		 * アップロード後のコールバック
		 * @param {Object} json
		 */
		read : function(json){
			if(json == undefined){
				this.getList("");
				return;
			}
			if(json.success == "success"){
				this.getList(json.message);
			}
			else if(json.success == "failure"){
				alert(json.error_message);
			}
		},
		/**
		 * Submitの入力チェック
		 * @param {Object} form_name
		 */
		submit : function(form_name){
			//アップロードファイルチェック
			var filename = document.forms[form_name].filename.value;
			//何も選択されていなければNG
			if(filename == ""){
				alert("アップロードするファイルを指定してください");
				return false;	
			}
			else {
				this.loading('on');
				return true;
			}
		},
		/**
		 * チェックボックスの必須チェック
		 * @param {Object} form_name
		 * @param {Object} elem_name
		 */
		check : function(form_name, elem_name){
			//チェックボックスの数をカウント
			var elem = document.forms[form_name].elements[elem_name];
			var cnt = 0;
			//必須チェック
			for(var i = 0; i < elem.length; i++){
				if(elem[i].checked == true)
					cnt++;
			}
			//一つもチェックされていないとNG
			if (cnt == 0) {
				alert("チェックボックスを選択してください");
				return false;
			}
			//一つでもチェックがあればOK
			else {
				this.loading('on');
				return true;
			}
		},
		/**
		 * ファイルアーカイブ一覧を表示
		 */
		getList : function(msg){
			var _this = this;
			var url = this.filearchives_list_url + "?" + "callback=?";	
			//JSONP開始
			$.getJSON(url, function(json){
				var idx = json.length - 1;
				if(json[idx].Result == "success"){
					var html = "<form name=\"file_archives_form\" action=\"" + _this.filearchives_delete_url + "\" target=\"upload\" method=\"POST\" onsubmit=\"return ds.filearchives.check('file_archives_form', 'delete');\">";
					//html += "<input type=\"submit\" value=\"削除\" onclick=\"ds.filearchives.loading('on');\" />";
					html += "<input type=\"submit\" value=\"削除\" />";				
					html += "<span class=\"upload_result\">" + msg + "</span>";
					html += "<table id=\"file_archives_list_table\" class=\"tablesorter\">";
					html += "<thead>";
					html += "<tr>";
					html += "<th scope='col' width='1%'></th>";
					html += "<th scope='col' width='20%'>ファイル名</th>";
					html += "<th scope='col' width='30%'>オリジナルファイル名</th>";
					html += "<th scope='col' width='5%'>種類</th>";
					html += "<th scope='col' width='20%'>ファイルサイズ</th>";
					html += "<th scope='col' width='24%'>保存日時</th>";
					html += "</tr>"
					html += "</thead>";
					html += "<tbody>";
					for(var i = 0; i < idx; i++){
						html += "<tr>";
						html += "<td>" + "<input type=\"checkbox\" value=\"" + json[i].id + "\" name=\"delete\" />" + "</td>"
						html += "<td>" + "<a href=\"./" + _this.dirname + "/" + json[i].filename + "\" target=\"_blank\">" + json[i].filename + "</a></td>";
						html += "<td>" + json[i].original_filename + "</td>";
						html += "<td>" + json[i].filetype + "</td>";	
						html += "<td>" + (json[i].filesize / 1024).toFixed(1) + "KB</td>";
						html += "<td>" + json[i].date + "</td>";
						html += "</tr>"
					}
					html += "</tbody>";
					html += "</table>";
					html += "</form>";
					$("#file_archives_list").html(html);
					$("#file_archives_list_table").tablesorter({widgets : ['zebra']});
				}
				else if(json[idx].Result == "failure"){
					$("#file_archives_list").html("No Data.");
				}
				_this.loading('off');
			});
		},
		/**
		 * ローディング画像表示
		 * @param {Object} m
		 */
		loading : function(m){
			if(m == "on"){
				$("#file_archives_progress").html("");
				$(document.createElement("img"))
					.attr("src", this.load_img)
					.appendTo("#file_archives_progress");
			}else if(m == "off"){
				$("#file_archives_progress").html("");
			}		
		}
	};
})(ds_common);