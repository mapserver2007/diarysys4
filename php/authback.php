<?php
/**
 * Livedoor Authのコールバック処理
 */
require_once('./class/Common.php');
require_once('./class/Auth.php');
require_once('./func/session.php');
session_start();
//インスタンス生成
$auth = new Auth();
//認証URL生成
$auth->prepare($_GET);
//認証結果
$state = $auth->auth_state();
//認証成功のとき
if($state){
	ds_session_set();
}
//認証失敗のとき
else{
	ds_session_init();
}
header("Location: ../?m=manage");
?>
