drop table if exists diary4_widget;
create table diary4_widget (
	id int not null auto_increment primary key,
	pid varchar(100) not null,
	pluginname varchar(100) not null,
	title varchar(100) not null,
	priority int not null,
	state varchar(10) not null,
	url varchar(200),
	image varchar(100),
	disp int
);

-- Yahoo! Topics
drop table if exists diary4_widget_yahootopics;
create table diary4_widget_yahootopics (
	id int not null auto_increment primary key,
	title varchar(100) not null,
	url varchar(100) not null,
	date datetime not null
);