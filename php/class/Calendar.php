<?php
require_once 'Calendar/Month.php';
/**
 * カレンダークラス
 * Pear::Calendarが必要
 * @author   Ryuichi TANAKA
 * @version  2008/12/06
 */
class dsCalendar {
	private $calendar;
	private $year, $month;
	/**
	 * コンストラクタ
	 * @return 
	 */
	function __construct(){
		//現在の年・月を取得
		list($this->year, $this->month) = explode("-", date("Y-m"));
		$this->db = new DB();
	}
	/**
	 * デストラクタ
	 * @return 
	 */
	function __destruct(){
		$this->calendar = null;
	}
	/**
	 * 年・月を指定する
	 * @return 
	 * @param object $year[optional]
	 * @param object $month[optional]
	 */
	public function setDate($year = null, $month = null, $day = null){
		if(preg_match("/^(\d{4})$/", $year)){
			$this->year = $year;
		}
		if($month >= 1 && $month <= 12){
			$this->month = $month;
		}
	}
	/**
	 * カレンダーを取得
	 * @return 
	 */
	public function getCalendar(){
		$res = array();
		//カレンダー取得
		$this->calendar = new Calendar_Month($this->year, $this->month);
		$this->calendar->build();
		//当該月にエントリがあるものだけを取得
		$entried = $this->getEntryByDay();
		$i = 0;
		while ($day = $this->calendar->fetch()) {
			$formated_day = sprintf("%02d", $day->thisDay());
			$formated_entried = "";
			//その日に登録済みのエントリがある場合
			if(in_array($formated_day, $entried)){
				$formated_entried = $entried[$i++];
			}
		    $res[] = array(
				"current_month" => $this->year . "-" . $this->month,
				"day" => $formated_day,
				"entried" => $formated_entried,
				"param" => $this->year . "-" . $this->month . "-" . $formated_day
			);
		}
		return $res;
	}
	/**
	 * エントリが登録されている日を取得する
	 * @return 
	 */
	private function getEntryByDay(){
		$sql = "SELECT * FROM diary4 WHERE date LIKE :day ORDER BY id";
		$bind = array("day" => $this->year . "-" . $this->month . "%");
		//SQL実行
		$this->db->sql($sql, $bind);
		//エントリIDと日付を取得する
		$res = array();
		$prev_day = "";
		while($row = $this->db->fetch()){
			$crnt_day = substr($row["date"], 8, 2);
			if($crnt_day == $prev_day) continue;
			$res[] = $crnt_day;
			$prev_day = $crnt_day;
		}
		return $res;
	}
	/**
	 * エントリが登録さている月を取得する
	 * @return 
	 */
	public function getEntryArchives(){
		$sql = "SELECT date FROM diary4 ORDER BY id";
		$bind = array();		
		//SQL実行
		$this->db->sql($sql, $bind);
		//基準となる年、月を定義
		$res = array();
		$i = 0;
		while($row = $this->db->fetch()){
			//現在の年月を取得
			$cymd = preg_split('/[\s]/', $row["date"]);
			list($cy, $cm, $cd) = split("-", $cymd[0]);
			$current_year = $cy;
			$current_month = (int)$cm;
			//エントリの最も古いを取り出し、開始年度を求める
			if(count($res) == 0){
				//1月からエントリが開始されるひとつ前の月まで空のデータを作る
				for($j = 1; $j < (int)$current_month; $j++){
					$res[$current_year][$j] = 0;
				}
			}
			//ひとつ前に$resに格納した年月と同じ場合はcountをインクリメントする
			if(count($res) > 0 && $preview_year == $current_year && $preview_month == $current_month){
				$res[$current_year][$current_month] += 1;
			}
			//次の月へ
			else{
				//前月がある場合のみ比較する
				if($preview_year){
					//前月と今月で1月以上開きがある場合、空のデータで埋める
					$blank_year = $current_year - $preview_year;
					$blank_month = ($current_month + 12 * $blank_year) - $preview_month;
					if($blank_month > 1){
						for($j = 0; $j < $blank_month; $j++){
							$res[$current_year][$preview_month + $j] = 0;
						}
					}
				}
				$res[$current_year][$current_month] = 1;
			}
			//次の比較のために今の年月値を保持しておく
			$preview_year = $current_year;
			$preview_month = $current_month;
		}
		return $res;
	}
}
?>