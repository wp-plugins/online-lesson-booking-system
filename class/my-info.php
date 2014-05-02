<?php
/** 
 *	プラグイン関連情報: Information of Plugin
 */
add_action( 'wp_dashboard_setup', array( 'olbInfo', 'olb_dashboard' ) );
add_action( 'olb_plugin_info', array( 'olbInfo', 'plugin_info') );
add_action( 'olb_latest_info', array( 'olbInfo', 'latest_info') );

class olbInfo {
	/** 
	 *	PLUGIN INFO (in PLUGIN OPTION PAGE)
	 */
	public static function plugin_info() {
		$url = OLBsystem::URL;
		if ( WPLANG != '' &&  WPLANG != 'ja' ) {
			$url .= WPLANG.'/';
		}
	?>
	<div class="postbox">
		<h3><span><?php _e( 'Plugin Information', OLBsystem::TEXTDOMAIN ); ?></span></h3>
		<div class="inside">
			<p><?php printf( 'Version: %s', OLBsystem::PLUGIN_VERSION ); ?></p>
			<p><a href="<?php echo $url; ?>" target="_blank">&raquo; <?php _e( "Online User's Guide", OLBsystem::TEXTDOMAIN ); ?></a></p>
			<p>
				<?php _e( 'Thank you for using "OLBsystem".', OLBsystem::TEXTDOMAIN ); ?><br />
				<?php _e( 'If wrong processing is found, please let me know.', OLBsystem::TEXTDOMAIN ); ?><br />
				<a href="<?php echo $url; ?>" target="_blank">&raquo; <?php _e( "Plugin site", OLBsystem::TEXTDOMAIN ); ?></a>
			</p>
			<p><i>
			</i></p>
		</div>
	</div>
	<?php
	}

	/** 
	 *	LATEST INFO (in PLUGIN OPTION PAGE)
	 */
	public static function latest_info() {
	?>
	<div class="postbox">
		<h3><span><?php _e( 'Latest from Plugin', OLBsystem::TEXTDOMAIN ); ?></span></h3>
		<div class="inside">
		<?php
		$url = OLBsystem::URL;
		if ( WPLANG != '' && WPLANG != 'ja' ) {
			$url .= WPLANG.'/';
		}
		$feed = fetch_feed( $url );
		$feed->set_cache_duration( 60*60 );
		$feed->init();
		$param = sprintf( 'title=%s&items=5&show_summary=0&show_author=0&show_date=0', __( 'Latest from Plugin', OLBsystem::TEXTDOMAIN ) );
		wp_widget_rss_output( $feed, $param );
		?>
		</div>
	</div>
	<?php
	}

	/** 
	 *	LATEST INFO (in DASHBOARD)
	 */
	public static function olb_dashboard() {
		wp_add_dashboard_widget( 'dashboard_custom_feed', __( 'Latest from "Online Lesson Booking" plugin', OLBsystem::TEXTDOMAIN ), array( 'olbInfo', 'latest_info_dashboard' ) );
	}

	/** 
	 *	LATEST INFO (in DASHBOARD)
	 */
	public static function latest_info_dashboard() {
		echo '<div class="rss-widget">';
		$url = OLBsystem::URL;
		if ( WPLANG != '' && WPLANG != 'ja' ) {
			$url .= WPLANG.'/';
		}
		$feed = fetch_feed( $url );
		$feed->set_cache_duration( 60*60 );
		$feed->init();
		$param = sprintf( 'title=%s&items=5&show_summary=0&show_author=0&show_date=1', __( 'Latest from "Online Lesson Booking" plugin', OLBsystem::TEXTDOMAIN ) );
		wp_widget_rss_output( $feed, $param );
		echo '</div>';
	}

}
?>
