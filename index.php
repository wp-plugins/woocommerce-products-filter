<?php
/*
  Plugin Name: WooCommerce Products Filter
  Plugin URI: http://woocommerce-filter.com/
  Description: WooCommerce Products Filter. Easy & Quick!
  Author: realmag777
  Version: 1.0.2
  Author URI: http://www.pluginus.net/
 */
define('WOOF_PATH', plugin_dir_path(__FILE__));
define('WOOF_LINK', plugin_dir_url(__FILE__));
define('WOOF_PLUGIN_NAME', plugin_basename(__FILE__));
include WOOF_PATH . 'helper.php';

//26-01-2015
final class WOOF {

    public $settings = array();
    public $version = '1.0.2';
    public $html_types = array(
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'select' => 'Drop-down',
        'mselect' => 'Multi drop-down',
    );

    public function __construct() {
        $this->init_settings();
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        //***
        $first_init = get_option('woof_first_init');
        if ($first_init != 1) {
            update_option('woof_first_init', 1);
            update_option('woof_set_automatically', 0);
            update_option('woof_show_count', 0);
        }
        //***
        load_plugin_textdomain('woocommerce-products-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');
        add_filter('plugin_action_links_' . WOOF_PLUGIN_NAME, array($this, 'plugin_action_links'), 50);
        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 50);
        add_action('woocommerce_settings_tabs_woof', array($this, 'print_plugin_options'), 50);
        //add_action('woocommerce_update_options_settings_tab_woof', array($this, 'update_settings'), 50);
        add_action('admin_head', array($this, 'admin_head'), 1);
        //+++
        add_action('wp_head', array($this, 'wp_head'), 999);
        add_shortcode('woof', array($this, 'woof_shortcode'));
        add_action('widgets_init', array($this, 'widgets_init'));
        //+++
        add_filter('widget_text', 'do_shortcode');
        add_filter('parse_query', array($this, "parse_query"), 1);
    }

    public static function widgets_init() {
        register_widget('WOOF_Widget');
    }

    /**
     * Show action links on the plugin screen
     */
    public function plugin_action_links($links) {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=woof') . '">' . __('Settings', 'woocommerce-products-filter') . '</a>',
            '<a target="_blank" href="' . esc_url('http://woocommerce-filter.com/documentation/') . '">' . __('Documentation', 'woocommerce-products-filter') . '</a>'
                ), $links);

        return $links;
    }

    public function parse_query($wp_query) {
        if ($wp_query->is_main_query()) {
            if (isset($_REQUEST['swoof'])) {
                $wp_query->set('post_type', 'product');
                $wp_query->is_post_type_archive = true;
                $wp_query->is_tax = false;
                $wp_query->is_tag = false;
                $wp_query->is_home = false;
                $wp_query->is_single = false;
                $wp_query->is_posts_page = false;
                $wp_query->is_page = false;
                $wp_query->is_search = false; //!!!
            }
        }

        return $wp_query;
    }

    public function woocommerce_settings_tabs_array($tabs) {
        $tabs['woof'] = __('Products Filter', 'woocommerce-products-filter');
        return $tabs;
    }

    public function admin_head() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('woof-admin', WOOF_LINK . 'js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
    }

    public function wp_head() {
        global $wp_query;
        wp_enqueue_style('woof', WOOF_LINK . 'css/front.css');
        //***
        ?>
        <script type="text/javascript">

        <?php
        $icheck_skin = $this->settings['icheck_skin'];
        $icheck_skin = explode('_', $icheck_skin);
        ?>
            var icheck_skin = {};
            icheck_skin.skin = "<?php echo $icheck_skin[0] ?>";
            icheck_skin.color = "<?php echo $icheck_skin[1] ?>";

            var woof_current_page_link = "<?php
        $link = "";
        if (isset($_SERVER['SCRIPT_URI'])) {
            $link = $_SERVER['SCRIPT_URI'];
        }

        $page_id = get_option('woocommerce_shop_page_id');
        if ($page_id > 0) {
            $link = get_permalink($page_id);
        }
//+++

        /*
          if (is_product_taxonomy()) {
          $link = get_term_link($wp_query->queried_object->term_id, $wp_query->queried_object->taxonomy);
          }
         * 
         */

        if (!empty($wp_query->query)) {
            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product'))) {
                $taxes = $wp_query->query;
                if (isset($taxes['paged'])) {
                    unset($taxes['paged']);
                }
                foreach ($taxes as $key => $value) {
                    if (in_array($key, array_keys($_GET))) {
                        unset($taxes[$key]);
                    }
                }
                if (!empty($taxes) AND is_array($taxes)) {
                    $t = array_keys($taxes);
                    $v = array_values($taxes);
                    //***
                    $link = get_term_link($v[0], $t[0]);
                }
            }
        }
        /*
          if (isset($wp_query->query_vars['taxonomy'])) {
          if (in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product'))) {
          $link = get_term_link($wp_query->query_vars['term'], $wp_query->query_vars['taxonomy']);
          }
          }
         */
//+++

        if (is_string($link)) {
            echo $link;
        } else {
            $page_id = get_option('woocommerce_shop_page_id');
            if ($page_id > 0) {
                $link = get_permalink($page_id);
            } else {
                $link = home_url();
            }
            echo $link;
        }

        $GLOBALS['woof_link'] = $link;
        ?>";
            var woof_current_values = '<?php echo json_encode($_GET); ?>';
            jQuery(function () {
                woof_current_values = jQuery.parseJSON(woof_current_values);
            });
        </script>
        <?php
        if (!$icheck_skin) {
            $icheck_skin = 'square_green';
        }
        wp_enqueue_script('icheck-jquery', WOOF_LINK . 'js/icheck/icheck.min.js', array('jquery'));
        //wp_enqueue_style('icheck-jquery', self::get_application_uri() . 'js/icheck/all.css');
        wp_enqueue_style('icheck-jquery-color', WOOF_LINK . 'js/icheck/skins/' . $icheck_skin[0] . '/' . $icheck_skin[1] . '.css');
        if (is_shop()) {
            add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'));
        }

        //***
        wp_enqueue_script('woof_radio_html_items', WOOF_LINK . 'js/html_types/radio.js', array('jquery'));
        wp_enqueue_script('woof_checkbox_html_items', WOOF_LINK . 'js/html_types/checkbox.js', array('jquery'));
        wp_enqueue_script('woof_select_html_items', WOOF_LINK . 'js/html_types/select.js', array('jquery'));
        wp_enqueue_script('woof_mselect_html_items', WOOF_LINK . 'js/html_types/mselect.js', array('jquery'));
        wp_enqueue_script('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'));
        wp_enqueue_style('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.min.css');
    }

    //[woof]
    public function woof_shortcode($atts) {
        $args = array();
        $args['taxonomies'] = array();
        $taxonomies = $this->get_taxonomies();
        $allow_taxonomies = (array) $this->settings['tax'];
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $tax_key => $tax) {
                if (!in_array($tax_key, array_keys($allow_taxonomies))) {
                    continue;
                }
                //+++
                $args['woof_settings'] = get_option('woof_settings');
                $args['taxonomies_info'][$tax_key] = $tax;
                $args['taxonomies'][$tax_key] = WOOF_HELPER::get_terms($tax_key);
            }
        }
        return $this->render_html(WOOF_PATH . 'views/woof.php', $args);
    }

    public function woocommerce_before_shop_loop() {
        $woof_set_automatically = (int) get_option('woof_set_automatically');
        if ($woof_set_automatically == 1) {
            echo '<div class="woof_auto_show">';
            echo do_shortcode('[woof]');
            echo '</div>';
        }
    }

    public function print_plugin_options() {
        if (isset($_POST['woof_settings'])) {
            WC_Admin_Settings::save_fields($this->get_options());
            update_option('woof_settings', $_POST['woof_settings']);
            $this->init_settings();
        }
        //+++
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('woof', WOOF_LINK . 'js/plugin_options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
        wp_enqueue_style('woof', WOOF_LINK . 'css/plugin_options.css');

        $args = array("woof_settings" => get_option('woof_settings'));
        echo $this->render_html(WOOF_PATH . 'views/plugin_options.php', $args);
    }

    private function init_settings() {
        $this->settings = get_option('woof_settings', array());
    }

    private function get_taxonomies() {
        $taxonomies = get_object_taxonomies('product', 'objects');
        unset($taxonomies['product_shipping_class']);
        unset($taxonomies['product_type']);
        return $taxonomies;
    }

    public function get_options() {
        $options = array
            (array(
                'name' => __('Products Filter Options', 'woocommerce-products-filter') . ' ' . $this->version,
                'type' => 'title',
                'desc' => '',
                'id' => 'woof_general_settings'
            ),
            array(
                'name' => __('Set filter automatically', 'woocommerce-products-filter'),
                'desc' => __('Set filter automatically on the shop page', 'woocommerce-products-filter'),
                'id' => 'woof_set_automatically',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    1 => __('Yes', 'woocommerce-products-filter'),
                    0 => __('No', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show count', 'woocommerce-products-filter'),
                'desc' => __('Show count of items near taxonomies terms on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_count',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Dynamic recount', 'woocommerce-products-filter'),
                'desc' => __('Show count of items near taxonomies terms on the front dynamically. Must be switched on "Show count"', 'woocommerce-products-filter'),
                'id' => 'woof_show_count_dynamic',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Hide empty terms', 'woocommerce-products-filter'),
                'desc' => __('Hide empty terms in "Dynamic recount" mode', 'woocommerce-products-filter'),
                'id' => 'woof_hide_dynamic_empty_pos',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array('type' => 'sectionend', 'id' => 'woof_general_settings')
        );

        return apply_filters('wc_settings_tab_woof_settings', $options);
    }

    //for dynamic count
    public static function dynamic_count($curr_term, $type) {
        $show_count = false;

        if (isset($_GET['min_price']) AND isset($_GET['max_price'])) {
            $show_count = true;
        }

        if (isset($_GET['swoof'])) {
            $show_count = true;
        }

        if (isset($_REQUEST['WOOF_IS_TAX_PAGE'])) {
            $show_count = true;
        }

        if ($show_count === false) {
            return $curr_term['count'];
        }

        //+++
        global $wp_query;
        $taxonomies = $wp_query->tax_query->queries;
        //+++
        switch ($type) {
            case 'radio':
            case 'select':
                $is_in_query = -1;
                foreach ($taxonomies as $k => $tax_block) {
                    if ($tax_block['taxonomy'] == $curr_term['taxonomy']) {
                        $is_in_query = $k;
                    }
                }
                //***
                if ($is_in_query > -1) {
                    $taxonomies[$is_in_query]['terms'] = array($curr_term['slug']);
                } else {
                    $taxonomies[] = array(
                        'taxonomy' => $curr_term['taxonomy'],
                        'terms' => array($curr_term['slug']),
                        'include_children' => 1,
                        'field' => 'slug',
                        'operator' => 'IN'
                    );
                }

                break;

            default:
                //checkbox, mselect
                $is_in_query = false;
                $is_in_query_count = -1;
                foreach ($taxonomies as $k => $tax_block) {
                    if ($tax_block['taxonomy'] == $curr_term['taxonomy']) {
                        $is_in_query = true;
                        if (in_array($curr_term['slug'], $tax_block['terms'])) {
                            $is_in_query_count = $k;
                        }
                    }
                }
                //***
                if (!$is_in_query) {
                    $taxonomies[] = array(
                        'taxonomy' => $curr_term['taxonomy'],
                        'terms' => array($curr_term['slug']),
                        'include_children' => 1,
                        'field' => 'slug',
                        'operator' => 'IN'
                    );
                }
                break;
        }



        //***
        $args = array(
            'nopaging' => true,
            'fields' => 'ids'
        );
        $args['tax_query'] = $taxonomies;
        $args['meta_query'] = $wp_query->meta_query->queries;

        //check for price
        if (isset($_GET['min_price']) AND isset($_GET['max_price'])) {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => array($_GET['min_price'], $_GET['max_price']),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );

            $args['meta_query']['relation'] = 'AND';
        }

        //+++


        $query = new WP_QueryWoofCounter($args);
        $posts_count = $query->found_posts;
        unset($query);
        //***
        return $posts_count;
    }

    public function render_html($pagepath, $data = array()) {
        @extract($data);
        ob_start();
        include($pagepath);
        return ob_get_clean();
    }

}

//***

class WP_QueryWoofCounter extends WP_Query {

    function set_found_posts($q, $limits) {
        return false;
    }

}

//***

$WOOF = new WOOF();
$GLOBALS['WOOF'] = $WOOF;
add_action('init', array($WOOF, 'init'), 1);

class WOOF_Widget extends WP_Widget {

//Widget Setup
    function __construct() {
//Basic settings
        $settings = array('classname' => __CLASS__, 'description' => __('WooCommerce Products Filter by realmag777', 'woocommerce-products-filter'));

//Creation
        $this->WP_Widget(__CLASS__, __('WooCommerce Products Filter', 'woocommerce-products-filter'), $settings);
    }

//Widget view
    function widget($args, $instance) {
        $args['instance'] = $instance;
        $args['sidebar_id'] = $args['id'];
        $args['sidebar_name'] = $args['name'];
        ?>
        <div class="widget widget-woof">
            <?php if (!empty($instance['title'])): ?>
                <h3 class="widget-title"><?php echo $instance['title'] ?></h3>
            <?php endif; ?>
            <?php echo do_shortcode('[woof]'); ?>
        </div>
        <?php
    }

//Update widget
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

//Widget form
    function form($instance) {
//Defaults
        $defaults = array(
            'title' => __('WooCommerce Products Filter', 'woocommerce-products-filter')
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'woocommerce-products-filter') ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
        </p>
        <?php
    }

}
