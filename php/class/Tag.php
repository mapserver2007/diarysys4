<?php
/**
 * タグ処理系クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/28
 */
class Tag extends Common {
	private $tag_data = array();
	private $db;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		$this->db = new DB();
	}
	/**
	 * データベースに問い合わせてタグを取得する
	 * @return 
	 */
	public function prepare($mode, $data = null){
		//タグを登録
		if($mode == "i"){
			$this->insert($this->to_euc($data));
		}
		//タグを読み取り
		else if($mode == "s"){
			$this->select();
		}
		//タグ更新
		else if($mode == "u"){
			$this->update();
		}
		else{
			//$this->select();
		}
	}
	/**
	 * タグを取得する
	 * @return
	 */
	private function select(){
		//SQL
		$sql = "SELECT c.tid id, c.tname name, f.reference ref 
				FROM diary4_tag_classify as c, diary4_tag_reference as f 
				WHERE c.tid = f.tid ORDER BY c.tname";
		$bind = array();
		//SQL実行
		$this->db->sql($sql, $bind);
		//取得データを配列にまとめる
		$i = 0;
		while($row = $this->db->fetch()){
			$this->tag_data[$i++] = array(
				"id"   => $row["id"],
				"name" => htmlspecialchars($row["name"]),
				"ref"  => $row["ref"]
			);
		}		
	}
	/**
	 * タグ新規登録
	 * @return 
	 * @param object $tname
	 */
	private function insert($tname){
		//UTF-8に変換
		$tname = $this->to_utf8($tname);
	
		//タグ名が文字列でない場合は終了
		if(!is_string($tname)){
			$error_msg = defineTagError("tag_name_error", $tname);
			$this->error_report($error_msg);
			return;
		}		
		//タグ名SQL
		$sql_n = "INSERT INTO diary4_tag_classify (tname) VALUES (:tname)";

		//タグ参照回数SQL
		//注意1：新規登録なので、必ず「insert」になる(エントリ登録時はupdate)
		//注意2：referenceは「0」に設定する。エントリ登録時に初めて「1」になる。
		//注意3：エントリ登録時にreferenceが「0」のタグは消去される。新規登録しても使用されないものは消してしまう。
		$sql_f = "INSERT INTO diary4_tag_reference (tid, reference) VALUES 
				((SELECT tid FROM diary4_tag_classify WHERE tname = :tname), 0)";

		//バインドする変数をセット
		$bind = array("tname" => $tname);

		try{
			//SQL1実行
			$rev = $this->db->sql($sql_n, $bind);
			if(!$rev){
				$error_msg = $this->defineTagError("tag_name_error", $tname);
				$this->error_report($error_msg);
				return;
			}
			//SQL2実行
			$rev = $this->db->sql($sql_f, $bind);
			if(!$rev){
				$error_msg = $this->defineTagError("tag_insert_error", $tname);
				$this->error_report($error_msg);
				return;
			}			
		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * エラーレポートをセットする
	 * @return
	 */
	private function error_report($data){
		$this->error_data = $data;
	}
	/**
	 * タグを返す
	 * @return Object $this->tag_data 
	 */
	public function getTags(){
		if($this->error_data){
			return array("error" => $this->error_data);
		}
		else{
			return $this->tag_data;
		}
	}
}
?>
