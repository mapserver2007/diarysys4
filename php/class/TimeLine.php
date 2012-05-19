<?php
/**
 * TimeLineクラス
 * @nifty TimeLineを使用
 * @author   Ryuichi TANAKA
 * @version  2009/04/04
 */
class TimeLine {
	private $html;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		$this->defineTimeLineData();
	}
	/**
	 * TimeLineデータを定義する
	 * @return
	 */
	private function defineTimeLineData(){
		//表示するタイムラインの領域の高さ
		$height = 300;
		//パネルを表示するかどうか
		$panel = "open";
	
		$this->html = '<iframe src="http://timeline.nifty.com/flash/blogparts/12068?height='.$height.'&panel='.$panel.'&linktype=baloon" 
			width="100%" height="'.$height.'" scrolling="no" frameborder="0"></iframe>';
	}
	/**
	 * @nifty TimeLineのHTMLを返す
	 * @return
	 */
	public function getTimeLineHtml(){
		return $this->html;
	}
}
?>