#!/usr/bin/perl
use strict;
use warnings;
use CGI;
use JSON::Syck qw/Load Dump/;
use FindBin::libs qw{ export base=syscommon };
use MyLibs::Common::DB::Config;
use MyLibs::Common::DB::Schema;

my $cgi = new CGI;
print $cgi->header(-type=>"text/html", -charset => 'utf-8');
my $callback = $cgi->escapeHTML($cgi->param("callback")) || exit;

unless($cgi->referer() =~ /summer-lights.dyndns.ws/){
	print qq/(['Can't permit cross domain.'])/;
	exit;
}

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

my $carousel_data = [];
my $rs = $schema->resultset($schema_name)->search({},{order_by => 'id DESC'});
while(my $result = $rs->next){
	push @{$carousel_data}, {
		id => $result->id,
		filename => $result->filename,
		original_filename => $result->original_filename,
		date => $result->date,
		filetype => $result->filetype,
		filesize => $result->filesize
	};
}

print $callback . "(" . JSON::Syck::Dump($carousel_data) . ");";