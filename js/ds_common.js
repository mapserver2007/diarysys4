/**
 * @author summer-lights
 */
var ds_common = function(){};

/**
 * 全てのクラスから参照できる共通設定
 */	
(function(_$){
	
	var ds_base = function(){ this.common.construct(); };
	
	ds_base.prototype = {
		/**
		 * 共通設定
		 */
		common: {
			/**
			 * コンストラクタ
			 */
			construct: function(){
				//Facebox
				$(document).ready(function(){
					$('a[rel*=facebox]').facebox();
				});
			},
			/**
			 * ローディングエフェクト
			 * @param {Object} m
			 */
			loading : function(id, m){
				var _this = this;
				//画像パス
				var img_path = "./image/ajax-loader.gif";
				
				if(m == "on"){
					$("#" + id).css({
						"background-image": "url(\"" + img_path + "\")", 
						"background-repeat": "no-repeat", "background-position": "center"
					});
				}else if(m == "off"){
					$("#" + id).css({
						"background-image": "url(\"\")"
					});
				}
			}
		}
	};
	
	//ds_commonへ継承する
	_$.prototype = new ds_base();

})(ds_common);
