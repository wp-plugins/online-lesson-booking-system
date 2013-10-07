<?php
/** 
 *	ユーザー情報: User info 
 */

class olbAuth {

	public $data = array();
	public $loggedin = null;

	/** 
	 *	CONSTRUCT
	 */
	public function __construct($user_id = null) {
		global $current_user;

		$currentuser = false;
		if(!empty($current_user->ID)) {
			$currentuser = self::getUser($current_user->ID);
		}

		if(empty($user_id)){
			// current user
			if(!empty($currentuser)){
				$this->data = $currentuser;
				$this->loggedin = true;
			}
		}
		else {
			if($user_id == $currentuser['id']) {
				$this->data = $currentuser;
				$this->loggedin = true;
			}
			else {
				$this->data = self::getUser($user_id);
				if(!empty($this->data)){
					$this->loggedin = false;
				}
			}
		}
	}

	/** 
	 *	ログイン状態の検査: Log-in inspection
	 */
	public function isLoggedIn(){
		if($this->loggedin){
			return true;
		}
		return false;
	}

	/**  
	 *	特定ユーザーの情報を取得: Get user info
	 */
	public static function getUser($user_id){
		$args = array(
			'include' => array($user_id),
			);
		$user_query = new WP_User_Query($args);
		list($user) = $user_query->results;
		/*
		WP_User Object(
		    [data] => stdClass Object(
		            [ID] => 2
		            [user_login] => user02
		            [user_pass] => **********************************
		            [user_nicename] => user02
		            [user_email] => hoge@example.com
		            [user_url] => 
		            [user_registered] => 2013-01-01 00:00:00
		            [user_activation_key] => 
		            [user_status] => 0
		            [display_name] => user02
		        )
		    [ID] => 2
		    [caps] => Array(
		            [subscriber] => 1
		        )
		    [cap_key] => a_wp_capabilities
		    [roles] => Array(
		            [0] => subscriber
		        )
		    [allcaps] => Array(
		            [read] => 1
		            [level_0] => 1
		            [subscriber] => 1
		        )
		    [filter] => 
		)
		*/
		$userdata = array();
		if(!empty($user->ID)) {
			$userdata = array(
				'id' => $user->ID,
				'loginname' => $user->user_login,
				'email' => $user->user_email,
				'firstname' => $user->user_firstname,
				'lastname' => $user->user_lastname,
				'name' => $user->display_name,
				'roles' => $user->roles,
				'address' => get_user_meta($user->ID, 'user_address', true),
				'phone' => get_user_meta($user->ID, 'user_phone', true),
				'skype' => get_user_meta($user->ID, 'user_skype', true),
				'olbgroup' => get_user_meta($user->ID, 'olbgroup', true),
				'olbterm' => get_user_meta($user->ID, 'olbterm', true),
			);
		}
		return $userdata;
	}

	/** 
	 *	管理者の検査: Administrator inspection 
	 */
	public function isAdmin(){
		if(in_array('administrator', $this->data['roles'])){
			return true;
		}
		return false;
	}

	/** 
	 *	講師の検査: Room manager inspection 
	 */
	public function isRoomManager(){
		if(in_array('author', $this->data['roles']) && $this->data['olbgroup']=='teacher'){
			return true;
		}
		return false;
	}

	/** 
	 *	会員の検査: Member inspection 
	 */
	public function isMember(){
		if(in_array('subscriber', $this->data['roles']) && !self::isRoomManager()){
			return true;
		}
		return false;
	}

	/** 
	 *	会員の有効期限の検査: Member term of validity inspection 
	 */
	public function isNotExpire($date){
		if(self::isMember() && !empty($this->data['olbterm']) && $this->data['olbterm']>=$date){
			return true;
		}
		return false;
	}

	/** 
	 *	無料予約の可否: Propriety of free reservation  
	 */
	public function canFreeReservation(){
		global $wpdb, $olb;

		if(self::isMember() && $olb->free > 0) {
			$prefix = $wpdb->prefix.OLBsystem::TABLEPREFIX;
			$query = 'SELECT COUNT(*) as count FROM '.$prefix.'history WHERE `user_id`=%d AND `free`=%d';
			$ret = $wpdb->get_row($wpdb->prepare($query, array($this->data['id'], 1)), ARRAY_A);
			return $olb->free - $ret['count'];
		}
		return 0;
	}

}
?>
