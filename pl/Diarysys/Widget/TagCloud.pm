package Diarysys::Widget::TagCloud;

{
	use Class::Std::Utils;
	use JSON;

	my %tags;

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
		$self->tags();

		return;
	}

	# タグをDBから取得
	sub tags {
		my $self = shift;

		my $db = Diarysys::DB->new();
		$db->DBConnect();

		# SQL実行
		my $sql = "SELECT c.tid id, c.tname name, r.reference ref FROM diary4_tag_reference r LEFT JOIN diary4_tag_classify c ON r.tid = c.tid ORDER BY c.tname";
		my @bind = ();
		$db->fetch($sql, @bind);
		my $tags = $db->get_data() || die("Can't display Tag data num.");
		$tags{ident $self} = to_json($tags);

		$db->DBClose();

		return;
	}

	# タグ取得
	sub get_data {
		my $self = shift;
		return $tags{ident $self};
	}

	# オブジェクト破棄時に属性をクリーンアップする
	sub DESTROY {
		my ($self) = @_;

		delete $tags{ident $self};

		return;
	}
}

1;