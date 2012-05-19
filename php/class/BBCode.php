<?php
class BBCode {
	private $bbcode = array();
	/**
	 * コンストラクタ
	 * @return 
	 * @param $list Object
	 */
	function __construct(){
		$this->defineBBCodeRegexp();
	}
	
	private function defineBBCodeRegexp(){
		//[color]
		$this->bbcode["color"] = array(
			"pattern" => '/\[color\=\#([0-9a-fA-F]{3})\](.+)\[\/color\]/',
			"replacement" => '<span style="color: #$1;">$2</span>'
		);
		//[image]
		$this->bbcode["image1"] = array(
			"pattern" => '/\[image\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))\](.+?)\[\/image\]/',
			"replacement" => '<a href="$1" rel="facebox"><img src="$1" alt="$3" title="$3" class="pic2" /></a>'
		);
		//[image]
		$this->bbcode["image2"] = array(
			"pattern" => '/\[image\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+\.(gif|jpe?g|png|bmp))\[\/image\]/',
			"replacement" => '<a href="$1" rel="facebox"><img src="$1" alt="$1" title="$1" class="pic2" /></a>'
		);
		//[url]
		$this->bbcode["url1"] = array(
			"pattern" => '/\[url\=(https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)\](.+?)\[\/url\]/',
			"replacement" => '<a href="$1">$2</a>'
		);
		//[url]
		$this->bbcode["url2"] = array(
			"pattern" => '/\[url\](https?:\/\/[\-\_\.\!\~\*\'\(\)a-zA-Z0-9\;\/\?\:\@\&\=\+\$,\%\#]+)\[\/url\]/',
			"replacement" => '<a href="$1">$1</a>'
		);
		//[code]
		$this->bbcode["code"] = array(
			"pattern" => '/\[code\](.+?)\[\/code\]/',
			"replacement" => '<pre class="prettyprint">$1</pre>'
		);
		//[left]
		$this->bbcode["left"] = array(
			"pattern" => '/\[left\](.+?)\[\/left\]/',
			"replacement" => '<p style="text-align: left;">$1</p>'
		);
		//[center]
		$this->bbcode["center"] = array(
			"pattern" => '/\[center\](.+?)\[\/center\]/',
			"replacement" => '<p style="text-align: center;">$1</p>'
		);
		//[right]
		$this->bbcode["right"] = array(
			"pattern" => '/\[right\](.+?)\[\/right\]/',
			"replacement" => '<p style="text-align: right;">$1</p>'
		);
		//Others
		$this->bbcode["others"] = array(
			"pattern" => '/\[(.+?)\](.+?)\[\/(.+?)\]/',
			"replacement" => '<$1>$2</$3>'
		);
		
		
		
		//[image=url]
		
		
		
		//"month" => array("type" => "Regexp", "reg" => '/^(\d{4}+\-+\d{2})$/', "length" => 7),
		
	
	}
	
	public function bbcode2html($bbstr){
		foreach($this->bbcode as $codes){
			$bbstr = preg_replace($codes["pattern"], $codes["replacement"], $bbstr);
		}
	
	
		echo $bbstr;
	
	}
	
	
}
?>