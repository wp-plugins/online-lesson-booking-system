<?php
/** 
 *	講師情報: Room info (as teacher)
 */

class olbRoom extends olbPaging {

	/** 
	 *	CONSTRUCT
	 */
	public function __construct($limit) {
		$this->limit = $limit;			// スケジュールページの表示講師数(1ページ当たり)
		$this->recordmax = self::recordMax();				// 有効な講座数
		$this->pagemax = ceil($this->recordmax/$this->limit);	// ページ数

		self::getCurrentPage();
	}

	/** 
	 *	講師ID指定で講師情報を取得: Get room-info by room_id
	 */
	public static function get($room_id) {
		global $wpdb;

		$args = array(
				'include' => array($room_id),
				'meta_key' => 'olbgroup',
				'meta_value' => 'teacher',
				'meta_compare' => '=',
				'number' => 1,
			);

		list($u) = get_users($args);
		$user = array(
				'id'       => $u->data->ID,
				'nicename' => $u->data->user_nicename,
				'name'     => $u->data->display_name,
				'status'   => $u->data->user_status,
				'email'    => $u->data->user_email,
				'url'      => $u->data->user_url,
				'olbgroup'  => get_user_meta($u->data->ID, 'olbgroup', true),
			);
		return $user;
	}

	/** 
	 *	講師一覧情報を取得: Get list of room
	 */
	public function getList(){
		global $wpdb;

		$args = array(
				'meta_key' => 'olbgroup',
				'meta_value' => 'teacher',
				'meta_compare' => '=',
			);
		if($this->limit!=0) {
			if($this->offset!=0) {
				$args['offset'] = $this->offset;
				$args['number'] = $this->limit;
			}
			else {
				$args['number'] = $this->limit;
			}
		}
		$users = array();
		$org = get_users($args);
		foreach($org as $u){
			$users[] = array(
					'id'       => $u->data->ID,
					'nicename' => $u->data->user_nicename,
					'name'     => $u->data->display_name,
					'status'   => $u->data->user_status,
					'email'    => $u->data->user_email,
					'url'      => $u->data->user_url,
					'olbgroup'  => get_user_meta($u->data->ID, 'olbgroup', true),
				);
		}
		return $users;
	}

	/** 
	 *	全講師情報を取得: Get all rooms info
	 */
	public static function getAll(){
		$args = array(
				'meta_key' => 'olbgroup',
				'meta_value' => 'teacher',
				'meta_compare' => '=',
			);

		$users = array();
		$org = get_users($args);
		foreach($org as $u){
			$users[] = array(
					'id'       => $u->data->ID,
					'nicename' => $u->data->user_nicename,
					'name'     => $u->data->display_name,
					'status'   => $u->data->user_status,
					'email'    => $u->data->user_email,
					'olbgroup'  => get_user_meta($u->data->ID, 'olbgroup', true),
				);
		}
		return $users;
	}

	/** 
	 *	講師数を取得: Get count of rooms
	 */
	public function recordMax(){
		global $wpdb;

		$args = array();
		$prefix = $wpdb->prefix;
		$query = "SELECT COUNT(*) as count FROM ".$prefix."users as u "
				."INNER JOIN ".$prefix."usermeta as um "
				."WHERE um.meta_key='olbgroup' AND um.meta_value='teacher' AND um.user_id=u.ID ";
		$ret = $wpdb->get_row($wpdb->prepare($query, $args), ARRAY_A);
		return $ret['count'];
	}


}
?>
