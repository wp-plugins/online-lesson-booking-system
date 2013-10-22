<?php
/** 
 *	タイムテーブル: Timetable
 */

class olbFormAction {

	/**
	 *	予約・キャンセル: Reservation and Cancellation by member
	 */
	public static function reservation() {
		global $wpdb, $olb;

		$error = '';
		if (empty($_POST['onetimetoken']) || !wp_verify_nonce($_POST['onetimetoken'], OLBsystem::TEXTDOMAIN)) {
			$error = 'NONCE_ERROR';
		}
		else if(empty($_POST['room_id']) || empty($_POST['user_id']) || empty($_POST['reservedate']) || empty($_POST['reservetime'])) {
			$error = 'PARAMETER_INSUFFICIENT';
		}
		else if($_POST['reserveaction']!='reserve' && $_POST['reserveaction']!='cancel') {
			$error = 'INVALID_PARAMETER';
		}
		else {
			$result = $olb->canReservation($_POST['room_id'], $_POST['user_id'], $_POST['reservedate'], $_POST['reservetime']);
			/**
			 *	$result = array( 
			 *		'code'   => 'RESERVE_OK',
			 *		'record' => array(
			 *			'id'      => 56,
			 *			'room_id' => 5,
			 *			'user_id' => 6,
			 *			'date'    => '2013-07-11',
			 *			'time'    => '10:00:00'
			 *			'free'    => 0
			 *			'absent'  => 0
			 *		),
			 *		'user'   => olbAuth Object(
			 *		),
			 *		'room'   => array(
			 *		),
			 *	)
			 */
			extract($result);	// $code, $record, $user, $room

			$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
			// 予約
			if($_POST['reserveaction']=='reserve' && $code=='NOT_RESERVED'){
				$record['user_id'] = $user->data['id'];
				if($user->canFreeReservation()){
					$record['free'] = 1;
				}
				$table = $prefix."history";
				$result = $wpdb->insert(
								$table,
								array(
									'date'=>$record['date'],
									'time'=>$record['time'],
									'room_id' => $record['room_id'],
									'user_id' => $record['user_id'],
									'free' => $record['free']
								)
							);
				$record = $olb->reserved($record['room_id'], $record['date'], $record['time']);
			}
			// CANCEL
			else if($_POST['reserveaction']=='cancel' && $code=='ALREADY_RESERVED'){
				$record['user_id'] = '';
				$record['free'] = 0;
				$query = "DELETE FROM ".$prefix."history WHERE `id`=%d";
				$ret = $wpdb->query($wpdb->prepare($query, array($record['id'])), ARRAY_A);
				if(!$ret){
					$error = 'CANCEL_FAILED';
				}
			}
			// エラーあり
			else {
				$error = $code;
			}
		}
		// エラーあり
		if($error) {
			$url = get_permalink(get_page_by_path($olb->reserve_form_page)->ID);
			$query_string = (strstr($url, '?')) ? '&' : '?';
			$query_string .= sprintf('error=%s', $error);
			header('Location:'.$url.$query_string);
			exit;
		}


		$options = $olb->getPluginOptions('mail');
		$search = array(
			'%USER_ID%',
			'%USER_NAME%',
			'%USER_EMAIL%',
			'%USER_SKYPE%',
			'%ROOM_NAME%',
			'%RESERVE_ID%',
			'%RESERVE_DATE%',
			'%RESERVE_TIME%',
			'%SEND_TIME%',
			);
		$replace = array(
			$user->data['id'],
			$user->data['name'],
			$user->data['email'],
			$user->data['skype'],
			$room['name'],
			$record['id'],
			$record['date'],
			substr($record['time'], 0, 5),
			date('Y-m-d H:i:s', current_time('timestamp')),
			);

		// 予約通知
		if($_POST['reserveaction']=='reserve'){
			$datetime = olbTimetable::getTimetableKey($_POST['reservedate'], $_POST['reservetime']);
			$cancel_url = get_permalink(get_page_by_path($olb->reserve_form_page)->ID);
			$cancel_query = (strstr($cancel_url, '?')) ? '&' : '?';
			$cancel_query .= sprintf('t=%s&room_id=%d', $datetime, $_POST['room_id']);
			$search = array_merge($search, array(
				'%CANCEL_DEADLINE%',
				'%CANCEL_URL%',
				));
			$replace = array_merge($replace, array(
				sprintf(__('%d minutes before start time', OLBsystem::TEXTDOMAIN), $olb->cancel_deadline),
				$cancel_url.$cancel_query,
				));

			list($mail_body, $to_user_subject, $to_teacher_subject) = str_replace(
					$search,
					$replace,
					array(
						$options['reservation_message'],
						$options['reservation_subject'],
						$options['reservation_subject_to_teacher']
						)
				);
			if($olb->free > 0){
				$free = $user->canFreeReservation();
				if($record['free'] || $free) {
					$mail_body .= "\n";
					if($record['free']){
						$mail_body .= __('Free reservation applied.', OLBsystem::TEXTDOMAIN)."\n";
					}
					$mail_body .= sprintf(__('Your free reservation: %d times left.', OLBsystem::TEXTDOMAIN)."\n", $user->canFreeReservation());
				}
			}
			$to_user_signature = $options['signature'];
		}
		// キャンセル通知
		else {
			list($mail_body, $to_user_subject, $to_teacher_subject) = str_replace(
					$search,
					$replace,
					array(
						$options['cancel_message'],
						$options['cancel_subject'],
						$options['cancel_subject_to_teacher']
						)
				);
			$to_user_signature = $options['signature'];
		}
		$to_user_body = $mail_body.$to_user_signature;
		$to_user_headers = sprintf("From: %s\r\n", $options['from_email']);
		$to_user_email = sprintf('%s <%s>', $user->data['name'], $user->data['email']);

		$ret = olbTimetable::sendReserveMail($to_user_email , $to_user_subject, $to_user_body, $to_user_headers);
		// エラーあり
		if(!$ret) {
			$error = 'USER_SEND_ERROR';
			$url = get_permalink(get_page_by_path($olb->reserve_form_page)->ID);
			$query_string = (strstr($url, '?')) ? '&' : '?';
			$query_string .= sprintf('error=%s', $error);
			header('Location:'.$url.$query_string);
			exit;
		}

		// 講師宛
		$to_teacher_email = $room['email'];
		$to_teacher_body = $mail_body;
		$to_teacher_headers = sprintf("From: %s\r\n", $to_user_email);

		$ret = olbTimetable::sendReserveMail($to_teacher_email, $to_teacher_subject, $to_teacher_body, $to_teacher_headers);

		// ex. '?t=2013-07-08_1200&room_id=2'
		$datetime = olbTimetable::getTimetableKey($record['date'], $record['time']);
		$url = get_permalink(get_page_by_path($olb->reserve_form_page)->ID);
		$query_string = (strstr($url, '?')) ? '&' : '?';
		$query_string .= sprintf('t=%s&room_id=%d&success=%s', $datetime, $record['room_id'], $_POST['reserveaction']);
		header('Location:'.$url.$query_string);
		exit;
	}

	/**
	 *	講師都合のキャンセル: Cancellation by teacher
	 */
	public static function cancellation() {
		global $wpdb, $olb;
		
		$error = '';
		if (empty($_POST['onetimetoken']) || !wp_verify_nonce($_POST['onetimetoken'], OLBsystem::TEXTDOMAIN)) {
			$error = 'NONCE_ERROR';
		}
		else if(empty($_POST['room_id']) || empty($_POST['user_id']) || empty($_POST['reservedate']) || empty($_POST['reservetime'])) {
			$error = 'PARAMETER_INSUFFICIENT';
		}
		else if($_POST['reserveaction']!='cancel') {
			$error = 'INVALID_PARAMETER';
		}
		else {
			$result = olbTimetable::canCancellation($_POST['room_id'], $_POST['reservedate'], $_POST['reservetime']);
			/**
			 *	$result = array( 
			 *		'code'=> 'RESERVE_OK',
			 *		'record' => array(
			 *			'id' => 56,
			 *			'room_id' => 5,
			 *			'user_id' => 6,
			 *			'date' => '2013-07-11',
			 *			'time' => '10:00:00'
			 *		),
			 *		'user' => olbAuth Object(
			 *		),
			 *		'room' => array(
			 *		),
			 *	)
			 */
			extract($result);	// $code, $record, $user, $room

			// CANCEL
			if($_POST['reserveaction']=='cancel' && $code=='ALREADY_RESERVED'){
				$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
				$query = "DELETE FROM ".$prefix."history WHERE `id`='%d'";
				$ret = $wpdb->query($wpdb->prepare($query, array($record['id'])), ARRAY_A);
				if(!$ret){
					$error = 'CANCEL_FAILED';
				}
				$query = "DELETE FROM ".$prefix."timetable WHERE `room_id`='%d' AND `date`='%s' AND `time`='%s'";
				$ret = $wpdb->query($wpdb->prepare($query, array($record['room_id'], $record['date'], $record['time'])), ARRAY_A);
			}
			// エラーあり
			else {
				$error = $code;
			}
		}
		// エラーあり
		if($error) {
			$url = get_permalink(get_page_by_path($olb->cancel_form_page)->ID);
			$query_string = (strstr($url, '?')) ? '&' : '?';
			$query_string .= sprintf('error=%s', $error);
			header('Location:'.$url.$query_string);
			exit;
		}

		$options = $olb->getPluginOptions('mail');
		// キャンセル通知
		$search = array(
			'%USER_ID%',
			'%USER_NAME%',
			'%USER_EMAIL%',
			'%USER_SKYPE%',
			'%ROOM_NAME%',
			'%RESERVE_ID%',
			'%RESERVE_DATE%',
			'%RESERVE_TIME%',
			'%SEND_TIME%',
			'%MESSAGE%'
			);
		$replace = array(
			$user->data['id'],
			$user->data['name'],
			$user->data['email'],
			$user->data['skype'],
			$room['name'],
			$record['id'],
			$record['date'],
			substr($record['time'], 0, 5),
			date('Y-m-d H:i:s', current_time('timestamp')),
			$_POST['message'],
			);

		list($mail_body, $to_user_subject, $to_teacher_subject) = str_replace(
				$search,
				$replace,
				array(
					$options['cancel_message_by_teacher'],
					$options['cancel_subject_by_teacher'],
					$options['cancel_subject_by_teacher_to_teacher']
					)
			);
		$to_user_signature = $options['signature'];
		$to_user_body = $mail_body.$to_user_signature;
		$to_user_headers = sprintf("From: %s\r\n", $options['from_email']);
		$to_user_email = sprintf('%s <%s>', $user->data['name'], $user->data['email']);

		$ret = olbTimetable::sendReserveMail($to_user_email , $to_user_subject, $to_user_body, $to_user_headers);
		// エラーあり
		if(!$ret) {
			$error = 'USER_SEND_ERROR';
			$url = get_permalink(get_page_by_path($olb->reserve_form_page)->ID);
			$query_string = (strstr($url, '?')) ? '&' : '?';
			$query_string .= sprintf('error=%s', $error);
			header('Location:'.$url.$query_string);
			exit;
		}

		// 講師宛
		$to_teacher_email = $room['email'];
		$to_teacher_body = $mail_body;
		$to_teacher_headers = sprintf("From: %s\r\n", $to_user_email);

		$ret = olbTimetable::sendReserveMail($to_teacher_email, $to_teacher_subject, $to_teacher_body, $to_teacher_headers);
		$url = get_permalink(get_page_by_path($olb->edit_schedule_page)->ID);
		if(empty($_POST['returnurl'])) {
			header('Location:'.$url );
		}
		else {
			header('Location:'.$_POST['returnurl'] );
		}
		exit;
	}

	/**
	 *	出欠レポート: Report of absent by teacher
	 */
	public static function report() {
		global $wpdb, $olb;
		
		$error = '';
		if (empty($_POST['onetimetoken']) || !wp_verify_nonce($_POST['onetimetoken'], OLBsystem::TEXTDOMAIN)) {
			$error = 'NONCE_ERROR';
		}
		else if(empty($_POST['room_id']) || empty($_POST['user_id']) || empty($_POST['reservedate']) || empty($_POST['reservetime'])) {
			$error = 'PARAMETER_INSUFFICIENT';
		}
		else if($_POST['reserveaction']!='report') {
			$error = 'INVALID_PARAMETER';
		}
		else {
			$result = olbTimetable::canReport($_POST['room_id'], $_POST['reservedate'], $_POST['reservetime']);
			/**
			 *	$result = array( 
			 *		'code'=> 'RESERVE_OK',
			 *		'record' => array(
			 *			'id' => 56,
			 *			'room_id' => 5,
			 *			'user_id' => 6,
			 *			'date' => '2013-07-11',
			 *			'time' => '10:00:00'
			 *		),
			 *		'user' => olbAuth Object(
			 *		),
			 *		'room' => array(
			 *		),
			 *	)
			 */
			extract($result);	// $code, $record, $user, $room

			$where = array('id'=>$record['id']);
			$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
			$query = "UPDATE ".$prefix."history SET absent=%d WHERE `id`=%d";
			// REPORT
			if($_POST['reserveaction']=='report' && $code=='ALREADY_RESERVED'){
				$absent = (isset($_POST['absent'])) ? 1 : 0;
				$ret = $wpdb->query($wpdb->prepare($query, array($absent, $record['id'])), ARRAY_A);
				if(!$ret){
					$error = 'REPORT_FAILED';
				}
			}
			// エラーあり
			else {
				$error = $code;
			}
		}

		$url = get_permalink(get_page_by_path($olb->report_form_page)->ID);
		// エラーあり
		if($error) {
			$query_string = (strstr($url, '?')) ? '&' : '?';
			$query_string .= sprintf('error=%s', $error);
			header('Location:'.$url.$query_string);
			exit;
		}
		
		if(empty($_POST['returnurl'])) {
			header('Location:'.$url );
		}
		else {
			header('Location:'.$_POST['returnurl'] );
		}
		exit;
	}

	/**
	 *	講師用スケジューラ: Scheduler for teacher
	 */
	public static function scheduler() {
		global $wpdb, $olb;
		
		$error = '';

		if (empty($_POST['onetimetoken']) || !wp_verify_nonce($_POST['onetimetoken'], OLBsystem::TEXTDOMAIN)) {
			$url = get_permalink(get_page_by_path($olb->edit_schedule_page)->ID);
			$query_string = (strstr($url, '?')) ? '&' : '?';
			if(!empty($olb->qs)){
				$query_string .= implode('&', $olb->qs).'&';
			}
			$query_string .= 'error=NONCE_ERROR';
			header('Location:'.$url.$query_string);
			exit;
		}
		$user = new olbAuth();
		$roominfo = olbRoom::get($user->data['id']);

		$x = array();

		foreach($_POST['new'] as $key=>$value){
			if($_POST['org'][$key]!=$value){
				$x[$key]=$value;
			}
		}

		$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
		foreach($x as $key=>$value){
			list($date, $time) = olbTimetable::parseTimetableKey($key);
			if($value=='close'){
				$query = "DELETE FROM ".$prefix."timetable "
					  ."WHERE `date`='%s' AND `time`='%s' AND `room_id`='%s' ";
				$result = $wpdb->query($wpdb->prepare($query, array($date, $time, $_POST['room_id'])), ARRAY_A);
			}
			// $value == 'open'
			else {
				$table = $prefix."timetable";
				$result = $wpdb->insert(
								$table,
								array(
									'date'=>$date,
									'time'=>$time,
									'room_id' => $_POST['room_id']
								)
							);
			}
		}
		$qs = array();
		$query_string = '';
		if($user->isAdmin() && !empty($_POST['room_id'])){
			$qs[] = 'room_id='.$_POST['room_id'];
		}
		if(!empty($_POST['date'])){
			$qs[] = 'date='.$_POST['date'];
		}
		$url = get_permalink(get_page_by_path($olb->edit_schedule_page)->ID);
		$query_string = (strstr($url, '?')) ? '&' : '?';
		$query_string .= implode('&', $qs);
		header('Location:'.$url.$query_string);
		exit;
	}
}
?>