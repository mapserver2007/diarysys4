<?php
/**
 * ウィジェット機能クラス
 * @author   Ryuichi TANAKA
 * @version  2008/10/13
 */
class Widget extends Common {
	/**
	 * コンストラクタ
	 * @return 
	 * @param $list Object
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * ウィジェット設定の準備
	 * @return 
	 * @param $post Object
	 */
	public function prepare($post){
		$json = "";
		$url = $this->getWidgetURL();
		//POSTデータがある場合はWriteとみなす
		if($post){
			//送信するためにPOSTデータを編成する
			$renew_post = $this->renewPostData($post);
			//Invalidなレポートがある場合はエラー表示して終了
			$invalid_report = array();
			foreach($renew_post as $tmp){
				if(is_array($tmp["invalid"]))
					$invalid_report[] = $tmp["invalid"];
			}
			//Invalid
			if(count($invalid_report)){
				$this->invalidReport($renew_post);
				return;
			}
			//Valid
			else{
				$json = $this->post2post($url, $post);
			}
		}
		//POSTデータがない場合はReadとみなす
		else{
			$json = $this->post2post($url);
		}
		//JSONを表示用変数にセットする
		$this->setWidgetData($json);
	}
	/**
	 * Invalidなデータをを検出し、表示用データentry_dataに再格納する
	 * @return 
	 * @param $data Object
	 */
	private function invalidReport($data){
		$renew_data = array();
		foreach($data as $value){
			$data_tmp = array();
			//Validデータはキーを変更する
			if(is_array($value["valid"])){
				foreach($value["valid"] as $k => $v){
					$tmp_key = $k;
					$data_tmp[$tmp_key] = $v;
				}
			}
			//Invalidデータは新たにデータを作る
			if(is_array($value["invalid"])){
				foreach($value["invalid"] as $k){
					$tmp_key = $k . "_invalid";
					$data_tmp[$tmp_key] = true;
				}
			}
			$renew_data[] = $data_tmp;
		}
		$this->entry_data = array("data" => $renew_data, "flg" => "success");
	}
	/**
	 * ウィジェットの設定を表示用データentry_dataに格納する
	 * @return 
	 * @param $json Object
	 */
	private function setWidgetData($json){
		//PHPの配列に変換
		$tmp = json_decode($this->to_utf8($json), true);
		//ウィジェット設定の取得が成功したかチェックする
		$enc_json = "";
		$tmp_result = "";
		foreach($tmp as $val){
			$enc_json_tmp = "";
			foreach($val as $k => $v){
				switch($k){
					case "image" : 
						$enc_json_tmp[$k] = (file_exists("./image/" . $v) && $v)? $v : "noimg.gif";
						break;
					case "Result" : 
						$tmp_result = $v;
						break;
					default : 
						$enc_json_tmp[$k] = $v;
						break;		
				}
			}
			if($enc_json_tmp)
				$enc_json[] = $enc_json_tmp;	
		}
		$this->entry_data = array("data" => $enc_json, "flg" => $tmp_result);
	}
	/**
	 * widget.plに送信するためにPOSTデータを編成する
	 * @return 
	 * @param $post Object
	 */
	private function renewPostData($post){
		$renew_data = array();
		$renew_valid_data = array();
		//POSTデータを編成
		foreach($post as $key => $value){
			$tmp = explode("_", $key);
			$renew_data[(int)$tmp[1]-1][substr($tmp[0], 1)] = $value;
		}
		//POSTデータをValidなデータに変換する
		foreach($renew_data as $ary){
			$renew_valid_data[] = $this->getValidRequestData($ary, "Widget");
		}
		return $renew_valid_data;
	}
	/**
	 * エントリを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->entry_data);
	}
}
?>
