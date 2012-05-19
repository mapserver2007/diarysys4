<?php
/**
 * 天気情報取得クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/24
 */
class Weather {
	private $weather, $wid, $wurl;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		$this->defineWeatherURL();
		//$this->parseXML();
	}
	/**
	 * WeatherHacksのURL定義
	 * @return 
	 */
	private function defineWeatherURL(){
		$this->weather = array(
			"detail" => "http://weather.livedoor.com/area/13/63.html?v=1",
			"icon" => "http://image.weather.livedoor.com/img/icon/?.gif",
			"xml" => "http://weather.livedoor.com/forecast/webservice/rest/v1?city=63&day=today"
		);
	}
	/**
	 * WeatherHacksのXMLをパースし、ICONの画像IDをセットする
	 * @return 
	 */
	public function parseXML(){
		$xml = simplexml_load_file($this->weather["xml"]);
		$ids = preg_split("[/|\.]", $xml->image->url);
		$this->wid = $ids[8];
		$this->wurl = preg_replace("/\?/", $this->wid, $this->weather["icon"]);
	}
	/**
	 * WeatherHacksのICONの画像IDを返す
	 * @return $this->wid int
	 */
	public function getWeatherId(){
		return $this->wid;
	}
	/**
	 * WeatherHacksのICONの画像URLを返す
	 * @return 
	 */
	public function getWeatherImage($wid){
		return preg_replace("/\?/", $wid, $this->weather["icon"]);
	}
	
}
?>
