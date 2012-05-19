<?php
/**
 * サイドバー系クラス
 * @author   Ryuichi TANAKA
 * @version  2008/10/11
 */
class Sidebar extends Common {
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * サイドバーを表示する
	 * @return 
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->entry_data);
	}	
}
?>
