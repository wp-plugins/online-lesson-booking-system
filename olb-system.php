<?php
/*
Plugin Name: Online Lesson Booking system
Plugin URI: http://sukimalab.com/olb/
Description: Online Lesson Booking system (OLB) is reservation-form and scheduler for an one-to-one online lesson.
Version: 0.3.1
Author: tnomi
Author URI: http://sukimalab.com
*/

	require_once dirname(__FILE__).'/class/my-settings.php';
	require_once dirname(__FILE__).'/class/my-shortcodes.php';
	require_once dirname(__FILE__).'/class/my-hookactions.php';
	require_once dirname(__FILE__).'/class/my-adminpage.php';
	require_once dirname(__FILE__).'/class/my-widget.php';
	require_once dirname(__FILE__).'/class/my-functions.php';
	require_once dirname(__FILE__).'/class/my-paging.php';
	require_once dirname(__FILE__).'/class/my-auth.php';
	require_once dirname(__FILE__).'/class/my-room.php';
	require_once dirname(__FILE__).'/class/my-history.php';
	require_once dirname(__FILE__).'/class/my-timetable.php';
	require_once dirname(__FILE__).'/class/my-formaction.php';

	if (!isset($_SESSION)) {
		session_start();
	}

	$olb = new olbTimetable();

?>
