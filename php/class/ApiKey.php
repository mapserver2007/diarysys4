<?php
/**
 * API KEY登録クラス
 * @author   Ryuichi TANAKA
 * @version  2009/03/16
 */
class ApiKey extends Common {
	private $api_data;
	private $db;
	/**
	 * コンストラクタ
	 * @return
	 * @param $list Object
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
		$this->db = new DB("mysql", "apikey");
	}
	/**
	 * 処理の準備
	 * @return
	 * @param $list Object
	 */
	public function prepare($post = null, $get = null){
		//APIキー登録処理
		if($post){
			$invalid_report = array();
			//POSTデータがValidかどうかチェック
			$valid_data_check = $this->getValidRequestData($post, "Apikey");
			//Invalid
			if(count($valid_data_check["invalid"])){
				foreach($valid_data_check["invalid"] as $key){
					$this->setInvalidReport($key);
				}
			}
			//Valid
			else{
				$this->execApiKeyRegister($valid_data_check["valid"]);
			}
		}
		//APIキー削除処理
		else if($get){
			//GETデータがValidかどうかチェック
			$valid_data_check = $this->getValidRequestData($get, "Apikey");
			$this->execApiKeyDelete($valid_data_check["valid"]);
		}
		//APIリスト取得しセット(プルダウンで使用)
		$this->setApiNameList();
		//API一覧取得しセット
		$this->setApiList();
	}
	/**
	 * Invalidなパラメータを表示用に整形しセットする
	 * @return
	 * @param $invalid_report Object
	 */
	private function setInvalidReport($key){
		$this->api_data["invalid_data"][$key] = $this->defineApiKeyError($key);
	}
	/**
	 * APIキーの生成
	 * @return $api_list Object
	 * @param int $api_id
	 * @param String $domain
	 * @param date $tiemstamp
	 */
	private function createApiKey($api_id, $domain, $date){
		$keystr = $api_id . $domain . $date;
		$apikey = hash(sha256, $keystr);
		return $apikey;
	}
	/**
	 * APIキーの登録
	 * @return
	 * @param $valid_data Object
	 */
	private function execApiKeyRegister($valid_data){
		//APIのID
		$api_id = $valid_data["id"];
		//ドメイン名
		preg_match('@^(?:http://)?([^/]+)@i', $valid_data["referer"], $match);
		$domain = $match[1];
		//タイムスタンプ
		$date = date("Y-m-d H:i:s");

		//APIキーを生成する
		$apikey = $this->createApiKey($api_id, $domain, $date);

		try{
			//DBに登録
			$sql = "INSERT INTO apikey (domain, date, apikey, api_id) VALUES (:domain, :date, :apikey, :api_id)";
			$bind = array("domain" => $domain, "date" => $date, "apikey" => $apikey, "api_id" => $api_id);
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
			if($rv != true) throw new Exception("SQL excecute failed");
		}catch(Exception $e){
			$this->setInvalidReport("conflict");
		}
	}
	/**
	 * APIキーの削除
	 * @param $valid_data Object
	 */
	private function execApiKeyDelete($valid_data){
		//Validなキーがひとつのとき
		if(count($valid_data) === 1){
			$delid = $valid_data["delid"];
			//DBから削除
			$sql = "DELETE FROM apikey WHERE id = :delid";
			$bind = array("delid" => $delid);
			//SQL実行
			$rv = $this->db->sql($sql, $bind);
		}
	}
	/**
	 * API名のリストを取得しセット
	 * @return
	 */
	private function setApiNameList(){
		//SQL
		$sql = "SELECT * FROM apimaster ORDER BY id";
		$bind = array();
		//SQL実行
		$this->db->sql($sql, $bind);
		$i = 0;
		$row = $this->db->fetchAll();
		foreach($row as $elem){
			$this->api_data["api_data"][$i++] = array(
				"id"   => $elem["id"],
				"name" => $elem["name"]
			);
		}
	}
	/**
	 * APIの一覧を取得しセット
	 * @return
	 */
	private function setApiList(){
		//SQL
		$sql = "SELECT apikey.id AS id, apikey.domain AS domain, apikey.date AS date, apikey.apikey AS apikey, apimaster.name AS apiname
			FROM apikey INNER JOIN apimaster ON apikey.api_id = apimaster.id ORDER BY apikey.id";
		$bind = array();
		//SQL実行
		$this->db->sql($sql, $bind);
		$row = $this->db->fetchAll();
		$i = 0;
		foreach($row as $elem){
			$this->api_data["api_list"][$i++] = array(
				"id"   => $elem["id"],
				"domain" => $elem["domain"],
				"date" => $elem["date"],
				"apikey" => $elem["apikey"],
				"apiname" => $elem["apiname"]
			);
		}
	}
	/**
	 * テンプレートを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->api_data);
	}
}
?>