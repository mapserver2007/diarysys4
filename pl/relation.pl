#!/usr/bin/perl
use strict;
use warnings;
use Data::Dumper;
use String::Trigram;
use Diarysys::DB;
use CGI;

use constant KEY => "mapserver";

# BBコードなどを除去
sub trim_bbcode {
	my $str = shift;
	$str =~ s/\[.+?\]//g; #BBコードタグを除去
	$str =~ s/(\n|\t|\s)//g; #改行,タブ,スペースを除去
	return $str;
}

# 類似エントリを抽出し登録する(関連タグを対象)
sub entry_comp_tag {
	my $rel_entry_cnt = 5; # 抽出するエントリ数(最大5件まで)
	my ($sql, @bind);
	
	# DB接続
	my $db = Diarysys::DB->new();
	$db->DBConnect();

	# エントリ内容とIDを取得
	$sql = "SELECT id, tag, description FROM diary4 ORDER BY id DESC";
	#$sql = "select id, description from diary4 order by id desc";　# 全エントリ用
	@bind = ();
	$db->fetch($sql, @bind);
	my $entries = $db->get_data();

	# テーブルを空にする
	$sql = "DELETE FROM diary4_relation";
	@bind = ();
	$db->register($sql, @bind);

	# タグを比較
	my $comp = sub {
		my ($t1, $t2) = @_;
		my $res = 0;
		for my $split_t1 (split(/,/, $t1)){
			for my $split_t2 (split(/,/, $t2)){
				$res = 1 if $split_t1 == $split_t2;
			}
		}
		return $res;
	};

	#　エントリの関連度を抽出
	for my $base_entry (@{$entries}){
		my @res = ();
		my @tmp = ();

		# 全てのエントリと比較
		for my $entry (@{$entries}){
			# 自分自身とは比較しない
			next if $base_entry->{id} == $entry->{id};
			# 同じタグが検出されない場合は比較しない
			next if $comp->($base_entry->{tag}, $entry->{tag}) == 0; # タグで関連付ける場合
			
			# 類似度を取得
			my $smlty = String::Trigram::compare(trim_bbcode($base_entry->{description}), trim_bbcode($entry->{description}));
			# 配列にキャッシュ
			push @tmp, {id => $entry->{id}, rel => sprintf("%.2f", $smlty * 100)};
		}

		# 関連度が高い順にソート
		my @sorted_tmp = sort { $b->{rel} <=> $a->{rel} } @tmp;

		# 関連度の高い上位x件を取得
		for (my $i = 0; $i < $rel_entry_cnt; $i++){
			push @res, scalar keys(%{$sorted_tmp[$i]}) > 0 ? $sorted_tmp[$i] : {id => "", rel => ""};
		}

		# 変数に展開
		my ($e1, $e2, $e3, $e4, $e5) = @res;

		# DB登録
		$sql = "INSERT INTO diary4_relation (eid, rel_entry1, rel_entry2, rel_entry3, rel_entry4, rel_entry5, rel_value1, rel_value2, rel_value3, rel_value4, rel_value5) ";
		$sql.= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		@bind = ($base_entry->{id}, $e1->{id}, $e2->{id}, $e3->{id}, $e4->{id}, $e5->{id}, $e1->{rel}, $e2->{rel}, $e3->{rel}, $e4->{rel}, $e5->{rel});

		$db->register($sql, @bind);
	}

	# DB切断
	$db->DBClose();
}

# CGI開始
#my $cgi = new CGI();
#print $cgi->header(-type=>"text/html", -charset=>"euc-jp");

# 関連エントリIDと関連度を取得
entry_comp_tag();
#entry_comp_tag() if $cgi->param("key") eq KEY;

