<?php
/** 
 *	管理画面: WP Hook Actions
 */

class olbHookAction {
	/**
	 *	MOファイルをロード: load textdomain
	 */
	public static function moLoad(){
		load_textdomain(OLBsystem::TEXTDOMAIN, dirname(plugin_dir_path(__FILE__)).'/lang/'.OLBsystem::TEXTDOMAIN.'-'.get_locale().'.mo');
	}
	
	/** 
	 *	管理バーの項目を非表示: Hide menu in admin-bar (for member)
	 */
	public static function hideAdminBarMenu($wp_admin_bar){
		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_menu('my-account');
	}
	public static function hideAdminHeadMenu(){
		echo <<<EOD
<style type="text/css">
#contextual-help-link-wrap { display: none; }
#footer-upgrade { display: none; }
</style>
EOD;
	}
	public static function addAdminBarMenu(){
		global $wp_admin_bar, $olb;
		$wp_admin_bar->add_menu(
			array(
				'id' => 'olb_mypage',
				'title' => __('My page', OLBsystem::TEXTDOMAIN),
				'href' => get_permalink(get_page_by_path($olb->member_page)->ID)
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'olb_logout',
				'title' => __('Logout', OLBsystem::TEXTDOMAIN),
				'href' => wp_logout_url()
			)
		);
	}
	public static function hideAdminFooter(){
		echo '';
	}
	public static function hideDashboard(){
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);		// 現在の状況
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);	// 最近のコメント
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);	// 被リンク
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);			// プラグイン
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);		// クイック投稿
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);		// 最近の下書き
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);			// WordPressブログ
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);			// WordPressフォーラム
	}
	public static function hideSideMenu(){
		global $menu;
		unset($menu[2]);	// ダッシュボード
		unset($menu[4]);	// 区切り線
		unset($menu[5]);	// 投稿
		unset($menu[10]);	// メディア
		unset($menu[15]);	// リンク
		unset($menu[20]);	// ページ
		unset($menu[25]);	// コメント
		unset($menu[59]);	// 区切り線
		unset($menu[60]);	// テーマ
		unset($menu[65]);	// プラグイン
	//	unset($menu[70]);	// プロフィール
		unset($menu[75]);	// ツール
		unset($menu[80]);	// 設定
		unset($menu[90]);	// 区切り線
	}

	/** 
	 *	プロフィール項目の非表示: Hide profile items
	 */
	public static function hideProfileItem(){
		$version = get_bloginfo('version');

		if(substr($version, 0, 3) < '3.6') {
			$settings = array(
				'table:nth-of-type(1){display:none;}',						// 個人設定: private setting method
				'table:nth-of-type(3) tr:nth-child(2){display:none;}',		// 	ウェブサイト: website
				'table:nth-of-type(3) tr:nth-child(3){display:none;}',		// 	AIM
				'table:nth-of-type(3) tr:nth-child(4){display:none;}',		// 	Yahoo ID
				'table:nth-of-type(3) tr:nth-child(5){display:none;}',		// 	Jabber / Google Talk
			//	'table:nth-of-type(3) tr:nth-child(6){display:none;}',		// 	ほか(1) 住所: address
			//	'table:nth-of-type(3) tr:nth-child(7){display:none;}',		// 	ほか(2) 電話番号: phone
			//	'table:nth-of-type(3) tr:nth-child(8){display:none;}',		// 	ほか(3) スカイプID: skype ID

				'table:nth-of-type(4) tr:nth-child(1){display:none;}',		// 	プロフィール: profile
				);
		}
		else {
			$settings = array(
				'table:nth-of-type(1){display:none;}',						// 個人設定: private setting method
				'table:nth-of-type(3) tr:nth-child(2){display:none;}',		// 	ウェブサイト: website
				'table:nth-of-type(4) tr:nth-child(1){display:none;}',		// 	プロフィール: profile
				);
		}

		$css = '';
		foreach($settings as $c){
			$css .= $c."\n";
		}
		echo <<<EOD
<style type="text/css">
{$css}
</style>
EOD;
	}

	/** 
	 *	プロフィール項目の追加: Add profile items for contact
	 */
	public static function addProfileContact($meta) {
		//項目の追加
		$meta['user_address'] = __('Address', OLBsystem::TEXTDOMAIN);
		$meta['user_phone'] = __('Phone', OLBsystem::TEXTDOMAIN);
		$meta['user_skype'] = __('Skype ID', OLBsystem::TEXTDOMAIN);
		return $meta;
	}

	/** 
	 *	プロフィール追加項目の表示: Show profile items (term of validity)
	 */
	public static function showAddedProfile(){
		$user = new olbAuth();
		// 購読者
		if(in_array('subscriber', $user->data['roles'])){
			$description = __('The term of validity is updated after the check of payment.', OLBsystem::TEXTDOMAIN);
			$format = <<<EOD
<h3>%s</h3>
<table class="form-table">
<tr>
<th><label for="olbterm">%s</label></th>
<td><input type="text" name="olbterm" id="olbterm" value="%s" readonly /> <span class="description">%s</span><br></td>
</tr>
</table>
EOD;
			printf($format, __('Term of validity', OLBsystem::TEXTDOMAIN), __('Term of validity', OLBsystem::TEXTDOMAIN), $user->data['olbterm'], $description);
		}
	}

	/** 
	 *	プロフィール項目の追加(管理者用): Add profile items (for admin)
	 */
	public static function addProfileMeta(){

		if (empty($_POST['user_id'])) {
			$user_id = $_GET['user_id'];
		}
		else {
			$user_id = $_POST['user_id'];
		}
		$user = new olbAuth($user_id);
		// 投稿者のみ
		if(in_array('author', $user->data['roles'])){
			$checked = '';
			if($user->isRoomManager()){
				$checked = 'checked="checked"';
			}
			$format = <<<EOD
<h3>%s</h3>
<table class="form-table">
<tr>
<th><label for="olbgroup">%s</label></th>
<td><input type="checkbox" name="olbgroup" id="olbgroup" value="teacher" %s/></td>
</tr>
</table>
EOD;
			printf($format, __('property "Teacher"', OLBsystem::TEXTDOMAIN), __('Teacher', OLBsystem::TEXTDOMAIN), $checked);
		}

		// 購読者のみ
		if(in_array('subscriber', $user->data['roles'])){
			$format = <<<EOD
<h3>%s</h3>
<table class="form-table">
<tr>
<th><label for="olbterm">%s</label></th>
<td><input type="text" name="olbterm" id="olbterm" value="%s" /></td>
</tr>
</table>
EOD;
			printf($format, __('Term of validity', OLBsystem::TEXTDOMAIN), __('Term of validity', OLBsystem::TEXTDOMAIN), $user->data['olbterm'], $description);
		}
	}


	/** 
	 *	ツールバー非表示: Hide admin tool bar
	 */
	public static function inUserRegister($user_id){
		// ツールバー非表示: Hide admin bar
		update_user_meta($user_id, "show_admin_bar_front", 'false');
	}

	/** 
	 *	プロフィール追加項目の保存: Save added items of profile
	 */
	public static function inUpdateProfile(){
		$options = get_option('tt_options');

		if (empty($_POST['user_id'])) {
			$user_id = $_GET['user_id'];
		}
		else {
			$user_id = $_POST['user_id'];
		}
		// 講師
		$oldgroup = get_user_meta($user_id, 'olbgroup', true);
		if (!empty($_POST['olbgroup'])){
			update_user_meta($user_id, 'olbgroup', $_POST['olbgroup']);
		}
		else {
			delete_user_meta($user_id, 'olbgroup', '');
		}
		$newgroup = get_user_meta($user_id, 'olbgroup', true);
		if($oldgroup != $newgroup){
			// Any action (if needed)
		}

		// 有効期限
		$oldlimit = get_user_meta($user_id, 'olbterm', true);
		if (!empty($_POST['olbterm'])){
			update_user_meta($user_id, 'olbterm', $_POST['olbterm']);
		}
		else {
			delete_user_meta($user_id, 'olbterm', '');
		}
		$newlimit = get_user_meta($user_id, 'olbterm', true);
		if($oldgroup != $newgroup){
			// Any action (if needed)
		}
	}

	/** 
	 *	プロフィール追加項目の削除: Delete added items of profile (when delete user)
	 */
	public static function inDeleteUser($user_id){
		delete_user_meta($user_id, 'olbgroup');
		delete_user_meta($user_id, 'olbterm');
	}

	/** 
	 *	ログイン後のリダイレクト: Redirect after login
	 */
	public static function redirectAfterLogin($user_login, $current_user){
		global $olb;

		$user = new olbAuth($current_user->ID);
		if($user->isMember()) {
			header('Location: '.get_permalink(get_page_by_path($olb->member_page)->ID));
			exit;
		}
		else if($user->isRoomManager()){
			header('Location: '.get_permalink(get_page_by_path($olb->edit_schedule_page)->ID));
			exit;
		}
	}

	/** 
	 *	ログアウト後のリダイレクト: Redirect after logout
	 */
	public static function redirectAfterLogout(){

		header('Location: '.get_option('siteurl'));
		exit;
	}

	/** 
	 *	各ページへのアクセス制限: Control for access to  special page
	 */
	public static function inSpecialPageAccess($query_vars) {
		global $olb;

		if(!empty($query_vars->query_vars['name'])) {
			list($current_post) = query_posts('name='.$query_vars->query_vars['name']);
		}
		else if(!empty($query_vars->query_vars['pagename'])) {
			$current_post = get_page_by_path($query_vars->query_vars['pagename']);
		}
		else if(!empty($query_vars->query_vars['p'])) {
			$current_post = get_post($query_vars->query_vars['p']);
		}
		else if(!empty($query_vars->query_vars['page_id'])) {
			$current_post = get_post($query_vars->query_vars['page_id']);
		}
		else {
			$current_post = null;
		}
		$user = new olbAuth();

		// 先祖postのID
		$ancestor_id = array_pop(get_post_ancestors($current_post->ID));
		$ancestor = null;
		if($ancestor_id) {
			$ancestor = get_post($ancestor_id);
		}

		// 会員ページ(ログイン中のみ): Member-page(only login member)
		if($current_post->post_name==$olb->member_page || $ancestor->post_name==$olb->member_page){
			if(!$user->isLoggedIn()){
				if(empty($olb->login_page)) {
					$url = wp_login_url();
				}
				else {
					$url = get_permalink(get_page_by_path($olb->login_page)->ID);
				}
				header('Location: '.$url);
				exit;
			}
			if(!$user->isMember()){
				header('Location: '.$olb->home);
				exit;
			}
		}

		// 予約ページ(ログイン中のみ): Reservation-page(only login member)
		if($current_post->post_name==$olb->reserve_form_page || $ancestor->post_name==$olb->reserve_form_page){
			if(!$user->isLoggedIn()){
				if(empty($olb->login_page)) {
					$url = wp_login_url();
				}
				else {
					$url = get_permalink(get_page_by_path($olb->login_page)->ID);
				}
				header('Location: '.$url);
				exit;
			}
			if(!$user->isMember()){
				header('Location: '.$olb->home);
				exit;
			}
		}

		// スケジュール設定ページ(管理者か講師のみ): Scheduling-page(only admin and room manager)
		if($current_post->post_name==$olb->edit_schedule_page || $ancestor->post_name==$olb->edit_schedule_page){
			if(!$user->isLoggedIn()){
				if(empty($olb->login_page)) {
					$url = wp_login_url();
				}
				else {
					$url = get_permalink(get_page_by_path($olb->login_page)->ID);
				}
				header('Location: '.$url);
				exit;
			}
			if(!$user->isAdmin() && !$user->isRoomManager()){
				header('Location: '.$olb->home);
				exit;
			}
		}

		// 講師によるキャンセルページ(ログイン中のみ): Cancellation-page(only admin and room manager)
		if($current_post->post_name==$olb->cancel_form_page || $ancestor->post_name==$olb->cancel_form_page){
			if(!$user->isLoggedIn()){
				if(empty($olb->login_page)) {
					$url = wp_login_url();
				}
				else {
					$url = get_permalink(get_page_by_path($olb->login_page)->ID);
				}
				header('Location: '.$url);
				exit;
			}
			if(!$user->isAdmin() && !$user->isRoomManager()){
				header('Location: '.$olb->home);
				exit;
			}
		}

	}

	/**
	 *	フォームアクション: form action
	 */
	public static function formAction($query_vars){
		global $olb;
		
		if($_SERVER['REQUEST_METHOD']=='POST'){
			if(!empty($query_vars->query_vars['name'])) {
				list($current_post) = query_posts('name='.$query_vars->query_vars['name']);
			}
			else if(!empty($query_vars->query_vars['pagename'])) {
				$current_post = get_page_by_path($query_vars->query_vars['pagename']);
			}
			else if(!empty($query_vars->query_vars['p'])) {
				$current_post = get_post($query_vars->query_vars['p']);
			}
			else if(!empty($query_vars->query_vars['page_id'])) {
				$current_post = get_post($query_vars->query_vars['page_id']);
			}
			else {
				$current_post = null;
			}
			$olbform = new olbFormAction();

			switch($current_post->post_name){
			case $olb->reserve_form_page:
				$olbform->reservation();
				break;

			case $olb->cancel_form_page:
				$olbform->cancellation();
				break;
			
			case $olb->report_form_page:
				$olbform->report();
				break;

			case $olb->edit_schedule_page:
				$olbform->scheduler();
				break;
			}
		}
	}

	/** 
	 *	フロント用style/script読込: Load css and js for front page
	 */
	public static function loadFrontHeader() {
		global $olb;

		echo <<<EOD
<link rel="stylesheet" href="{$olb->mypluginurl}front.css" type="text/css" />
<script type="text/javascript" src="{$olb->mypluginurl}front.js"></script>
EOD;
	}

	/** 
	 *	講師URL保存: Save url of teacher's page
	 */
	public static function saveRoomURL($post_id){
		// 週間スケジュールから講師IDを取得しページURLを保存: get room-id from tag of weekly schecule, save url
		$content = str_replace("\"", '', stripslashes($_POST['content']));
		$match = preg_match('/\[olb_weekly_schedule\sid=[0-9]+\]/', $content, $matches);
		if($match){
			$id = preg_replace('/[^0-9]/', '', $matches[0]);
			$room = new olbAuth($id);
			if(!empty($room->data['id'])){
				if($room->isRoomManager()) {
					wp_update_user(array('ID'=>$room->data['id'], 'user_url'=>get_permalink($post_id)));
				}
			}
		}
	}

	/** 
	 *	講師URL削除: Delete url of teacher's page
	 */
	public static function deleteRoomURL($post_id){
		// 週間スケジュールから講師IDを取得しページURLを削除: get room-id from tag of weekly schecule, delete url
		$post = get_post($post_id);
		$content = str_replace("\"", '', stripslashes($post->post_content));
		if(!empty($post->ID)){
			$match = preg_match('/\[olb_weekly_schedule\sid=[0-9]+\]/', $content, $matches);
			if($match){
				$id = preg_replace('/[^0-9]/', '', $matches[0]);
				$room = new olbAuth($id);
				if(!empty($room->data['id'])){
					if($room->isRoomManager()) {
						wp_update_user(array('ID'=>$room->data['id'], 'user_url'=>''));
					}
				}
			}
		}
	}

	/** 
	 *	ユーザー一覧: Customize of user list
	 */
	public static function addUsersColumns($column_headers){
		$column_headers['olbgroup'] = __('Teacher', OLBsystem::TEXTDOMAIN);
		$column_headers['ID'] = __('ID', OLBsystem::TEXTDOMAIN);
		return $column_headers;
	}

	public static function customUsersColumn($custom_column, $column_name, $user_id) {
	
		$user_info = get_userdata($user_id);
	
		${$column_name} = $user_info->$column_name;
		$custom_column = "\t".${$column_name}."\n";
	
		return $custom_column;
	}

	public static function sortableUsersColumns($columns){
		$columns['olbgroup'] = __('Teacher', OLBsystem::TEXTDOMAIN);
		return $columns;
	}

	function orderbyUsersColumn($vars) {
		if(isset($vars['orderby']) && $vars['orderby'] == 'olbgroup') {
			$vars = array_merge($vars, array(
				'meta_key' => 'olbgruoup',
				'orderby' => 'meta_value',
				'order'     => 'asc'
			) );
		}
		return $vars;
	}

	/** 
	 *	プラグイン有効化
	 */
	public static function activation() {
		global $wpdb;

		$version = get_option('olbversion');

		// CREATE TABLE
		$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
		if($wpdb->get_var("SHOW TABLES LIKE '{$prefix}timetable'") != $prefix.'timetable' &&
			$wpdb->get_var("SHOW TABLES LIKE '{$prefix}history'") != $prefix.'history') {
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS {$prefix}timetable (
id bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Reserve ID',
date date NOT NULL COMMENT 'Reserve date',
time time NOT NULL COMMENT 'Reserve time',
room_id int NOT NULL COMMENT 'Room ID',
user_id int NOT NULL COMMENT 'User ID',
free int NOT NULL  COMMENT 'Free',
absent int NOT NULL  COMMENT 'Absent',
PRIMARY KEY (id)
);
EOD;
			dbDelta($sql);

			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS {$prefix}history (
id bigint(20) NOT NULL COMMENT 'Reserve ID',
date date NOT NULL COMMENT 'Reserve date',
time time NOT NULL COMMENT 'Reserve time',
room_id int NOT NULL COMMENT 'Room ID',
user_id int NOT NULL COMMENT 'User ID',
free int NOT NULL  COMMENT 'Free',
absent int NOT NULL  COMMENT 'Absent',
PRIMARY KEY (id)
);
EOD;
			dbDelta($sql);

			$version['db'] = OLBsystem::DB_VERSION;
		}
		$version['plugin'] = OLBsystem::PLUGIN_VERSION;
		update_option('olbversion', $version);

		$default_options = OLBsystem::setDefaultOptions();
		$specialpages = $default_options['specialpages'];
		/*
				'daily_schedule_page' => 'schedule',
				'reserve_form_page'   => 'reservation',
				'cancel_form_page'    => 'cancel',
				'report_form_page'    => 'report',
				'edit_schedule_page'  => 'editschedule',
				'member_page'         => 'mypage',
				'login_page'          => '',
		*/
		$memer_page_content = __('
<p>
Hello [olb_member_data key="name"].
</p>

<h4>Term of validity:</h4>
<p>
[olb_member_data key="olbterm"]
[olb_if_expire]Expired[/olb_if_expire]
</p>

<h4>Free ticket:</h4>
<p>[olb_member_data key="free"] left.</p>

<h4>History:</h4>
[olb_members_history perpage="5" pagenavi="0"]
<p>(No history)</p>
[/olb_members_history]

<h4>Reserved:</h4>
[olb_members_schedule perpage="5" pagenavi="0"]
<p>(No schedule)</p>
[/olb_members_schedule]
', OLBsystem::TEXTDOMAIN);

		// INSERT PAGE
		$pages = array(
			'edit_schedule' => array(
				'post_title'     => __('Scheduler for teacher', OLBsystem::TEXTDOMAIN),
				'post_content'   => '[olb_edit_schedule]',
				'post_name'      => $specialpages['edit_schedule_page'],
			),
			'cancel_form' => array(
				'post_title'     => __('Cancel form for teacher', OLBsystem::TEXTDOMAIN),
				'post_content'   => '[olb_cancel_form]',
				'post_name'      => $specialpages['cancel_form_page'],
			),
			'report_form' => array(
				'post_title'     => __('Report form for teacher', OLBsystem::TEXTDOMAIN),
				'post_content'   => '[olb_report_form]',
				'post_name'      => $specialpages['report_form_page'],
			),
			'reserve_form' => array(
				'post_title'     => __('Reservation and cancellation form', OLBsystem::TEXTDOMAIN),
				'post_content'   => '[olb_reserve_form]',
				'post_name'      => $specialpages['reserve_form_page'],
			),
			'member_page' => array(
				'post_title'     => __('Members my-page', OLBsystem::TEXTDOMAIN),
				'post_content'   => $memer_page_content,
				'post_name'      => $specialpages['member_page'],
			),
			'daily_schedule' => array(
				'post_title'     => __('Daily schedule', OLBsystem::TEXTDOMAIN),
				'post_content'   => '[olb_daily_schedule]',
				'post_name'      => $specialpages['daily_schedule_page'],
			),
		);
		$child_pages = array(
			'edit_schedule' => array(
				'teachers_history' => array(
					'post_title'     => __('Teachers history', OLBsystem::TEXTDOMAIN),
					'post_content'   => '[olb_teachers_history]<p>'.__('No history', OLBsystem::TEXTDOMAIN).'</p>[/olb_teachers_history]',
					'post_name'     => __('Teachers history', OLBsystem::TEXTDOMAIN),
				),
				'teachers_schedule' => array(
					'post_title'     => __('Teachers schedule', OLBsystem::TEXTDOMAIN),
					'post_content'   => '[olb_teachers_schedule]<p>'.__('No schedule', OLBsystem::TEXTDOMAIN).'</p>[/olb_teachers_schedule]',
					'post_name'     => __('Teachers schedule', OLBsystem::TEXTDOMAIN),
				),
			),
			'member_page' => array(
				'members_history' => array(
					'post_title'     => __('Members history', OLBsystem::TEXTDOMAIN),
					'post_content'   => '[olb_members_history]<p>'.__('No history', OLBsystem::TEXTDOMAIN).'</p>[/olb_members_history]',
					'post_name'     => __('Members history', OLBsystem::TEXTDOMAIN),
				),
				'members_schedule' => array(
					'post_title'     => __('Members schedule', OLBsystem::TEXTDOMAIN),
					'post_content'   => '[olb_members_schedule]<p>'.__('No schedule', OLBsystem::TEXTDOMAIN).'</p>[/olb_members_schedule]',
					'post_name'     => __('Members schedule', OLBsystem::TEXTDOMAIN),
				),
			)
		);
		foreach($pages as $name=>$page){
			if(!get_page_by_path($page['post_name'])->ID){
				$args = array_merge(
					$page, 
					array(
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'comment_status' => 'closed',
						'ping_status'    => 'closed'
					)
				);
				$parent_id= wp_insert_post($args);
			}
			if(isset($child_pages[$name])){
				foreach($child_pages[$name] as $child){
					if(empty(get_page_by_path($page['post_name'].'/'.$child['post_name'])->ID)){
						$args = array_merge(
							$child, 
							array(
								'post_parent'    => $parent_id,
								'post_status'    => 'publish',
								'post_type'      => 'page',
								'comment_status' => 'closed',
								'ping_status'    => 'closed'
							)
						);
						$child_id = wp_insert_post($args);
					}
				}
			}
		}
	}

	/** 
	 *	プラグイン無効化
	 */
	public static function deactivation() {
		wp_clear_scheduled_hook('olb_cron');
	}

	/** 
	 *	プラグイン削除
	 */
	public static function uninstall() {
		delete_option(OLBsystem::TEXTDOMAIN);
		delete_option('olbversion');
	}

	/** 
	 *	CRON処理のインターバル追加
	 */
	public static function cron_add_interval($schedules){
		$schedules['10sec'] = array(
			'interval' => 10,
			'display' => '10sec'
		);
		$schedules['halfhour'] = array(
			'interval' => 60*30,
			'display' => 'harfhour'
		);
		return $schedules;
	}

	/** 
	 *	CRON処理
	 */
	public static function olb_cron_do(){
		global $wpdb, $olb;

		$preserve_day = current_time('timestamp') - ($olb->preserve_past*60*60*24);
		$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
		$query = "DELETE FROM ".$prefix."timetable WHERE `date`<%s AND `user_id`=0";
		$ret = $wpdb->query($wpdb->prepare($query, array(date('Y-m-d', $preserve_day))), ARRAY_A);
	}

	/** 
	 *	CRON更新
	 */
	public static function olb_cron_update() {
		global $olb;

		if ( !wp_next_scheduled( 'olb_cron' ) ) {
			wp_schedule_event(time(), $olb->cron_interval, 'olb_cron');
		}
	}


}
?>
