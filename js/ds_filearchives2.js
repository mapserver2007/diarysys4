(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.filearchives2 = {
		/**
		 * パラメータ初期化
		 */
		init: function(){
		 	//初期パラメータ
		 	this.send_to = './pl/filearchives2.pl';
		 	this.prefix = 'archive'; //ファイルIDにつけるプレフィックス
		 	this.mouseover_color = "lightyellow";
		 	this.mouseout_color = "";
		 	this.click_color = "beige"
		 	this.current_page = 1; //初期表示のページ番号
		 	this.page_slice = [10, 20, 30, 40, 50];
		 	this.page_slice_num = this.page_slice[2]; //一度に表示する件数

		 	//初期実行メソッド
		 	this.init_upload();
		 	this.init_delete();
		 	this.init_delete_list();
		 	this.init_list();
		 	this.create_select();
		},
		/**
		 * 処理開始
		 */
		start: function(){
			this.init();
		},
		/**
		 * アップロード処理初期化
		 */
		init_upload: function(){
			var self = this;
			new AjaxUpload('#upload_button', {
				action: this.send_to,
				name: 'filename',
				data: {method: 'insert'},
				responseType: 'json',
				onSubmit: function(){
					$("#file_archives_list").html("");
					self.message('file_archives_init', "画像をアップロードしています...");
				},
				onComplete: function(file, response) {
					if(response == "success."){
						self.init_list();
					}
					else{
						$("#file_archives_list").html("");
						self.message('file_archives_failed', "画像のアップロードに失敗しました。");
					}
				}
			});
		},
		/**
		 * 削除処理初期化
		 */
		init_delete: function(){
			var self = this;
			$("#delete_button").click(function(e){
				if(self.delete_list.length == 0){
					alert("削除する画像を1つ以上選択してください。");
					return;
				}
				var query = '[' + self.delete_list.toString() + "]";
				$("#file_archives_list").html("");
				self.message('file_archives_init', "画像を削除しています...");
				$.ajax({
					type: 'POST',
					url: self.send_to,
					data: {method: 'delete', query: query},
					dataType: 'json',
					success: function(res){
						if(res == "success."){
							self.init_list();
						}
						else{
							alert(res);
						}
						self.init_delete_list();
					}
				});
			});
		},
		/**
		 * 一覧表示処理初期化
		 */
		init_list: function(){
			var self = this;
			var image_dir = "./upload/";
			var thumbnail_dir = "./upload/thumbnail/";
			//初期表示
			$("#file_archives_list").html("");
			this.message('file_archives_init', "画像のリストを読み込んでいます...");
			//一覧取得
			$.ajax({
				type: 'POST',
				url: self.send_to,
				data: {method: 'select', page: self.current_page, slice: self.page_slice_num},
				dataType: 'json',
				success: function(response){
					var res = response.custom;
					$("#file_archives_init").remove();
					if(res.length == 0){
						self.message('file_archives_notfound', "画像は登録されていません。");
					}
					else{
						var ol = $("<ol>").addClass("file_archives_list");
						for(var i = 0; i < res.length; i++){
							var tbl = "<table border='0'><tbody>" +
								"<tr><td class='thumbnail_bg'>" +
								"<a rel='facebox' href='" + image_dir + res[i].filename + "' target='_blank'>" +
								"<img src='" + thumbnail_dir + res[i].filename + "'/></a>" +
								"</td></tr><td>" +
								"<p>" + res[i].filename + "</p>" +
								"<p>" + (res[i].filesize / 1024).toFixed(1) + "KB" + "</p>" +
								"<p>" + res[i].date + "</p>" +
								"</td><tr></tr>" +
								"</tbody></table>";
							$("<li>").addClass("file_archives_thumbnail").attr("id", self.prefix + res[i].id)
								.append($("<div>").addClass("thumbnail_field").html(tbl))
								.mouseover(function(){
									if(this.style.backgroundColor != self.click_color){
										this.style.backgroundColor = self.mouseover_color;
									}
								})
								.mouseout(function(){
									if(this.style.backgroundColor != self.click_color){
										this.style.backgroundColor = self.mouseout_color;
									}
								})
								.click(function(e){
									if(this.style.backgroundColor != self.click_color){
										this.style.backgroundColor = self.click_color;
										self.push_delete_list(this.id);
									}
									else{
										this.style.backgroundColor = self.mouseout_color;
										self.pop_delete_list(this.id);
									}
								})
								.appendTo(ol);
						}
						$("#file_archives_list").append(ol);
						$('a[rel*=facebox]').facebox(); //Requre facebox plugin
						self.create_pager(response.common);
					}
				}
			});
		},
		/**
		 * Pagerを生成する
		 */
		create_pager: function(common){
			var self = this;
			$("#file_archives_pagination").html("");
			//ページ数を算出
			var page_num = Math.ceil(common.count / this.page_slice_num);
			var elem = $("<div>").attr("id", "pagination");
			for(var i = 1; i <= page_num; i++){
				//PREVIEW
				if(i == 1 && common.current == 1){
					$("<span>").addClass("disabled prev_page").html("&laquo;Preview").appendTo(elem);
				}
				else if(i == 1 && common.current != 1){
					$("<a>").attr("href", "javascript:void(0);")
						.html("&laquo;Preview")
						.click(function(){
							self.current_page--;
							self.init_list();
						})
						.appendTo(elem);
				}

				//Paginate
				if(i == common.current){
					$("<span>").attr("href", "javascript:void(0);")
						.addClass("current").html(i).appendTo(elem);
				}
				else{
					$("<a>").attr("href", "javascript:void(0);")
					.click(function(){
						self.current_page = this.innerHTML;
						self.init_list();
					})
					.html(i)
					.appendTo(elem);
				}

				//NEXT
				if(i == page_num && common.current == page_num){
					$("<span>").addClass("disabled next_page").html("Next&raquo;").appendTo(elem);
				}
				else if(i == page_num && common.current != page_num){
					$("<a>").attr("href", "javascript:void(0);")
					.html("Next&raquo;")
					.click(function(){
						self.current_page++;
						self.init_list();
					})
					.appendTo(elem);
				}
			}
			$("#file_archives_pagination").append(elem);
		},
		/**
		 * セレクトボックスを生成する
		 */
		create_select: function(){
			var self = this;
			var select = $("<select>").change(function(){
				self.page_slice_num = this.value;
				self.init_list();
			});
			for(var i = 0; i < this.page_slice.length; i++){
				var option = $("<option>").attr("value", this.page_slice[i])
					.html(this.page_slice[i] + "件")
				if(this.page_slice[i] == this.page_slice_num){
					option.attr("selected", "selected")
					.appendTo(select);
				}
				else{
					option.appendTo(select);
				}
			}
			$("#file_archives_select").append(select);
		},
		/**
		 * 削除するファイルIDを格納するリストの初期化
		 */
		init_delete_list: function(){
			this.delete_list = [];
		},
		/**
		 * 削除するファイルIDを格納
		 */
		push_delete_list: function(fileid){
			this.delete_list.push(fileid.replace(this.prefix, ""));
		},
		/**
		 * 削除するファイルIDを除去
		 */
		pop_delete_list: function(fileid){
			var tmp_list = [];
			fileid = fileid.replace(this.prefix, "");
			for(var i = 0; i < this.delete_list.length; i++){
				if(this.delete_list[i] != fileid){ tmp_list.push(this.delete_list[i]); }
			}
			this.delete_list = tmp_list;
		},
		/**
		 * メッセージの表示
		 */
		message: function(id, msg){
			$("#file_archives_list").append(
			 	$("<div>").attr("id", id).html(msg)
			 );
		}
	}
})(ds_common);