package Diarysys::Widget;

{
	use Class::Std::Utils;

	my %result;
	my %param;

	# コンストラクタ
	sub new {
		my ($class, $init_ref) = @_;

		# スカラーをブレスする
		my $obj = bless \do{my $anon_scalar}, $class;

		# メンバデータを初期化する
		$param{ident $obj} = $init_ref;

		return $obj;
	}

	# 表示件数をDBから取得する
	sub set_disp {
		my $self = shift;

		# DB処理開始
		my $db = Diarysys::DB->new();
		$db->DBConnect();

		# SQL実行(表示件数取得)
		my $sql = "SELECT disp FROM diary4_widget WHERE pid = ?";
		my @bind = ($pid);
		$db->fetch($sql, @bind);

		# 表示件数
		my $disp = $db->get_data()->[0]->{disp};
		$param{ident $self}->{disp} = $disp if ($disp > 0);
		$db->DBClose();

		return;
	}

	# 各Widgetを呼び出す
	sub call_widget {
		my $self = shift;
		my $widget_obj;
		$self->set_disp();

		# widget_id によって呼び出すWidgetを変更する
		if($param{ident $self}->{wid} eq "livedoorclip"){
			$widget_obj = Diarysys::Widget::LivedoorClip->new({
				uid  => $param{ident $self}->{uid},
				disp => $param{ident $self}->{disp}
			});
		}
		elsif($param{ident $self}->{wid} eq "tagcloud"){
			$widget_obj = Diarysys::Widget::TagCloud->new();
		}
		elsif($param{ident $self}->{wid} eq "yahootopics"){
			$widget_obj = Diarysys::Widget::YahooTopics->new({
				disp => $param{ident $self}->{disp}
			});
		}
		elsif($param{ident $self}->{wid} eq "archives"){
			$widget_obj = Diarysys::Widget::Archives->new();
		}
		else{
			die qq(Can't get widget data);
		}

		# 実行
		$widget_obj->batch();

		# 結果を格納
		$result{ident $self} = $param{ident $self}->{callback} ? $param{ident $self}->{callback} . "(" . $widget_obj->get_data() . ")" : $widget_obj->get_data();

		return;
	}

	# Widgetデータを返す
	sub get_data {
		$self = shift;
		return $result{ident $self};
	}

	# オブジェクト破棄時に属性をクリーンアップする
	sub DESTROY {
		my ($self) = @_;

		delete $result{ident $self};
		delete $param{ident $self};

		return;
	}
}

1;