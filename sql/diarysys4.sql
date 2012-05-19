--エントリテーブル

drop table if exists diary4;
create table diary4 (
	id int not null auto_increment primary key,
	title text not null,
	description text not null,
	tag varchar(100) not null,
	date datetime not null,
	weather_id int not null
);

-- タグ種類テーブル

drop table if exists diary4_tag_classify;
create table diary4_tag_classify (
	tid int not null auto_increment primary key,
	tname varchar(100) unique not null
);

-- タグ参照回数テーブル

drop table if exists diary4_tag_reference;
create table diary4_tag_reference (
	tid int not null primary key,
	reference int not null
);

-- 関連エントリテーブル
drop table if exists diary4_relation;
create table diary4_relation (
	eid int not null primary key,
	rel_entry1 int,
	rel_entry2 int,
	rel_entry3 int,
	rel_entry4 int,
	rel_entry5 int,
	rel_value1 float,
	rel_value2 float,
	rel_value3 float,
	rel_value4 float,
	rel_value5 float
);


