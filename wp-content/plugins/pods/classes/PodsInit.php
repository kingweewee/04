<?php
/**
 * @package Pods
 */
class PodsInit {

    /**
     * @var array
     */
    static $no_conflict = array();

    /**
     * @var PodsComponents
     */
    static $components;

    /**
     * @var PodsMeta
     */
    static $meta;

    /**
     * @var
     */
    static $admin;

    /**
     * @var mixed|void
     */
    static $version;

    /**
     * @var mixed|void
     */
    static $version_last;

    /**
     * Upgrades to trigger (last installed version => upgrade version)
     *
     * @var array
     */
    static $upgrades = array(
        '1.0.0' => '2.0.0'
        //'2.0.0' => '2.1.0'
    );

    /**
     * Whether an Upgrade is needed
     *
     * @var bool
     */
    static $upgrade_needed = false;

    /**
     * Setup and Initiate Pods
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.8.9
     */
    function __construct() {
        self::$version = get_option( 'pods_framework_version' );
        self::$version_last = get_option( 'pods_framework_version_last' );

        if ( !empty( self::$version ) ) {
            self::$upgrade_needed = false;

            foreach ( self::$upgrades as $old_version => $new_version ) {
                /*if ( '2.1.0' == $new_version && ( !defined( 'PODS_DEVELOPER' ) || PODS_DEVELOPER ) )
                    continue;*/

                if ( version_compare( $old_version, self::$version, '<=' ) && version_compare( self::$version, $new_version, '<' ) )
                    self::$upgrade_needed = true;
            }
        }
        elseif ( 0 < strlen( get_option( 'pods_version' ) ) )
            self::$upgrade_needed = true;

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

        add_action( 'init', array( $this, 'activate_install' ), 9 );

        if ( !empty( self::$version ) ) {
            // Meta handling and Components
            add_action( 'plugins_loaded', array( $this, 'load' ) );

            add_action( 'init', array( $this, 'init' ), 11 );

            add_action( 'init', array( $this, 'setup_content_types' ), 11 );

            add_filter( 'post_updated_messages', array( $this, 'setup_updated_messages' ), 10, 1 );
            add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );

            if ( is_admin() )
                add_action( 'init', array( $this, 'admin_init' ), 12 );

            // Register widgets
            add_action( 'widgets_init', array( $this, 'register_widgets' ) );

            // Show admin bar links
            add_action( 'admin_bar_menu', array( $this, 'admin_bar_links' ), 81 );
        }
    }

    /**
     * Load the plugin textdomain and set default constants
     */
    public function plugins_loaded () {
        if ( !defined( 'PODS_LIGHT' ) )
            define( 'PODS_LIGHT', false );

        if ( !defined( 'PODS_TABLELESS' ) )
            define( 'PODS_TABLELESS', false );

        load_plugin_textdomain( 'pods', false, dirname( PODS_DIR . 'init.php' ) . '/languages/' );
    }

    /**
     * Load Pods Meta and Components
     */
    public function load () {
        self::$meta = pods_meta()->init();

        if ( !defined( 'PODS_LIGHT' ) || !PODS_LIGHT )
            self::$components = pods_components();
    }

    /**
     * Set up the Pods core
     */
    public function init () {
        // Session start
        if ( ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || false === headers_sent() ) && '' == session_id() && ( !defined( 'PODS_SESSION_AUTO_START' ) || PODS_SESSION_AUTO_START ) )
            @session_start();

        add_shortcode( 'pods', 'pods_shortcode' );

        $security_settings = array(
            'pods_disable_file_browser' => 0,
            'pods_files_require_login' => 1,
            'pods_files_require_login_cap' => '',
            'pods_disable_file_upload' => 0,
            'pods_upload_require_login' => 1,
            'pods_upload_require_login_cap' => ''
        );

        foreach ( $security_settings as $security_setting => $setting ) {
            $setting = get_option( $security_setting );
            if ( !empty( $setting ) )
                $security_settings[ $security_setting ] = $setting;
        }

        foreach ( $security_settings as $security_setting => $setting ) {
            if ( 0 == $setting )
                $setting = false;
            elseif ( 1 == $setting )
                $setting = true;

            if ( in_array( $security_setting, array( 'pods_files_require_login', 'pods_upload_require_login' ) ) ) {
                if ( 0 < strlen( $security_settings[ $security_setting . '_cap' ] ) )
                    $setting = $security_settings[ $security_setting . '_cap' ];
            }
            elseif ( in_array( $security_setting, array( 'pods_files_require_login_cap', 'pods_upload_require_login_cap' ) ) )
                continue;

            if ( !defined( strtoupper( $security_setting ) ) )
                define( strtoupper( $security_setting ), $setting );
        }

        $this->register_assets();

        $this->register_pods();
    }

    /**
     * Register Scripts and Styles
     */
    public function register_assets () {
        if ( !wp_style_is( 'jquery-ui', 'registered' ) )
            wp_register_style( 'jquery-ui', PODS_URL . 'ui/css/smoothness/jquery-ui.custom.css', array(), '1.8.16' );

        wp_register_script( 'pods-json', PODS_URL . 'ui/js/jquery.json.js', array( 'jquery' ), '2.3' );

        wp_register_style( 'pods-qtip', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.0-2012-07-03' );
        wp_register_script( 'jquery-qtip', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.0-2012-07-03' );

        wp_register_script( 'pods', PODS_URL . 'ui/js/jquery.pods.js', array( 'jquery', 'pods-json', 'jquery-qtip' ), PODS_VERSION );

        wp_register_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION );

        wp_register_style( 'pods-cleditor', PODS_URL . 'ui/css/jquery.cleditor.css', array(), '1.3.0' );
        wp_register_script( 'pods-cleditor', PODS_URL . 'ui/js/jquery.cleditor.min.js', array( 'jquery' ), '1.3.0' );

        wp_register_style( 'pods-codemirror', PODS_URL . 'ui/css/codemirror.css', array(), '2.33' );
        wp_register_script( 'pods-codemirror', PODS_URL . 'ui/js/codemirror.js', array(), '2.33', true );
        wp_register_script( 'pods-codemirror-loadmode', PODS_URL . 'ui/js/codemirror/utils/loadmode.js', array( 'pods-codemirror' ), '2.33', true );

        if ( !wp_style_is( 'jquery-ui-timepicker', 'registered' ) )
            wp_register_style( 'jquery-ui-timepicker', PODS_URL . 'ui/css/jquery.ui.timepicker.css', array(), '1.0.1' );

        if ( !wp_script_is( 'jquery-ui-timepicker', 'registered' ) ) {
            wp_register_script( 'jquery-ui-timepicker', PODS_URL . 'ui/js/jquery.ui.timepicker.min.js', array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-datepicker',
                'jquery-ui-slider'
            ), '1.0.1' );
        }

        wp_register_style( 'pods-attach', PODS_URL . 'ui/css/jquery.pods.attach.css', array(), PODS_VERSION );
        wp_register_script( 'pods-attach', PODS_URL . 'ui/js/jquery.pods.attach.js', array(), PODS_VERSION );

        wp_register_style( 'pods-select2', PODS_URL . 'ui/css/select2.css', array(), '3.1' );
        wp_register_script( 'pods-select2', PODS_URL . 'ui/js/select2.min.js', array( 'jquery' ), '3.1' );

        wp_register_script( 'pods-handlebars', PODS_URL . 'ui/js/handlebars.js', array(), '1.0.0.beta.6' );
    }

    /**
     * Register internal Post Types
     */
    public function register_pods () {
        $args = array(
            'label' => 'Pods',
            'labels' => array( 'singular_name' => 'Pod' ),
            'public' => false,
            'can_export' => false,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'pods_pod',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array( 'title', 'author' ),
            'menu_icon' => PODS_URL . 'ui/images/icon16.png'
        );

        $args = self::object_label_fix( $args, 'post_type' );

        register_post_type( '_pods_pod', apply_filters( 'pods_internal_register_post_type_pod', $args ) );

        $args = array(
            'label' => 'Pod Fields',
            'labels' => array( 'singular_name' => 'Pod Field' ),
            'public' => false,
            'can_export' => false,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'pods_pod',
            'has_archive' => false,
            'hierarchical' => true,
            'supports' => array( 'title', 'editor', 'author' ),
            'menu_icon' => PODS_URL . 'ui/images/icon16.png'
        );

        $args = self::object_label_fix( $args, 'post_type' );

        register_post_type( '_pods_field', apply_filters( 'pods_internal_register_post_type_field', $args ) );
    }

    /**
     * Include Admin
     */
    public function admin_init () {
        self::$admin = pods_admin();
    }

    /**
     * Register Post Types and Taxonomies
     */
    public function setup_content_types () {
        global $wp_version;

        $post_types = PodsMeta::$post_types;
        $taxonomies = PodsMeta::$taxonomies;

        $existing_post_types = get_post_types();
        $existing_taxonomies = get_taxonomies();

        $pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

        if ( false === $pods_cpt_ct ) {
            $pods_cpt_ct = array(
                'post_types' => array(),
                'taxonomies' => array()
            );

            $pods_post_types = $pods_taxonomies = array();
            $supported_post_types = $supported_taxonomies = array();

            foreach ( $post_types as $post_type ) {
                // Post Type exists already
                if ( empty( $post_type[ 'object' ] ) && isset( $existing_post_types[ $post_type[ 'name' ] ] ) )
                    continue;

                if ( !empty( $post_type[ 'object' ] ) && isset( $existing_post_types[ $post_type[ 'object' ] ] ) )
                    continue;

                $post_type[ 'options' ][ 'name' ] = $post_type[ 'name' ];
                $post_type = array_merge( $post_type, (array) $post_type[ 'options' ] );

                // Labels
                $cpt_label = esc_html( pods_var_raw( 'label', $post_type, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $post_type ) ) ), null, true ) );
                $cpt_singular = esc_html( pods_var_raw( 'label_singular', $post_type, ucwords( str_replace( '_', ' ', pods_var_raw( 'label', $post_type, pods_var( 'name', $post_type ), null, true ) ) ), null, true ) );

                $cpt_labels[ 'name' ] = $cpt_label;
                $cpt_labels[ 'singular_name' ] = $cpt_singular;
                $cpt_labels[ 'menu_name' ] = pods_var_raw( 'label_menu_name', $post_type, '', null, true );
                $cpt_labels[ 'add_new' ] = pods_var_raw( 'label_add_new', $post_type, '', null, true );
                $cpt_labels[ 'add_new_item' ] = pods_var_raw( 'label_add_new_item', $post_type, '', null, true );
                $cpt_labels[ 'new_item' ] = pods_var_raw( 'label_new_item', $post_type, '', null, true );
                $cpt_labels[ 'edit' ] = pods_var_raw( 'label_edit', $post_type, '', null, true );
                $cpt_labels[ 'edit_item' ] = pods_var_raw( 'label_edit_item', $post_type, '', null, true );
                $cpt_labels[ 'view' ] = pods_var_raw( 'label_view', $post_type, '', null, true );
                $cpt_labels[ 'view_item' ] = pods_var_raw( 'label_view_item', $post_type, '', null, true );
                $cpt_labels[ 'all_items' ] = pods_var_raw( 'label_all_items', $post_type, '', null, true );
                $cpt_labels[ 'search_items' ] = pods_var_raw( 'label_search_items', $post_type, '', null, true );
                $cpt_labels[ 'not_found' ] = pods_var_raw( 'label_not_found', $post_type, '', null, true );
                $cpt_labels[ 'not_found_in_trash' ] = pods_var_raw( 'label_not_found_in_trash', $post_type, '', null, true );
                $cpt_labels[ 'parent' ] = pods_var_raw( 'label_parent', $post_type, '', null, true );
                $cpt_labels[ 'parent_item_colon' ] = pods_var_raw( 'label_parent_item_colon', $post_type, '', null, true );

                // Supported
                $cpt_supported = array(
                    'title' => (boolean) pods_var( 'supports_title', $post_type, false ),
                    'editor' => (boolean) pods_var( 'supports_editor', $post_type, false ),
                    'author' => (boolean) pods_var( 'supports_author', $post_type, false ),
                    'thumbnail' => (boolean) pods_var( 'supports_thumbnail', $post_type, false ),
                    'excerpt' => (boolean) pods_var( 'supports_excerpt', $post_type, false ),
                    'trackbacks' => (boolean) pods_var( 'supports_trackbacks', $post_type, false ),
                    'custom-fields' => (boolean) pods_var( 'supports_custom_fields', $post_type, false ),
                    'comments' => (boolean) pods_var( 'supports_comments', $post_type, false ),
                    'revisions' => (boolean) pods_var( 'supports_revisions', $post_type, false ),
                    'page-attributes' => (boolean) pods_var( 'supports_page_attributes', $post_type, false ),
                    'post-formats' => (boolean) pods_var( 'supports_post_formats', $post_type, false )
                );

                // WP needs something, if this was empty and none were enabled, it would show title+editor pre 3.5 :(
                $cpt_supports = array( '_bug_fix_pre_35' );

                foreach ( $cpt_supported as $cpt_support => $supported ) {
                    if ( true === $supported )
                        $cpt_supports[] = $cpt_support;
                }

                if ( 1 == count( $cpt_supports ) && version_compare( '3.5-alpha', $wp_version, '<' ) )
                    $cpt_supports = false;

                // Rewrite
                $cpt_rewrite = (boolean) pods_var( 'rewrite', $post_type, true );
                $cpt_rewrite_array = array(
                    'slug' => pods_var( 'rewrite_custom_slug', $post_type, str_replace( '_', '-', pods_var( 'name', $post_type ) ), null, true ),
                    'with_front' => (boolean) pods_var( 'rewrite_with_front', $post_type, true ),
                    'feeds' => (boolean) pods_var( 'rewrite_feeds', $post_type, (boolean) pods_var( 'has_archive', $post_type, false ) ),
                    'pages' => (boolean) pods_var( 'rewrite_pages', $post_type, true )
                );

                if ( false !== $cpt_rewrite )
                    $cpt_rewrite = $cpt_rewrite_array;

                $capability_type = pods_var( 'capability_type', $post_type, 'post' );

                if ( 'custom' == $capability_type )
                    $capability_type = pods_var( 'capability_type_custom', $post_type, 'post' );

                // Register Post Type
                $pods_post_types[ pods_var( 'name', $post_type ) ] = array(
                    'label' => $cpt_label,
                    'labels' => $cpt_labels,
                    'description' => esc_html( pods_var_raw( 'description', $post_type ) ),
                    'public' => (boolean) pods_var( 'public', $post_type, true ),
                    'publicly_queryable' => (boolean) pods_var( 'publicly_queryable', $post_type, (boolean) pods_var( 'public', $post_type, true ) ),
                    'exclude_from_search' => (boolean) pods_var( 'exclude_from_search', $post_type, ( (boolean) pods_var( 'public', $post_type, true ) ? false : true ) ),
                    'show_ui' => (boolean) pods_var( 'show_ui', $post_type, (boolean) pods_var( 'public', $post_type, true ) ),
                    'show_in_menu' => (boolean) pods_var( 'show_in_menu', $post_type, true ),
                    'show_in_admin_bar' => (boolean) pods_var( 'show_in_admin_bar', $post_type, (boolean) pods_var( 'show_in_menu', $post_type, true ) ),
                    'menu_position' => (int) pods_var( 'menu_position', $post_type, 20, null, true ),
                    'menu_icon' => pods_var( 'menu_icon', $post_type, null, null, true ),
                    'capability_type' => $capability_type,
                    //'capabilities' => $cpt_capabilities,
                    'map_meta_cap' => (boolean) pods_var( 'map_meta_cap', $post_type, true ),
                    'hierarchical' => (boolean) pods_var( 'hierarchical', $post_type, false ),
                    'supports' => $cpt_supports,
                    //'register_meta_box_cb' => array($this, 'manage_meta_box'),
                    //'permalink_epmask' => EP_PERMALINK,
                    'has_archive' => (boolean) pods_var( 'has_archive', $post_type, false ),
                    'rewrite' => $cpt_rewrite,
                    'query_var' => ( false !== (boolean) pods_var( 'query_var', $post_type, true ) ? pods_var( 'query_var_string', $post_type, pods_var( 'name', $post_type ), null, true ) : false ),
                    'can_export' => (boolean) pods_var( 'can_export', $post_type, true ),
                    'show_in_nav_menus' => (boolean) pods_var( 'show_in_nav_menus', $post_type, (boolean) pods_var( 'public', $post_type, true ) )
                );

                // Taxonomies
                $cpt_taxonomies = array();
                $_taxonomies = get_taxonomies();
                $_taxonomies = array_merge_recursive( $_taxonomies, $pods_taxonomies );
                $ignore = array( 'nav_menu', 'link_category', 'post_format' );

                foreach ( $_taxonomies as $taxonomy => $label ) {
                    if ( in_array( $taxonomy, $ignore ) )
                        continue;

                    if ( false !== (boolean) pods_var( 'built_in_taxonomies_' . $taxonomy, $post_type, false ) ) {
                        $cpt_taxonomies[] = $taxonomy;

                        if ( isset( $supported_post_types[ $taxonomy ] ) && !in_array( pods_var( 'name', $post_type ), $supported_post_types[ $taxonomy ] ) )
                            $supported_post_types[ $taxonomy ][] = pods_var( 'name', $post_type );
                    }
                }

                if ( isset( $supported_taxonomies[ pods_var( 'name', $post_type ) ] ) )
                    $supported_taxonomies[ pods_var( 'name', $post_type ) ] = array_merge( (array) $supported_taxonomies[ pods_var( 'name', $post_type ) ], $cpt_taxonomies );
                else
                    $supported_taxonomies[ pods_var( 'name', $post_type ) ] = $cpt_taxonomies;
            }

            foreach ( $taxonomies as $taxonomy ) {
                // Taxonomy exists already
                if ( empty( $taxonomy[ 'object' ] ) && isset( $existing_taxonomies[ $taxonomy[ 'name' ] ] ) )
                    continue;

                if ( !empty( $taxonomy[ 'object' ] ) && isset( $existing_taxonomies[ $taxonomy[ 'object' ] ] ) )
                    continue;

                $taxonomy[ 'options' ][ 'name' ] = $taxonomy[ 'name' ];
                $taxonomy = array_merge( $taxonomy, (array) $taxonomy[ 'options' ] );

                // Labels
                $ct_label = esc_html( pods_var_raw( 'label', $taxonomy, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $taxonomy ) ) ), null, true ) );
                $ct_singular = esc_html( pods_var_raw( 'label_singular', $taxonomy, ucwords( str_replace( '_', ' ', pods_var_raw( 'label', $taxonomy, pods_var_raw( 'name', $taxonomy ), null, true ) ) ), null, true ) );

                $ct_labels[ 'name' ] = $ct_label;
                $ct_labels[ 'singular_name' ] = $ct_singular;
                $ct_labels[ 'menu_name' ] = pods_var_raw( 'label_menu_name', $taxonomy, '', null, true );
                $ct_labels[ 'search_items' ] = pods_var_raw( 'label_search_items', $taxonomy, '', null, true );
                $ct_labels[ 'popular_items' ] = pods_var_raw( 'label_popular_items', $taxonomy, '', null, true );
                $ct_labels[ 'all_items' ] = pods_var_raw( 'label_all_items', $taxonomy, '', null, true );
                $ct_labels[ 'parent_item' ] = pods_var_raw( 'label_parent_item', $taxonomy, '', null, true );
                $ct_labels[ 'parent_item_colon' ] = pods_var_raw( 'label_parent_item_colon', $taxonomy, '', null, true );
                $ct_labels[ 'edit_item' ] = pods_var_raw( 'label_edit_item', $taxonomy, '', null, true );
                $ct_labels[ 'update_item' ] = pods_var_raw( 'label_update_item', $taxonomy, '', null, true );
                $ct_labels[ 'add_new_item' ] = pods_var_raw( 'label_add_new_item', $taxonomy, '', null, true );
                $ct_labels[ 'new_item_name' ] = pods_var_raw( 'label_new_item_name', $taxonomy, '', null, true );
                $ct_labels[ 'separate_items_with_commas' ] = pods_var_raw( 'label_separate_items_with_commas', $taxonomy, '', null, true );
                $ct_labels[ 'add_or_remove_items' ] = pods_var_raw( 'label_add_or_remove_items', $taxonomy, '', null, true );
                $ct_labels[ 'choose_from_most_used' ] = pods_var_raw( 'label_choose_from_most_used', $taxonomy, '', null, true );

                // Rewrite
                $ct_rewrite = (boolean) pods_var( 'rewrite', $taxonomy, true );
                $ct_rewrite_array = array(
                    'slug' => pods_var( 'rewrite_custom_slug', $taxonomy, str_replace( '_', '-', pods_var( 'name', $taxonomy ) ), null, true ),
                    'with_front' => (boolean) pods_var( 'rewrite_with_front', $taxonomy, true ),
                    'hierarchical' => (boolean) pods_var( 'rewrite_hierarchical', $taxonomy, (boolean) pods_var( 'hierarchical', $taxonomy, false ) )
                );

                if ( false !== $ct_rewrite )
                    $ct_rewrite = $ct_rewrite_array;

                // Register Taxonomy
                $pods_taxonomies[ pods_var( 'name', $taxonomy ) ] = array(
                    'label' => $ct_label,
                    'labels' => $ct_labels,
                    'public' => (boolean) pods_var( 'public', $taxonomy, true ),
                    'show_in_nav_menus' => (boolean) pods_var( 'show_in_nav_menus', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ),
                    'show_ui' => (boolean) pods_var( 'show_ui', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ),
                    'show_tagcloud' => (boolean) pods_var( 'show_tagcloud', $taxonomy, (boolean) pods_var( 'show_ui', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ) ),
                    'hierarchical' => (boolean) pods_var( 'hierarchical', $taxonomy, false ),
                    'update_count_callback' => pods_var( 'update_count_callback', $taxonomy, null, null, true ),
                    'query_var' => ( false !== (boolean) pods_var( 'query_var', $taxonomy, true ) ? pods_var( 'query_var_string', $taxonomy, pods_var( 'name', $taxonomy ), null, true ) : false ),
                    'rewrite' => $ct_rewrite,
                    'sort' => (boolean) pods_var( 'sort', $taxonomy, false )
                );

                if ( is_array( $ct_rewrite ) && !$pods_taxonomies[ pods_var( 'name', $taxonomy ) ][ 'query_var' ] )
                    $pods_taxonomies[ pods_var( 'name', $taxonomy ) ] = pods_var( 'query_var_string', $taxonomy, pods_var( 'name', $taxonomy ), null, true );

                // Post Types
                $ct_post_types = array();
                $_post_types = get_post_types();
                $_post_types = array_merge_recursive( $_post_types, $pods_post_types );
                $ignore = array( 'attachment', 'revision', 'nav_menu_item' );

                foreach ( $_post_types as $post_type => $options ) {
                    if ( in_array( $post_type, $ignore ) )
                        continue;

                    if ( false !== (boolean) pods_var( 'built_in_post_types_' . $post_type, $taxonomy, false ) ) {
                        $ct_post_types[] = $post_type;

                        if ( isset( $supported_taxonomies[ $post_type ] ) && !in_array( pods_var( 'name', $taxonomy ), $supported_taxonomies[ $post_type ] ) )
                            $supported_taxonomies[ $post_type ][] = pods_var( 'name', $taxonomy );
                    }
                }

                if ( isset( $supported_post_types[ pods_var( 'name', $taxonomy ) ] ) )
                    $supported_post_types[ pods_var( 'name', $taxonomy ) ] = array_merge( $supported_post_types[ pods_var( 'name', $taxonomy ) ], $ct_post_types );
                else
                    $supported_post_types[ pods_var( 'name', $taxonomy ) ] = $ct_post_types;
            }

            $pods_post_types = apply_filters( 'pods_wp_post_types', $pods_post_types );
            $pods_taxonomies = apply_filters( 'pods_wp_taxonomies', $pods_taxonomies );

            $supported_post_types = apply_filters( 'pods_wp_supported_post_types', $supported_post_types );
            $supported_taxonomies = apply_filters( 'pods_wp_supported_taxonomies', $supported_taxonomies );

            foreach ( $pods_taxonomies as $taxonomy => $options ) {
                $ct_post_types = null;
                if ( isset( $supported_post_types[ $taxonomy ] ) && !empty( $supported_post_types[ $taxonomy ] ) )
                    $ct_post_types = $supported_post_types[ $taxonomy ];

                $pods_cpt_ct[ 'taxonomies' ][ $taxonomy ] = array(
                    'post_types' => $ct_post_types,
                    'options' => $options
                );
            }

            foreach ( $pods_post_types as $post_type => $options ) {
                if ( isset( $supported_taxonomies[ $post_type ] ) && !empty( $supported_taxonomies[ $post_type ] ) )
                    $options[ 'taxonomies' ] = $supported_taxonomies[ $post_type ];

                $pods_cpt_ct[ 'post_types' ][ $post_type ] = $options;
            }

            pods_transient_set( 'pods_wp_cpt_ct', $pods_cpt_ct );
        }

        foreach ( $pods_cpt_ct[ 'taxonomies' ] as $taxonomy => $options ) {
            $ct_post_types = $options[ 'post_types' ];
            $options = $options[ 'options' ];

            $options = apply_filters( 'pods_register_taxonomy_' . $taxonomy, $options );

            $options = self::object_label_fix( $options, 'taxonomy' );

            register_taxonomy( $taxonomy, $ct_post_types, $options );
        }

        foreach ( $pods_cpt_ct[ 'post_types' ] as $post_type => $options ) {
            $options = apply_filters( 'pods_register_post_type_' . $post_type, $options );

            $options = self::object_label_fix( $options, 'post_type' );

            register_post_type( $post_type, $options );
        }

        $flush = pods_transient_get( 'pods_flush_rewrites' );

        if ( 1 == $flush ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
            $wp_rewrite->init();

            pods_transient_set( 'pods_flush_rewrites', 0 );
        }
    }

    /**
     * Update Post Type messages
     *
     * @param array $messages
     *
     * @return array
     * @since 2.0.2
     */
    public function setup_updated_messages ( $messages ) {
        global $post, $post_ID;

        $post_types = PodsMeta::$post_types;
        $existing_post_types = get_post_types();

        $pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

        if ( empty( $pods_cpt_ct ) || empty( $post_types ) )
            return $messages;

        foreach ( $post_types as $post_type ) {
            if ( !isset( $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ] ) )
                continue;

            $labels = self::object_label_fix( $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ], 'post_type' );
            $labels = $labels[ 'labels' ];

            $messages[ $post_type[ 'name' ] ] = array(
                1 => sprintf( __( '%s updated. <a href="%s">%s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( get_permalink( $post_ID ) ), $labels[ 'view_item' ] ),
                2 => __( 'Custom field updated.', 'pods' ),
                3 => __( 'Custom field deleted.', 'pods' ),
                4 => sprintf( __( '%s updated.', 'pods' ), $labels[ 'singular_name' ] ),
                /* translators: %s: date and time of the revision */
                5 => isset( $_GET[ 'revision' ] ) ? sprintf( __( '%s restored to revision from %s', 'pods' ), $labels[ 'singular_name' ], wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
                6 => sprintf( __( '%s published. <a href="%s">%s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( get_permalink( $post_ID ) ), $labels[ 'view_item' ] ),
                7 => sprintf( __( '%s saved.', 'pods' ), $labels[ 'singular_name' ] ),
                8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels[ 'singular_name' ] ),
                9 => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'pods' ),
                    $labels[ 'singular_name' ],
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
                    esc_url( get_permalink( $post_ID ) ),
                    $labels[ 'singular_name' ]
                ),
                10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels[ 'singular_name' ] )
            );

            if ( false === (boolean) $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ][ 'public' ] ) {
                $messages[ $post_type[ 'name' ] ][ 1 ] = sprintf( __( '%s updated.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 6 ] = sprintf( __( '%s published.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 8 ] = sprintf( __( '%s submitted.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 9 ] = sprintf( __( '%s scheduled for: <strong>%1$s</strong>.', 'pods' ),
                    $labels[ 'singular_name' ],
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
                );
                $messages[ $post_type[ 'name' ] ][ 10 ] = sprintf( __( '%s draft updated.', 'pods' ), $labels[ 'singular_name' ] );
            }
        }

        return $messages;
    }

    /**
     * @param $args
     * @param string $type
     *
     * @return array
     */
    public static function object_label_fix ( $args, $type = 'post_type' ) {
        if ( empty( $args ) || !is_array( $args ) )
            $args = array();

        if ( !isset( $args[ 'labels' ] ) || !is_array( $args[ 'labels' ] ) )
            $args[ 'labels' ] = array();

        $label = pods_var_raw( 'name', $args[ 'labels' ], pods_var_raw( 'label', $args, __( 'Items', 'pods' ), null, true ), null, true );
        $singular_label = pods_var_raw( 'singular_name', $args[ 'labels' ], pods_var_raw( 'label_singular', $args, __( 'Item', 'pods' ), null, true ), null, true );

        $labels = $args[ 'labels' ];

        $labels[ 'name' ] = $label;
        $labels[ 'singular_name' ] = $singular_label;

        if ( 'post_type' == $type ) {
            $labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $labels, $label, null, true );
            $labels[ 'add_new' ] = pods_var_raw( 'add_new', $labels, __( 'Add New', 'pods' ), null, true );
            $labels[ 'add_new_item' ] = pods_var_raw( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'new_item' ] = pods_var_raw( 'new_item', $labels, sprintf( __( 'New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'edit' ] = pods_var_raw( 'edit', $labels, __( 'Edit', 'pods' ), null, true );
            $labels[ 'edit_item' ] = pods_var_raw( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'view' ] = pods_var_raw( 'view', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'view_item' ] = pods_var_raw( 'view_item', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'all_items' ] = pods_var_raw( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), null, true );
            $labels[ 'search_items' ] = pods_var_raw( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), null, true );
            $labels[ 'not_found' ] = pods_var_raw( 'not_found', $labels, sprintf( __( 'No %s Found', 'pods' ), $label ), null, true );
            $labels[ 'not_found_in_trash' ] = pods_var_raw( 'not_found_in_trash', $labels, sprintf( __( 'No %s Found in Trash', 'pods' ), $label ), null, true );
            $labels[ 'parent' ] = pods_var_raw( 'parent', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'parent_item_colon' ] = pods_var_raw( 'parent_item_colon', $labels, sprintf( __( 'Parent %s:', 'pods' ), $singular_label ), null, true );
        }
        elseif ( 'taxonomy' == $type ) {
            $labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $labels, $label, null, true );
            $labels[ 'search_items' ] = pods_var_raw( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), null, true );
            $labels[ 'popular_items' ] = pods_var_raw( 'popular_items', $labels, sprintf( __( 'Popular %s', 'pods' ), $label ), null, true );
            $labels[ 'all_items' ] = pods_var_raw( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), null, true );
            $labels[ 'parent_item' ] = pods_var_raw( 'parent_item', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'parent_item_colon' ] = pods_var_raw( 'parent_item_colon', $labels, sprintf( __( 'Parent %s :', 'pods' ), $singular_label ), null, true );
            $labels[ 'edit_item' ] = pods_var_raw( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'update_item' ] = pods_var_raw( 'update_item', $labels, sprintf( __( 'Update %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'add_new_item' ] = pods_var_raw( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'new_item_name' ] = pods_var_raw( 'new_item_name', $labels, sprintf( __( 'New %s Name', 'pods' ), $singular_label ), null, true );
            $labels[ 'separate_items_with_commas' ] = pods_var_raw( 'separate_items_with_commas', $labels, sprintf( __( 'Separate %s with commas', 'pods' ), $label ), null, true );
            $labels[ 'add_or_remove_items' ] = pods_var_raw( 'add_or_remove_items', $labels, sprintf( __( 'Add or remove %s', 'pods' ), $label ), null, true );
            $labels[ 'choose_from_most_used' ] = pods_var_raw( 'choose_from_most_used', $labels, sprintf( __( 'Choose from the most used %s', 'pods' ), $label ), null, true );
        }

        $args[ 'labels' ] = $labels;

        return $args;
    }

    /**
     * Activate and Install
     */
    public function activate_install () {
        register_activation_hook( PODS_DIR . 'init.php', array( $this, 'activate' ) );
        register_deactivation_hook( PODS_DIR . 'init.php', array( $this, 'deactivate' ) );

        add_action( 'wpmu_new_blog', array( $this, 'new_blog' ), 10, 6 );

        if ( empty( self::$version ) || version_compare( self::$version, PODS_VERSION, '<' ) )
            $this->setup();
    }

    /**
     *
     */
    public function activate () {
        global $wpdb;

        if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $_GET[ 'networkwide' ] ) && 1 == $_GET[ 'networkwide' ] ) {
            $_blog_ids = $wpdb->get_col( "SELECT `blog_id` FROM `{$wpdb->blogs}`" );

            foreach ( $_blog_ids as $_blog_id ) {
                $this->setup( $_blog_id );
            }
        }
        else
            $this->setup();
    }

    /**
     *
     */
    public function deactivate () {
        pods_api()->cache_flush_pods();
    }

    /**
     * @param $_blog_id
     * @param $user_id
     * @param $domain
     * @param $path
     * @param $site_id
     * @param $meta
     */
    public function new_blog ( $_blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() && is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) )
            $this->setup( $_blog_id );
    }

    /**
     * @param null $_blog_id
     */
    public function setup ( $_blog_id = null ) {
        global $wpdb;

        // Switch DB table prefixes
        if ( null !== $_blog_id && $_blog_id != $wpdb->blogid )
            switch_to_blog( pods_absint( $_blog_id ) );
        else
            $_blog_id = null;

        // Setup DB tables
        $pods_version = get_option( 'pods_framework_version' );

        // Update Pods and run any required DB updates
        if ( 0 < strlen( $pods_version ) ) {
            if ( !self::$upgrade_needed ) {
                if ( PODS_VERSION != $pods_version && false !== apply_filters( 'pods_update_run', null, PODS_VERSION, $pods_version, $_blog_id ) && !isset( $_GET[ 'pods_bypass_update' ] )) {
                    do_action( 'pods_update', PODS_VERSION, $pods_version, $_blog_id );

                    // Update 2.0 alpha / beta sites
                    if ( version_compare( '2.0.0-a-1', $pods_version, '<=' ) && version_compare( $pods_version, '2.0.0-b-15', '<=' ) )
                        include( PODS_DIR . 'sql/update-2.0-beta.php' );

                    include( PODS_DIR . 'sql/update.php' );

                    do_action( 'pods_update_post', PODS_VERSION, $pods_version, $_blog_id );
                }

                update_option( 'pods_framework_version_last', $pods_version );

                self::$version_last = $pods_version;
            }
        }
        // Install Pods
        else {
            pods_upgrade()->install( $_blog_id );

            $old_version = get_option( 'pods_version' );

            if ( !empty( $old_version ) ) {
                if ( false === strpos( $old_version, '.' ) )
                    $old_version = pods_version_to_point( $old_version );

                update_option( 'pods_framework_version_last', $old_version );

                self::$version_last = $old_version;
            }
        }

        update_option( 'pods_framework_version', PODS_VERSION );

        pods_api()->cache_flush_pods();

        // Restore DB table prefix (if switched)
        if ( null !== $_blog_id )
            restore_current_blog();
    }

    /**
     * @param null $_blog_id
     */
    public function reset ( $_blog_id = null ) {
        global $wpdb;

        // Switch DB table prefixes
        if ( null !== $_blog_id && $_blog_id != $wpdb->blogid )
            switch_to_blog( pods_absint( $_blog_id ) );
        else
            $_blog_id = null;

        $api = pods_api();

        $pods = $api->load_pods();

        foreach ( $pods as $pod ) {
            $api->delete_pod( array( 'id' => $pod[ 'id' ] ) );
        }

        $templates = $api->load_templates();

        foreach ( $templates as $template ) {
            $api->delete_template( array( 'id' => $template[ 'id' ] ) );
        }

        $pages = $api->load_pages();

        foreach ( $pages as $page ) {
            $api->delete_page( array( 'id' => $page[ 'id' ] ) );
        }

        $helpers = $api->load_helpers();

        foreach ( $helpers as $helper ) {
            $api->delete_helper( array( 'id' => $helper[ 'id' ] ) );
        }

        $tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_N );

        if ( !empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $table = $table[ 0 ];

                pods_query( "DROP TABLE `{$table}`", false );
            }
        }

        // Remove any orphans
        $wpdb->query( "
                DELETE `p`, `pm`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON `pm`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` LIKE '_pods_%'
            " );

        delete_option( 'pods_framework_version' );

        delete_option( 'pods_framework_upgrade_2_0' );
        delete_option( 'pods_framework_upgraded_1_x' );

        delete_option( 'pods_component_settings' );

        $api->cache_flush_pods();

        delete_transient( 'pods_flush_rewrites' );

        self::$version = '';

        // Restore DB table prefix (if switched)
        if ( null !== $_blog_id )
            restore_current_blog();
    }

    /**
     * Delete Attachments from relationships
     *
     * @param int $_ID
     */
    public function delete_attachment ( $_ID ) {
        global $wpdb;

        $_ID = (int) $_ID;

        do_action( 'pods_delete_attachment', $_ID );

        $file_types = "'" . implode( "', '", apply_filters( 'pods_file_field_types', array( 'file', 'avatar' ) ) ) . "'";

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            $sql = "
                DELETE `rel` FROM `@wp_podsrel` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                        AND ( `p`.`ID` = `rel`.`field_id` OR `p`.`ID` = `rel`.`related_field_id` )
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`item_id` = {$_ID}";

            pods_query( $sql, false );
        }

        // Post Meta
        if ( !empty( PodsMeta::$post_types ) ) {
            $sql =  "
                DELETE `rel`
                FROM `@wp_postmeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                        AND ( `p`.`ID` = `rel`.`field_id` OR `p`.`ID` = `rel`.`related_field_id` )
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }

        // User Meta
        if ( !empty( PodsMeta::$user ) ) {
            $sql = "
                DELETE `rel`
                FROM `@wp_usermeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                        AND ( `p`.`ID` = `rel`.`field_id` OR `p`.`ID` = `rel`.`related_field_id` )
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }

        // Comment Meta
        if ( !empty( PodsMeta::$comment ) ) {
            $sql = "
                DELETE `rel`
                FROM `@wp_commentmeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                        AND ( `p`.`ID` = `rel`.`field_id` OR `p`.`ID` = `rel`.`related_field_id` )
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }
    }

    /**
     * Register widgets for Pods
     */
    public function register_widgets () {
        $widgets = array(
            'PodsWidgetSingle',
            'PodsWidgetList',
            'PodsWidgetField',
            'PodsWidgetForm'
        );

        foreach ( $widgets as $widget ) {
            if ( !file_exists( PODS_DIR . 'classes/widgets/' . $widget . '.php' ) )
                continue;

            require_once PODS_DIR . 'classes/widgets/' . $widget . '.php';

            register_widget( $widget );
        }
    }

    /**
     * Add Admin Bar links
     */
    public function admin_bar_links () {
        global $wp_admin_bar, $pods;

        if ( !is_user_logged_in() || !is_admin_bar_showing() )
            return;

        $all_pods = pods_api()->load_pods( array( 'type' => 'pod' ) );

        // Add New item links for all pods
        foreach ( $all_pods as $pod ) {
            if ( 0 == $pod[ 'options' ][ 'show_in_menu' ] )
                continue;

            if ( !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) && !current_user_can( 'pods_content' ) && !current_user_can( 'pods_add_' . $pod[ 'name' ] ) )
                continue;

            $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );

            $wp_admin_bar->add_node( array(
                'id' => 'new-pod-' . $pod[ 'name' ],
                'title' => $singular_label,
                'parent' => 'new-content',
                'href' => admin_url( 'admin.php?page=pods-manage-' . $pod[ 'name' ] . '&action=add' )
            ) );
        }

        // Add edit link if we're on a pods page
        if ( is_object( $pods ) && !is_wp_error( $pods ) && !empty( $pods->id ) && isset( $pods->pod_data ) && !empty( $pods->pod_data ) && 'pod' == $pods->pod_data[ 'type' ] ) {
            $pod = $pods->pod_data;

            if ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) || current_user_can( 'pods_content' ) || current_user_can( 'pods_edit_' . $pod[ 'name' ] ) ) {
                $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );

                $wp_admin_bar->add_node( array(
                    'title' => sprintf( __( 'Edit %s', 'pods' ), $singular_label ),
                    'id' => 'edit-pod',
                    'href' => admin_url( 'admin.php?page=pods-manage-' . $pod[ 'name' ] . '&action=edit&id=' . $pods->id() )
                ) );
            }
        }

    }
}