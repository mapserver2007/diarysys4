#!/usr/bin/perl
use strict;
use warnings;
use CGI;
use Diarysys::DB;
use Diarysys::Widget;
use Diarysys::Widget::TagCloud;
use Diarysys::Widget::Archives;
use Diarysys::Widget::LivedoorClip;
use Diarysys::Widget::YahooTopics;

# CGI開始
my $cgi = new CGI();
print $cgi->header(-type=>"text/html", -charset=>"utf-8");

# Widgetモジュール呼び出し開始
my $widget = Diarysys::Widget->new({
	wid      => $cgi->param("wid")      || "",        # WidgetID(必須)
	uid      => $cgi->param("uid")      || "",        # 外部APIのユーザID(任意)
	disp     => $cgi->param("disp")     || "",        # 表示件数(任意:通常はDBから取得)
	callback => $cgi->param("callback") || ""         # コールバック関数名(必須)
});

$widget->call_widget();
print $widget->get_data();
