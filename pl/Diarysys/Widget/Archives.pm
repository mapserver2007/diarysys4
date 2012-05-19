package Diarysys::Widget::Archives;

{
	use Class::Std::Utils;
	use JSON;

	my %archives;

	# コンストラクタ
	sub new {
		my ($class, $init_ref) = @_;

		# スカラーをブレスする
		my $obj = bless \do{my $anon_scalar}, $class;

		return $obj;
	}

	# タグ取得処理開始
	sub batch {
		my $self = shift;
		$self->archives();

		return;
	}

	# 月別アーカイブをDBから取得
	sub archives {
		my $self = shift;

		my $db = Diarysys::DB->new();
		$db->DBConnect();

		# SQL実行
		my $sql = "SELECT date FROM diary4 ORDER BY id";
		my @bind = ();
		$db->fetch($sql, @bind);
		my $archive_date = $db->get_data();

		my ($current_year, $current_month, $counter, $res);
		for my $archives (@{$archive_date}){
			# 現在の年月を取得
			my @cymd = split(/\s/, $archives->{date});
			my ($cy, $cm, $cd) = split(/-/, $cymd[0]);

			# 各年/月のエントリ数をカウント
			my $ym = qq/$cy$cm/;
			$counter->{$ym} = 0 if !$counter->{$ym};
			$counter->{$ym}++;
		}

		# 各年/月ごとに配列の要素として格納
		push @{$res}, map{ {year => substr($_, 0, 4), month => substr($_, 4, 2), entry => $counter->{$_}};} sort { $a <=> $b } keys %{$counter};

		$archives{ident $self} = to_json($res);

		$db->DBClose();

		return;
	}

	# アーカイブ数取得
	sub get_data {
		my $self = shift;
		return $archives{ident $self};
	}


	# オブジェクト破棄時に属性をクリーンアップする
	sub DESTROY {
		my ($self) = @_;

		delete $archives{ident $self};

		return;
	}
}

1;