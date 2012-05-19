#!/usr/bin/perl
use strict;
use warnings;
use Data::Alias;
use DBI;

# 初期データ
sub init {
	# Livedoor クリップ
	my $widget_01 = {
		pid => "livedoorclip",
		pluginname => "LivedoorClip",
		title => "Livedoor クリップ",
		priority => 2,
		state => 0,
		url => "http://clip.livedoor.com/",
		image => "widget_livedoorclip.png",
		disp => 10
	};
	# TMAP
	my $widget_02 = {
		pid => "tmap",
		pluginname => "TMAP",
		title => "TMAP ver.3",
		priority => 1,
		state => 1,
		url => "http://summer-lights.dyndns.ws/tmap/",
		image => "widget_tmap.png",
		disp => ""
	};
	# タグクラウド
	my $widget_03 = {
		pid => "tagcloud",
		pluginname => "tagcloud",
		title => "タグクラウド",
		priority => 3,
		state => 0,
		url => "http://summer-lights.dyndns.ws/",
		image => "",
		disp => ""
	};
	# 月別アーカイブ
	my $widget_04 = {
		pid => "archives",
		pluginname => "archives",
		title => "月別アーカイブ",
		priority => 4,
		state => 1,
		url => "http://summer-lights.dyndns.ws/",
		image => "noimg.gif",
		disp => ""
	};
	# Yahooトピック
	my $widget_05 = {
		pid => "yahootopics",
		pluginname => "YahooTopics",
		title => "Yahoo!トピックス",
		priority => 5,
		state => 1,
		url => "http://headlines.yahoo.co.jp/hl",
		image => "widget_yahootopics.png",
		disp => 10
	};
	
	return [$widget_01, $widget_02, $widget_03, $widget_04, $widget_05];
}

# データベース接続
my $dsn = "DBI:mysql:diarysys:localhost:3306;mysql_read_default_file=/etc/mysql/my.cnf";
my $user = "mysql";
my $pass = "mysql";
my $dbh = DBI->connect($dsn, $user, $pass);

# 登録する初期データを取得
my $init_data = init();

# 初期設定をデータベースに書き込む
my ($stmt, @bind, $sth, $rev);
my $idx = scalar(@{$init_data}) - 1;
for my $i (0..$idx){
	alias my $widget_item = $init_data->[$i];
	$stmt = "INSERT INTO diary4_widget (pid, pluginname, title, priority, state, url, image, disp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	@bind = ($widget_item->{'pid'}, $widget_item->{'pluginname'}, $widget_item->{'title'}, $widget_item->{'priority'}, $widget_item->{'state'}, $widget_item->{'url'}, $widget_item->{'image'}, $widget_item->{'disp'});
	$sth = $dbh->prepare($stmt) or die $dbh->errstr;
	$rev = $sth->execute(@bind);
	$sth->finish();
	print "初期化$i件目：OK\n" if ($rev == 1);
}

$dbh->disconnect;

