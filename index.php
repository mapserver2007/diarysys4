<?php require_once("../syscommon/common.php"); ?>
<?php require_once("./php/load.php"); ?>
<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
	<link type="text/css" rel="stylesheet" href="../syscommon/common.css" />
	<link type="text/css" rel="stylesheet" href="css/main.css" />
	<link type="text/css" rel="stylesheet" href="css/hoverbox.css" />
	<link type="text/css" rel="stylesheet" href="css/facebox.css" />
	<link type="text/css" rel="stylesheet" href="css/quicktags_plus.css" />
	<link type="text/css" rel="stylesheet" href="css/tablesorter.css" />
	<link type="text/css" rel="stylesheet" href="css/jcarousel.css" />
	<link type="text/css" rel="stylesheet" href="css/paginate.css" />
	<link type="text/css" rel="stylesheet" href="css/prettify.css" />
	<script type="text/javascript" language="JavaScript" src="js/lib/jquery.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/jquery.tablesorter.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/jquery.jcarousel.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/facebox.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/quicktags_plus.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/prettify.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/minmax.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/lib/ajaxupload.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_common.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_manage.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_new.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_tag.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_widget.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_filearchives.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/ds_filearchives2.js"></script>
	<script type="text/javascript" language="JavaScript" src="js/quicktags_plus_extension.js"></script>
	<script type="text/javascript">var ds = new ds_common();</script>
	<title>DIARY SYSTEM Ver.4</title>
</head>
<body>
	<!-- Common Header -->
	<?php common_header(); ?>
	<!-- Main Container -->
	<div id="main_container">
		<!-- Header Part -->
		<div id="header_container">
			<!-- メニュー表示 -->
			<?php ds_menu(); ?>
		</div>
		<?php if(ds_prepare() == "manage") : ?>
			<!-- 管理画面モード -->
			<div id="edit_container">
				<?php ds_manage(); ?>
			</div>
		<?php elseif(ds_prepare() == "new") : ?>
			<!-- 管理画面モード：新規エントリ -->
			<div id="edit_container">
				<?php ds_new(); ?>
			</div>
		<?php elseif(ds_prepare() == "newconfirm") : ?>
			<!-- 管理画面モード：新規エントリ登録確認 -->
			<div id="edit_container">
				<?php ds_newconfirm(); ?>
			</div>
		<?php elseif(ds_prepare() == "newregister") : ?>
			<!-- 管理画面モード：新規エントリ登録完了 -->
			<div id="edit_container">
				<?php ds_newregister(); ?>
			</div>
		<?php elseif(ds_prepare() == "edit") : ?>
			<!-- 管理画面モード：エントリ編集 -->
			<div id="edit_container">
				<?php ds_edit(); ?>
			</div>
		<?php elseif(ds_prepare() == "editconfirm") : ?>
			<!-- 管理画面モード：エントリ編集確認 -->
			<div id="edit_container">
				<?php ds_editconfirm(); ?>
			</div>
		<?php elseif(ds_prepare() == "editregister") : ?>
			<!-- 管理画面モード：エントリ編集完了 -->
			<div id="edit_container">
				<?php ds_editregister(); ?>
			</div>
		<?php elseif(ds_prepare() == "delete") : ?>
			<!-- 管理画面モード：エントリ削除確認 -->
			<div id="edit_container">
				<?php ds_deleteconfirm(); ?>
			</div>
		<?php elseif(ds_prepare() == "deleteregister") : ?>
			<!-- 管理画面モード：エントリ削除完了 -->
			<div id="edit_container">
				<?php ds_deleteregister(); ?>
			</div>
		<?php elseif(ds_prepare() == "archives") : ?>
			<!-- 管理画面モード：ファイルアーカイブス -->
			<div id="edit_container">
				<?php ds_archives(); ?>
			</div>
		<?php elseif(ds_prepare() == "archives2") : ?>
			<!-- 管理画面モード：ファイルアーカイブス -->
			<div id="edit_container">
				<?php ds_archives2(); ?>
			</div>
		<?php elseif(ds_prepare() == "widget") : ?>
			<!-- 管理画面モード：ウィジェット管理 -->
			<div id="edit_container">
				<?php ds_widget(); ?>
			</div>
		<?php elseif(ds_prepare() == "apikey") : ?>
			<!-- 管理画面モード：API KEY管理 -->
			<div id="edit_container">
				<?php ds_apikey(); ?>
			</div>
		<?php elseif(ds_prepare() == "entry") : ?>
			<!-- エントリ表示モード -->
			<!-- Entry Part -->
			<div id="left_container">
				<div class="ds_submenu">
					<!-- サブメニュー表示 -->
					<?php ds_submenu(); ?>
				</div>
				<!--
				<div class="ds_timeline">
					<!-- @nifty TimeLine表示 -->
					<?php ds_timeline(); ?>
				</div>
				-->
				<div class="ds_entry">
					<!-- エントリ表示 -->
					<?php ds_entry(); ?>
				</div>
			</div>
			<!-- Side Part -->
			<div id="right_container">
				<!-- サイドバー表示 -->
				<?php ds_sidebar(); ?>
			</div>
		<?php endif ?>
	</div>
	<!-- Common Footer -->
	<?php common_footer(); ?>
</body>
</html>