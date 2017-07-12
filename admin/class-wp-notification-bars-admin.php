<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://mythemeshop.com
 * @since      1.0
 *
 * @package    MTSNBF
 * @subpackage MTSNBF/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    MTSNBF
 * @subpackage MTSNBF/admin
 * @author     MyThemeShop
 */
class MTSNBF_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since 1.0
	 *
	 * @var   string
	 */
	private $plugin_screen_hook_suffix = null;

	private $force_bar_post_types;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0
	 * @param string    $plugin_name       The name of this plugin.
	 * @param string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since 1.0
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();
		$screen_id = $screen->id;

		$force_bar_post_types = $this->force_bar_post_types;

		if ( 'mts_notification_bar' === $screen_id || in_array( $screen_id, $force_bar_post_types ) ) {

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-notification-bars-admin.css', array(), $this->version, 'all' );
			if ( 'mts_notification_bar' !== $screen_id ) {
				wp_enqueue_style( $this->plugin_name.'_select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
			}
		}
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();
		$screen_id = $screen->id;

		$force_bar_post_types = $this->force_bar_post_types;

		if ( 'mts_notification_bar' === $screen_id || in_array( $screen_id, $force_bar_post_types ) ) {

			wp_enqueue_script( 'wp-color-picker' );

			if ( 'mts_notification_bar' !== $screen_id ) {

				wp_enqueue_script(
					$this->plugin_name.'_select2',
					plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js',
					array('jquery'),
					$this->version,
					false
				);
			}

			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/wp-notification-bars-admin.js',
				array(
					'jquery',
					'wp-color-picker',
				),
				$this->version, false
			);

			wp_localize_script(
				$this->plugin_name,
				'mtsnb_locale',
				array(
					'select_placeholder' => __( 'Enter Notification Bar Title', $this->plugin_name ),
				)
			);
		}
	}

	//////////////////////
	//////// CPT /////////
	//////////////////////

	/**
	 * Register MTS Notification Bar Post Type, attached to 'init'
	 *
	 * @since    1.0
	 */
	public function mts_notification_cpt() {
		$labels = array(
			'name'               => _x( 'Notification Bars', 'post type general name', $this->plugin_name ),
			'singular_name'      => _x( 'Notification Bar', 'post type singular name', $this->plugin_name ),
			'menu_name'          => _x( 'Notification Bars', 'admin menu', $this->plugin_name ),
			'name_admin_bar'     => _x( 'Notification Bar', 'add new on admin bar', $this->plugin_name ),
			'add_new'            => _x( 'Add New', 'notification bar', $this->plugin_name ),
			'add_new_item'       => __( 'Add New Notification Bar', $this->plugin_name ),
			'new_item'           => __( 'New Notification Bar', $this->plugin_name ),
			'edit_item'          => __( 'Edit Notification Bar', $this->plugin_name ),
			'view_item'          => __( 'View Notification Bar', $this->plugin_name ),
			'all_items'          => __( 'All Notification Bars', $this->plugin_name ),
			'search_items'       => __( 'Search Notification Bars', $this->plugin_name ),
			'parent_item_colon'  => __( 'Parent Notification Bars:', $this->plugin_name ),
			'not_found'          => __( 'No notification bars found.', $this->plugin_name ),
			'not_found_in_trash' => __( 'No notification bars found in Trash.', $this->plugin_name )
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'publicly_queryable' => false,
			'menu_position' => 100,
			'menu_icon' => 'dashicons-info',
			'has_archive' => false,
			'supports' => array('title')
		);

		register_post_type( 'mts_notification_bar' , $args );

		// Filter supported post types where user ca override bar on single view
		$force_bar_supported_post_types = apply_filters( 'mtsnb_force_bar_post_types', array( 'post', 'page' ) );

		if ( ( $key = array_search( 'mts_notification_bar', $force_bar_supported_post_types ) ) !== false ) {

			unset( $force_bar_supported_post_types[ $key ] );
		}

		$this->force_bar_post_types = $force_bar_supported_post_types;
	}

	/**
	 * Add preview button to edit bar page.
	 *
	 * @since    1.0
	 */
	public function add_preview_button() {
		global $post;
		if ( 'mts_notification_bar' === $post->post_type ) {
			echo '<div class="misc-pub-section">';
				echo '<a href="#" class="button" id="preview-bar"><i class="dashicons dashicons-visibility"></i> '.__( 'Preview Bar', $this->plugin_name ).'</a>';
			echo '</div>';
		}
	}

	/**
	 * Add the Meta Box
	 *
	 * @since    1.0
	 */
	public function add_custom_meta_box() {
	    
        add_meta_box(
            'custom_meta_box',
            __( 'Settings', $this->plugin_name ),
            array( $this, 'show_custom_meta_box' ),
            'mts_notification_bar',
            'normal',
            'high'
        );
	}

	/**
	 * The Callback, Meta Box Content
	 *
	 * @since    1.0
	 */
	public function show_custom_meta_box( $post ) {

		$general_options = array(
			array(
				'type' => 'select',
				'name' => 'button',
				'label' => __( 'Hide/Close Button', $this->plugin_name ),
				'default'=> 'no_button',
				'options' => array(
					'no_button' => __( 'No Button', $this->plugin_name ),
					'toggle_button' => __( 'Toggle Button', $this->plugin_name ),
					'close_button' => __( 'Close Button', $this->plugin_name ),
				),
				'class' => 'mtsnb-has-child-opt'
			),
			array(
				'type' => 'number',
				'name' => 'content_width',
				'label' => __( 'Content Width (px)', $this->plugin_name ),
				'default'=> '960'
			),
			array(
				'type' => 'select',
				'name' => 'css_position',
				'label' => __( 'Notification bar CSS position', $this->plugin_name ),
				'default'=> 'fixed',
				'options' => array(
					'fixed' => __( 'Fixed', $this->plugin_name ),
					'absolute' => __( 'Absolute', $this->plugin_name ),
				)
			),
		);

		$style_options = array(
			array(
				'type' => 'color',
				'name' => 'bg_color',
				'label' => __( 'Background Color', $this->plugin_name ),
				'default'=> '#d35151'
			),
			array(
				'type' => 'color',
				'name' => 'txt_color',
				'label' => __( 'Text Color', $this->plugin_name ),
				'default'=> '#ffffff'
			),
			array(
				'type' => 'color',
				'name' => 'link_color',
				'label' => __( 'Link Color/Button Color', $this->plugin_name ),
				'default'=> '#f4a700'
			),
			array(
				'type' => 'number',
				'name' => 'font_size',
				'label' => __( 'Font size (px)', $this->plugin_name ),
				'default'=> '15'
			),
		);

		$button_content_type_options = array(
			array(
				'type' => 'select',
				'name' => 'basic_link_style',
				'label' => __( 'Link Style', $this->plugin_name ),
				'default'=> 'link',
				'options' => array(
					'link' => __( 'Link', $this->plugin_name ),
					'button' => __( 'Button', $this->plugin_name ),
				)
			),
			array(
				'type' => 'text',
				'name' => 'basic_text',
				'label' => __( 'Text', $this->plugin_name ),
				'default'=> ''
			),
			array(
				'type' => 'text',
				'name' => 'basic_link_text',
				'label' => __( 'Link/Button Text', $this->plugin_name ),
				'default'=> ''
			),
			array(
				'type' => 'text',
				'name' => 'basic_link_url',
				'label' => __( 'Link/Button Url', $this->plugin_name ),
				'default'=> ''
			),
		);

		$custom_content_type_options = array(
			array(
				'type' => 'textarea',
				'name' => 'custom_content',
				'label' => __( 'Add custom content, shortcodes allowed', $this->plugin_name ),
				'default'=> ''
			),
		);

		// Add an nonce field so we can check for it later.
		wp_nonce_field('mtsnb_meta_box', 'mtsnb_meta_box_nonce');
		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, '_mtsnb_data', true );//var_dump($value);
		?>
		<div class="mtsnb-tabs clearfix">
			<div class="mtsnb-tabs-inner clearfix">
				<?php $active_tab = ( isset( $value['active_tab'] ) && !empty( $value['active_tab'] ) ) ? $value['active_tab'] : 'general'; ?>
				<input type="hidden" class="mtsnb-tab-option" name="mtsnb_fields[active_tab]" id="mtsnb_fields_active_tab" value="<?php echo $active_tab; ?>" />
				<ul class="mtsnb-tabs-nav" id="main-tabs-nav">
					<li>
						<a href="#tab-general" <?php if ( $active_tab === 'general' ) echo 'class="active"'; ?>>
							<span class="mtsnb-tab-title"><i class="dashicons dashicons-admin-generic"></i><?php _e( 'General', $this->plugin_name ); ?></span>
						</a>
					</li>
					<li>
						<a href="#tab-type" <?php if ( $active_tab === 'type' ) echo 'class="active"'; ?>>
							<span class="mtsnb-tab-title"><i class="dashicons dashicons-edit"></i><?php _e( 'Content', $this->plugin_name ); ?></span>
						</a>
					</li>
					<li>
						<a href="#tab-style" <?php if ( $active_tab === 'style' ) echo 'class="active"'; ?>>
							<span class="mtsnb-tab-title"><i class="dashicons dashicons-admin-appearance"></i><?php _e( 'Style', $this->plugin_name ); ?></span>
						</a>
					</li>
					<li>
						<a href="#tab-conditions" <?php if ( $active_tab === 'conditions' ) echo 'class="active"'; ?>>
							<span class="mtsnb-tab-title"><i class="dashicons dashicons-admin-settings"></i><?php _e( 'Conditions', $this->plugin_name ); ?></span>
						</a>
					</li>
				</ul>
				<div class="mtsnb-tabs-wrap" id="main-tabs-wrap">
					<div id="tab-general" class="mtsnb-tabs-content <?php if ( $active_tab === 'general' ) echo 'active'; ?>">
						<div class="mtsnb-tab-desc"><?php _e( 'Select basic settings like close button type and CSS position of the bar.', $this->plugin_name ); ?></div>
						<div class="mtsnb-tab-options clearfix">
							<?php
							foreach ( $general_options as $option_args ) {
								$this->custom_meta_field( $option_args, $value );
							}
							?>
						</div>
					</div>
					<div id="tab-type" class="mtsnb-tabs-content <?php if ( $active_tab === 'type' ) echo 'active'; ?>">
						<div class="mtsnb-tab-desc"><?php _e( 'Set up notification bar content. Select content type and fill in the fields.', $this->plugin_name ); ?></div>
						<div class="mtsnb-tab-options clearfix">
							<?php $content_type = ( isset( $value['content_type'] ) && !empty( $value['content_type'] ) ) ? $value['content_type'] : 'button'; ?>
							<input type="hidden" class="mtsnb-tab-option" name="mtsnb_fields[content_type]" id="mtsnb_fields_content_type" value="<?php echo $content_type; ?>" />
							<ul class="mtsnb-tabs-nav" id="sub-tabs-nav">
								<li><a href="#tab-button" <?php if ( $content_type === 'button' ) echo 'class="active"'; ?>><?php _e( 'Text and Link/Button', $this->plugin_name ); ?></a></li>
								<li><a href="#tab-custom" <?php if ( $content_type === 'custom' ) echo 'class="active"'; ?>><?php _e( 'Custom', $this->plugin_name ); ?></a></li>
							</ul>
							<div class="meta-tabs-wrap" id="sub-tabs-wrap">
								<div id="tab-button" class="mtsnb-tabs-content <?php if ( $content_type === 'button' ) echo 'active'; ?>">
									<?php
									foreach ( $button_content_type_options as $option_args ) {
										$this->custom_meta_field( $option_args, $value );
									}
									?>
								</div>
								<div id="tab-custom" class="mtsnb-tabs-content <?php if ( $content_type === 'custom' ) echo 'active'; ?>">
									<?php
									foreach ( $custom_content_type_options as $option_args ) {
										$this->custom_meta_field( $option_args, $value );
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div id="tab-style" class="mtsnb-tabs-content <?php if ( $active_tab === 'style' ) echo 'active'; ?>">
						<div class="mtsnb-tab-desc"><?php _e( 'Change the appearance of the notification bar.', $this->plugin_name ); ?></div>
						<div class="mtsnb-tab-options clearfix">
						<?php
						foreach ( $style_options as $option_args ) {
							$this->custom_meta_field( $option_args, $value );
						}
						?>
						</div>
					</div>
					<div id="tab-conditions" class="mtsnb-tabs-content <?php if ( $active_tab === 'conditions' ) echo 'active'; ?>">
						<div class="mtsnb-tab-desc"><?php _e( 'Choose when and where to display the notification bar.', $this->plugin_name ); ?></div>
						<div id="conditions-selector-wrap" class="clearfix">
							<div id="conditions-selector">
								<ul>
									<?php $condition_location_state       = isset( $value['conditions'] ) && isset( $value['conditions']['location'] ) && ( isset( $value['conditions']['location']['state'] ) && !empty( $value['conditions']['location']['state'] ) ) ? $value['conditions']['location']['state'] : ''; ?>
									<?php $condition_notlocation_state    = isset( $value['conditions'] ) && isset( $value['conditions']['notlocation'] ) && ( isset( $value['conditions']['notlocation']['state'] ) && !empty( $value['conditions']['notlocation']['state'] ) ) ? $value['conditions']['notlocation']['state'] : ''; ?>
									<?php $condition_location_disabled    = empty( $condition_notlocation_state ) ? '' : ' disabled'; ?>
									<?php $condition_notlocation_disabled = empty( $condition_location_state ) ? '' : ' disabled'; ?>
									<li id="condition-location" data-disable="notlocation" class="condition-checkbox <?php echo $condition_location_state.$condition_location_disabled; ?>">
										<?php _e( 'On specific locations', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_location_state" name="mtsnb_fields[conditions][location][state]" value="<?php echo $condition_location_state; ?>">
									</li>
									<li id="condition-notlocation" data-disable="location" class="condition-checkbox <?php echo $condition_notlocation_state.$condition_notlocation_disabled; ?>">
										<?php _e( 'Not on specific locations', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_notlocation_state" name="mtsnb_fields[conditions][notlocation][state]" value="<?php echo $condition_notlocation_state; ?>">
									</li>
									<?php $condition_google_state       = isset( $value['conditions'] ) && isset( $value['conditions']['google'] ) && ( isset( $value['conditions']['google']['state'] ) && !empty( $value['conditions']['google']['state'] ) ) ? $value['conditions']['google']['state'] : ''; ?>
									<?php $condition_notgoogle_state    = isset( $value['conditions'] ) && isset( $value['conditions']['notgoogle'] ) && ( isset( $value['conditions']['notgoogle']['state'] ) && !empty( $value['conditions']['notgoogle']['state'] ) ) ? $value['conditions']['notgoogle']['state'] : ''; ?>
									<?php $condition_google_disabled    = empty( $condition_notgoogle_state ) ? '' : ' disabled'; ?>
									<?php $condition_notgoogle_disabled = empty( $condition_google_state ) ? '' : ' disabled'; ?>
									<li id="condition-google" data-disable="notgoogle" class="condition-checkbox <?php echo $condition_google_state.$condition_google_disabled; ?>">
										<?php _e( 'From Google', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_google_state" name="mtsnb_fields[conditions][google][state]" value="<?php echo $condition_google_state; ?>">
									</li>
									<li id="condition-notgoogle" data-disable="google" class="condition-checkbox <?php echo $condition_notgoogle_state.$condition_notgoogle_disabled; ?>">
										<?php _e( 'Not from Google', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_notgoogle_state" name="mtsnb_fields[conditions][notgoogle][state]" value="<?php echo $condition_notgoogle_state; ?>">
									</li>
									<?php $condition_facebook_state       = isset( $value['conditions'] ) && isset( $value['conditions']['facebook'] ) && ( isset( $value['conditions']['facebook']['state'] ) && !empty( $value['conditions']['facebook']['state'] ) ) ? $value['conditions']['facebook']['state'] : ''; ?>
									<?php $condition_notfacebook_state    = isset( $value['conditions'] ) && isset( $value['conditions']['notfacebook'] ) && ( isset( $value['conditions']['notfacebook']['state'] ) && !empty( $value['conditions']['notfacebook']['state'] ) ) ? $value['conditions']['notfacebook']['state'] : ''; ?>
									<?php $condition_facebook_disabled    = empty( $condition_notfacebook_state ) ? '' : ' disabled'; ?>
									<?php $condition_notfacebook_disabled = empty( $condition_facebook_state ) ? '' : ' disabled'; ?>
									<li id="condition-facebook" data-disable="notfacebook" class="condition-checkbox <?php echo $condition_facebook_state.$condition_facebook_disabled; ?>">
										<?php _e( 'From Facebook', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_facebook_state" name="mtsnb_fields[conditions][facebook][state]" value="<?php echo $condition_facebook_state; ?>">
									</li>
									<li id="condition-notfacebook" data-disable="facebook" class="condition-checkbox <?php echo $condition_notfacebook_state.$condition_notfacebook_disabled; ?>">
										<?php _e( 'Not from Facebook', $this->plugin_name ); ?>
										<div class="mtsnb-check"></div>
										<input type="hidden" class="mtsnb-condition-checkbox-input" id="mtsnb_fields_conditions_notfacebook_state" name="mtsnb_fields[conditions][notfacebook][state]" value="<?php echo $condition_notfacebook_state; ?>">
									</li>
								</ul>
							</div>
							<div id="conditions-panels">
								<div id="condition-location-panel" class="mtsnb-conditions-panel <?php echo $condition_location_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'On specific locations', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc"><?php _e( 'Show Notification Bar on the following locations', $this->plugin_name ); ?></div>
										<div class="mtsnb-conditions-panel-opt">
											<?php $location_home       = isset( $value['conditions'] ) && isset( $value['conditions']['location'] ) && ( isset( $value['conditions']['location']['home'] ) && !empty( $value['conditions']['location']['home'] ) ) ? $value['conditions']['location']['home'] : '0'; ?>
											<?php $location_blog_home  = isset( $value['conditions'] ) && isset( $value['conditions']['location'] ) && ( isset( $value['conditions']['location']['blog_home'] ) && !empty( $value['conditions']['location']['blog_home'] ) ) ? $value['conditions']['location']['blog_home'] : '0'; ?>
											<?php $location_pages      = isset( $value['conditions'] ) && isset( $value['conditions']['location'] ) && ( isset( $value['conditions']['location']['pages'] ) && !empty( $value['conditions']['location']['pages'] ) ) ? $value['conditions']['location']['pages'] : '0'; ?>
											<?php $location_posts      = isset( $value['conditions'] ) && isset( $value['conditions']['location'] ) && ( isset( $value['conditions']['location']['posts'] ) && !empty( $value['conditions']['location']['posts'] ) ) ? $value['conditions']['location']['posts'] : '0'; ?>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][location][home]" id="mtsnb_fields_conditions_location_home" value="1" <?php checked( $location_home, '1', true ); ?> />
													<?php _e( 'Homepage.', $this->plugin_name ); ?>
												</label>
											</p>
											<?php if ( 'page' === get_option('show_on_front') && '0' !== get_option('page_for_posts') && '0' !== get_option('page_on_front') ) { ?>
												<p>
													<label>
														<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][location][blog_home]" id="mtsnb_fields_conditions_location_blog_home" value="1" <?php checked( $location_blog_home, '1', true ); ?> />
														<?php _e( 'Blog Homepage.', $this->plugin_name ); ?>
													</label>
												</p>
											<?php } ?>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][location][pages]" id="mtsnb_fields_conditions_location_pages" value="1" <?php checked( $location_pages, '1', true ); ?> />
													<?php _e( 'Pages.', $this->plugin_name ); ?>
												</label>
											</p>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][location][posts]" id="mtsnb_fields_conditions_location_posts" value="1" <?php checked( $location_posts, '1', true ); ?> />
													<?php _e( 'Posts.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
								<div id="condition-notlocation-panel" class="mtsnb-conditions-panel <?php echo $condition_notlocation_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'Not on specific locations', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc"><?php _e( 'Hide Notification Bar on the following locations', $this->plugin_name ); ?></div>
										<div class="mtsnb-conditions-panel-opt">
											<?php $notlocation_home      = isset( $value['conditions'] ) && isset( $value['conditions']['notlocation'] ) && ( isset( $value['conditions']['notlocation']['home'] ) && !empty( $value['conditions']['notlocation']['home'] ) ) ? $value['conditions']['notlocation']['home'] : '0'; ?>
											<?php $notlocation_blog_home = isset( $value['conditions'] ) && isset( $value['conditions']['notlocation'] ) && ( isset( $value['conditions']['notlocation']['blog_home'] ) && !empty( $value['conditions']['notlocation']['blog_home'] ) ) ? $value['conditions']['notlocation']['blog_home'] : '0'; ?>
											<?php $notlocation_pages     = isset( $value['conditions'] ) && isset( $value['conditions']['notlocation'] ) && ( isset( $value['conditions']['notlocation']['pages'] ) && !empty( $value['conditions']['notlocation']['pages'] ) ) ? $value['conditions']['notlocation']['pages'] : '0'; ?>
											<?php $notlocation_posts     = isset( $value['conditions'] ) && isset( $value['conditions']['notlocation'] ) && ( isset( $value['conditions']['notlocation']['posts'] ) && !empty( $value['conditions']['notlocation']['posts'] ) ) ? $value['conditions']['notlocation']['posts'] : '0'; ?>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][notlocation][home]" id="mtsnb_fields_conditions_notlocation_home" value="1" <?php checked( $notlocation_home, '1', true ); ?> />
													<?php _e( 'Homepage.', $this->plugin_name ); ?>
												</label>
											</p>
											<?php if ( 'page' === get_option('show_on_front') && '0' !== get_option('page_for_posts') && '0' !== get_option('page_on_front') ) { ?>
												<p>
													<label>
														<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][notlocation][blog_home]" id="mtsnb_fields_conditions_notlocation_blog_home" value="1" <?php checked( $notlocation_blog_home, '1', true ); ?> />
														<?php _e( 'Blog Homepage.', $this->plugin_name ); ?>
													</label>
												</p>
											<?php } ?>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][notlocation][pages]" id="mtsnb_fields_conditions_notlocation_pages" value="1" <?php checked( $notlocation_pages, '1', true ); ?> />
													<?php _e( 'Pages.', $this->plugin_name ); ?>
												</label>
											</p>
											<p>
												<label>
													<input type="checkbox" class="mtsnb-checkbox" name="mtsnb_fields[conditions][notlocation][posts]" id="mtsnb_fields_conditions_notlocation_posts" value="1" <?php checked( $notlocation_posts, '1', true ); ?> />
													<?php _e( 'Posts.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
								<div id="condition-google-panel" class="mtsnb-conditions-panel <?php echo $condition_google_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'From Google', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc">
											<p>
												<label>
													<?php _e( 'Show Notification Bar if visitor arrived via Google search engine.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
								<div id="condition-notgoogle-panel" class="mtsnb-conditions-panel <?php echo $condition_notgoogle_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'Not from Google', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc">
											<p>
												<label>
													<?php _e( 'Hide Notification Bar if visitor arrived via Google search engine.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
								<div id="condition-facebook-panel" class="mtsnb-conditions-panel <?php echo $condition_facebook_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'From Facebook', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc">
											<p>
												<label>
													<?php _e( 'Show Notification Bar if visitor arrived from Facebook.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
								<div id="condition-notfacebook-panel" class="mtsnb-conditions-panel <?php echo $condition_notfacebook_state; ?>">
									<div class="mtsnb-conditions-panel-title"><?php _e( 'Not from Facebook', $this->plugin_name ); ?></div>
									<div class="mtsnb-conditions-panel-content">
										<div class="mtsnb-conditions-panel-desc">
											<p>
												<label>
													<?php _e( 'Hide Notification Bar if visitor arrived from Facebook.', $this->plugin_name ); ?>
												</label>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Helper function for common fields
	 *
	 * @since    1.0
	 */
	public function custom_meta_field( $args, $value, $b = false ) {

		$type = isset( $args['type'] ) ? $args['type'] : '';
		$name = isset( $args['name'] ) ? $args['name'] : '';
		$name = $b ? 'b_'.$name : $name;
		$label = isset( $args['label'] ) ? $args['label'] : '';
		$options = isset( $args['options'] ) ? $args['options'] : array();
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$min = isset( $args['min'] ) ? $args['min'] : '0';

		$class = isset( $args['class'] ) ? $args['class'] : '';

		// Option value
		$opt_val = isset( $value[ $name ] ) ? $value[ $name ] : $default;

		?>
		<div id="mtsnb_fields_<?php echo $name;?>_row" class="form-row">
			<label class="form-label" for="mtsnb_fields_<?php echo $name;?>"><?php echo $label; ?></label>
			<div class="form-option <?php echo $class; ?>">
			<?php
			switch ( $type ) {

				case 'text':
				?>
					<input type="text" name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>" value="<?php echo esc_attr( $opt_val );?>" />
				<?php
				break;
				case 'select':
				?>
					<select name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>">
					<?php foreach ( $options as $val => $label ) { ?>
						<option value="<?php echo $val; ?>" <?php selected( $opt_val, $val, true); ?>><?php echo $label ?></option>
					<?php } ?>
					</select>
				<?php
				break;
				case 'number':
				?>
					<input type="number" step="1" min="<?php echo $min;?>" name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>" value="<?php echo $opt_val;?>" class="small-text"/>
				<?php
				break;
				case 'color':
				?>
					<input type="text" name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>" value="<?php echo $opt_val;?>" class="mtsnb-color-picker" />
				<?php
				break;
				case 'textarea':
				?>
					<textarea name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>" class="mtsnb-textarea"><?php echo esc_textarea( $opt_val );?></textarea>
				<?php
				break;
				case 'checkbox':
				?>
					<input type="checkbox" name="mtsnb_fields[<?php echo $name;?>]" id="mtsnb_fields_<?php echo $name;?>" value="1" <?php checked( $opt_val, '1', true ); ?> />
				<?php
				break;
				case 'info':
				?>
					<small class="mtsnb-option-info">
						<?php echo $default; ?>
					</small>
				<?php
				break;
			}
			?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the Data
	 *
	 * @since    1.0
	 */
	public function save_custom_meta( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['mtsnb_meta_box_nonce'] ) ) {
			return;
		}
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['mtsnb_meta_box_nonce'], 'mtsnb_meta_box' ) ) {
			return;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'mts_notification_bar' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */
		if ( ! isset( $_POST['mtsnb_fields'] ) ) {
			return;
		}

		$my_data = $_POST['mtsnb_fields'];

		// Update the meta field in the database.
		update_post_meta( $post_id, '_mtsnb_data', $my_data );
	}

	/**
	 * Deactivate plugin if pro is active
	 *
	 * @since    1.0
	 */
	public function check_version() {

		if ( defined( 'MTSNB_PLUGIN_BASE' ) ) {

			if ( is_plugin_active( MTSNB_PLUGIN_BASE ) || class_exists('MTSNB') ) {

				deactivate_plugins( MTSNBF_PLUGIN_BASE );
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Deactivation notice
	 *
	 * @since    1.0
	 */
	public function disabled_notice() {
		?>
		<div class="updated">
			<p><?php _e( 'Free version of WP Notification Bars plugin disabled. Pro version is active!', $this->plugin_name ); ?></p>
		</div>
	<?php
	}

	/**
	 * Notification Bar update messages
	 *
	 * @since    1.0
	 *
	 * @param array   $messages
	 * @return array   $messages
	 */
	public function mtsnb_update_messages( $messages ) {

		global $post;

		$post_ID = $post->ID;
		$post_type = get_post_type( $post_ID );

		if ('mts_notification_bar' == $post_type ) {

			$messages['mts_notification_bar'] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => __( 'Notification Bar updated.', $this->plugin_name ),
				2 => __( 'Custom field updated.', $this->plugin_name ),
				3 => __( 'Custom field deleted.', $this->plugin_name ),
				4 => __( 'Notification Bar updated.', $this->plugin_name ),
				5 => isset($_GET['revision']) ? sprintf( __('Notification Bar restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => __( 'Notification Bar published.', $this->plugin_name ),
				7 => __( 'Notification Bar saved.', $this->plugin_name ),
				8 => __( 'Notification Bar submitted.', $this->plugin_name),
				9 => sprintf( __('Notification Bar  scheduled for: <strong>%1$s</strong>.', $this->plugin_name ), date_i18n( __( 'M j, Y @ H:i' ), strtotime( $post->post_date ) ) ),
				10 => __('Notification Bar draft updated.', $this->plugin_name ),
			);
		}

		return $messages;
	}


	/**
	 * Single post view bar select
	 *
	 * @since    1.0.1
	 */
	public function mtsnb_select_metabox_insert() {
		
		$force_bar_post_types = $this->force_bar_post_types;

		if ( $force_bar_post_types && is_array( $force_bar_post_types ) ) {

			foreach ( $force_bar_post_types as $screen ) {

				add_meta_box(
					'mtsnb_single_bar_metabox',
					__( 'Notification Bar', $this->plugin_name ),
					array( $this, 'mtsnb_select_metabox_content' ),
					$screen,
					'side',
					'default'
				);
			}
		}
	}
	public function mtsnb_select_metabox_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field('mtsnb_select_metabox_save', 'mtsnb_select_metabox_nonce');

		/*
		* Use get_post_meta() to retrieve an existing value
		* from the database and use the value for the form.
		*/
		$bar = get_post_meta( $post->ID, '_mtsnb_override_bar', true );

		$processed_item_ids = '';
		if ( !empty( $bar ) ) {
			// Some entries may be arrays themselves!
			$processed_item_ids = array();
			foreach ($bar as $this_id) {
				if (is_array($this_id)) {
					$processed_item_ids = array_merge( $processed_item_ids, $this_id );
				} else {
					$processed_item_ids[] = $this_id;
				}
			}

			if (is_array($processed_item_ids) && !empty($processed_item_ids)) {
				$processed_item_ids = implode(',', $processed_item_ids);
			} else {
				$processed_item_ids = '';
			}
		}
		?>
		<p>
			<label for="mtsnb_override_bar_field"><?php _e( 'Select Notification Bar (optional):', $this->plugin_name ); ?></label><br />
			<input style="width: 400px;" type="hidden" id="mtsnb_override_bar_field" name="mtsnb_override_bar_field" class="mtsnb-bar-select"  value="<?php echo $processed_item_ids; ?>" />
		</p>
		<p>
			<i><?php _e( 'Selected notification bar will override any other bar.', $this->plugin_name ); ?></i>
		</p>
		<?php
	}

	public function mtsnb_select_metabox_save( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['mtsnb_select_metabox_nonce'] ) ) {
			return;
		}
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['mtsnb_select_metabox_nonce'], 'mtsnb_select_metabox_save' ) ) {
			return;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
		}

		/* OK, its safe for us to save the data now. */
		if ( ! isset( $_POST['mtsnb_override_bar_field'] ) ) {
			return;
		}

		$val = $_POST['mtsnb_override_bar_field'];

		if (strpos($val, ',') === false) {
			// No comma, must be single value - still needs to be in an array for now
			$post_ids = array( $val );
		} else {
			// There is a comma so it's explodable
			$post_ids = explode(',', $val);
		}

		// Update the meta field in the database.
		update_post_meta( $post_id, '_mtsnb_override_bar', $post_ids );
	}

	/**
	 * Bar select ajax function
	 *
	 * @since    1.0.1
	 */
	public function mtsnb_get_bars() {

		$result = array();

		$search = $_REQUEST['q'];

		$ads_query = array(
			'posts_per_page' => -1,
			'post_status' => array('publish'),
			'post_type' => 'mts_notification_bar',
			'order' => 'ASC',
			'orderby' => 'title',
			'suppress_filters' => false,
			's'=> $search
		);
		$posts = get_posts( $ads_query );

		// We'll return a JSON-encoded result.
		foreach ( $posts as $this_post ) {
			$post_title = $this_post->post_title;
			$id = $this_post->ID;

			$result[] = array(
				'id' => $id,
				'title' => $post_title,
			);
		}

	    echo json_encode( $result );

	    die();
	}

	public function mtsnb_get_bar_titles() {
		$result = array();

		if (isset($_REQUEST['post_ids'])) {
			$post_ids = $_REQUEST['post_ids'];
			if (strpos($post_ids, ',') === false) {
				// There is no comma, so we can't explode, but we still want an array
				$post_ids = array( $post_ids );
			} else {
				// There is a comma, so it must be explodable
				$post_ids = explode(',', $post_ids);
			}
		} else {
			$post_ids = array();
		}

		if (is_array($post_ids) && ! empty($post_ids)) {

			$posts = get_posts(array(
				'posts_per_page' => -1,
				'post_status' => array('publish'),
				'post__in' => $post_ids,
				'post_type' => 'mts_notification_bar'
			));
			foreach ( $posts as $this_post ) {
				$result[] = array(
					'id' => $this_post->ID,
					'title' => $this_post->post_title,
				);
			}
		}

		echo json_encode( $result );

		die();
	}
}