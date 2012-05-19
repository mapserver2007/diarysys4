(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.manage = {
		/**
		 * パラメータ初期化
		 */
		init : function(){
			this.url = "./php/ajax.php"; //問い合わせURL
			//tablesorter開始
			$("#manage_list_table").tablesorter({
				headers : {
					0 : {sorter : false},
					1 : {sorter : false},
					2 : {sorter : false},
					3 : {sorter : false},
					4 : {sorter : false}
				},
				widgets : ['zebra']
			});
		},
		/**
		 * 処理開始
		 */
		start : function(){
			//初期化
			this.init();
		},
		/**
		 * 本文を表示する
		 * @param {Object} id
		 */
		description: function(id){
			//var parent = "manage_list_detail_" + id;
			var img  = "manage_list_detail_img_" + id;
			var target = "manage_list_description_" + id;
			//パラメータ設定
			var param = {mode : "description", id : id};
			//JSONP開始
			var url = this.url + "?" + "callback=?";
			$.getJSON(url, param, function(json){
				//本文がすでに表示されているときは消す
				if($("#" + target).get(0).innerHTML){
					$("#" + target).html("");
					$("#" + img).attr("src", "./image/table_asc.gif");
				}
				else{
					$("#" + target).html(json[0].description);
					ds.newpost.setEscape("off");
					ds.newpost.preview({to : target, from : target});
					prettyPrint();
					$("#" + img).attr("src", "./image/table_desc.gif");
				}
			});
		}
	};
})(ds_common);