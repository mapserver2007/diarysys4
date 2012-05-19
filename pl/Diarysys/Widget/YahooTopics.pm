package Diarysys::Widget::YahooTopics;

{
	use Class::Std::Utils;
	use LWP::UserAgent;
	use XML::Simple;
	use Encode;
	use JSON;
	
	use constant URL => "http://news.yahooapis.jp/NewsWebService/V1/Topics?";
	
	my %request_url;  # リクエストURL
	my %request_num;  # 取得する件数
	my %result;       # 取得結果を格納
	
	# コンストラクタ
	sub new {
		my ($class, $init_ref) = @_;
		
		# スカラーをブレスする
		my $obj = bless \do{my $anon_scalar}, $class;
		# メンバデータを初期化する
		#$result{ident $obj} = $init_ref->{num};
		$request_num{ident $obj} = shift;
		
		return $obj;
	}
	
	# Topics取得処理をまとめて実行
	sub batch {
		my $self = shift;
		
		# リクエストURL生成
		$self->create_url();
		
		# Topics取得＆セット
		$self->topics();
		
		return;
	}
	
	# URL生成
	sub create_url {
		my $self = shift;
		#my $disp = shift;
		
		# パラメータ定義
		my $param = {
			appid => "2UhFXQyxg65WNt.KM6hVB4hesXqHznDF72cn4mkC6qPbc9k_id6NcQFMYSiqfRNESePgJg--",
			topicname => "",
			category => "domestic",
			word => "",
			topflg => "",
			midashiflg => "",
			relatedtopics => 0,
			relatedsite => 0,
			sort => "pvindex",
			order => "d",
			num => $request_num{ident $obj}
		};

		# URL生成
		my $url = URL;
		foreach my $key (%{$param}){
			($url .= $key . "=" . $param->{$key} . "&") if ($param->{$key});
		}
		chop $url;
		
		# URLをセット
		$request_url{ident $self} = $url;

		return;
	}
	
	# XML取得
	sub topics {
		my $self = shift;
		
		# XMLを取得
		my $ua = LWP::UserAgent->new();
		my $res = $ua->get($request_url{ident $self});
		$res->is_success or die "Can't connect to Yahoo Developer Network.";
		my $xml = $res->content;
		
		#　XMLをハッシュに変換
		my $hash = XMLin($xml);
	
		# 必要な個所を抜く
		my $topics = [];
		my $require_data = $hash->{"Result"};
		foreach my $data (@{$require_data}){
			next if (ref($data->{title}) eq "HASH");
			my $topic = {
				"date" => $data->{datetime},
				"title" => $data->{title},
				"url" => $data->{url}
			};
			push @{$topics}, $topic;
		}
		
		$result{ident $self} = to_json($topics);
		
		return;
	}
	
	# トピックス取得
	sub get_data {
		my $self = shift;
		return $result{ident $self};
	}
	
	# オブジェクト破棄時に属性をクリーンアップする
	sub DESTROY {
		my ($self) = @_;
		
		delete $request_url{ident $self};
		delete $request_num{ident $self};
		delete $result{ident $self};
		
		return;
	}
}

1;