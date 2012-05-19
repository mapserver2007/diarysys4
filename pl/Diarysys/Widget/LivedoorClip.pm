package Diarysys::Widget::LivedoorClip;

{
	use Class::Std::Utils;
	use LWP::UserAgent;
	use Encode;
	
	use constant URL => "http://api.clip.livedoor.com/json/clips?";
	use constant LIVEDOOR_DEFAULT_ID => "mapserver2007";
	use constant CLIP_DEFAULT_LIMIT => 10;
	
	my %result;       # 取得結果を格納
	my %request_url;  # リクエストURL
	my %livedoor_id;  # Livedoor id;
	my %limit;        # 表示件数
	
	# コンストラクタ
	sub new {
		my ($class, $init_ref) = @_;	
		
		# スカラーをブレスする
		my $obj = bless \do{my $anon_scalar}, $class;
		
		# メンバデータを初期化する
		$livedoor_id{ident $obj} = $init_ref->{uid}  || LIVEDOOR_DEFAULT_ID; 
		$limit{ident $obj}       = $init_ref->{disp} || CLIP_DEFAULT_LIMIT;

		return $obj;
	}
	
	# Clip取得処理をまとめて実行
	sub batch {
		my $self = shift;

		# リクエストURL生成
		$self->create_url();
		
		# Clip取得＆セット
		$self->clip();
		
		return;
	}
	
	# URL生成
	sub create_url {
		my $self = shift;
		
		# URL生成
		my $url = URL . "livedoor_id=" . $livedoor_id{ident $self} . "&limit=" . $limit{ident $self};
		$request_url{ident $self} = $url;
		
		return;
	}
	
	# クリップ取得
	sub clip {
		my $self = shift;
		
		# JSON取得
		my $ua = LWP::UserAgent->new();
		my $res = $ua->get($request_url{ident $self});
		$res->is_success or die "Can't connect to Livedoor Clip.";
		$result{ident $self} = $res->content;
	
		return;
	}
	
	# クリップ取得
	sub get_data {
		my $self = shift;
		return $result{ident $self};
	}
	
	# オブジェクト破棄時に属性をクリーンアップする
	sub DESTROY {
		my ($self) = @_;
	
		delete $result{ident $self};
		delete $request_url{ident $self};
		delete $livedoor_id{ident $self};
		delete $limit{ident $self};

		return;
	}
}

1;