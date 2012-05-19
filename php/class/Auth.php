<?php
/**
 * 認証クラス
 * @author   Ryuichi TANAKA
 * @version  2008/10/18
 */
class Auth extends Common {
	private $url, $livedoorauth_url, $livedoorauth_rpc, $format, $userid, $auth_state;
	private $app_key, $secret;
	/**
	 * コンストラクタ
	 * @return 
	 * @param $list Object
	 */
	function __construct(){
		$this->defineAuthParam();
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * 認証用パラメータの定義
	 * @return 
	 */
	private function defineAuthParam(){
		//Livedoor AuthのURL
		$this->livedoorauth_url = "http://auth.livedoor.com/login/?";
		//Livedoor AuthのRPCURL
		$this->livedoorauth_rpc = "http://auth.livedoor.com/rpc/auth";
		//アプリケーションキー
		$this->app_key = "";
		//秘密鍵
		$this->secret = "";
		//戻り値のフォーマット
		$this->format = "json";
		//ユーザID
		$this->userid = "";
		//リダイレクト先
		$this->redirect = "?m=manage";
	}
	/**
	 * 処理の準備
	 * @return 
	 * @param $get Object[optional]
	 */
	public function prepare($get = null){
		if(count($get))
			$this->setLivedoorAuthId($get);
		else
			$this->setLivedoorAuthURL();
	}
	/**
	 * LivedoorAuth認証URL生成
	 * @return 
	 */
	private function setLivedoorAuthURL(){
		//認証用パラメータ
		$auth_param = array(
			"app_key" => $this->app_key,
			"v" => "1.0",
			"t" => time(),
			"perms" => "id"
		);
		$sig = "";
		//キーの昇順にソート
		ksort($auth_param);
		//SHA1パラメータ生成
		foreach($auth_param as $key => $value){
			$sig .= $key . $value;
		}
		//シグネチャ生成
		$sig = $this->hmacsha1($this->secret, $sig);
		//URLパラメータ生成
		foreach($auth_param as $key => $value){
			$this->livedoorauth_url .= $key . "=" . $value . "&";
		}
		//認証URL生成
		$this->url = $this->livedoorauth_url . "sig=" . $sig;
	}
	/**
	 * LivedoorID取得
	 * @return 
	 * @param $get Object
	 */
	private function setLivedoorAuthId($get){
		//認証状態
		$auth_state = false;
		//シグネチャ生成
		$sig = "app_key" . $get["app_key"] . "format" . $this->format 
			. "t" . $get["t"] . "token" . $get["token"] . "v" . $get["v"];
		$sig = $this->hmacsha1($this->secret, $sig);
		//パラメータ生成
		$param = array(
			"app_key" => $get["app_key"],
			"t" => $get["t"],
			"v" => $get["v"],
			"format" => $this->format,
			"token" => $get["token"],
			"sig" => $sig
		);
		//ID取得
		$res = $this->post2post($this->livedoorauth_rpc, $param);
		//JSONから配列に変換
		$obj = json_decode($res);
		//認証成功
		if($obj->error == 0 && $obj->user->livedoor_id == $this->userid){
			$auth_state = true;
		}
		$this->auth_state = $auth_state;
	}
	/**
	 * LivedoorAuth認証URLを返す
	 * @return 
	 */
	public function getLivedoorAuthURL(){
		return $this->url;
	}
	/**
	 * SHA1変換
	 * @return 
	 * @param $key Object
	 * @param $data Object
	 */
	private function hmacsha1($key, $data){
		$blocksize = 64;
		$hashfunc = 'sha1';
		if (strlen($key) > $blocksize)
			$key = pack('H*', $hashfunc($key));
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack(
			'H*', $hashfunc(
				($key^$opad).pack(
					'H*',$hashfunc(
						($key^$ipad).$data
					)
				)
			)
		);
		return bin2hex($hmac);
	}
	/**
	 * テンプレートを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->entry_data);
	}
	/**
	 * 認証結果を返す
	 * @return 
	 */
	public function auth_state(){
		return $this->auth_state;
	}
}
?>
