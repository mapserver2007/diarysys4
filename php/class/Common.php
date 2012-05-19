<?php
/**
 * 共通設定クラス
 * @author   Ryuichi TANAKA
 * @version  2008/09/15
 */
class Common {
	private $allow_mode, $template, $widget_url, $require_method, $entry_error_id, $pager;
	protected $tmplname = null;
	protected $type;
	protected $rel_url, $rel_key;
	protected $bbcode;
	protected $entry_data = array();
	/**
	 * コンストラクタ
	 * @return
	 */
	function __construct(){
		$this->defineParameter();
		$this->defineAllowExpression();
		$this->defineTemplate();
		$this->defineAPIURL();
		$this->defineEntryError();
		$this->defineBBCodeExpression();
	}
	/**
	 * 表示関係のパラメータを設定する
	 * @return
	 */
	protected function defineParameter(){
		$this->entry_num = 20; //一度に表示するエントリ数
		$this->rss_num   = 20; //一度に表示するRSSのエントリ数
		$this->rel_key   = "mapserver"; //関連エントリ登録時のキー
	}
	/**
	 * パラメータ名と値の型を指定する
	 * @return
	 */
	private function defineAllowExpression(){
		$this->allow_mode = array(
			//Entry
			"Entry" => array(
				"id" => array("type" => "int", "length" => ""),
				"page" => array("type" => "int", "length" => ""),
				"tag" => array("type" => "int", "length" => ""),
				"month" => array("type" => "Regexp", "reg" => '/^(\d{4}+\-+\d{2})$/', "length" => 7),
				"day" => array("type" => "Regexp", "reg" => '/^(\d{4}+\-+\d{2}+\-+\d{2})$/', "length" => 10),
			),
			//Manage
			"Manage" => array(
				"page" => array("type" => "int", "length" => ""),
				"m" => array("type" => "String", "length" => "6")
			),
			//Widget
			"Widget" => array(
				"id" => array("type" => "int", "length" => 1),
				"pluginname" => array("type" => "Regexp", "reg" => "/^[\d|a-zA-Z]+$/", "length" => "50"),
				"title" => array("type" => "String", "length" => "50"),
				"priority" => array("type" => "int", "length" => 1),
				"state" => array("type" => "boolean", "length" => 1),
				"url" => array("type" => "Regexp", "reg" => "/s?https?:\/\/[-_.!~*'()a-zA-Z0-9;\/?:\@&=+\$,%#]+/", "length" => ""),
				"image" => array("type" => "Regexp", "reg" => "/^[-_.!~*'()a-zA-Z0-9;\/?:\@&=+\$,%#]+$/", "length" => ""),
				"disp" => array("type" => "int", "length" => "20")
			),
			//API Key
			"Apikey" => array(
				"id" => array("type" => "int", "length" => ""),
				"referer" => array("type" => "Regexp", "reg" => "/s?https?:\/\/[-_.!~*'()a-zA-Z0-9;\/?:\@&=+\$,%#]+/", "length" => ""),
				"delid" => array("type" => "int", "length" => "")
			)
		);
	}
	/**
	 * BBコードの正規表現を定義
	 * @return
	 */
	protected function defineBBCodeExpression(){
		//[color]
		$this->bbcode["color"] = array(
			"pattern" => '/\[color\=\#([0-9a-fA-F]{3})\](.+)\[\/color\]/',
			"replacement" => '<span style="color: #$1;">$2</span>'
		);
		//[image]
		$this->bbcode["image1"] = array(
			"pattern" => '/\[image\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))\](.+?)\[\/image\]/',
			"replacement" => '<a href="$1" rel="facebox"><img src="$1" alt="$3" title="$3" class="pic2" /></a>'
		);
		//[image]
		$this->bbcode["image2"] = array(
			"pattern" => '/\[image\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))\[\/image\]/',
			"replacement" => '<a href="$1" rel="facebox"><img src="$1" alt="$1" title="$1" class="pic2" /></a>'
		);
		//[url]
		$this->bbcode["url1"] = array(
			"pattern" => '/\[url\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)\](.+?)\[\/url\]/',
			"replacement" => '<a href="$1">$2</a>'
		);
		//[url]
		$this->bbcode["url2"] = array(
			"pattern" => '/\[url\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)\[\/url\]/',
			"replacement" => '<a href="$1">$1</a>'
		);
		//[code]
		$this->bbcode["code"] = array(
			"pattern" => '/\[code\](.+?)\[\/code\]/',
			"replacement" => '<pre class="prettyprint">$1</pre>'
		);
		//[left]
		$this->bbcode["left"] = array(
			"pattern" => '/\[left\](.+?)\[\/left\]/',
			"replacement" => '<p style="text-align: left;">$1</p>'
		);
		//[center]
		$this->bbcode["center"] = array(
			"pattern" => '/\[center\](.+?)\[\/center\]/',
			"replacement" => '<p style="text-align: center;">$1</p>'
		);
		//[right]
		$this->bbcode["right"] = array(
			"pattern" => '/\[right\](.+?)\[\/right\]/',
			"replacement" => '<p style="text-align: right;">$1</p>'
		);
		//Amazon
		$this->bbcode["amazon"] = array(
			"pattern" => '/\[amazon\](.+?)\=(.+?),(.+?)\=(.+?),(.+?)\=(.+?),(.+?)\=(.+?),(.+?)\=(.+?)\[\/amazon\]/',
			"replacement" => '<div><img src="$6"/></div><div><a href="$10">$2</a></div>'
		);
		//Others(left separate)
		$this->bbcode["others_left"] = array(
			"pattern" => '/\[(.+?)\]/',
			"replacement" => '<$1>'
		);
		//Others(right separate)
		$this->bbcode["others_right"] = array(
			"pattern" => '/\[\/(.+?)\]/',
			"replacement" => '</$1>'
		);
	}
	/**
	 * テンプレート名とパスを定義してセットする
	 * @return
	 */
	private function defineTemplate(){
		$this->template = array(
			"entry"          => "./tmpl/entry.tmpl",
			"deleteconfirm"  => "./tmpl/deleteconfirm.tmpl",
			"deleteregister" => "./tmpl/deleteregister.tmpl",
			"edit"           => "./tmpl/edit.tmpl",
			"editback"       => "./tmpl/editback.tmpl",
			"editconfirm"    => "./tmpl/editconfirm.tmpl",
			"editregister"   => "./tmpl/editregister.tmpl",
			"sidebar"        => "./tmpl/sidebar.tmpl",
			"manage"         => "./tmpl/manage.tmpl",
			"new"            => "./tmpl/new.tmpl",
			"newback"        => "./tmpl/newback.tmpl",
			"newconfirm"     => "./tmpl/newconfirm.tmpl",
			"newregister"    => "./tmpl/newregister.tmpl",
			"widget"         => "./tmpl/widget.tmpl",
			"menu"           => "./tmpl/menu.tmpl",
			"submenu"        => "./tmpl/submenu.tmpl",
			"timeline"       => "./tmpl/timeline.tmpl",
			"auth"           => "./tmpl/auth.tmpl",
			"apikey"         => "./tmpl/apikey.tmpl",
			"filearchives"   => "./tmpl/filearchives.tmpl",
			"filearchives2"   => "./tmpl/filearchives2.tmpl",
			"rss"            => "./tmpl/rss.tmpl",
			"error"          => "./tmpl/error.tmpl"
		);
	}
	/**
	 * タグ登録関連のエラーメッセージ定義
	 * @return Object $tag_error_message[$key]
	 */
	protected function defineTagError($key = "", $data = null){
		$data = htmlspecialchars($data);
		//エラーメッセージの定義
		$tag_error_message = array(
			"tag_name_error" => "タグ名「" . $data . "」はすでに存在しています", //タグ名がすでに存在
			"tag_insert_error" => "タグ名「" . $data . "」の登録に失敗しました", //タグのInsertに失敗
			"empty" => "Error message setting failed"
		);
		//定義に含まれないキーが指定された場合
		if(!$tag_error_message[$key]){
			$key = "empty";
		}

		return $tag_error_message[$key];
	}
	/**
	 * ApiKey登録関連のエラーメッセージ定義
	 * @return Object $apikey_error_message[$key]
	 */
	protected function defineApiKeyError($key = ""){
		//エラーメッセージの定義
		$apikey_error_message = array(
			"id"       => "適用するAPIの指定が正しくありません", //API名のプルダウンで指定が不正(選択されていない)
			"referer"  => "APIを適用するサイトのURLが正しくありません", //APIを適用するサイトのURLの形式が不正
			"conflict" => "APIを適用するサイトのURLはすでに登録されています" //APIを適用するサイトがすでに登録されている
		);
		//定義に含まれないキーが指定された場合
		if(!$apikey_error_message[$key]){
			$key = "empty";
		}

		return $apikey_error_message[$key];
	}
	/**
	 * エントリ表示関連のエラーID定義
	 * @return
	 * @param object $key
	 */
	private function defineEntryError(){
		//エラーIDの定義
		$this->entry_error_id = array(
			"id" => "entry_id_error",
			"tag" => "entry_tag_error",
			"page" => "entry_page_error",
			"month" => "entry_month_error",
			"day" => "entry_day_error"
		);
	}
	/**
	 * WebAPIのURLを設定する
	 * @return
	 */
	private function defineAPIURL(){
		//ウィジェットAPI URL
		$this->widget_url = "http://summer-lights.dyndns.ws/diarysys4/pl/widget.pl";
		//関連エントリAPI　URL
		$this->rel_url = "http://summer-lights.dyndns.ws/diarysys4/pl/relation.pl";
	}
	/**
	 * エントリ表示の表示モードを決める(通常orタグ指定orID指定or年月指定)
	 * @return $res Array
	 * @param $modes Array
	 */
	protected function dispModeChack($modes){
		$res = array();
		//invalidが報告されたらエラー扱い
		if(is_array($modes["invalid"]) && count($modes["invalid"]) >= 1){
			$res[] = array("type" => "error", "value" => "");
			//エラーID判定用
			//$this->type = $modes["invalid"][0];
		}
		//validが報告されたら成功
		else if(is_array($modes["valid"]) && count($modes["valid"]) >= 1){
			foreach($modes["valid"] as $k => $v){
				$res[] = array("type" => $k, "value" => $v);
			}
			//$this->type = $k;
		}
		//それ以外は通常表示
		else{
			$res[] = array("type" => "default", "value" => "");
		}
		return $res;
	}
	/**
	 * 指定したGETパラメータにエラーがあるエントリ
	 * @return
	 */
	protected function setEntryByError(){
		//インスタンス生成
		$error = new Error();
		//エラー内容を指定
		$error->prepare($this->getEntryError($this->type));
		//エラー内容を取得
		$this->entry_data = $error->getEntryData();
		//テンプレート名
		$this->tmplname = "error";
	}
	/**
	 * エントリ表示モードの定義を返す
	 * @return $this->allow_mode Array
	 */
	protected function getAllowEntryMode($key){
		return $this->allow_mode[$key];
	}
	/**
	 * エントリ表示エラーIDの定義を返す
	 * @return $this->allow_mode Array
	 */
	protected function getEntryError($key){
		return $this->entry_error_id[$key];
	}

	/**
	 * ウィジェット処理URLを返す
	 * @return
	 */
	protected function getWidgetURL(){
		return $this->widget_url;
	}
	/**
	 * GET/POSTデータの中身をエスケープ、妥当な値にして返し、不正なデータをレポートする
	 * @return
	 * @param object $data
	 * @param object $key
	 */
	protected function getValidRequestData($data, $key){
		$valid_data = array();
		$allow_mode = $this->getAllowEntryMode($key);

		foreach($data as $key => $value){
			if(array_key_exists($key, $allow_mode)){
				//int
				if($allow_mode[$key]["type"] == "int" && preg_match("/^[\d]+$/", $value) && $value > 0 &&
					($allow_mode[$key]["length"] == "" || mb_strlen($value, "UTF-8") <= $allow_mode[$key]["length"])){
					$valid_data[$key] = (int)$value;
				}
				//String
				else if($allow_mode[$key]["type"] == "String" &&
					($allow_mode[$key]["length"] == "" || mb_strlen($value, "UTF-8") <= $allow_mode[$key]["length"])){
					$valid_data[$key] = htmlspecialchars($value);
				}
				//Regexp
				else if($allow_mode[$key]["type"] == "Regexp" && preg_match($allow_mode[$key]["reg"], $value) &&
					($allow_mode[$key]["length"] == "" || mb_strlen($value, "UTF-8") <= $allow_mode[$key]["length"])){
					$valid_data[$key] = $value;
				}
				//boolean
				else if($allow_mode[$key]["type"] == "boolean" && preg_match("/^[0-1]+$/", $value) &&
					($allow_mode[$key]["length"] == "" || mb_strlen($value, "UTF-8") == $allow_mode[$key]["length"])){
					$valid_data[$key] = (int)$value;
				}
				//Invalid Value
				else{
					$invalid_data[] = $key;
				}
			}
		}
		return array("valid" => $valid_data, "invalid" => $invalid_data);
	}
	/**
	 * ここのテンプレート名を取得する
	 * @return
	 * @param $tmplname Object
	 */
	private function getTemplate($tmplname){
		return $this->template[$tmplname];
	}
	/**
	 * テンプレートをインクルードする
	 * @return
	 * @param $filename Object
	 * @param $items Object[optional]
	 */
	protected function showTemplate($tmplname, $items = array()){
		try{
			$filename = $this->getTemplate($tmplname);
			if(isset($items) && count($items) > 0)
				extract($items);
			if(!file_exists($filename))
				throw new Exception("テンプレートファイル $filename は存在しません");
			include $filename;
		}catch(Exception $e){
			die($e);
		}
	}
	/**
	 * サーバサイドからサーバサイドへPOSTする
	 * @return
	 * @param $post_data Object
	 */
	protected function post2post($url, $post_data = array()){
		$opt = array(
			'http' => array(
				'method' => 'POST',
				'content' => http_build_query($post_data)
			)
		);
		return file_get_contents($url, false, stream_context_create($opt));
	}
	/**
	 * 文字列をUTF-8に変換する
	 * @return
	 * @param $val Object
	 */
	protected function to_utf8($val){
		return mb_convert_encoding($val, "utf8", "auto");
	}
	/**
	 * 文字列をEUC-JPに変換する
	 * @return
	 * @param $val Object
	 */
	protected function to_euc($val){
		return mb_convert_encoding($val, "euc-jp", "auto");
	}
	/**
	 * エスケープする
	 * @return
	 * @param object $val
	 */
	protected function to_escape($val){
		$val = $this->h($val);
		$val = $this->s($val);
		$val = $this->n($val);
		return $val;
	}
	/**
	 * エスケープ解除する
	 * @return
	 * @param object $val
	 */
	protected function to_descape($val){
		//$val = $this->h($val);
		//$val = $this->ds($val);
		$val = $this->dn($val);
		return $val;
	}

	protected function h($val){
		return htmlspecialchars($val);
	}

	protected function n($val){
		return preg_replace('/\t/', '&nbsp;&nbsp;&nbsp;&nbsp;', $val);
	}

	protected function s($val){
		return preg_replace('/\r\n|\r|\n/', '<br />', $val);
	}

	protected function dn($val){
		return preg_replace('/&nbsp;&nbsp;&nbsp;&nbsp;/', '\t', $val);
	}

	protected function ds($val){
		return preg_replace('/<br \/>/', '\r\n', $val);
	}
}
?>
