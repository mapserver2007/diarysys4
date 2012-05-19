<?php
/**
 * 通常のロード処理
 */
//クラス読み込み
require_once('./php/class/Common.php');
require_once('./php/class/Weather.php');
require_once('./php/class/DB.php');
require_once('./php/class/Entry.php');
require_once('./php/class/Sidebar.php');
require_once('./php/class/Calendar.php');
require_once('./php/class/Chart.php');
require_once('./php/class/TimeLine.php');
require_once('./php/class/Manage.php');
require_once('./php/class/Widget.php');
require_once('./php/class/Menu.php');
require_once('./php/class/Auth.php');
require_once('./php/class/ApiKey.php');
require_once('./php/class/FileArchives.php');
require_once('./php/class/FileArchives2.php');
require_once('./php/class/Rss.php');
require_once('./php/class/Error.php');
require_once('./php/func/session.php');
/**
 * メニューを表示する関数
 * @return
 */
function ds_menu(){
	//インスタンス生成
	$menu = new Menu();
	//メニューリンクを生成
	$menu->prepare("main");
	//メニューリンクを表示する
	$menu->show("menu");
}
/**
 * サブメニューを表示する関数
 * @return
 */
function ds_submenu(){
	//インスタンス生成
	$submenu = new Menu();
	//メニューリンクを生成
	$submenu->prepare("sub_01", $_GET);
	//メニューリンクを表示する
	$submenu->show("submenu");
}
/**
 * タイムラインを表示する関数
 * @return
 */
function ds_timeline(){
	//インスタンス生成
	$submenu = new Menu();
	//メニューリンクを生成
	$submenu->prepare("sub_02");
	//メニューリンクを表示する
	$submenu->show("timeline");
}
/**
 * エントリ表示をする関数
 * @return
 */
function ds_entry(){
	//トップ画面ではSESSION初期化する
	ds_session_init();
	//インスタンス生成
	$entry = new Entry();
	//GETデータを渡して表示処理を開始する
	$entry->prepare($_GET);
	//表示する
	$entry->show("entry");
}
/**
 * サイドバーを表示する関数
 * @return
 */
function ds_sidebar(){
	//インスタンス生成
	$sidebar = new Sidebar();
	//表示する
	$sidebar->show("sidebar");
}
/**
 * エントリの編集をする関数
 * @return
 */
function ds_manage(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$edit = new Manage();
		//管理画面に表示するリストを作る
		$edit->entryList();
		//表示する
		$edit->show("manage");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリの新規登録をする関数
 * @return
 */
function ds_new(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$edit = new Manage();
		//確認画面から戻ってきた場合
		if($_POST["m"] == "newback"){
			//POSTデータをセットする
			$edit->setBack($_POST);
			//表示する
			$edit->show("newback");
		}
		//初回登録の場合
		else{
			//表示する
			$edit->show("new");
		}
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリの新規登録内容を確認する関数
 * @return
 */
function ds_newconfirm(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$confirm = new Manage();
		//POSTデータをセット
		$confirm->setConfirm($_POST);
		//表示する
		$confirm->show("newconfirm");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリをDBに登録する関数
 * @return
 */
function ds_newregister(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$register = new Manage();
		//POSTデータをセット
		$register->setEntryData($_POST);
		//表示する
		$register->show("newregister");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリを編集する関数
 * @return
 */
function ds_edit(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$edit = new Manage();
		//確認画面から戻ってきた場合
		if($_POST["m"] == "editback"){
			//POSTデータをセットする
			$edit->setBack($_POST);
			//表示する
			$edit->show("editback");
		}
		//初回登録の場合
		else{
			//インスタンス生成
			$edit = new Manage();
			//IDからエントリリストを取得する
			$edit->editList($_GET["id"], "edit");
			//表示する
			$edit->show("edit");
		}
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリの編集内容を確認する関数
 * @return
 */
function ds_editconfirm(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$confirm = new Manage();
		//POSTデータをセット
		$confirm->setConfirm($_POST);
		//表示する
		$confirm->show("editconfirm");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリ編集をDBに登録する関数
 * @return
 */
function ds_editregister(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$register = new Manage();
		//POSTデータをセット
		$register->setEditData($_POST);
		//表示する
		$register->show("editregister");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリの削除内容を確認する関数
 * @return
 */
function ds_deleteconfirm(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$confirm = new Manage();
		//POSTデータをセット
		$confirm->editList($_GET["id"], "delete");
		//表示する
		$confirm->show("deleteconfirm");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * エントリ削除をDBに登録する関数
 * @return
 */
function ds_deleteregister(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$register = new Manage();
		//POSTデータをセット
		$register->setDeleteData($_POST);
		//表示する
		$register->show("deleteregister");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * ウィジェットを設定する関数
 * @return
 */
function ds_widget(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$widget = new Widget();
		//ウィジェット画面に表示するリストを作る
		$widget->prepare($_POST);
		//表示する
		$widget->show("widget");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * ファイルアーカイブスを設定する関数
 * @return
 */
function ds_archives(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$archives = new FileArchives();
		//準備
		$archives->prepare();
		//表示する
		$archives->show("filearchives");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * ファイルアーカイブスを設定する関数
 * @return
 */
function ds_archives2(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$archives = new FileArchives2();
		//準備
		$archives->prepare();
		//表示する
		$archives->show("filearchives2");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * APIKEYを設定する関数
 * @return
 */
function ds_apikey(){
	//認証済みの場合
	if($_SESSION["auth_id"] == ds_session_define()){
		//インスタンス生成
		$apikey = new ApiKey();
		//準備
		$apikey->prepare($_POST, $_GET);
		//表示する
		$apikey->show("apikey");
	}
	//認証していない場合
	else{
		ds_error("auth_error");
	}
}
/**
 * RSSを出力する関数
 * @return
 */
function ds_rss(){
	//インスタンス生成
	$rss = new Rss();
	//準備
	$rss->prepare();
	//表示する
	$rss->show("rss");
}
/**
 * エラー処理をする関数
 * @return
 * @param object $error_id
 */
function ds_error($error_id){
	//インスタンス生成
	$error = new Error();
	//エラー内容を指定
	$error->prepare($error_id);
	//表示する
	$error->show("error");
}

function ds_receive(){
	//RSS
	if(ds_prepare() == "rss"){
		ds_rss();
		exit;
	}
}

/**
 * DIARY SYSTEMのモードをチェックする
 * @return
 */
function ds_prepare(){
	$valid_mode = "";
	//GETパラメータの値定義
	$define_mode_manage_get = array("manage", "new", "widget", "archives", "archives2", "apikey", "edit", "delete", "rss");
	//POSTパラメータの値定義
	$define_mode_manage_post = array("newconfirm", "newregister", "editconfirm", "editregister", "deleteregister");
	//GETモードのバリデート
	if(in_array($_GET["m"], $define_mode_manage_get)){
		$valid_mode = $_GET["m"];
		//管理モード：新規エントリorエントリ編集orウィジェット
		if(in_array($_GET["r"], $define_mode_manage_get)){
			$valid_mode = $_GET["r"];
			//管理モード：新規エントリ登録確認
			if(in_array($_POST["m"], $define_mode_manage_post)){
				$valid_mode = $_POST["m"];
			}
		}
	}
	//エントリ表示モード
	else{
		$valid_mode = "entry";
	}
	return $valid_mode;
}

//初回時に実行
ds_receive();
?>
