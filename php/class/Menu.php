<?php
/**
 * メニューリンククラス
 * @author   Ryuichi TANAKA
 * @version  2008/10/10
 */
class Menu extends Common {
	/**
	 * コンストラクタ
	 * @return
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * リンク要素を定義
	 * @return
	 */
	private function defineLinkMenu(){
		$this->entry_data["link"] = array(
			array(
				"url" => ".",
				"text" => "Home",
				"img" => "./image/home.png"
			),
			array(
				"url" => $this->getLink(),
				"text" => "Manage",
				"img" => "./image/manage.png"
			),
			array(
				"url" => "?m=rss",
				"text" => "RSS",
				"img" => "./image/rss.gif"
			)
		);
	}
	/**
	 * カレンダー要素を定義
	 * @return
	 * @param object $get
	 */
	private function defineCalendar($get){
		$calendar = new dsCalendar();
		//GETデータをバリデーション
		if($get){
			$mode = $this->dispModeChack($this->getValidRequestData($get, "Entry"));
			if($mode["type"] == "month" || $mode["type"] == "day"){
				list($year, $month) = explode("-", $mode["value"]);
				$calendar->setDate($year, $month);
			}
		}
		$this->entry_data["calendar"] = $calendar->getCalendar();
	}
	/**
	 * 月別アーカイブを定義
	 * @return
	 */
	private function defineArchives(){
		$calendar = new dsCalendar();
		$this->entry_data["archives"] = $calendar->getEntryArchives();
	}
	/**
	 * チャートへのURLを定義
	 * @return
	 */
	private function defineChartURL(){
		$chart = new Chart();
		$chart->encodeChartData($this->entry_data["archives"]);
		$this->entry_data["chart"] = $chart->getChartURL($this->entry_data["archives"]);
	}
	/**
	 * タイムラインのHTMLを定義
	 * @return
	 */
	private function defineTimeLine(){
		$timeline = new TimeLine();
		$this->entry_data["timeline"] = $timeline->getTimeLineHtml();
	}
	/**
	 * 表示の準備
	 * @return
	 */
	public function prepare($mode, $get = null){
		//メインメニュー(サイドバー)
		if($mode == "main"){
			//リンクを定義
			$this->defineLinkMenu();
		}
		//サブメニュー01(カレンダー、チャート)
		else if($mode == "sub_01"){
			//カレンダーを定義
			$this->defineCalendar($get);
			//月別アーカイブを定義
			$this->defineArchives();
			//チャート
			//$this->defineChartURL();
		}
		//サブメニュー02(タイムライン)
		else if($mode == "sub_02"){
			//タイムラインを定義
			$this->defineTimeLine();
		}
	}
	/**
	 * 認証のリンクを取得
	 * @return
	 */
	private function getLink(){
		$auth = new Auth();
		$auth->prepare();
		$url = $auth->getLivedoorAuthURL();
		return $url;
	}
	/**
	 * テンプレートを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->entry_data);
	}
}
?>
