#!/usr/bin/perl
## DBパッケージ名前空間
package DB;

use strict;
use warnings;
use JSON;
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

# Read
sub read {
	my $self = shift;
	my ($stmt, @bind, $sth);
	my $rv = 0;
	$stmt = "SELECT * FROM diary4_filearchives ORDER BY id DESC";
	$sth = $self->{db}->prepare($stmt) or die $DBI::errstr;
	$sth->execute();
	while(my $res = $sth->fetchrow_hashref){
		push @{$self->{data}}, $res;
		$rv = 1;
	}
	$sth->finish();
	$rv == 1 ? $self->success() : $self->failure();
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

# CGI開始
my $cgi = new CGI();
print $cgi->header(-type=>"text/javascript+json", -charset=>"euc-jp");

# パラメータ取得
my $callback = $cgi->param("callback") || exit;

# DB接続
my $db = DB->new();
$db->DBConnect();

# ファイルアーカイブリストを取得
$db->read();
my $json = $db->get_json();

# DB切断
$db->DBClose();

print $callback . "(" . $json . ")";