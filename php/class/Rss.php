<?php
/**
 * RSSクラス
 * @author   Ryuichi TANAKA
 * @version  2008/12/14
 */
class Rss extends Common {
	private $db;
	const ROOT_URL  = "http://summer-lights.dyndns.ws/diarysys4/";
	const SITE_NAME = "SUMMER-LIGHTS";
	const CREATOR   = "summer-lights";
	const DESCRIPT  = "DIARY SYSTEM Ver.4";
	/**
	 * コンストラクタ
	 * @return 
	 * @param $list Object
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
		$this->db = new DB();
	}
	
	public function prepare(){
		//ヘッダを出力
		header('content-type: text/xml; charset=utf-8');
		//RSSのタイトル
		$this->entry_data["channel"] = array(
			'about' => self::ROOT_URL,           // RSSのURL
			'title' => self::SITE_NAME,          // サイト名
			'link' => self::ROOT_URL,            // サイトのURL
			'description' => self::DESCRIPT,
			'creator' =>self::CREATOR
		);
		//最新のエントリを取得する
		$sql = "SELECT * FROM diary4 ORDER BY id DESC LIMIT 0, $this->rss_num";
		$bind = array();
		//SQL実行
		$this->db->sql($sql, $bind);
		//取得データを配列にまとめる
		$i = 0;
		while($row = $this->db->fetch()){
			$this->entry_data["rss"][$i++] = array(
				"title" => $this->to_utf8($row["title"]),
				"description" => $this->to_utf8($this->to_cdata($row["description"])),
				'link' => self::ROOT_URL . "?id=" . $row["id"],
				"subject" => $row["tag"],
				"date" => $row["date"]
			);
		}
		//タグIDからタグ名を取得
		$this->setTagName();
	}
	/**
	 * BBコードをHTMLにする
	 * @return
	 */
	private function bbcode2html($bbstr){
		//改行を変換	
		$bbstr = $this->s($bbstr);
		//BBコードをHTMLに変換
		foreach($this->bbcode as $code){
			$bbstr = preg_replace($code["pattern"], $code["replacement"], $bbstr);
		}
		return $bbstr;
	}
	/**
	 * descriptionをCDATA化する
	 * @return String
	 */
	private function to_cdata($description){
		return "<![CDATA[{$this->bbcode2html($description)}]]>";
	}
	/**
	 * タグIDをタグ名にして再格納する
	 * @return 
	 */
	private function setTagName(){
		$rss_data = $this->entry_data["rss"];
		for($j = 0; $j < count($rss_data); $j++){
			$tag = explode(",", $rss_data[$j]["subject"]);
			$tag_ary = "";
			for($i = 0; $i < count($tag); $i++){
				//タグIDをタグ名に変換するSQL
				$sql = "SELECT tname FROM diary4_tag_classify WHERE tid = :tid";
				$bind = array("tid" => $tag[$i]);
				//SQL実行
				$this->db->sql($sql, $bind);
				$row = $this->db->fetch();
				$tag_ary .= $row["tname"] . ",";
			}
			//再度edit_dataに格納する
			$this->entry_data["rss"][$j]["subject"] = substr($this->to_utf8($tag_ary), 0, -1);
		}
	}
	/**
	 * エントリを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		//$tmplname = $this->tmplname ? $this->tmplname : $tmplname;
		$this->showTemplate($tmplname, $this->entry_data);
		//var_dump($this->entry_data);
	}
}
?>