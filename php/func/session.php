<?php
//SESSIONのIDを定義
define("SESS_ID", "mapserver2007");
/**
 * セッションを初期化
 * @return 
 */
function ds_session_init(){
	session_destroy();
}
/**
 * セッションIDを定義
 * @return 
 */
function ds_session_define(){
	return md5(SESS_ID);
}
/**
 * セッションIDをセット
 * @return 
 */
function ds_session_set(){
	$_SESSION["auth_id"] = ds_session_define();
}
?>
