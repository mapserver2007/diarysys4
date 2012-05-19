#!/usr/bin/perl
## DBパッケージ名前空間
package DB;

use strict;
use warnings;
use DBI;
use JSON;
use Data::Dumper;

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
	return;
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

# Read
sub read {
	my $self = shift;
	my ($stmt, @bind, $sth);
	my $rv = 0;
	$stmt = "SELECT * FROM diary4_widget ORDER BY priority, id";
	$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
	$sth->execute();
	while(my $res = $sth->fetchrow_hashref){
		push @{$self->{data}}, $res;
		$rv = 1;
	}
	$sth->finish();
	$rv == 1 ? $self->success() : $self->failure();
}

# Write
sub write {
	my $self = shift;
	my $write_data = $_[0];
	my ($stmt, @bind, $sth, $rv);
	$stmt = "UPDATE diary4_widget SET pluginname=?, title=?, priority=?, state=?, ";
	$stmt.= "url=?, image=?, disp=? WHERE id = ?";
	foreach my $param (@{$write_data}){
		@bind = ($param->{pluginname}, $param->{title}, $param->{priority}, $param->{state}, $param->{url}, $param->{image}, $param->{disp}, $param->{id});
		$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
		$sth->execute(@bind) or die $DBI::errstr;
	}
	$sth->finish() or die $DBI::errstr;;
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
use CGI;
use Data::Dumper;

# CGI開始
my $cgi = new CGI();
print $cgi->header(-type=>"text/javascript+json", -charset=>"utf-8");

# DB接続開始
my $db = DB->new();
$db->DBConnect();

# POSTデータ取得
my $write_data = [];
if($cgi->param){
	foreach my $param ($cgi->param) {
		# POSTデータからの配列を作る。配列のキーとインデックスを抽出
		my @tmp = split(/_/, $param);
		# 2次元配列化する
		$write_data->[$tmp[1] - 1]->{substr($tmp[0], 1)} = $cgi->param($param);
	}
	# Widgetデータ書き込み
	$db->write($write_data);
}
# Widgetデータ読み込み
$db->read();
my $json = $db->get_json();

# DB接続終了
$db->DBClose();

# JSONで出力
print $json;
