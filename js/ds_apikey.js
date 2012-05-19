(function(_$){
	/**
	 * @author summer-lights
	 */
	_$.prototype.apikey = {
		/**
		 * パラメータ初期化
		 */
		init: function(){
			$("#apikey_list_table").tablesorter({
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
		}
	 };
})(ds_common);