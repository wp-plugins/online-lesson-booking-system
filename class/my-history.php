<?php
/** 
 *	予約履歴: Reservation history
 */

class olbHistory extends olbPaging {
	public $currenttime = null;
	public $mode = null;			// 'future' or 'history'
	public $target = null;			// 'user' or 'room'
	public $target_id = null;		// 'user_id' or 'room_id'

	/** 
	 *	CONSTRUCT
	 */
	public function __construct($mode, $target, $target_id, $limit) {
		global $olb;

		$currenttime = current_time('timestamp');
		if( $olb->closetime >= '24:00' && olbTimetable::calcHour($olb->closetime, -24*60) >= olbTimetable::calcHour(date('H:i:s', $currenttime), 0)){
			list($y, $m, $d) = explode('-', date('Y-m-d', $currenttime));
			$this->currentdate = date('Y-m-d', mktime(0, 0, 0, $m, $d-1, $y));
			$this->currenttime = olbTimetable::calcHour(date('H:i:s', $currenttime), 24*60);
		}
		else {
			$this->currentdate = date('Y-m-d', $currenttime);
			$this->currenttime = date('H:i:s', $currenttime);
		}
		$this->mode = $mode;
		$this->target = $target;
		$this->target_id = $target_id;
		$this->limit = $limit;			// 予約履歴表示ページの表示件数(1ページ当たり)
		$this->recordmax = self::recordMax();				// 有効な講座数
		$this->pagemax = ceil($this->recordmax/$this->limit);	// ページ数

		self::getCurrentPage();
	}

	/** 
	 *	予約履歴数を取得: Get count of reservation history
	 */
	public function recordMax($target_id = null){
		global $wpdb;

		$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
		$query = 'SELECT COUNT(*) as count FROM '.$prefix.'history ';
		if(!$target_id){
			$target_id = $this->target_id;
		}
		if($this->mode=='history'){
			if($this->target=='user'){
				$query .= 'WHERE `user_id`=%d AND (`date`<%s OR (`date`=%s AND `time`<=%s))';
			}
			else {
				$query .= 'WHERE `room_id`=%d AND `user_id`>0 AND (`date`<%s OR (`date`=%s AND `time`<=%s))';
			}
		}
		else {
			if($this->target=='user'){
				$query .= 'WHERE `user_id`=%d AND (`date`>%s OR (`date`=%s AND `time`>=%s))';
			}
			else {
				$query .= 'WHERE `room_id`=%d AND `user_id`>0 AND (`date`>%s OR (`date`=%s AND `time`>=%s))';
			}
		}
		$ret = $wpdb->get_row($wpdb->prepare($query, array($target_id, $this->currentdate, $this->currentdate, $this->currenttime)), ARRAY_A);
		return $ret['count'];
	}

	/** 
	 *	予約履歴を取得: Get reservation history (past from now)
	 */
	public function get($target_id = null){
		global $wpdb, $olb;

		$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
		$query = 'SELECT * FROM '.$prefix.'history ';
		if(!$target_id){
			$target_id = $this->target_id;
		}

		$limit = '';
		if($this->limit!=0) {
			if($this->offset!=0) {
				$limit = sprintf(' LIMIT %d, %d', $this->offset, $this->limit);
			}
			else {
				$limit = sprintf(' LIMIT %d', $this->limit);
			}
		}
		if($this->mode=='history') {
			if($this->target=='user'){
				$query .= 'WHERE `user_id`=%d AND (`date`<%s OR (`date`=%s AND `time`<=%s)) ';
			}
			else {
				$query .= 'WHERE `room_id`=%d AND `user_id`>0 AND (`date`<%s OR (`date`=%s AND `time`<=%s)) ';
			}
			$query .= 'ORDER BY date DESC, time DESC';
		}
		else {
			if($this->target=='user'){
				$query .= 'WHERE `user_id`=%d AND (`date`>%s OR (`date`=%s AND `time`>=%s)) ';
			}
			else {
				$query .= 'WHERE `room_id`=%d AND `user_id`>0 AND (`date`>%s OR (`date`=%s AND `time`>=%s)) ';
			}
			$query .= 'ORDER BY date ASC, time ASC';
		}
		$query.= $limit;
		$ret = $wpdb->get_results($wpdb->prepare($query, array($target_id, $this->currentdate, $this->currentdate, $this->currenttime)), ARRAY_A);
		return $ret;
	}

	/** 
	 *	会員の受講履歴: Show member's attendance history 
	 */
	public function htmlMembersHistory($out = false){
		global $olb;

		$records = $this->get();

		ob_start();
		if(!empty($records)) {
			echo '<table id="members_history" class="history_list">'."\n";
			echo '<tr class="head">'."\n";
			printf('<th class="date">%s</th><th class="room">%s</th><th class="absent">%s</th>',
				__('Date/Time', OLBsystem::TEXTDOMAIN),
				__('Teacher', OLBsystem::TEXTDOMAIN),
				__('Absent', OLBsystem::TEXTDOMAIN)
				);
			echo '</tr>'."\n";

			foreach($records as $r) {
				$room = olbRoom::get($r['room_id']);
				echo '<tr>'."\n";
				$roomlink = $room['name'];
				if(!empty($room['url'])){
					$roomlink = sprintf('<a href="%s">%s</a>', $room['url'], $room['name']);
				}
				$absent = '';
				if($r['absent']){
					$absent = sprintf('<span class="absent">%s</span>', __('Absent', OLBsystem::TEXTDOMAIN));
				}
				printf('<td class="date">%s %s</td><td class="room">%s</td><td class="absent">%s</td>',
					$r['date'],
					substr($r['time'], 0, 5),
					$roomlink,
					$absent
					);
				echo '</tr>'."\n";
			}
			echo '</table>';
		}
		$html = ob_get_contents();
		ob_end_clean();
		if ($out) {
			echo $html;
		}
		else {
			return $html;
		}
	}

	/** 
	 *	講師の実施履歴: Show teacher's lecture history 
	 */
	public function htmlRoomHistory($out = false){
		global $olb;

		$records = $this->get();

		ob_start();
		if(!empty($records)) {
			echo '<table id="room_history" class="history_list">'."\n";
			echo '<tr class="head">'."\n";
			printf('<th class="date">%s</th><th class="room">%s</th><th class="absent">%s</th>',
				__('Date/Time', OLBsystem::TEXTDOMAIN),
				__('Member(Skype)', OLBsystem::TEXTDOMAIN),
				__('Absent', OLBsystem::TEXTDOMAIN)
				);
			echo '</tr>'."\n";

			foreach($records as $r) {
				$user = new olbAuth($r['user_id']);
				echo '<tr>'."\n";
				$time = $olb->getTimetableKey($r['date'], $r['time']);
				$reportlink = $olb->htmlReportLink($time, '#');
				if($r['absent']){
					$reportlink .= sprintf(' <span class="absent">%s</span>', __('Absent', OLBsystem::TEXTDOMAIN));
				}
				printf('<td class="date">%s %s</td><td class="member">%s(%s)</td><td class="absent">%s</td>',
					$r['date'],
					substr($r['time'], 0, 5),
					$user->data['name'],
					$user->data['skype'],
					$reportlink
					);
				echo '</tr>'."\n";
			}
			echo '</table>';
		}
		$html = ob_get_contents();
		ob_end_clean();
		if ($out) {
			echo $html;
		}
		else {
			return $html;
		}
	}

	/** 
	 *	会員の予約予定: Show member's future schedule
	 */
	public function htmlMembersSchedule($out = false){
		global $olb;

		$records = $this->get();
		ob_start();
		if(!empty($records)) {
			echo '<table id="members_schedule" class="future_list">'."\n";
			echo '<tr class="head">'."\n";
			printf('<th class="date">%s</th><th class="waiting">%s</th><th class="room">%s</th><th class="cancel">%s</th>',
				__('Date/Time', OLBsystem::TEXTDOMAIN),
				__('Waiting', OLBsystem::TEXTDOMAIN),
				__('Teacher', OLBsystem::TEXTDOMAIN),
				__('Cancel', OLBsystem::TEXTDOMAIN)
				);
			echo '</tr>'."\n";

			foreach($records as $r) {
				$room = olbRoom::get($r['room_id']);
				echo '<tr>'."\n";
				if(!empty($room['url'])){
					$roomlink = sprintf('<a href="%s">%s</a>', $room['url'], $room['name']);
				}
				else {
					$roomlink = $room['name'];
				}
				$time = olbTimetable::getTimetableKey($r['date'], $r['time']);
				if(olbTimetable::isTimeover('cancel', $r['date'], $r['time'])){
					$cancellink = __('Time over', OLBsystem::TEXTDOMAIN);
				}
				else {
					$cancellink = $olb->htmlReserveLink($r['room_id'], $time, __('CANCEL', OLBsystem::TEXTDOMAIN));
				}
				$waiting = self::waitingTime($r['date'], $r['time'], current_time('timestamp'));
				printf('<td class="date">%s %s</td><td class="waiting">%s</td><td class="room">%s</td><td class="cancel">%s</td>',
					$r['date'],
					substr($r['time'], 0, 5),
					$waiting,
					$roomlink,
					$cancellink
					);
				echo '</tr>'."\n";
			}
			echo '</table>';
		}
		$html = ob_get_contents();
		ob_end_clean();
		if ($out) {
			echo $html;
		}
		else {
			return $html;
		}
	}

	/** 
	 *	講師の予約予定: Show teacher's future schedule
	 */
	public function htmlRoomSchedule($out = false){
		global $olb;

		$records = $this->get();

		ob_start();
		if(!empty($records)) {
			echo '<table id="room_schedule" class="future_list">'."\n";
			echo '<tr class="head">'."\n";
			printf('<th class="date">%s</th><th class="waiting">%s</th><th class="room">%s</th><th class="cancel">%s</th>',
				__('Date/Time', OLBsystem::TEXTDOMAIN),
				__('Waiting', OLBsystem::TEXTDOMAIN),
				__('Member(Skype)', OLBsystem::TEXTDOMAIN),
				__('Cancel', OLBsystem::TEXTDOMAIN)
				);
			echo '</tr>'."\n";

			foreach($records as $r) {
				$user = new olbAuth($r['user_id']);
				echo '<tr>'."\n";
				$time = olbTimetable::getTimetableKey($r['date'], $r['time']);

				if(olbTimetable::isTimeover('cancel', $r['date'], $r['time'])){
					$cancellink = __('Time over', OLBsystem::TEXTDOMAIN);
				}
				else {
					$cancellink = $olb->htmlCancelLink($time, __('CANCEL', OLBsystem::TEXTDOMAIN));
				}

				$waiting = self::waitingTime($r['date'], $r['time'], current_time('timestamp'));
				printf('<td class="date">%s %s</td><td class="waiting">%s</td><td class="member">%s(%s)</td><td class="cancel">%s</td>',
					$r['date'],
					substr($r['time'], 0, 5),
					$waiting,
					$user->data['name'],
					$user->data['skype'],
					$cancellink
					);
				echo '</tr>'."\n";
			}
			echo '</table>';
		}
		$html = ob_get_contents();
		ob_end_clean();
		if ($out) {
			echo $html;
		}
		else {
			return $html;
		}
	}

	/** 
	 *	待ち時間表示: set waiting time
	 */
	public function waitingTime($date, $time, $now){

		if($time >= '24:00'){
			$time = olbTimetable::calcHour($time, -24*60);
			list($y, $m, $d) = explode('-', $date);
			list($h, $i, $s) = explode(':', $time);
			$t = strtotime(date('Y-m-d H:i:s', mktime($h, $i, $s, $m, $d+1, $y)));
		}
		else {
			$t = strtotime($date.' '.$time);
		}
		$waitingtime = $t - $now;
		$waiting = '';
		$whour = floor($waitingtime / (60*60));
		if($whour < 24) {
			if($whour == 1) {
				$hform = __('%1$d hour ', OLBsystem::TEXTDOMAIN);
			}
			else if($whour > 0) {
				$hform = __('%1$d hours ', OLBsystem::TEXTDOMAIN);
			}
			else {
				$hform = '';
			}
			$wmin = floor(($waitingtime % (60*60)) / 60);
			if($wmin == 1) {
				$mform = __('%2$d minute ', OLBsystem::TEXTDOMAIN);
			}
			else if($wmin > 0) {
				$mform = __('%2$d minutes ', OLBsystem::TEXTDOMAIN);
			}
			else {
				$mform = '';
			}
			if(strlen($hform.$mform)) {
				$waiting .= sprintf($hform.$mform.__('later', OLBsystem::TEXTDOMAIN), $whour, $wmin);
			}
		}
		else {
			if($whour == 24){
				$format = __('%d day later', OLBsystem::TEXTDOMAIN);
			}
			else {
				$format = __('%d days later', OLBsystem::TEXTDOMAIN);
			}
			$waiting = sprintf($format, $whour/24);
		}
		return $waiting;
	}
}
?>
