<?php
/**
 * Chartクラス
 * Google Chart APIを使用
 * @author   Ryuichi TANAKA
 * @version  2009/01/25
 */
class Chart {
	private $chart_data;
	private $api_url;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		$this->defineChartData();
	}
	/**
	 * Chartデータを定義する
	 * @return
	 */
	private function defineChartData(){
		//APIへのURL
		$this->api_url = "http://chart.apis.google.com/chart?";
		//グラフパラメータ
		$this->chart_data = array(
			"chxl" => "0:|0|10|20|30|40|50", //y軸ラベル
			"chs"  => "200x100",             //画像サイズ
			"chd"  => "",                    //表示するデータ
			"cht"  => "lc",                  //グラフの種類
			"chxt" => "y",                   //有効にする軸のラベル
			"chls" => "2.0",                 //ラインスタイル
			"chco" => "76A4FB"               //ラインカラー
		);
	}
	/**
	 * Chartデータをエンコードする
	 * @return
	 */
	public function encodeChartData($chart_data_obj){
		//エンコード文字列定義
		$simpleEncoding = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		//エンコード
		$chart_data = "s:";
		foreach($chart_data_obj as $year){
			foreach($year as $month => $count){
				if(is_int($count) && $count >= 0){
					$chart_data .= substr($simpleEncoding, round((strlen($simpleEncoding)-1) * $count / 50), 1);
				}
				else{
					$chart_data .= "_";
				}
			}
 		}
 		$this->chart_data["chd"] = $chart_data;
 		
		//URL生成
		foreach($this->chart_data as $k => $v)
			$this->api_url .= "{$k}={$v}&";
		
		//末尾を削る
		$this->api_url = substr($this->api_url, 0, -1);
	}
	/**
	 * Google Chart APIへのURLを返す
	 * @return
	 */
	public function getChartURL(){
		return $this->api_url;
	}
}
?>