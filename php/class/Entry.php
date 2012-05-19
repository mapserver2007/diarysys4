<?php
/**
 * エントリ系クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/15
 */
class Entry extends Common {
	private $db;
	private $limit, $offset;
	private $type_value;
	const PAGER = true;
	//const CALENDAR = true; //一行カレンダーを有効にする場合はtrue
	/**
	 * コンストラクタ
	 * @return
	 */
	public function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
		//$this->db = new DB();
		$this->limit = 0;
		$this->offset = $this->entry_num;
	}
	/**
	 * エントリ表示処理の準備
	 * @return 
	 * @param $mode Array
	 */
	public function prepare($get){
		$this->db = new DB();
		//表示モード
		$modes = $this->dispModeChack($this->getValidRequestData($get, "Entry"));
		//Pageが指定された場合
		foreach($modes as $value){
			if($value["type"] == "page" && self::PAGER){
				//ページ番号のセット
				$this->setEntryByPage($value["value"]);
			}
			else{
				$mode = $value;
			}
		}
		//表示モードごとにSQL構築
		switch($mode["type"]){
			case "id" : 
				$this->setEntryById($mode["value"]);
				break;
			case "tag" : 
				$this->setEntryByTag($mode["value"]);
				break;
//			case "page" :
//				$this->setEntryByPage($mode["value"]);
//				break;
			case "month" :
				$this->setEntryByMonth($mode["value"]);
				break;
			case "day" : 
				$this->setEntryByDay($mode["value"]);
				break;
			case "error" : 
				$this->setEntryByError();
				break;				
			default : 
				$this->setEntryDefault();
				break;
		}
	}
	/**
	 * 通常のエントリ表示
	 * @return 
	 */
	private function setEntryDefault($sql = "SELECT * FROM diary4 ORDER BY id DESC", $bind = array()){
		//Weatherオブジェクト生成
		$weather = new Weather();
		//Pager
		$entry_count = 0;
		$page_res = "";
		if(self::PAGER){
			//全エントリの総数を取得
			$this->db->sql($sql, $bind);
			$entry_count = $this->db->num();
			//表示数を制限する
			$sql .= " LIMIT {$this->limit}, {$this->offset}";
			//Pagerのリンク先生成
			$href = $this->type_value ? "?{$this->type}={$this->type_value}&page=%d" : "?page=%d";
			//結果に格納
			$page_res = array(
				"count" => $entry_count,
				"offset" => $this->offset,
				"current" => $this->limit / $this->offset + 1,
				"href" => $href
			);
		}
		//SQL実行
		$i = 0;
		if($this->db->sql($sql, $bind)){
			//取得データを配列にまとめる
			while($row = $this->db->fetch()){
				$this->entry_data[$i++] = array(
					"id" => $row["id"],
					"title" => $this->to_escape($row["title"]),
					"description" => $this->to_escape($row["description"]),
					"tag" => $row["tag"],
					"date" => $row["date"],
					"weather" => $weather->getWeatherImage($row["weather_id"]),
				);
			}
			if($page_res){
				$this->entry_data[0]["page"] = $page_res;
			}
		}
		//エントリが見つからなかったら、エラー表示
		if($i == 0){
			$this->setEntryByError();
			return;
		}
		//タグIDからタグ名を取得
		$this->setTagName();
		
		//関連エントリ
		$j = 0;
		foreach($this->entry_data as $value){
			$res = array();
			//SQL
			$sql = "SELECT d.id id, d.title title, 
				r.rel_value1 val1, r.rel_value2 val2, r.rel_value3 val3, r.rel_value4 val4, r.rel_value5 val5, 
				r.rel_entry1 eid1, r.rel_entry2 eid2, r.rel_entry3 eid3, r.rel_entry4 eid4, r.rel_entry5 eid5 
				FROM diary4 d, diary4_relation r 
				WHERE (d.id = rel_entry1 OR d.id = rel_entry2 OR d.id = rel_entry3 OR d.id = rel_entry4 OR d.id = rel_entry5) 
				AND r.eid = :id GROUP BY d.id";	
			$bind = array("id" => $value["id"]);
			//実行
			if($this->db->sql($sql, $bind)){
				while($row = $this->db->fetch()){
					$rel_key = "";
					for($i = 1; $i <= 5; $i++){
						$eid_key = "eid" . $i;
						if($row["id"] == $row[$eid_key]){
							$rel_key = "val" . $i;
							break;
						}
					}
					$res[] = array(
						"id" => $row["id"],
						"title" => $row["title"],
						"rel" => $row[$rel_key]
					);
				}
			}
			//関連度の降順にソート
			$rel_tmp = array();
			foreach ($res as $v) $rel_tmp[] = $v['rel'];
			array_multisort($rel_tmp, SORT_DESC, SORT_NUMERIC, $res);

			//関連エントリをに統合
			$this->entry_data[$j++]["relation"] = $res;
		}
	}
	/**
	 * IDを指定してエントリ表示
	 * @return 
	 * @param object $id
	 */
	private function setEntryById($id){
		//SQL
		$sql = "SELECT * FROM diary4 WHERE id = :id";
		$bind = array("id" => $id);
		//実行
		$this->setEntryDefault($sql, $bind);
	}
	/**
	 * タグを指定してエントリ表示
	 * @return 
	 * @param object $tid
	 */
	private function setEntryByTag($tid){
		//type値保存
		$this->type = "tag";
		$this->type_value = $tid;
		//SQL
		$sql = "SELECT id, tag FROM diary4 ORDER BY id DESC";
		$bind = array();		
		//SQL実行
		$this->db->sql($sql, $bind);
		//取得データを配列にまとめる
		$i = 0;
		$res_tid = array();
		while($row = $this->db->fetch()){
			//カンマ区切りを配列にする
			$tag_ary = explode(",", $row["tag"]);
			//照合し、一致したものがあれば配列に格納
			foreach($tag_ary as $t){
				if($tid == $t)
					$res_tid[] = $row["id"];
			}
		}
		//エントリが見つからなかったら、エラー表示
		if(count($res_tid) == 0){
			$this->setEntryByError();
			return;
		}
		//SQL
		$sql = "SELECT * FROM diary4 WHERE ";
		$bind = array();
		//タグをSQLの条件文化
		foreach($res_tid as $id){
			$sql .= "id = $id OR ";
		}
		$sql = substr($sql, 0, -3);
		$sql.= "ORDER BY id desc";
		//実行
		$this->setEntryDefault($sql, $bind);		
	}
	/**
	 * ページを指定してエントリ表示
	 * @return 
	 * @param object $page[optional]
	 */
	private function setEntryByPage($page = 1){
		//スタート位置
		$this->limit = --$page * $this->entry_num;
		//一度に表示する件数
		$this->offset = $this->entry_num;
		//実行
		//$this->setEntryDefault();		
	}
	/**
	 * 月を指定してエントリ表示
	 * @return 
	 * @param object $month
	 */
	private function setEntryByMonth($month){
		//type値保存
		$this->type = "month";
		$this->type_value = $month;
		//SQL
		$sql = "SELECT * FROM diary4 WHERE date LIKE :month ORDER BY id DESC";
		$bind = array("month" => $month . "%");
		//実行
		$this->setEntryDefault($sql, $bind);
	}
	/**
	 * 日を指定してエントリ表示
	 * @return 
	 * @param object $day
	 */
	private function setEntryByDay($day){
		//SQL
		$sql = "SELECT * FROM diary4 WHERE date LIKE :day ORDER BY id DESC";
		$bind = array("day" => $day . "%");
		//実行
		$this->setEntryDefault($sql, $bind);
	}
	/**
	 * タグIDをタグ名にして再格納する
	 * @return 
	 */
	private function setTagName(){
		$this->db = new DB();
		for($j = 0; $j < count($this->entry_data); $j++){
			$tag = explode(",", $this->entry_data[$j]["tag"]);
			$tag_ary = array();
			for($i = 0; $i < count($tag); $i++){
				//タグIDをタグ名に変換するSQL
				$sql = "SELECT tid, tname FROM diary4_tag_classify WHERE tid = :tid";
				$bind = array("tid" => $tag[$i]);
				//SQL実行
				$this->db->sql($sql, $bind);
				$row = $this->db->fetch();
				$tag_ary[] = array(
					"id" => $row["tid"],
					"name" => $row["tname"]
				);
			}
			//再度edit_dataに格納する
			$this->entry_data[$j]["tag"] = $tag_ary;
		}
	}
	/**
	 * エントリを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$tmplname = $this->tmplname ? $this->tmplname : $tmplname;
		$this->showTemplate($tmplname, $this->entry_data);
	}
}
?>
