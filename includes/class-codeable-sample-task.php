<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Codeable_Sample_Task {

	/**
	 * The single instance of Codeable_Sample_Task.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * ID of the post to be updated
	 * @var     id
	 * @access  private
	 * @since   1.0.0
	 */
	private $id;

	/**
	 * The new title the post must receive
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $title;

	/**
	 * The URL to which the user will be redirected afterwards
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $redirect;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'codeable_sample_task';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		register_activation_hook( $this->file, array( $this, 'install' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Check if required $_GET vars available, then execute submission
		if ( $this->has_get_vars() ) {
			add_action( 'wp', array( $this, 'run' ) ); // #code trail ... 4
		}

		// For testing
		add_shortcode( 'cdbl-form', array( $this, 'create_shortcode_form' ) );

		// For front-end form
		add_action( 'wp_ajax_nopriv_fetch_title', array( $this, 'fetch_title' ) );
		add_action( 'wp_ajax_fetch_title', array( $this, 'fetch_title' ) );

	} // End __construct ()

	/**
	 * Run the title update sequence
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function run() {
		if ( !$this->has_permissions() ) wp_die( "You do not have sufficient permissions to update the title." );

		$this->set_redirect( get_permalink() );
		$this->set_get_vars();

		if ( $this->post_exists() ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				array( 'post_title' => $this->title ),
				array( 'ID' => $this->id )
			); // #code trail ... 5

			if ( $this->has_redirect() ) $this->set_redirect( get_permalink( $this->id ) );
		}

		add_action( 'template_redirect', array( $this, 'go_redirect' ) );
	}

	/**
	 * Check if user has permission to update
	 * @access  private
	 * @since   1.0.0
	 * @return  boolean
	 */
	private function has_permissions() {
		if ( defined( 'CAN_GUEST_UPDATE' )
			&& CAN_GUEST_UPDATE
			&& wp_verify_nonce( $_REQUEST[ 'update_post_title' ], 'secret_post_title_value' )
		) return true;
		else if ( is_super_admin() ) return true;
		else return false;
	}

	/**
	 * Checks presence of required $_GET vars
	 * @access  private
	 * @since   1.0.0
	 * @return  boolean
	 */
	private function has_get_vars() {
		if ( !empty( $_GET ) ) {
			if ( isset( $_GET[ 'id' ] ) && $_GET[ 'id' ] &&
				isset( $_GET[ 'title' ] ) && $_GET[ 'title' ]
			) return true;
			else return false;
		} else return false;
	}

	/**
	 * Stores required $_GET vars in class properties
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function set_get_vars() {
		$input = stripslashes_deep( $_GET );
		$this->id = intval( $input[ 'id' ] );
		$this->title = $input[ 'title' ];
	}

	/**
	 * Check if the post to update does exist
	 * @access  private
	 * @since   1.0.0
	 * @return  boolean
	 */
	private function post_exists( $id = null ) {
		if ( !$id ) $id = $this->id;
		return FALSE !== get_post_status( $id );
	}

	/**
	 * Set the where user will redirect after form submission
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function set_redirect( $redirect ) {
		$this->redirect = $redirect;
	}

	/**
	 * Check if user wants to redirect after form submission
	 * @access  private
	 * @since   1.0.0
	 * @return  boolean
	 */
	private function has_redirect() {
		return isset( $_GET[ 'redirectme' ] ) && $_GET[ 'redirectme' ];
	}

	/**
	 * Execute the redirect
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function go_redirect() {
		if ( !DISABLE_REDIRECT && $this->redirect ) {
			wp_redirect( $this->redirect );
			exit;
		}
	}

	/**
	 * Returns current post title. Utilised via AJAX
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function fetch_title() {
		if ( $this->has_permissions() ) {
			if ( isset( $_POST[ 'id' ] ) && $_POST[ 'id' ] ) {
				$id = intval( $_POST[ 'id' ] );
				if ( $this->post_exists( $id ) ) {
					echo get_the_title( $id );
				}
			} else echo '';
		} else echo '';
		exit;
	}	

	/**
	 * Returns HTML for testing form's shortcode
	 * @access  public
	 * @since   1.0.0
	 * @return  String
	 */
	public function create_shortcode_form() {
		$form = <<<EOD
		<p>Update a post with <span id="cdbl-id">%ID%</span> to <span id="cdbl-title">%title%</span>.</p>
		<form id="cdbl-test" method="get" action="">
			<label>
				ID:
				<input type="number" name="id" required>
			</label><br />
			<label>
				Title:
				<input type="text" name="title" required>
			</label><br />
			<label>
				<input type="checkbox" name="redirectme" />Redirect me to this post after update
			</label>
			<br />
			<input type="submit" value="Update Title" />
EOD;
		$form .= wp_nonce_field( 'secret_post_title_value', 'update_post_title' ) . '</form>';
		return $form;
	}

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
		wp_localize_script( $this->_token . '-frontend', 'uris',
            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
        );
	} // End enqueue_scripts ()

	/**
	 * Main Codeable_Sample_Task Instance
	 *
	 * Ensures only one instance of Codeable_Sample_Task is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Codeable_Sample_Task()
	 * @return Main Codeable_Sample_Task instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version ); // #code trail ... 3
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
