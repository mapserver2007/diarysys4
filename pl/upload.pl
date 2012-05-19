#!/usr/bin/perl
## Uploadパッケージ名前空間
package Upload;

use strict;
use warnings;
use CGI;
use Imager;
use File::Basename;
use Date::Simple;
use JSON;

# コンストラクタ
sub new {
	my $class = shift;
	my $self = {};
	return bless $self, $class;
}

# エラー処理
sub error {
	my $self = shift;
	my $msg = $_[0];
	$self->json({
		error_message => $msg,
		success => "failure"
	});
	exit;
}

sub json {
	my $self = shift;
	my $data = $_[0];
	# Javascriptコードと一緒に出力
	print "<script type=\"text/javascript\">window.parent.ds.filearchives.start(" .  to_json($data) . ");</script>";
	return;
}

# 正しいディレクトリパスかチェック
sub canonicalize {
	my $self = shift;
	my $dir = $_[0];
	
	if($dir !~ m|^/|){
		my $cwd = `/bin/pwd`; #UNIXコマンド実行
		chop($cwd);
		$dir = "$cwd/$dir";
	}
	
	# パスの正規化
	my @components = ();
	foreach my $component (split('/', $dir)){
		next if($component eq "");          # // は無視
		next if($component eq ".");         # /./ は無視
		if($component eq "..") {            # /../ なら
			pop(@components);               # 1 つ前の構成要素も無視
			next;
		}
		push(@components, $component);      # 構成要素を追加
	}
	$dir = '/'.join('/', @components);      # パス名文字列を生成
	
	return $dir;
}

# ファイルをサーバに保存する
sub save {
	my $self = shift;
	my $res = {};
	my $query = $_[0];
	
	# 保存ディレクトリパス
	my $dir = '../upload/';
	my $thumb_dir = $dir . 'thumbnail/';

	# アップロードを許可する拡張子とMIME
	my $mime = {
		'image/jpeg' => 'jpg',                   # jpeg
		'image/pjpeg' => 'jpg',                  # プログレッシブjpeg
		'image/png' => 'png',                    # png
		'image/gif' => 'gif',                    # gif
	};

	# あしたのおれへ
	# http://www.hidekik.com/cookbook/p2h.cgi?id=upload
	# どうやらバイナリをWhileまわしてコピーは効率悪いっぽいCopyモジュールをつかうがよい
	# あと、なぜかZIPがアップできない。原因不明。
	
	# ファイルを受信
	my $fh = $query->upload('filename');
	
	# オリジナルファイル名取得
	my $original_filename = basename($query->upload('filename'));
	$original_filename =~ s/^.*\\//g;
	$res->{original_filename} = $original_filename;
	
	# ファイル受信エラーチェック
	my $errmsg = $query->cgi_error;
	$self->error($errmsg) if ($errmsg);
	
	# MIMEタイプ取得
	my $mimetype = $query->uploadInfo($fh)->{'Content-Type'};
	
	# 拡張子をセット
	my $ext = $mime->{$mimetype} ? $mime->{$mimetype} : $self->error("Can't permit this file.");

	# 拡張子取得
	$res->{filetype} = $ext;
	
	# ファイル名の設定
	my $filename = time . "." . $ext;
	
	# ファイル名取得
	$res->{filename} = $filename;	
	
	# サイズ制限
	#my $maxsize = $cfg->param('MAX_FILE_SIZE');
	#error("The filesize is too large. Max $maxsize KB") if ($size > $maxsize * 1024);
	
	# ファイル保存
	my $buffer;
	my $path = $self->canonicalize($dir) . '/' . $filename;
	open my $out, '>', $path || $self->error("Can't open '$path'");
	binmode $out;
	while(read($fh, $buffer, 1024)){
		print $out $buffer || $self->error("Can't write '$path'");
	}
	close($out) || $self->error("Can't close '$path'");
	
	# サムネイル生成
	my $thumbnail = Imager->new();
	$thumbnail->read(file => $path) || $self->error("Can't open '$path'");
	$thumbnail = $thumbnail->scale(ypixels => 75);
	my $thumb_path = $self->canonicalize($thumb_dir) . '/' . $filename;
	$thumbnail->write(file => $thumb_path) || $self->error("Can't write '$thumb_path'");
	
	# 日付を取得
	my ($sec, $min, $hour, $day, $month, $year) = (localtime(time))[0..5];
	$year += 1900;
	$month += 1;
	$res->{date} = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $min . ":" . $sec;
	
	# ファイルサイズ取得
	$res->{filesize} = (-s $path);
	
	# ファイル保存成功
	$self->json({
		message => "file '$filename' save success!",
		success => "success"
	});

	# DB登録
	my $db = DB->new();
	$db->DBConnect();
	$db->write($res);
	$db->DBClose();	
	
	return;
}

# ファイル削除処理
sub remove {
	my $self = shift;
	my @fileid = @_;
	my $rv = 0;
	my $filenames;
	# 保存ディレクトリパス
	my $dir = '../upload/';	
	
	# DB接続
	my $db = DB->new();
	$db->DBConnect();
	
	# ファイルを削除する
	my $files = $db->select(@fileid);
	foreach my $filename (@{$files}){
		my $path = $dir . $filename;
		my $fullpath = $self->canonicalize($path);
		unlink($fullpath);
		$filenames .= "'" . $filename . "' ";
		$rv = 1;
	}
	chop($filenames);
	$self->error("Can't Remove file $filenames from Directory.") if ($rv != 1);
	
	# DBから削除する
	$rv = $db->delete(@fileid);
	$self->error("Can't Remove file $filenames from DB.") if ($rv != 1);
	
	# ファイル保存成功
	$self->json({
		message => "file $filenames delete success!",
		success => "success"
	});
	
	# DB切断
	$db->DBClose();		
	
	return;
}

## DBパッケージ名前空間
package DB;

use strict;
use warnings;
use Data::Dumper;
use DBI;

# コンストラクタ
sub new {
	my $class = shift;
	my $self = {};
	return bless $self, $class;
}

# データベース接続
sub DBConnect {
	my $self = shift;
	my $dsn = "DBI:mysql:diarysys:localhost:3306;mysql_read_default_file=/etc/mysql/my.cnf";
	my $user = "mysql";
	my $pass = "mysql";
	$self->{db} = DBI->connect($dsn, $user, $pass);
}

# データベース切断
sub DBClose {
	my $self = shift;
	$self->{db}->disconnect;
}

sub success {
	my $self = shift;
	push @{$self->{data}}, {Result => "success"};
}

sub failure {
	my $self = shift;
	push @{$self->{data}}, {Result => "failure"};
}

# Write
sub write {
	my $self = shift;
	my $data = $_[0];
	my ($stmt, @bind, $sth);
	$stmt = "INSERT INTO diary4_filearchives (filename, original_filename, date, filetype, filesize) VALUES (?, ?, ?, ?, ?)";
	@bind = ($data->{filename}, $data->{original_filename}, $data->{date}, $data->{filetype}, $data->{filesize});
	$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
	$sth->execute(@bind) or die $DBI::errstr;
	$sth->finish() or die $DBI::errstr;
	return;
}

# Select
sub select {
	my $self = shift;
	my @data = @_;
	my $fn = [];
	my ($stmt, @bind, $sth);
	$stmt = "SELECT filename FROM diary4_filearchives WHERE ";
	@bind = ();
	foreach my $sel_id (@data){
		$stmt .= "id=" . $sel_id . " OR ";
	}
	for(0..3){ chop($stmt); }
	$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
	$sth->execute(@bind) or die $DBI::errstr;
	while(my $res = $sth->fetchrow_hashref){	
		push @{$fn}, $res->{filename};
	}
	$sth->finish() or die $DBI::errstr;

	return $fn;
}

# Delete
sub delete {
	my $self = shift;
	my @data = @_;
	my ($stmt, @bind, $sth);
	$stmt = "DELETE FROM diary4_filearchives WHERE ";
	@bind = ();
	foreach my $del_id (@data){
		$stmt .= "id=" . $del_id . " OR ";
	}
	for(0..3){ chop($stmt); }
	$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
	$sth->execute(@bind) or die $DBI::errstr;
	$sth->finish() or die $DBI::errstr;
	
	return 1;
}

# JSON化して返す
sub get_json {
	my $self = shift;
	return to_json($self->{data});
}

## mainパッケージ名前空間
package main;

use strict;
use warnings;
use Data::Dumper;
use CGI;

# CGI開始
$CGI::POST_MAX = 1024 * 1024; # 1MB
my $cgi = new CGI;
print $cgi->header(-type=>"text/html", -charset => 'euc-jp');

# POSTのみ有効にする
if($cgi->request_method() eq 'POST'){
	# データ削除処理
	if($cgi->param("delete")){
		my $del = Upload->new();
		# ファイル削除
		$del->remove($cgi->param("delete"));
	}
	# アップロード処理
	elsif($cgi->upload("filename")){
		my $up = Upload->new();
		# ファイル保存
		$up->save($cgi);
	}
}

