#!/usr/bin/perl
use strict;
use warnings;
use Data::Dumper;
use CGI;
use JSON::Syck qw/Load Dump/;
use FindBin::libs qw{ export base=syscommon };
use MyLibs::Common::Util::Upload;
use MyLibs::Common::Util::Thumbnail;
use MyLibs::Common::DB::Config;
use MyLibs::Common::DB::Schema;

# アップロードファイルの保存パス(UNIXパス)
my $save_path = '/usr/local/apache2/htdocs/diarysys4/upload/';
# アップロードファイルのサムネイル保存パス(UNIXパス)
my $save_path_thumbnail = '/usr/local/apache2/htdocs/diarysys4/upload/thumbnail/';

# 使用するDBMSを指定(mysql or pgsql)
my $dbms = "mysql";

# データベース名を指定
my $dbname = "diarysys";

# スキーマファイル名を指定
my $schema_name = "Diary4Filearchives2";

# DBの接続設定を取得
my ($conf_obj, $db_conf);
$conf_obj = MyLibs::Common::DB::Config->new();
$conf_obj->use_db($dbms);
$db_conf = $conf_obj->get_db_config();

## ORマッピング開始
my $connect_info = ["dbi:$db_conf->{dbms}:dbname=$dbname;host=$db_conf->{host}", $db_conf->{user}, $db_conf->{pass}];
my $schema = MyLibs::Common::DB::Schema->connect(@{$connect_info});
$schema->storage->dbh->do("SET names utf8");

sub upload_insert {
	### ここからUpload処理 ###
	my $upload = MyLibs::Common::Util::Upload->new({
		dir_path => $save_path
	});

	unless ($upload->save()) {
		print JSON::Syck::Dump($upload->get_error());
		exit;
	}

	my $fileinfo = $upload->get_fileinfo();

	### ここからサムネイル処理 ###
	my $thumbnail = MyLibs::Common::Util::Thumbnail->new({
		image_path => $fileinfo->{save_filepath}, # 必須、間違い不可
		dir_path => $save_path_thumbnail, # 必須、間違い不可
		#size_x => 15,
		#size_y => 75, # size_x、size_yどちらかが指定してあればOK。片方省略時は比率を維持(優先度：高)
		size_auto => 75, # 縦横のサイズを自動検出して長いほうの辺のサイズに適用する(優先度：中)
		#percentage => 0.5, # 最大1、最小0.0..01、縦横比維持、size_x or size_y、size_autoとの併用不可(優先度：低)
	});

	unless ($thumbnail->save()) {
		print Dumper $thumbnail->get_error();
		print JSON::Syck::Dump($thumbnail->get_error());
		exit;
	}

	### ここからDB登録処理 ###
	my $result = $schema->resultset($schema_name)->create({
		filename => $fileinfo->{conv_filename},
		original_filename => $fileinfo->{origin_filename},
		date => $fileinfo->{date},
		filetype => $fileinfo->{file_ext},
		filesize => $fileinfo->{file_size}
	});

	print qq/(["success."])/;
	return;
}

sub upload_delete {
	my $del_id = JSON::Syck::Load(shift);
	### ここからUpload処理 ###
	my $upload = MyLibs::Common::Util::Upload->new({
		dir_path => $save_path
	});

	### ここからサムネイル処理 ###
	my $thumbnail = MyLibs::Common::Util::Thumbnail->new({
		dir_path => $save_path_thumbnail
	});

	my $i = scalar(@{$del_id}) - 1;
	for (0..$i) {
		my $filename = $schema->resultset($schema_name)->find($del_id->[$_])->filename;

		### ここから画像削除処理  ###
		unless ($upload->remove($filename)) {
			print JSON::Syck::Dump($upload->get_error());
			exit;
		}

		### ここからサムネイル画像削除処理  ###
		unless ($thumbnail->remove($filename)) {
			print JSON::Syck::Dump($thumbnail->get_error());
			exit;
		}

		### ここからDB削除処理 ###
		unless ($schema->resultset($schema_name)->search(id => $del_id->[$_])->delete == 1) {
			print qq/(["Could not delete record."])/;
			exit;
		}
	}
	print qq/(["success."])/;
	return;
}

sub upload_select {
	my ($page, $limit) = @_;
	my $limit_start = ($page - 1) * $limit;
	my $limit_end   = $limit_start + $limit - 1;

	my $offset = $limit * ($page - 1);

	unless($limit =~ /\d/ || $page =~ /\d/){ return; }

	### ここからDB登録処理 ###
	my $common_data = {
		count => $schema->resultset($schema_name)->search()->count,
		current => $page
	};

	my $custom_data = [];
	my $rs = $schema->resultset($schema_name)->search({},{order_by => 'id DESC'})->slice($limit_start, $limit_end);
	while(my $result = $rs->next){
		push @{$custom_data}, {
			id => $result->id,
			filename => $result->filename,
			original_filename => $result->original_filename,
			date => $result->date,
			filetype => $result->filetype,
			filesize => $result->filesize
		};
	}

	my $res = {
		common => $common_data,
		custom => $custom_data
	};

	print JSON::Syck::Dump($res);
}

my $cgi = new CGI;
print $cgi->header(-type=>"text/html", -charset => 'utf-8');

unless($cgi->referer() =~ /summer-lights.dyndns.ws/){
	print qq/(['Can't permit cross domain.'])/;
	exit;
}

# POSTのみ有効にする
if($cgi->request_method() eq 'POST'){
	if($cgi->param("method") eq "insert"){
		upload_insert();
	}
	elsif($cgi->param("method") eq "select"){
		upload_select($cgi->param("page"), $cgi->param("slice"));
	}
	elsif($cgi->param("method") eq "delete"){
		upload_delete($cgi->param("query"));
	}
}

