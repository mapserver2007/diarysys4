<?php
/**
 * Ajax/JSONP系のロード処理(外部通信なし)
 */
require_once('./class/Common.php');
require_once('./class/DB.php');
require_once('./class/Tag.php');
require_once('./class/Manage.php');
/**
 * タグを取得・登録する
 * @return 
 * @param object $mode
 */
function ds_tags($mode, $tname = null){
	//インスタンス生成
	$tag = new Tag();
	//準備
	$tag->prepare($mode, $tname);
	//タグオブジェクト取得
	$tags = $tag->getTags();
	return $tags;
}
/**
 * エントリの本文を取得する
 * @return 
 * @param object $id
 */
function ds_description($id){
	//インスタンス生成
	$description = new Manage();
	//管理画面に表示するリストを作る
	$description->entryList("description", array("id" => $id));
	//本文オブジェクト取得
	$description_list = $description->getEditData();
	return $description_list;
}
//GETデータごとに処理分岐
$mode = $_GET["mode"];
//コールバック関数
$callback = htmlspecialchars($_GET["callback"]);
//JSON化前のオブジェクト
$obj = "";
//タグを表示するモード
if($mode == "select"){
	$obj = ds_tags("s");
}
//タグを新規登録するモード
else if($mode == "insert"){
	$obj = ds_tags("i", $_GET["tag"]);
	//$objが空の場合は成功とみなしてselectを実行
	if(!$obj){$obj = ds_tags("s");}
}
//管理画面で本文を表示するモード
else if($mode == "description"){
	$obj = ds_description($_GET["id"]);
}

//JSONP化する
echo $callback . "(" . json_encode($obj) . ")";

?>
