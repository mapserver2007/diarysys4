<?php
/**
 * 管理系クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/??
 */
class Edit extends Common {
	private $edit_data;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
		$this->db = new DB();
	}
	/**
	 * 管理画面に表示するエントリ一覧を取得する
	 * @return 
	 */
	public function editlist(){
		//SQL
		$sql = "SELECT id, title, tag, date FROM diary4 
				ORDER BY date DESC";
		$bind = array();
		//SQL実行
		$this->db->sql($sql, $bind);
		//取得データを配列にまとめる
		$i = 0;
		while($row = $this->db->fetch()){
			$this->edit_data[$i++] = array(
				"id" => $row["id"],
				"title" => $row["title"],
				"tag" =>  $row["tag"],
				"date" => $row["date"]
			);
		}
		$this->setTagName();
	}
	
	//public function 
	
	/**
	 * タグIDをタグ名にして再格納する
	 * @return 
	 */
	private function setTagName(){
		for($j = 0; $j < count($this->edit_data); $j++){
			$tag = explode(",", $this->edit_data[$j]["tag"]);
			$tname = "";
			for($i = 0; $i < count($tag); $i++){
				//タグIDをタグ名に変換するSQL
				$sql = "SELECT tname FROM diary4_tag_classify WHERE tid = :tid";
				$bind = array("tid" => $tag[$i]);
				//SQL実行
				$this->db->sql($sql, $bind);
				$row = $this->db->fetch();
				$tname .= ($row["tname"] . " ");
			}
			//再度edit_dataに格納する
			$this->edit_data[$j]["tag"] = chop($tname);
		}
	}
	/**
	 * 管理画面を表示する(テンプレート表示メソッドの呼び出しのみ。)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->edit_data);
	}	
}
?>
