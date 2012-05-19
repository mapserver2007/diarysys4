<?php
/**
 * 管理系クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/20
 */
class Manage extends Common {
	private $edit_id;
	private $edit_type = array();
	private $limit, $offset;
	const PAGER = true;
	/**
	 * コンストラクタ
	 * @return
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
		$this->db = new DB();
		$this->limit = 0;
		$this->offset = $this->entry_num;
	}
	/**
	 * 管理画面に表示するエントリ一覧を取得する
	 * @return
	 */
	public function entryList($mode = "default", $data = null){
		$column_list = "";
		$escape_list = array("utf8" => array());
		$esc_mode = "escape";
		$sql = "";
		$bind = "";
		try{
			//modeごとに実行するSQLを定義
			//管理画面で本文を表示するとき
			if($mode == "description"){
				if(!$data["id"]){
					throw new Exception("Can't set description id");
				}
				$sql = "SELECT description FROM diary4 WHERE id = :id";
				$bind = array("id" => $data["id"]);
				$column_list = array("description");
				$escape_list["utf8"] = array("description");
			}
			//管理画面でエントリ一覧を表示するとき(デフォルト)
			else if($mode == "default"){
				$sql = "SELECT id, title, tag, date FROM diary4 ORDER BY date DESC";
				$bind = array();
				$column_list = array("id", "title", "tag", "date");
			}
			//編集画面にエントリデータを表示するとき
			else if($mode == "edit"){
				if(!$data["id"]){
					throw new Exception("Can't set edit id");
				}
				$sql = "SELECT id, title, description, tag FROM diary4 WHERE id = :id";
				$bind = array("id" => $data["id"]);
				$column_list = array("id", "title", "description", "tag");
				$esc_mode = "descape";
			}
			//削除確認画面にエントリデータを表示するとき
			else if($mode == "delete"){
				if(!$data["id"]){
					throw new Exception("Can't set delete id");
				}
				$sql = "SELECT id, title, description, tag FROM diary4 WHERE id = :id";
				$bind = array("id" => $data["id"]);
				$column_list = array("id", "title", "description", "tag");
			}
			//Pager
			$entry_count = 0;
			$page_res = "";
			if(self::PAGER){
				//ここでバリデート
				$modes = $this->dispModeChack($this->getValidRequestData(array("page" => $_GET["page"] ? $_GET["page"] : 1), "Manage"));
				$page = $modes[0]["type"] == "page" ? $modes[0]["value"] : 0;
				//現在のページ番号
				$this->limit = --$page * $this->entry_num;
				//全エントリの総数を取得
				$this->db->sql($sql, $bind);
				$entry_count = $this->db->num();
				//表示数を制限する
				$sql .= " LIMIT {$this->limit}, {$this->offset}";
				//結果に格納
				$page_res = array("count" => $entry_count, "offset" => $this->offset, "current" => $this->limit / $this->offset + 1);
			}
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
			if($rv != true) throw new Exception("SQL excecute failed");
		}catch(Exception $e){
			die($e);
		}
		//取得データを配列にまとめる
		$i = 0;
		while($row = $this->db->fetch()){
			$this->edit_data[$i] = array();
			foreach($column_list as $value){
				$res = in_array($value, $escape_list["utf8"]) ? $this->to_utf8($row[$value]) : $row[$value];
				if($esc_mode == "escape"){
					$this->entry_data[$i][$value] = $this->to_escape($res);
				}
				else if($esc_mode == "descape"){
					$this->entry_data[$i][$value] = $this->to_descape($res);
				}
			}
			$i++;
		}
		//エントリが見つからなかったら、エラー表示
		if($i == 0){
			$this->setEntryByError();
			return;
		}
		//指定したカラムの中に「tag」があればタグ変換をする
		foreach($column_list as $value){
			if($value == "tag")
				$this->setTagName();
		}
		//Pagerデータをセット
		if($page_res){
			$this->entry_data[0]["page"] = $page_res;
		}
	}
	/**
	 * エントリ登録処理
	 * @return
	 * @param object $post
	 */
	public function setEntryData($post){
		//タイトル
		$title = $post["ds_title"];
		//本文
		$description = $post["ds_description"];
		//タグ
		$tag = "";
		foreach($post["ds_tagid"] as $v){
			$tag .= $v . ",";
		}
		$tag = substr($tag, 0, -1);
		//日付
		$date = date("Y-m-d H:i:s");
		//天気
		$ldw = new Weather();
		$ldw->parseXML();
		$weather = $ldw->getWeatherId();

		try{
			//SQL(エントリ)
			$sql = "INSERT INTO diary4 (title, description, tag, date, weather_id) VALUES (:title, :description, :tag, :date, :weather_id)";
			$bind = array("title" => $title, "description" => $description, "tag" => $tag, "date" => $date, "weather_id" => $weather);
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
			if($rv != true) throw new Exception("SQL excecute failed");

			//タグ参照回数の更新
			$this->tagUpdate($post["ds_tagid"], "ins");

			//参照されていないタグの消去
			$this->tagDelete();

			//関連エントリ処理
			//$this->setRelatedEntry();

		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * エントリ編集処理
	 * @return
	 * @param object $post
	 */
	public function setEditData($post){
		//ID
		$id = $post["ds_id"];
		//タイトル
		$title = $post["ds_title"];
		//本文
		$description = $post["ds_description"];
		//タグ
		$tag = "";
		foreach($post["ds_tagid"] as $v){
			$tag .= $v . ",";
		}
		$tag = substr($tag, 0, -1);

		try{
			//アップデートする前のタグリストを取得する
			$sql_b = "SELECT tag FROM diary4 WHERE id = :id";
			$bind_b = array("id" => $id);
			$rv = $this->db->sql($sql_b, $bind_b);
			if($rv != true) throw new Exception("SQL excecute failed");
			$row = $this->db->fetch();

			//アップデート前に登録されたタグ参照回数を-1する
			$this->tagUpdate(explode(",", $row["tag"]), "del");

			//SQL
			$sql = "UPDATE diary4 SET title = :title, description = :description, tag = :tag WHERE id = :id";
			$bind = array("title" => $title, "description" => $description, "tag" => $tag, "id" => $id);
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
			if($rv != true) throw new Exception("SQL excecute failed");

			//タグ参照回数の更新
			$this->tagUpdate($post["ds_tagid"], "ins");

			//参照されていないタグの消去
			$this->tagDelete();

			//関連エントリ処理
			//$this->setRelatedEntry();

		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * エントリ削除処理
	 * @return
	 * @param object $post
	 */
	public function setDeleteData($post){
		//ID
		$id = $post["ds_id"];
		try{
			//アップデートする前のタグリストを取得する
			$sql_b = "SELECT tag FROM diary4 WHERE id = :id";
			$bind_b = array("id" => $id);
			$rv = $this->db->sql($sql_b, $bind_b);
			if($rv != true) throw new Exception("SQL excecute failed");
			$row = $this->db->fetch();

			//エントリ削除前に登録されたタグ参照回数を-1する
			$this->tagUpdate(explode(",", $row["tag"]), "del");

			//SQL
			$sql = "DELETE FROM diary4 WHERE id = :id";
			$bind = array("id" => $id);
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
			if($rv != true) throw new Exception("SQL excecute failed");

			//参照されていないタグの消去
			$this->tagDelete();

			//関連エントリ処理
			//あまりに重いので、バッチで処理すること
			//$this->setRelatedEntry();

		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * タグ参照回数を更新する
	 * @return
	 * @param object $tag_ary
	 * @param object $mode
	 */
	private function tagUpdate($tag_ary, $mode){
		try{
			//タグ参照回数の更新
			foreach($tag_ary as $tagid){
				//現在の参照回数を取得
				$sql_cf = "SELECT reference FROM diary4_tag_reference WHERE tid = :tid";
				$bind_cf = array("tid" => $tagid);
				//SQL実行
				$rv = $this->db->sql($sql_cf, $bind_cf);
				if($rv != true) throw new Exception("SQL excecute failed");
				$row = $this->db->fetch();
				//タグ参照回数を増やすか減らすか
				$tag_ref = "";
				if($mode == "ins")
					$tag_ref = $row["reference"] + 1;
				else if($mode == "del")
					$tag_ref = $row["reference"] - 1;
				else
					throw new Exception("Tag reference update failed");
				//参照回数を+1する
				$sql_f = "UPDATE diary4_tag_reference SET reference = :reference WHERE tid = :tid";
				$bind_f = array("reference" => $tag_ref, "tid" => $tagid);
				//SQL実行
				$rv = $this->db->sql($sql_f, $bind_f);
				if($rv != true) throw new Exception("SQL excecute failed");
			}
		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * 参照されていないタグを消す
	 * @return
	 */
	private function tagDelete(){
		try{
			//どこからも参照されていないタグはこの時点で消す
			$sql_d = "SELECT tid FROM diary4_tag_reference WHERE reference = 0";
			$bind_d = array();
			//SQL実行
			$rv = $this->db->sql($sql_d, $bind_d);
			if($rv != true) throw new Exception("SQL excecute failed");

			$deltag = array();
			while($row = $this->db->fetch()){
				$deltag[] = $row["tid"];
			}

			foreach($deltag as $tid){
				//referenceから消去
				$dsql_f = "DELETE FROM diary4_tag_reference WHERE tid = :tid";
				$dbind_f = array("tid" => $tid);
				//SQL実行
				$rv = $this->db->sql($dsql_f, $dbind_f);
				if($rv != true) throw new Exception("SQL excecute failed");

				//classifyから消去
				$dsql_c = "DELETE FROM diary4_tag_classify WHERE tid = :tid";
				$dbind_c = array("tid" => $tid);
				//SQL実行
				$rv = $this->db->sql($dsql_c, $dbind_c);
				if($rv != true) throw new Exception("SQL excecute failed");
			}
		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * 編集/削除するエントリデータをセット
	 * @return
	 * @param object $id
	 * @param object $mode
	 */
	public function editList($id, $mode){
		//IDのバリデート。半角数値以外はすべてはじく
		$data = $this->getValidRequestData(array("id" => $id), "Entry");
		if(count($data["invalid"])){
			die("Request ID 「" . $id . "」 invalid");
		}
		//IDに該当するエントリを取得する
		$this->entryList($mode, array("id" => $id));
		//不正なIDが指定されたら終了
		if($this->entry_data == null){
			die("Request ID 「" . $id . "」 invalid");
		}
		$this->entry_data = array(
			"ds_id" => $this->entry_data[0]["id"],
			"ds_title" => $this->to_escape($this->entry_data[0]["title"]),
			"ds_description" => $this->entry_data[0]["description"],
			"ds_tag" => $this->entry_data[0]["tag"]
		);
	}
	/**
	 * タグIDをタグ名にして再格納する
	 * @return
	 */
	private function setTagName(){
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
			//再度entry_dataに格納する
			$this->entry_data[$j]["tag"] = $tag_ary;
		}
	}
	/**
	 * 登録画面に戻すときのデータに整形する
	 * @return
	 * @param object $post
	 */
	public function setBack($post){
		$back_data = array();
		//ID
		$back_data["ds_id"] = $post["ds_id"];
		//タイトル
		$back_data["ds_title"] = $post["ds_title"];
		//本文
		$back_data["ds_description"] = $post["ds_description"];
		//タグ(ここだけのちにJSON化)
		$back_data["ds_tag"] = array();
		for($i = 0; $i < count($post["ds_tagid"]); $i++){
			$back_data["ds_tag"][] = array(
				"id"   => $post["ds_tagid"][$i],
				"name" => $post["ds_tagname"][$i]
			);
		}
		$this->entry_data = $back_data;
	}
	/**
	 * エントリ登録内容を整形する
	 * @return
	 * @param object $post
	 */
	public function setConfirm($post){
		$confirm_data = array();
		foreach($post as $key => $value){
			//タイトルと本文はエスケープと置換をする
			if(in_array($key, array("title", "description"))){
				//表示するデータはHTMLエスケープする
				$confirm_data["disp"][$key] = $this->to_escape($value);
				//送信データはエスケープだけする
				$confirm_data["post"][$key] = $this->h($value);
			}
			else{
				$confirm_data["disp"][$key] = $confirm_data["post"][$key] = $value;
			}
		}
		$this->entry_data = $confirm_data;
	}
	/**
	 * エントリ編集に必要なIDセットする
	 * @return
	 * @param object $id
	 */
	public function setEditId($id){
		$this->edit_id = $id;
	}
	/**
	 * 関連エントリを登録する(CGIでは非推奨)
	 * @return
	 */
	private function setRelatedEntry(){
		$this->post2post($this->rel_url, array("key" => $this->rel_key));
	}
	/**
	 * 管理画面を表示する(テンプレート表示メソッドの呼び出しのみ。)
	 * @return
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$tmplname = $this->tmplname ? $this->tmplname : $tmplname;
		$this->showTemplate($tmplname, $this->entry_data);
	}
	/**
	 * エントリ内容を直接取得する(ajax時に使用)
	 * @return
	 */
	public function getEditData(){
		return $this->entry_data;
	}
}
?>
