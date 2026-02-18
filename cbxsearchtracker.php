<?php
/**
 * Plugin Name: CBX Search Tracker
 * Description: Tracks WordPress search keywords and shows most searched keywords in admin dashboard.
 * Plugin URI: https://github.com/codeboxrcodehub/cbxsearchtracker
 * Version: 1.2.1
 * Author: Codeboxr
 * Author URI:        http://codeboxr.com
 * Text Domain: cbxsearchtracker
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CBXSearchTracker {

	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'cbxsearchtracker_keywords';

		register_activation_hook( __FILE__, [ $this, 'create_table' ] );

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'wp', [ $this, 'track_search' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'handle_delete' ] );
		add_action('admin_init', [$this, 'handle_delete_all']);
	}

	/* ----------------------------
	 * Load Translation
	 * ---------------------------- */
	public function load_textdomain() {
		load_plugin_textdomain(
			'cbxsearchtracker',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/* ----------------------------
	 * Create DB Table
	 * ---------------------------- */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            keyword VARCHAR(255) NOT NULL,
            search_count BIGINT(20) UNSIGNED DEFAULT 1,
            last_searched DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY keyword (keyword)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/* ----------------------------
	 * Track Search
	 * ---------------------------- */
	public function track_search() {

		if ( is_admin() || ! is_search() ) {
			return;
		}

		$keyword = strtolower( trim( get_search_query() ) );
		if ( empty( $keyword ) ) {
			return;
		}

		global $wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE keyword = %s", $keyword )
		);

		if ( $existing ) {
			$wpdb->update(
				$this->table_name,
				[
					'search_count'  => $existing->search_count + 1,
					'last_searched' => current_time( 'mysql' )
				],
				[ 'id' => $existing->id ]
			);
		} else {
			$wpdb->insert(
				$this->table_name,
				[
					'keyword'       => $keyword,
					'search_count'  => 1,
					'last_searched' => current_time( 'mysql' )
				]
			);
		}
	}

	/* ----------------------------
	 * Admin Menu
	 * ---------------------------- */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Search Tracker', 'cbxsearchtracker' ),
			__( 'Search Tracker', 'cbxsearchtracker' ),
			'manage_options',
			'cbxsearchtracker',
			[ $this, 'admin_page' ],
			'dashicons-chart-bar',
			26
		);
	}

	/* ----------------------------
	 * Handle Delete
	 * ---------------------------- */
	public function handle_delete() {

		if ( ! isset( $_GET['cbx_delete'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id = intval( $_GET['cbx_delete'] );

		check_admin_referer( 'cbx_delete_keyword_' . $id );

		global $wpdb;
		$wpdb->delete( $this->table_name, [ 'id' => $id ] );

		wp_redirect( admin_url( 'admin.php?page=cbxsearchtracker&deleted=1' ) );
		exit;
	}

	/* ----------------------------
	 * Admin Page
	 * ---------------------------- */
	public function admin_page() {

		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT * FROM {$this->table_name} ORDER BY search_count DESC LIMIT 100"
		);

		$total_keywords = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->table_name}"
		);

		$total_searches = (int) $wpdb->get_var(
			"SELECT SUM(search_count) FROM {$this->table_name}"
		);


		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Most Searched Keywords', 'cbxsearchtracker' ) . '</h1>';

		if ( isset( $_GET['deleted'] ) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html__( 'Keyword deleted successfully.', 'cbxsearchtracker' ) . '</p>';
			echo '</div>';
		}

		if ( isset($_GET['deleted_all']) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html__('All keywords deleted successfully.', 'cbxsearchtracker') . '</p>';
			echo '</div>';
		}


		echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">';

		// LEFT SIDE — Delete All Button
		echo '<form method="post">';
		wp_nonce_field('cbx_delete_all_keywords');
		echo '<input type="hidden" name="cbx_delete_all" value="1" />';
		echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Delete All Keywords', 'cbxsearchtracker') . '" onclick="return confirm(\'Are you sure you want to delete all keywords?\');" />';
		echo '</form>';

		// RIGHT SIDE — Total Stats
		echo '<div style="font-weight:600;">';
		echo esc_html__('Total Keywords:', 'cbxsearchtracker') . ' ' . esc_html($total_keywords);
		echo ' | ';
		echo esc_html__('Total Searches:', 'cbxsearchtracker') . ' ' . esc_html($total_searches);
		echo '</div>';

		echo '</div>';



		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Keyword', 'cbxsearchtracker' ) . '</th>';
		echo '<th>' . esc_html__( 'Search Count', 'cbxsearchtracker' ) . '</th>';
		echo '<th>' . esc_html__( 'Last Searched', 'cbxsearchtracker' ) . '</th>';
		echo '<th>' . esc_html__( 'Action', 'cbxsearchtracker' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if ( $results ) {
			foreach ( $results as $row ) {

				$delete_url = wp_nonce_url(
					admin_url( 'admin.php?page=cbxsearchtracker&cbx_delete=' . $row->id ),
					'cbx_delete_keyword_' . $row->id
				);

				echo '<tr>';
				echo '<td>' . esc_html( $row->keyword ) . '</td>';
				echo '<td>' . esc_html( $row->search_count ) . '</td>';
				echo '<td>' . esc_html( $row->last_searched ) . '</td>';
				echo '<td>';
				echo '<a href="' . esc_url( $delete_url ) . '" class="button button-small button-danger">';
				echo esc_html__( 'Delete', 'cbxsearchtracker' );
				echo '</a>';
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="4">' . esc_html__( 'No searches recorded yet.', 'cbxsearchtracker' ) . '</td></tr>';
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Handle Delete All
	 *
	 * @return void
	 */
	public function handle_delete_all() {

		if ( ! isset( $_POST['cbx_delete_all'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'cbx_delete_all_keywords' );

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$this->table_name}" );

		wp_redirect( admin_url( 'admin.php?page=cbxsearchtracker&deleted_all=1' ) );
		exit;
	}

}

new CBXSearchTracker();