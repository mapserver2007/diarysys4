<?php
/**
 * ファイルアーカイブスクラス
 * @author   Ryuichi TANAKA
 * @version  2008/10/25
 */
class FileArchives2 extends Common {
	private $archives_data;
	/**
	 * コンストラクタ
	 * @return
	 */
	function __construct(){
		parent::__construct(); //スーパークラスCommonのコンストラクタ呼び出し
	}
	/**
	 * ファイルアーカイブ処理準備
	 * @return
	 * @param object $post
	 */
	public function prepare(){

	}
	/**
	 * アーカイブス画面を表示する(テンプレート表示メソッドの呼び出しのみ。)
	 * @return
	 * @param $tmplname Object
	 */
	public function show($tmplname){
		$this->showTemplate($tmplname, $this->edit_data);
	}
}

?>
