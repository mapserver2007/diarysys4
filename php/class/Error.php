<?php
/**
 * エラー生成クラス
 * @author   Ryuichi TANAKA
 * @version  2008/11/18
 */
class Error extends Common {
	private $error_data;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * 指定したエラーによるメッセージを出力する
	 * @return 
	 * @param $error_mode Object
	 */
	public function prepare($error_mode){
		switch($error_mode){
			case "entry_id_error" : //指定したエントリIDが存在しない場合
				$this->setEntryByIdError();
				break;
			case "entry_tag_error" : //指定したタグが存在しない場合
				$this->setEntryByTagError();
				break;
			case "entry_page_error" : //指定したページが存在しない場合
				$this->setEntryByPageError();
				break;
			case "entry_month_error" : //指定した月が存在しない場合
				$this->setEntryByMonthError();
				break;
			case "entry_day_error" : //指定した日が存在しない場合
				$this->setEntryByDayError();
				break;
			case "auth_error" :     //認証ができていない場合
				$this->setAuthError();
				break;
			default :
				$this->setExtraError();
				break;
		}
	}
	/**
	 * ID指定エントリ表示エラー
	 * @return 
	 */
	private function setEntryByIdError(){
		$this->error_data = array(
			"error_title" => "エントリ表示エラー",
			"error_description" => "指定したIDのエントリは存在しません。正しいエントリIDを指定してください。"
		);
	}
	/**
	 * タグ指定エントリ表示エラー
	 * @return 
	 */
	private function setEntryByTagError(){
		$this->error_data = array(
			"error_title" => "エントリ表示エラー",
			"error_description" => "指定したタグのエントリは存在しません。正しいタグIDを指定してください。"
		);
	}
	/**
	 * ページ指定エントリ表示エラー
	 * @return 
	 */
	private function setEntryByPageError(){
		$this->error_data = array(
			"error_title" => "エントリ表示エラー",
			"error_description" => "指定したページのエントリは存在しません。正しいページ番号を指定してください。"
		);
	}
	/**
	 * 月指定エントリ表示エラー
	 * @return 
	 */
	private function setEntryByMonthError(){
		$this->error_data = array(
			"error_title" => "エントリ表示エラー",
			"error_description" => "指定した月のエントリは存在しません。正しい月を指定してください。"
		);
	}
	/**
	 * 日指定エントリ表示エラー
	 * @return 
	 */
	private function setEntryByDayError(){
		$this->error_data = array(
			"error_title" => "エントリ表示エラー",
			"error_description" => "指定した日のエントリは存在しません。正しい月を指定してください。"
		);
	}
	/**
	 * 認証関連のエラー
	 * @return 
	 */
	private function setAuthError(){
		$this->error_data = array(
			"error_title" => "認証エラー",
			"error_description" => "認証が成功しないと管理画面には移行できません。正しく認証してください。"
		);
	}
	/**
	 * 想定外のエラー
	 * @return 
	 */
	private function setExtraError(){
		$this->error_data = array(
			"error_title" => "不明なエラー",
			"error_description" => "想定外のエラーが発生しました。管理者に問い合わせてください。"
		);		
	}
	/**
	 * エラーメッセージを返す
	 * @return 
	 */
	public function getEntryData(){
		return $this->error_data;
	}
	/**
	 * テンプレートを表示する(テンプレート表示メソッドの呼び出しのみ。すべての表示処理共通)
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$tmplname = $this->tmplname ? $this->tmplname : $tmplname;
		$this->showTemplate($tmplname, $this->error_data);
	}
}
?>
