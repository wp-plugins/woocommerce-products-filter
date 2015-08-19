<?php
/*
  Plugin Name: WOOF - WooCommerce Products Filter
  Plugin URI: http://woocommerce-filter.com/
  Description: WOOF - WooCommerce Products Filter. Easy & Quick!
  Requires at least: WP 4.1.0
  Tested up to: WP 4.3.0
  Author: realmag777
  Author URI: http://pluginus.net/
  Version: 1.1.1.1
  Author URI: http://www.pluginus.net/
  Tags: filter,search,woocommerce,products filter,filter of products
  Text Domain: woocommerce-products-filter
  Domain Path: /languages
  Forum URI: #
 */


if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
}

//https://wordpress.org/support/topic/filtering-by-attributes-stopped-working-after-update-to-232
if (!defined('WOOF_PATH'))
{
    define('WOOF_PATH', plugin_dir_path(__FILE__));
}
define('WOOF_LINK', plugin_dir_url(__FILE__));
define('WOOF_PLUGIN_NAME', plugin_basename(__FILE__));
//classes
include plugin_dir_path(__FILE__) . 'helper.php';

//***
//19-08-2015
final class WOOF
{

    public $settings = array();
    public $version = '1.1.1.1';
    public $html_types = array(
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'select' => 'Drop-down',
        'mselect' => 'Multi drop-down',
        'color' => 'Color'
    );
    public static $query_cache_table = 'woof_query_cache';
    private $session_rct_key = 'woof_really_current_term';

    public function __construct()
    {
        if (session_id() == '')
        {
            try
            {
                @session_start();
            } catch (Exception $e)
            {
                //***
            }
        }
        //+++
        if (!defined('DOING_AJAX'))
        {
            global $wp_query;
            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product')))
            {
                //unset($_SESSION['woof_really_current_term']);
                $this->set_really_current_term();
            }
        }
        //+++
        $this->init_settings();
        global $wpdb;
        $attribute_taxonomies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies");
        set_transient('wc_attribute_taxonomies', $attribute_taxonomies);
        if (!empty($attribute_taxonomies) AND is_array($attribute_taxonomies))
        {
            foreach ($attribute_taxonomies as $att)
            {
                //fixing for woo >= 2.3.2
                add_filter("woocommerce_taxonomy_args_pa_{$att->attribute_name}", array($this, 'change_woo_att_data'));
            }
        }
        //add_filter("woocommerce_taxonomy_args_pa_color", array($this, 'change_woo_att_data'));
        //add_action('init', array($this, 'price_filter_init'));
        
        add_action('widgets_init', array($this, 'widgets_init'));
    }

    public function init()
    {
        if (!class_exists('WooCommerce'))
        {
            return;
        }

        //***
        $first_init = (int) get_option('woof_first_init');
        if ($first_init != 1)
        {
            update_option('woof_first_init', 1);
            update_option('woof_set_automatically', 0);
            update_option('woof_autosubmit', 1);
            update_option('woof_show_count', 1);
            update_option('woof_show_count_dynamic', 1);
            update_option('woof_hide_dynamic_empty_pos', 0);
            update_option('woof_try_ajax', 0);
            update_option('woof_use_chosen', 1);
            update_option('woof_checkboxes_slide', 1);
            update_option('woof_use_beauty_scroll', 1);
            update_option('woof_show_title_search', 0);
            update_option('woof_show_in_stock_only', 0);
            update_option('woof_show_sales_only', 0);
            update_option('woof_show_price_search', 1);
            update_option('woof_filter_btn_txt', '');
            update_option('woof_reset_btn_txt', '');
            update_option('woof_show_price_search_button', 0);
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
        add_action('wp_footer', array($this, 'wp_footer'), 999);
        add_shortcode('woof', array($this, 'woof_shortcode'));
        //add_action('widgets_init', array($this, 'widgets_init'));
        //+++
        add_action('wp_ajax_woof_draw_products', array($this, 'woof_draw_products'));
        add_action('wp_ajax_nopriv_woof_draw_products', array($this, 'woof_draw_products'));
        add_action('wp_ajax_woof_redraw_woof', array($this, 'woof_redraw_woof'));
        add_action('wp_ajax_nopriv_woof_redraw_woof', array($this, 'woof_redraw_woof'));
        //+++
        add_filter('widget_text', 'do_shortcode');
        add_filter('parse_query', array($this, "parse_query"), 9999);
        add_filter('woocommerce_product_query', array($this, "woocommerce_product_query"), 9999);
        //add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999); //for searching by title
        //+++
        add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'));
        add_action('woocommerce_after_shop_loop', array($this, 'woocommerce_after_shop_loop'));
        add_shortcode('woof_products', array($this, 'woof_products'));
        //add_filter('woocommerce_pagination_args', array($this, 'woocommerce_pagination_args'));
        add_action('wp_ajax_woof_cache_count_data_clear', array($this, 'cache_count_data_clear'));

        add_filter('woof_exclude_tax_key', array($this, 'woof_exclude_tax_key'));
        add_filter('sidebars_widgets', array($this, 'sidebars_widgets'));
        //own filters of WOOF
        add_filter('woof_modify_query_args', array($this, 'woof_modify_query_args'), 1);
        //sheduling
        if (isset($this->settings['cache_count_data_auto_clean']) AND $this->settings['cache_count_data_auto_clean'])
        {
            add_action('woof_cache_count_data_auto_clean', array($this, 'cache_count_data_clear'));
            if (!wp_next_scheduled('woof_cache_count_data_auto_clean'))
            {
                wp_schedule_event(time(), $this->settings['cache_count_data_auto_clean'], 'woof_cache_count_data_auto_clean');
            }
        }

        //for pagination
        //http://docs.woothemes.com/document/change-number-of-products-displayed-per-page/
        add_filter('loop_shop_per_page', create_function('$cols', "return {$this->settings['per_page']};"), 9999);

        //custom filters
        //add_filter('woof_before_term_name', array($this, 'woof_before_term_name'));
    }

    public function widgets_init()
    {
        register_widget('WOOF_Widget');
    }

    //fix for woo 2.3.2 and higher with attributes filtering
    public function change_woo_att_data($taxonomy_data)
    {
        $taxonomy_data['query_var'] = true;
        return $taxonomy_data;
    }

    public function sidebars_widgets($sidebars_widgets)
    {
        if (get_option('woof_show_price_search'))
        {
            $sidebars_widgets['sidebar-woof'] = array('woocommerce_price_filter');
        }

        return $sidebars_widgets;
    }

    /*
      public function price_filter_init()
      {
      if (get_option('woof_show_price_search'))
      {
      $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

      wp_register_script('wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/frontend/jquery-ui-touch-punch' . $suffix . '.js', array('jquery-ui-slider'), WC_VERSION, true);
      wp_register_script('wc-price-slider', WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch'), WC_VERSION, true);

      wp_localize_script('wc-price-slider', 'woocommerce_price_slider_params', array(
      'currency_symbol' => get_woocommerce_currency_symbol(),
      'currency_pos' => get_option('woocommerce_currency_pos'),
      'min_price' => isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : '',
      'max_price' => isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''
      ));

      global $woocommerce;
      add_filter('loop_shop_post_in', array($woocommerce->query, 'price_filter'));
      }
      }
     */

    /**
     * Show action links on the plugin screen
     */
    public function plugin_action_links($links)
    {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=woof') . '">' . __('Settings', 'woocommerce-products-filter') . '</a>',
            '<a target="_blank" href="' . esc_url('http://woocommerce-filter.com/documentation/') . '">' . __('Documentation', 'woocommerce-products-filter') . '</a>'
                ), $links);

        return $links;
    }

    public function get_swoof_search_slug()
    {
        $slug = 'swoof';


        return $slug;
    }

    public function woocommerce_product_query($q)
    {
        //http://docs.woothemes.com/wc-apidocs/class-WC_Query.html
        //wp-content\plugins\woocommerce\includes\class-wc-query.php -> public function product_query( $q )
        /*
          $woo_obj->meta_query = $this->assemble_stock_sales_params($woo_obj->meta_query);
          $q->set('meta_query', $woo_obj->meta_query);
          return $woo_obj;
         *
         */
        add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999); //for searching by title
        $meta_query = $q->get('meta_query');
        $q->set('meta_query', $this->assemble_stock_sales_params($meta_query));
        return $q;
    }

    public function parse_query($wp_query)
    {
        $_REQUEST['woof_parse_query'] = 1;

        if (!defined('DOING_AJAX'))
        {
            if (isset($_REQUEST['woof_products_doing']))
            {
                return $wp_query;
            }
        }

        //+++
        if ($wp_query->is_main_query())
        {
            if (isset($_GET[$this->get_swoof_search_slug()]))
            {
                if (!is_page())
                {
                    $wp_query->set('post_type', 'product');
                    if (!isset($_GET['stock']))
                    {
                        $wp_query->is_page = false;
                    }
                    $wp_query->is_post_type_archive = true;
                }

                $wp_query->is_tax = false;
                $wp_query->is_tag = false;
                $wp_query->is_home = false;
                $wp_query->is_single = false;
                $wp_query->is_posts_page = false;
                $wp_query->is_search = false; //!!!
                //+++
                $meta_query = array();
                if (isset($wp_query->query_vars['meta_query']))
                {
                    $meta_query = $wp_query->query_vars['meta_query'];
                }
                $meta_query['relation'] = 'AND';
                //+++
                $assemble_stock_sales_params = !is_page();
                if (!$assemble_stock_sales_params)
                {
                    $assemble_stock_sales_params = ($wp_query->query_vars['page_id'] == wc_get_page_id('shop'));
                }

                if (!$assemble_stock_sales_params)
                {
                    $assemble_stock_sales_params = $wp_query->is_post_type_archive;
                }

                if ($assemble_stock_sales_params)
                {
                    $this->assemble_stock_sales_params($meta_query);
                }
                //***
                //out of stock products
                //http://stackoverflow.com/questions/24480982/hide-out-of-stock-products-in-woocommerce
                /*
                  if (get_option('woof_exclude_out_stock_products', 0))
                  {
                  $meta_query[] = array(
                  'key' => '_stock_status',
                  'value' => array('instock'),
                  'compare' => 'IN'
                  );
                  }
                 */
                //***
                $wp_query->set('meta_query', $meta_query);
            }
        }

        return $wp_query;
    }

    private function assemble_stock_sales_params(&$meta_query)
    {
        if (isset($_GET['stock']))
        {
            if ($_GET['stock'] == 'instock')
            {
                $meta_query[] = array(
                    'key' => '_stock_status',
                    'value' => 'outofstock', //instock,outofstock
                    'compare' => 'NOT IN'
                );
            }

            if ($_GET['stock'] == 'outofstock')
            {
                $meta_query[] = array(
                    array(
                        'key' => '_stock_status',
                        'value' => 'outofstock', //instock,outofstock
                        'compare' => 'IN'
                    )
                );
            }
        }
        //+++
        if (isset($_GET['insales']) AND $_GET['insales'] == 'salesonly')
        {
            //http://stackoverflow.com/questions/20990199/woocommerce-display-only-on-sale-products-in-shop
            $meta_query[] = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'DECIMAL'
                    ),
                    array(
                        'key' => '_min_variation_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'DECIMAL'
                    )
                )
            );
        }


        return $meta_query;
    }

    public function woof_post_title_filter($where = '')
    {

        global $wp_query;

        if (defined('DOING_AJAX'))
        {
            $conditions = (isset($wp_query->query_vars['post_type']) AND $wp_query->query_vars['post_type'] == 'product') OR isset($_REQUEST['woof_products_doing']);
        } else
        {
            $conditions = isset($_REQUEST['woof_products_doing']);
        }
        //***
        //if ($conditions)
        {
            if (isset($_GET['woof_title']))
            {
                
            }
        }
        //***
        return $where;
    }

    public function woocommerce_settings_tabs_array($tabs)
    {
        $tabs['woof'] = __('Products Filter', 'woocommerce-products-filter');
        return $tabs;
    }

    public function admin_head()
    {
        if (isset($_GET['page']) AND isset($_GET['tab']))
        {
            if ($_GET['page'] == 'wc-settings' AND $_GET['tab'] == 'woof')
            {
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('woof-admin', WOOF_LINK . 'js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
            }
        }
    }

    public function wp_head()
    {
        global $wp_query;
        //***
        if (!isset($wp_query->query_vars['taxonomy']) AND ! defined('DOING_AJAX'))
        {
            $this->set_really_current_term();
        }
        //***        
        wp_enqueue_style('woof', WOOF_LINK . 'css/front.css');
        //***
        ?>

        <?php //if (isset($this->settings['custom_css_code'])):           ?>
        <style type="text/css">
        <?php
        if (isset($this->settings['custom_css_code']))
        {
            echo stripcslashes($this->settings['custom_css_code']);
        }
        ?>

        <?php
        if (isset($this->settings['overlay_skin_bg_img']))
        {
            if (!empty($this->settings['overlay_skin_bg_img']))
            {
                ?>
                    .plainoverlay {
                        background-image: url('<?php echo $this->settings['overlay_skin_bg_img'] ?>');
                    }
                <?php
            }
        }



//***
        if (isset($this->settings['plainoverlay_color']))
        {
            if (!empty($this->settings['plainoverlay_color']))
            {
                ?>
                    .jQuery-plainOverlay-progress {
                        border-top: 12px solid <?php echo $this->settings['plainoverlay_color'] ?> !important;
                    }
                <?php
            }
        }


//***
//***


        if ((int) get_option('woof_autosubmit'))
        {
            /*
              ?>
              .woof_price_search_container .price_slider_amount button.button{
              display: none;
              }

              .woof_price_search_container .price_slider_amount .price_label{
              text-align: left !important;
              }
              <?php
             *
             */
        }
        ?>



        <?php if (get_option('woof_show_price_search_button', 0) == 1): ?>


        <?php else: ?>


                /***** START: hiding submit button of the price slider ******/
                .woof_price_search_container .price_slider_amount button.button{
                    display: none;
                }

                .woof_price_search_container .price_slider_amount .price_label{
                    text-align: left !important;
                }

                .woof .widget_price_filter .price_slider_amount .button {
                    float: left;
                }

                /***** END: hiding submit button of the price slider ******/


        <?php endif; ?>




        </style>
        <?php //endif;               ?>

        <?php if (!current_user_can('create_users')): ?>
            <style type="text/css">
                .woof_edit_view{
                    display: none;
                }
            </style>
        <?php endif; ?>


        <script type="text/javascript">

            var woof_is_mobile = 0;
        <?php if ($this->isMobile()): ?>
                woof_is_mobile = 1;
        <?php endif; ?>


            var woof_show_price_search_button = 0;
        <?php if (get_option('woof_show_price_search_button', 0) == 1): ?>
                woof_show_price_search_button = 1;
        <?php endif; ?>

            var swoof_search_slug = "<?php echo $this->get_swoof_search_slug(); ?>";

        <?php $icheck_skin = $this->settings['icheck_skin']; ?>

            var icheck_skin = {};
        <?php if ($icheck_skin != 'none'): ?>
            <?php $icheck_skin = explode('_', $icheck_skin); ?>
                icheck_skin.skin = "<?php echo $icheck_skin[0] ?>";
                icheck_skin.color = "<?php echo $icheck_skin[1] ?>";
        <?php else: ?>
                icheck_skin = 'none';
        <?php endif; ?>

            var is_woof_use_chosen =<?php echo $this->is_woof_use_chosen() ?>;

            var woof_current_page_link = location.protocol + '//' + location.host + location.pathname;
            //***lets remove pagination from woof_current_page_link
            woof_current_page_link = woof_current_page_link.replace(/\page\/[0-9]/, "");
        <?php
        if (!isset($wp_query->query_vars['taxonomy']))
        {
            $page_id = get_option('woocommerce_shop_page_id');
            if ($page_id > 0)
            {
                $link = get_permalink($page_id);
            }

            if (is_string($link) AND ! empty($link))
            {
                ?>
                    woof_current_page_link = "<?php echo $link ?>";
                <?php
            }
        }


//code bone when filter child categories on the category page of parent
//like here: http://dev.pluginus.net/product-category/clothing/?swoof=1&product_cat=hoo1
        if (!defined('DOING_AJAX') AND ! is_page())
        {
            $request_data = $this->get_request_data();
            if (isset($wp_query->query_vars['taxonomy']) AND empty($request_data))
            {
                $queried_obj = get_queried_object();
                if (is_object($queried_obj))
                {
                    //$_SESSION['woof_really_current_term'] = $queried_obj;
                    $this->set_really_current_term($queried_obj);
                }
            }
        } else
        {
            if ($this->is_really_current_term_exists())
            {
                //unset($_SESSION['woof_really_current_term']);
                $this->set_really_current_term();
            }
        }
//+++
        $woof_use_beauty_scroll = (int) get_option('woof_use_beauty_scroll');
        ?>
            var woof_link = '<?php echo WOOF_LINK ?>';
            var woof_current_values = '<?php echo json_encode($this->get_request_data()); ?>';
            //+++
            var woof_lang_loading = "<?php _e('Loading ...', 'woocommerce-products-filter') ?>";


            var woof_lang_orderby = "<?php _e('orderby', 'woocommerce-products-filter') ?>";
            var woof_lang_title = "<?php _e('Title', 'woocommerce-products-filter') ?>";
            var woof_lang_insales = "<?php _e('In sales only', 'woocommerce-products-filter') ?>";
            var woof_lang_instock = "<?php _e('In stock only', 'woocommerce-products-filter') ?>";
            var woof_lang_perpage = "<?php _e('Per page', 'woocommerce-products-filter') ?>";
            var woof_lang_pricerange = "<?php _e('price range', 'woocommerce-products-filter') ?>";
            var woof_lang_show_products_filter = "<?php _e('show products filter', 'woocommerce-products-filter') ?>";
            var woof_lang_hide_products_filter = "<?php _e('hide products filter', 'woocommerce-products-filter') ?>";
            var woof_use_beauty_scroll =<?php echo $woof_use_beauty_scroll ?>;
            //+++
            var woof_autosubmit =<?php echo (int) get_option('woof_autosubmit') ?>;
            var woof_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var woof_submit_link = "";
            var woof_is_ajax = 0;
            var woof_ajax_page_num =<?php echo ((get_query_var('page')) ? get_query_var('page') : 1) ?>;
            var woof_ajax_first_done = false;
            var woof_checkboxes_slide_flag = <?php echo(((int) get_option('woof_checkboxes_slide') == 1 ? 'true' : 'false')); ?>;



            var woof_overlay_skin = "<?php echo(isset($this->settings['overlay_skin']) ? $this->settings['overlay_skin'] : 'default') ?>";
            jQuery(function () {
                woof_current_values = jQuery.parseJSON(woof_current_values);
                if (woof_current_values.length == 0) {
                    woof_current_values = {};
                }

            });
            //***
            function woof_js_after_ajax_done() {
        <?php echo(isset($this->settings['js_after_ajax_done']) ? stripcslashes($this->settings['js_after_ajax_done']) : ''); ?>
            }
        </script>
        <?php
        if ($icheck_skin != 'none')
        {
            if (!$icheck_skin)
            {
                $icheck_skin = 'square_green';
            }
            wp_enqueue_script('icheck-jquery', WOOF_LINK . 'js/icheck/icheck.min.js', array('jquery'));
            //wp_enqueue_style('icheck-jquery', self::get_application_uri() . 'js/icheck/all.css');
            wp_enqueue_style('icheck-jquery-color', WOOF_LINK . 'js/icheck/skins/' . $icheck_skin[0] . '/' . $icheck_skin[1] . '.css');
        }
        /*
          if (is_shop())
          {
          add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'));
          }
         */
        //***
        wp_enqueue_script('woof_front', WOOF_LINK . 'js/front.js', array('jquery'));
        wp_enqueue_script('woof_radio_html_items', WOOF_LINK . 'js/html_types/radio.js', array('jquery'));
        wp_enqueue_script('woof_checkbox_html_items', WOOF_LINK . 'js/html_types/checkbox.js', array('jquery'));
        wp_enqueue_script('woof_color_html_items', WOOF_LINK . 'js/html_types/color.js', array('jquery'));
        wp_enqueue_script('woof_select_html_items', WOOF_LINK . 'js/html_types/select.js', array('jquery'));
        wp_enqueue_script('woof_mselect_html_items', WOOF_LINK . 'js/html_types/mselect.js', array('jquery'));
        //+++
        if (get_option('woof_show_title_search'))
        {
            wp_enqueue_script('woof_title_html_items', WOOF_LINK . 'js/html_types/title.js', array('jquery'));
        }
        //+++
        if ($this->is_woof_use_chosen())
        {
            wp_enqueue_script('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'));
            wp_enqueue_style('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.min.css');
        }

        if ($this->settings['overlay_skin'] != 'default')
        {
            wp_enqueue_script('plainoverlay', WOOF_LINK . 'js/plainoverlay/jquery.plainoverlay.min.js', array('jquery'));
            wp_enqueue_style('plainoverlay', WOOF_LINK . 'css/plainoverlay.css');
        }


        if ($woof_use_beauty_scroll)
        {
            wp_enqueue_script('mousewheel', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mousewheel.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar-concat', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.concat.min.js', array('jquery'));
            wp_enqueue_style('malihu-custom-scrollbar', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.css');
        }


        if (get_option('woof_show_price_search'))
        {
            wp_enqueue_script('jquery-ui-core', array('jquery'));
            wp_enqueue_script('jquery-ui-slider', array('jquery-ui-core'));
            wp_enqueue_script('wc-jquery-ui-touchpunch', array('jquery-ui-core', 'jquery-ui-slider'));
            wp_enqueue_script('wc-price-slider', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch'));
        }
    }

    public function wp_footer()
    {
        
    }

    public function print_plugin_options()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        if (isset($_POST['woof_settings']))
        {
            WC_Admin_Settings::save_fields($this->get_options());
            //+++
            if (class_exists('SitePress'))
            {
                $lang = ICL_LANGUAGE_CODE;
                if (isset($_POST['woof_settings']['wpml_tax_labels']) AND ! empty($_POST['woof_settings']['wpml_tax_labels']))
                {
                    $translations_string = $_POST['woof_settings']['wpml_tax_labels'];
                    $translations_string = explode(PHP_EOL, $translations_string);
                    $translations = array();
                    if (!empty($translations_string) AND is_array($translations_string))
                    {
                        foreach ($translations_string as $line)
                        {
                            if (empty($line))
                            {
                                continue;
                            }

                            $line = explode(':', $line);
                            if (!isset($translations[$line[0]]))
                            {
                                $translations[$line[0]] = array();
                            }
                            $tmp = explode('^', $line[1]);
                            $translations[$line[0]][$tmp[0]] = $tmp[1];
                        }
                    }

                    $_POST['woof_settings']['wpml_tax_labels'] = $translations;
                }
            }
            //+++
            update_option('woof_settings', $_POST['woof_settings']);
            $this->init_settings();
        }
        //+++
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('woof', WOOF_LINK . 'js/plugin_options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
        wp_enqueue_style('woof', WOOF_LINK . 'css/plugin_options.css');

        $args = array("woof_settings" => get_option('woof_settings'));
        echo $this->render_html(WOOF_PATH . 'views/plugin_options.php', $args);
    }

    private function init_settings()
    {
        $this->settings = get_option('woof_settings', array());
        if (!isset($this->settings['per_page']) OR empty($this->settings['per_page']))
        {
            $this->settings['per_page'] = 12;
        }
    }

    private function get_taxonomies()
    {
        static $taxonomies = array();
        if (empty($taxonomies))
        {
            $taxonomies = get_object_taxonomies('product', 'objects');
            unset($taxonomies['product_shipping_class']);
            unset($taxonomies['product_type']);
        }
        return $taxonomies;
    }

    public function get_options()
    {
        $options = array
            (array(
                'name' => '',
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
                'name' => __('Autosubmit', 'woocommerce-products-filter'),
                'desc' => __('Start searching just after changing any of the elements on the search form', 'woocommerce-products-filter'),
                'id' => 'woof_autosubmit',
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
                    0 => __('No - Premium only', 'woocommerce-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Try to ajaxify the shop', 'woocommerce-products-filter'),
                'desc' => __('Select "Yes" if you want to TRY make filtering in your shop by AJAX. Not compatible for 100% of all wp themes.', 'woocommerce-products-filter'),
                'id' => 'woof_try_ajax',
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
                'name' => __('Use chosen', 'woocommerce-products-filter'),
                'desc' => __('Use chosen javascript library on the front of your site for drop-downs.', 'woocommerce-products-filter'),
                'id' => 'woof_use_chosen',
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
                'name' => __('Hide childs in checkboxes and radio', 'woocommerce-products-filter'),
                'desc' => __('Hide childs in checkboxes and radio. Near checkbox/radio which has childs will be plus icon to show childs.', 'woocommerce-products-filter'),
                'id' => 'woof_checkboxes_slide',
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
                'name' => __('Use beauty scroll', 'woocommerce-products-filter'),
                'desc' => __('Use beauty scroll when you apply max height for taxonomy block on the front', 'woocommerce-products-filter'),
                'id' => 'woof_use_beauty_scroll',
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
                'name' => __('Show "Search by title" textinput', 'woocommerce-products-filter'),
                'desc' => __('Show textinput for searching by products title', 'woocommerce-products-filter'),
                'id' => 'woof_show_title_search',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No - premium only', 'woocommerce-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "In stock only checkbox"', 'woocommerce-products-filter'),
                'desc' => __('Show "In stock only checkbox" on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_in_stock_only',
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
                'name' => __('Show "In sales only checkbox"', 'woocommerce-products-filter'),
                'desc' => __('Show "In sales only checkbox" on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_sales_only',
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
                'name' => __('Show "Filter by price"', 'woocommerce-products-filter'),
                'desc' => __('Show woocommerce filter by price inside woof search form', 'woocommerce-products-filter'),
                'id' => 'woof_show_price_search',
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
                'name' => __('Show button for "Filter by price"', 'woocommerce-products-filter'),
                'desc' => __('Show button for woocommerce filter by price inside woof search form', 'woocommerce-products-filter'),
                'id' => 'woof_show_price_search_button',
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
                'name' => __('Filter button text', 'woocommerce-products-filter'),
                'desc' => __('Filter button text in the search form', 'woocommerce-products-filter'),
                'id' => 'woof_filter_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array(
                'name' => __('Reset button text', 'woocommerce-products-filter'),
                'desc' => __('Reset button text in the search form', 'woocommerce-products-filter'),
                'id' => 'woof_reset_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array('type' => 'sectionend', 'id' => 'woof_general_settings')
        );

        return apply_filters('wc_settings_tab_woof_settings', $options);
    }

    //for dynamic count
    public function dynamic_count($curr_term, $type, $additional_taxes = array())
    {
        $show_count = false;

        if (isset($_GET['min_price']) AND isset($_GET['max_price']))
        {
            $show_count = true;
        }

        if (isset($_GET[$this->get_swoof_search_slug()]))
        {
            $show_count = true;
        }

        if (isset($_REQUEST['WOOF_IS_TAX_PAGE']))
        {
            $show_count = true;
        }
        /*
          if ($show_count === false)
          {
          //WPML compatibility
          //http://wpml.org/forums/topic/get_categories-in-the-main-language/
          if (class_exists('SitePress'))
          {
          if ($curr_term['taxonomy'] == 'product_cat')
          {
          global $sitepress;
          if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE)
          {
          $cat_id = icl_object_id($curr_term['term_id'], 'product_cat', false, ICL_LANGUAGE_CODE);
          return count(get_categories('child_of=' . $cat_id . '&hide_empty=1&taxonomy=product_cat'));
          }
          }
          }
          return $curr_term['count'];
          }
         */
        //+++
        global $wp_query;
        $taxonomies = array();
        if (isset($wp_query->tax_query->queries))
        {
            $taxonomies = $wp_query->tax_query->queries;
        }

        //if works ajax shortcode for products
        // OR is_page() - for shortcode on the page
        if (defined('DOING_AJAX') OR is_page())
        {
            $taxonomies = $this->get_tax_query($additional_taxes);
            //echo '<pre>';
            //print_r($taxonomies);
            unset($taxonomies['relation']);
            //***
            if ($this->is_really_current_term_exists())
            {
                //we need this when for dynamic recount on taxonomy page in ajax mode
                $o = $this->get_really_current_term();
                $this->_util_dynamic_count_add_term($taxonomies, $o->taxonomy, $o->slug);
            }
        }
        //+++ terms dynamic recount is here
        switch ($type)
        {
            case 'radio':
            case 'select':
                $is_in_query = -1;
                foreach ($taxonomies as $k => $tax_block)
                {
                    if (isset($tax_block['taxonomy']))
                    {
                        if ($tax_block['taxonomy'] == $curr_term['taxonomy'])
                        {
                            $is_in_query = $k;
                        }
                    }
                }
                //***
                if ($is_in_query > -1)
                {
                    $taxonomies[$is_in_query]['terms'] = array($curr_term['slug']);
                } else
                {
                    $taxonomies[] = array(
                        'taxonomy' => $curr_term['taxonomy'],
                        'terms' => array($curr_term['slug']),
                        'include_children' => 1,
                        'field' => 'slug',
                        'operator' => 'IN'
                    );
                }

                break;
            case 'color':
                $is_in_query = false;
                $is_in_query_index = -1;
                $in_query_terms = array();
                if (!empty($taxonomies) AND is_array($taxonomies))
                {
                    foreach ($taxonomies as $k => $tax_block)
                    {
                        if (isset($tax_block['taxonomy']))
                        {
                            if ($tax_block['taxonomy'] == $curr_term['taxonomy'])
                            {
                                if (in_array($curr_term['slug'], $tax_block['terms']))
                                {
                                    $is_in_query = true;
                                } else
                                {
                                    $is_in_query_index = $k;
                                    $in_query_terms = $tax_block['terms'];
                                }
                            }
                        }
                    }
                }
                if ($is_in_query)
                {
                    $taxonomies[$is_in_query_index]['terms'] = array($curr_term['slug']);
                } else
                {
                    $taxonomies[] = array(
                        'taxonomy' => $curr_term['taxonomy'],
                        'terms' => array($curr_term['slug']),
                        'include_children' => 1,
                        'field' => 'slug',
                        'operator' => 'IN'
                    );
                }
                break;


            case 'mselect':

                $is_in_query = false;
                $is_in_query_index = -1;
                $in_query_terms = array();
                if (!empty($taxonomies) AND is_array($taxonomies))
                {
                    foreach ($taxonomies as $k => $tax_block)
                    {
                        if (isset($tax_block['taxonomy']))
                        {
                            if ($tax_block['taxonomy'] == $curr_term['taxonomy'])
                            {
                                if (in_array($curr_term['slug'], $tax_block['terms']))
                                {
                                    $is_in_query = true;
                                } else
                                {
                                    $is_in_query_index = $k;
                                    $in_query_terms = $tax_block['terms'];
                                }
                            }
                        }
                    }
                }

                //***
                if (!$this->is_really_current_term_exists())
                {
                    //if we are not on the category page
                    if (!$is_in_query)
                    {

                        $this->_util_dynamic_count_add_term($taxonomies, $curr_term['taxonomy'], $curr_term['slug']);
                    }
                } else
                {
                    if (!$is_in_query)
                    {
                        $taxonomies[] = array(
                            'taxonomy' => $curr_term['taxonomy'],
                            //'terms' => array_merge($in_query_terms, array($curr_term['slug'])),
                            'terms' => array($curr_term['slug']),
                            'include_children' => 1,
                            'field' => 'slug',
                            'operator' => 'IN'
                        );
                    } else
                    {
                        $taxonomies[] = array(
                            'taxonomy' => $curr_term['taxonomy'],
                            'terms' => array_merge($in_query_terms, array($curr_term['slug'])),
                            //'terms' => array($curr_term['slug']),
                            'include_children' => 1,
                            'field' => 'slug',
                            'operator' => 'IN'
                        );
                    }
                }

            default:
                //checkbox
                $is_in_query = false;
                $is_in_query_index = -1;
                $in_query_terms = array();
                if (!empty($taxonomies) AND is_array($taxonomies))
                {
                    foreach ($taxonomies as $k => $tax_block)
                    {
                        if (isset($tax_block['taxonomy']))
                        {
                            if ($tax_block['taxonomy'] == $curr_term['taxonomy'])
                            {
                                if (in_array($curr_term['slug'], $tax_block['terms']))
                                {
                                    $is_in_query = true;
                                } else
                                {
                                    $is_in_query_index = $k;
                                    $in_query_terms = $tax_block['terms'];
                                }
                            }
                        }
                    }
                }
                //***
                if (!$this->is_really_current_term_exists())
                {
                    //if we are not on the category page
                    if (!$is_in_query)
                    {
                        //$this->_util_dynamic_count_add_term($taxonomies, $curr_term['taxonomy'], $curr_term['slug']);
                        if (defined('DOING_AJAX'))
                        {
                            $taxonomies[] = array(
                                'taxonomy' => $curr_term['taxonomy'],
                                'terms' => array($curr_term['slug']),
                                'include_children' => 1,
                                'field' => 'slug',
                                'operator' => 'IN'
                            );
                        } else
                        {
                            $this->_util_dynamic_count_add_term($taxonomies, $curr_term['taxonomy'], $curr_term['slug']);
                        }
                    }
                } else
                {
                    if (!$is_in_query)
                    {
                        $taxonomies[] = array(
                            'taxonomy' => $curr_term['taxonomy'],
                            //'terms' => array_merge($in_query_terms, array($curr_term['slug'])),
                            'terms' => array($curr_term['slug']),
                            'include_children' => 1,
                            'field' => 'slug',
                            'operator' => 'IN'
                        );
                    } else
                    {
                        $taxonomies[] = array(
                            'taxonomy' => $curr_term['taxonomy'],
                            'terms' => array_merge($in_query_terms, array($curr_term['slug'])),
                            //'terms' => array($curr_term['slug']),
                            'include_children' => 1,
                            'field' => 'slug',
                            'operator' => 'IN'
                        );
                    }
                }
                break;
        }


        //***
        $args = array(
            'nopaging' => true,
            'fields' => 'ids'
        );
        $args['tax_query'] = $taxonomies;
        if (isset($wp_query->meta_query->queries))
        {
            $args['meta_query'] = $wp_query->meta_query->queries;
        } else
        {
            $args['meta_query'] = array();
        }

        //check for price
        if (isset($_GET['min_price']) AND isset($_GET['max_price']))
        {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => array($_GET['min_price'], $_GET['max_price']),
                'type' => 'DECIMAL',
                'compare' => 'BETWEEN'
            );

            $args['meta_query']['relation'] = 'AND';
        }

        //WPML compatibility
        if (class_exists('SitePress'))
        {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }

        //for dynamic recount cache working with title search
        if (isset($_GET['woof_title']))
        {
            $args['woof_title'] = strtolower($_GET['woof_title']);
        }

        //***
        $atts = array();
        if (!isset($args['meta_query']))
        {
            $args['meta_query'] = array();
        }
        $this->assemble_stock_sales_params($args['meta_query']);
        $args = apply_filters('woocommerce_shortcode_products_query', $args, $atts);
        //***
        $_REQUEST['woof_dyn_recount_going'] = 1;
        remove_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
        remove_filter('posts_clauses', array(WC()->query, 'order_by_rating_post_clauses'));

        //out of stock products - remove from dyn recount
        //wp-admin/admin.php?page=wc-settings&tab=products&section=inventory
        if (get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes')
        {
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => array('instock'),
                'compare' => 'IN'
            );
        }

        //***
        //$post_count = WOOF_HELPER::get_post_count($args);
        //echo '<pre>';
        //print_r($args);
        static $woof_post_title_filter_added = 0; //just a flag to not add this filter a lot of times
        if ($woof_post_title_filter_added == 0)
        {
            add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
            $woof_post_title_filter_added++;
        }

        $query = new WP_QueryWoofCounter($args);
        unset($_REQUEST['woof_dyn_recount_going']);
        return $query->found_posts;
    }

    //need for checkboxes and multi-selects dynamic recount
    private function _util_dynamic_count_add_term(&$taxonomies, $curr_term_taxonomy, $curr_term_slug)
    {
        $is_tax_inside_index = -1;
        if (!empty($taxonomies))
        {
            foreach ($taxonomies as $k => $t)
            {
                if ($t['taxonomy'] == $curr_term_taxonomy)
                {
                    $is_tax_inside_index = $k;
                }
            }
        }

        if ($is_tax_inside_index === -1)
        {
            $taxonomies[] = array(
                'taxonomy' => $curr_term_taxonomy,
                'terms' => array($curr_term_slug),
                'field' => 'slug',
                'operator' => 'IN',
                'include_children' => 1
            );
        } else
        {
            $terms = $taxonomies[$is_tax_inside_index]['terms'];
            $terms[] = $curr_term_slug;
            $terms = array_unique($terms);
            $taxonomies[$is_tax_inside_index]['terms'] = $terms;
        }
    }

    public function is_woof_use_chosen()
    {
        return (int) get_option('woof_use_chosen');
    }

    public function woocommerce_before_shop_loop()
    {
        $woof_set_automatically = (int) get_option('woof_set_automatically');
        //$_REQUEST['woof_before_shop_loop_done'] - is just key lock
        if ($woof_set_automatically == 1 AND ! isset($_REQUEST['woof_before_shop_loop_done']))
        {
            $_REQUEST['woof_before_shop_loop_done'] = true;
            $shortcode_hide = false;
            if (isset($this->settings['woof_auto_hide_button']))
            {
                $shortcode_hide = intval($this->settings['woof_auto_hide_button']);
            }

            $price_filter = (int) get_option('woof_show_price_search');

            echo do_shortcode('[woof sid="auto_shortcode" autohide=' . $shortcode_hide . ' price_filter=' . $price_filter . ']');
        }
        ?>
        <div class="woof_products_top_panel"></div>

        <?php
        //for ajax output
        if (get_option('woof_try_ajax') AND ! isset($_REQUEST['woof_products_doing']))
        {
            //$_REQUEST['woocommerce_before_shop_loop_done']=true;
            echo '<div class="woocommerce woocommerce-page woof_shortcode_output">';
            $shortcode_txt = "woof_products is_ajax=1";
            if ($this->is_really_current_term_exists())
            {
                $o = $this->get_really_current_term();
                $shortcode_txt = "woof_products taxonomies={$o->taxonomy}:{$o->term_id} is_ajax=1";
                $_REQUEST['WOOF_IS_TAX_PAGE'] = $o->taxonomy;
            }
            echo '<div id="woof_results_by_ajax" data-shortcode="' . $shortcode_txt . '">';
        }
    }

    public function woocommerce_after_shop_loop()
    {
        //for ajax output
        if (get_option('woof_try_ajax') AND ! isset($_REQUEST['woof_products_doing']))
        {
            echo '</div>';
            echo '</div>';
        }
    }

    private function get_request_data()
    {
        if (isset($_GET['s']))
        {
            //$_GET['woof_title'] = $_GET['s'];
        }
        return $_GET;
    }

    public function get_catalog_orderby($orderby = '', $order = 'ASC')
    {
        if (empty($orderby))
        {
            $orderby = get_option('woocommerce_default_catalog_orderby');
        }
        //D:\myprojects\woocommerce-filter\wp-content\plugins\woocommerce\includes\class-wc-query.php#588
        //$orderby_array = array('menu_order', 'popularity', 'rating',
        //'date', 'price', 'price-desc','rand');
        $meta_key = '';
        global $wpdb;
        switch ($orderby)
        {
            case 'price-desc':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'DESC';
                $meta_key = '_price';
                break;
            case 'price':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'ASC';
                $meta_key = '_price';
                break;
            case 'popularity' :
                $meta_key = 'total_sales';
                // Sorting handled later though a hook
                add_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
                break;
            case 'rating' :
                $orderby = '';
                $meta_key = '';
                // Sorting handled later though a hook
                add_filter('posts_clauses', array(WC()->query, 'order_by_rating_post_clauses'));
                break;
            case 'title' :
                $orderby = 'title';
                break;
            case 'rand' :
                $orderby = 'rand';
                break;
            case 'date' :
                $order = 'DESC';
                $orderby = 'date';
                break;
            default:
                break;
        }


        return compact('order', 'orderby', 'meta_key');
    }

    private function get_tax_query($additional_taxes = '')
    {
        $data = $this->get_request_data();
        $res = array();
        //static $woo_taxonomies = NULL;
        $woo_taxonomies = NULL;
        //if (!$woo_taxonomies)
        {
            $woo_taxonomies = get_object_taxonomies('product');
        }

        //+++

        if (!empty($data) AND is_array($data))
        {
            foreach ($data as $tax_slug => $value)
            {
                if (in_array($tax_slug, $woo_taxonomies))
                {
                    $value = explode(',', $value);
                    $res[] = array(
                        'taxonomy' => $tax_slug,
                        'field' => 'slug',
                        'terms' => $value
                    );
                }
            }
        }
        //+++
        //for shortcode
        //[woof_products is_ajax=1 per_page=8 dp=0 taxonomies=product_cat:9,12+locations:30,31]
        //dp - ID of shortcode of Display Product for WooCommerce
        if (!empty($additional_taxes))
        {
            $t = explode('+', $additional_taxes);
            if (!empty($t) AND is_array($t))
            {
                foreach ($t as $string)
                {
                    $tmp = explode(':', $string);
                    $tax_slug = $tmp[0];
                    $tax_terms = explode(',', $tmp[1]);
                    $res[] = array(
                        'taxonomy' => $tax_slug,
                        'field' => 'id',
                        'terms' => $tax_terms
                    );
                }
            }
        }
        //+++
        if (!empty($res))
        {
            $res = array_merge(array('relation' => 'AND'), $res);
        }

        return $res;
    }

    private function get_meta_query($args = array())
    {
        //print_r(WC()->query); - will think about it
        $data = $this->get_request_data();
        $meta_query = WC()->query->get_meta_query();
        $meta_query = array_merge(array('relation' => 'AND'), $meta_query);
        //for out stock products
        if (isset($data['stock']) AND $data['stock'] == 'instock')
        {
            $meta_query[] = array(
                'key' => '_stock_status',
                'value' => array('instock'),
                'compare' => 'IN'
            );
        }

        //http://stackoverflow.com/questions/20990199/woocommerce-display-only-on-sale-products-in-shop
        if (isset($data['insales']) AND $data['insales'] == 'salesonly')
        {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_sale_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'DECIMAL'
                ),
                array(
                    'key' => '_min_variation_sale_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'DECIMAL'
                )
            );
        }

        if (isset($data['min_price']) AND isset($data['max_price']))
        {
            if ($data['min_price'] <= $data['max_price'])
            {
                $meta_query[] = array(
                    'key' => '_price',
                    'value' => array(floatval($data['min_price']), floatval($data['max_price'])),
                    'type' => 'DECIMAL',
                    'compare' => 'BETWEEN'
                );
            }
        }


        return $meta_query;
    }

    //plugins\woocommerce\includes\class-wc-shortcodes.php#295
    //[woof_products]
    public function woof_products($atts)
    {
        $_REQUEST['woof_products_doing'] = 1;
        add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
        $shortcode_txt = 'woof_products';
        if (!empty($atts))
        {
            foreach ($atts as $key => $value)
            {
                $shortcode_txt.=' ' . $key . '=' . $value;
            }
        }
        //***
        $data = $this->get_request_data();
        $catalog_orderby = $this->get_catalog_orderby(isset($data['orderby']) ? $data['orderby'] : '');


        extract(shortcode_atts(array(
            'columns' => apply_filters('loop_shop_columns', 4),
            'orderby' => $catalog_orderby['orderby'],
            'order' => $catalog_orderby['order'],
            'page' => 1,
            'per_page' => 0,
            'is_ajax' => 0,
            'taxonomies' => '',
            'sid' => '',
            'dp' => 0
                        ), $atts));

        //***
        //this needs just for AJAX mode for shortcode [woof] in woof_draw_products()
        $_REQUEST['woof_additional_taxonomies_string'] = $taxonomies;

        //+++
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'posts_per_page' => $per_page,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => $this->get_meta_query(),
            'tax_query' => $this->get_tax_query($taxonomies)
        );



        $args['posts_per_page'] = $this->settings['per_page'];


        if ($per_page > 0)
        {
            $args['posts_per_page'] = $per_page;
        } else
        {
            //compatibility for woocommerce-products-per-page
            if (WC()->session->__isset('products_per_page'))
            {
                $args['posts_per_page'] = WC()->session->__get('products_per_page');
            }
        }

        //Display Product for WooCommerce compatibility
        if (isset($_REQUEST['perpage']))
        {
            //if (is_integer($_REQUEST['perpage']))
            {
                $args['posts_per_page'] = $_REQUEST['perpage'];
            }
        }

        //if smth wrong, set default per page option
        if (!$args['posts_per_page'])
        {
            $args['posts_per_page'] = $this->settings['per_page'];
        }

        //***

        if (!empty($catalog_orderby['meta_key']))
        {
            $args['meta_key'] = $catalog_orderby['meta_key'];
            $args['orderby'] = $catalog_orderby['orderby'];
            if (!empty($catalog_orderby['order']))
            {
                $args['order'] = $catalog_orderby['order'];
            }
        } else
        {
            $args['orderby'] = $catalog_orderby['orderby'];
            if (!empty($catalog_orderby['order']))
            {
                $args['order'] = $catalog_orderby['order'];
            }
        }
        //print_r($args);
        //+++
        $pp = $page;
        if (get_query_var('page'))
        {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged'))
        {
            $pp = get_query_var('paged');
        }

        if ($pp > 1)
        {
            $args['paged'] = $pp;
        } else
        {
            $args['paged'] = ((get_query_var('page')) ? get_query_var('page') : $page);
        }
        //+++

        $wr = apply_filters('woocommerce_shortcode_products_query', $args, $atts);
        global $products, $wp_query;

        //print_r($wr);
        $_REQUEST['woof_wp_query'] = $wp_query = $products = new WP_Query($wr);
        $_REQUEST['woof_wp_query_args'] = $wr;
        //***
        ob_start();
        global $woocommerce_loop;
        $woocommerce_loop['columns'] = $columns;
        $woocommerce_loop['loop'] = 0;
        ?>

        <?php if ($is_ajax == 1): ?>
            <?php //if (!get_option('woof_try_ajax')):                                                      ?>
            <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
                <?php //endif;                   ?>
            <?php endif; ?>
            <?php
            if ($products->have_posts()) :
                add_filter('post_class', array($this, 'woo_post_class'));
                $_REQUEST['woof_before_shop_loop_done'] = true;
                ?>

                <div class="woocommerce columns-<?php echo $columns ?> woocommerce-page woof_shortcode_output">

                    <?php
                    if ($dp == 0)
                    {//Display Product for WooCommerce compatibility
                        do_action('woocommerce_before_shop_loop');
                    }
                    ?>


                    <?php
                    if (function_exists('woocommerce_product_loop_start'))
                    {
                        woocommerce_product_loop_start();
                    }
                    ?>

                    <?php
                    global $woocommerce_loop;
                    $woocommerce_loop['columns'] = $columns;
                    $woocommerce_loop['loop'] = 0;
                    //+++
                    wc_get_template('loop/loop-start.php');

                    //WOOCS compatibility
                    if (class_exists('WOOCS') AND defined('DOING_AJAX'))
                    {
                        global $WOOCS;
                        if (!method_exists($WOOCS, 'woocs_convert_currency'))
                        {
                            //woocs_convert_currency is from 2.0.9
                            add_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 1001);
                            add_filter('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 1001);
                        }
                    }
                    ?>



                    <?php
                    //products output
                    if ($dp == 0)
                    {//Display Product for WooCommerce compatibility
                        while ($products->have_posts()) : $products->the_post();
                            wc_get_template_part('content', 'product');
                        endwhile; // end of the loop.
                    } else
                    {
                        echo do_shortcode('[displayProduct id="' . $dp . '"]');
                    }
                    ?>



                    <?php wc_get_template('loop/loop-end.php'); ?>

                    <?php
                    if (function_exists('woocommerce_product_loop_end'))
                    {
                        woocommerce_product_loop_end();
                    }
                    ?>

                    <?php do_action('woocommerce_after_shop_loop'); ?>

                </div>


                <?php
            else:
                if ($is_ajax == 1)
                {
                    //if (!get_option('woof_try_ajax'))
                    {
                        ?>
                        <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
                            <?php
                        }
                    }
                    ?>
                    <div class="woocommerce woocommerce-page woof_shortcode_output">

                        <?php
                        if (!$is_ajax)
                        {
                            wc_get_template('loop/no-products-found.php');
                        } else
                        {
                            ?>
                            <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
                                <?php
                                wc_get_template('loop/no-products-found.php');
                                ?>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                    <?php
                    if ($is_ajax == 1)
                    {
                        if (!get_option('woof_try_ajax'))
                        {
                            echo '</div>';
                        }
                    }
                endif;
                ?>

                <?php if ($is_ajax == 1): ?>
                    <?php if (!get_option('woof_try_ajax')): ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php
            wp_reset_postdata();
            wp_reset_query();

            unset($_REQUEST['woof_products_doing']);

            return ob_get_clean();
        }

        public function raw_woocommerce_price($price)
        {
            if (class_exists('WOOCS'))
            {
                global $WOOCS;
                $currencies = $WOOCS->get_currencies();
                return $price * $currencies[$WOOCS->current_currency]['rate'];
            }

            return $price;
        }

        public function woocommerce_currency_symbol($currency)
        {
            if (class_exists('WOOCS'))
            {
                global $WOOCS;
                $currencies = $WOOCS->get_currencies();
                return $currencies[$WOOCS->current_currency]['symbol'];
            }

            return $currency;
        }

        //for shortcode woof_products
        public function woo_post_class($classes)
        {
            global $post;
            $classes[] = 'product';
            $classes[] = 'type-product';
            $classes[] = 'status-publish';
            $classes[] = 'has-post-thumbnail';
            $classes[] = 'post-' . $post->ID;
            return $classes;
        }

        //shortcode, works when ajax mode only for shop/category page
        public function woof_draw_products()
        {
            $link = parse_url($_REQUEST['link'], PHP_URL_QUERY);
            parse_str($link, $_GET);
            //add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
            $products = do_shortcode("[" . $_REQUEST['shortcode'] . " page=" . $_REQUEST['page'] . "]");
            //+++
            if (empty($_REQUEST['woof_additional_taxonomies_string']))
            {
                $form = do_shortcode("[" . $_REQUEST['woof_shortcode'] . "]");
            } else
            {
                $form = do_shortcode("[" . $_REQUEST['woof_shortcode'] . " taxonomies={$_REQUEST['woof_additional_taxonomies_string']}]");
            }
            wp_die(json_encode(compact('products', 'form')));
        }

        //[woof] [woof taxonomies=product_cat:46]
        public function woof_shortcode($atts)
        {
            $args = array();
            //this for synhronizating shortcode woof_products if its has attribute taxonomies
            if (isset($atts['taxonomies']))
            {
                //$args['additional_taxes'] = $this->get_tax_query($atts['taxonomies']);
                $args['additional_taxes'] = $atts['taxonomies'];
            } else
            {
                $args['additional_taxes'] = array();
            }

            //+++
            $taxonomies = $this->get_taxonomies();
            $allow_taxonomies = (array) $this->settings['tax'];
            $args['taxonomies'] = array();
            if (!empty($taxonomies))
            {
                foreach ($taxonomies as $tax_key => $tax)
                {
                    if (!in_array($tax_key, array_keys($allow_taxonomies)))
                    {
                        continue;
                    }
                    //+++
                    $args['woof_settings'] = get_option('woof_settings');
                    $args['taxonomies_info'][$tax_key] = $tax;
                    $hide_empty = 0;
                    $args['taxonomies'][$tax_key] = WOOF_HELPER::get_terms($tax_key, $hide_empty);
                }
            }
            //***
            if (isset($atts['skin']))
            {
                wp_enqueue_style('woof_skin_' . $atts['skin'], WOOF_LINK . 'css/shortcode_skins/' . $atts['skin'] . '.css');
            }
            //***

            if (isset($atts['sid']))
            {
                $args['sid'] = $atts['sid'];
                wp_enqueue_script('woof_sid', WOOF_LINK . 'js/woof_sid.js');
            }


            if (isset($atts['autohide']))
            {
                $args['autohide'] = $atts['autohide'];
            } else
            {
                $args['autohide'] = 0;
            }


            if (isset($atts['price_filter']))
            {
                $args['price_filter'] = $atts['price_filter'];
            } else
            {
                $args['price_filter'] = 0;
            }

            //***
            $args['show_woof_edit_view'] = 0;
            if (current_user_can('create_users'))
            {
                $args['show_woof_edit_view'] = 1;
                //wp_enqueue_script('jquery');
                //wp_enqueue_script('jquery-ui-core', array('jquery'));
                //wp_enqueue_script('jquery-ui-dialog', array('jquery', 'jquery-ui-core'));
                //wp_enqueue_style('jquery-ui-dialog',includes_url('css/jquery-ui-dialog.min.css'));
                //wp_enqueue_style('jquery-ui-dialog', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
            }
            return $this->render_html(WOOF_PATH . 'views/woof.php', $args);
        }

        //redraw search form
        public function woof_redraw_woof()
        {
            $_REQUEST['woof_shortcode_txt'] = $_REQUEST['shortcode'];
            wp_die(do_shortcode("[" . $_REQUEST['shortcode'] . "]"));
        }

        public function woocommerce_pagination_args($args)
        {
            return $args;
        }

        //for relevant excluding terms in shortcode [woof]
        public function woof_exclude_tax_key($terms)
        {
            if (!defined('DOING_AJAX'))
            {
                if ($this->is_really_current_term_exists())
                {
                    /*
                      $queried_obj = get_queried_object();
                      $current_term_id = $queried_obj->term_id;
                      $terms = $terms[$current_term_id]['childs'];
                     *
                     */

                    $queried_obj = $this->get_really_current_term();
                    $current_term_id = $queried_obj->term_id;
                    $parent_id = $queried_obj->parent;
                    //search for childs in cycle
                    if ($parent_id == 0)
                    {
                        $terms = $terms[$current_term_id]['childs'];
                    } else
                    {
                        foreach ($terms as $top_tid => $value)
                        {
                            if (!empty($value['childs']))
                            {
                                $terms = $this->_woof_exclude_tax_key_util1($current_term_id, $top_tid, $value['childs']);
                                if (!empty($terms))
                                {
                                    break;
                                }
                            }
                        }

                        //woocommerce-products-filter-bk21 - old code is here
                    }
                }
                //+++
            }

            return $terms;
        }

        ///just utilita for woof_exclude_tax_key
        private function _woof_exclude_tax_key_util1($current_term_id, $top_tid, $child_terms)
        {
            $terms = array();
            if (!empty($child_terms))
            {
                if (isset($child_terms[$current_term_id]['childs']))
                {
                    $terms = $child_terms[$current_term_id]['childs'];
                } else
                {
                    foreach ($child_terms as $tid => $value)
                    {
                        $parent_keys[] = $top_tid;
                        $terms = $this->_woof_exclude_tax_key_util1($current_term_id, $tid, $value['childs']);
                        if (!empty($terms))
                        {
                            break;
                        }
                    }
                }
            }

            return $terms;
        }

        //if we are on the category products page, or any another product taxonomy page
        private function get_really_current_term()
        {
            $res = NULL;
            $key = $this->session_rct_key;
            /*
              if (isset($_SESSION['woof_really_current_term']))
              {
              $res = $_SESSION['woof_really_current_term'];
              }
             */

            if (WC()->session->__isset($key))
            {
                $res = WC()->session->__get($key);
            }


            return $res;
        }

        private function is_really_current_term_exists()
        {
            return (bool) $this->get_really_current_term();
        }

        private function set_really_current_term($queried_obj = NULL)
        {

            $key = $this->session_rct_key;
            if ($queried_obj === NULL)
            {
                //unset($_SESSION['woof_really_current_term']);
                WC()->session->__unset($key);
            } else
            {
                //$_SESSION['woof_really_current_term'] = $queried_obj;
                WC()->session->set($key, $queried_obj);
            }

            return $queried_obj;
        }

        //ajax + wp_cron
        public function cache_count_data_clear()
        {
            //WOOF_HELPER::log('cache_count_data_clear ' . date('d-m-Y H:i:s'));
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . self::$query_cache_table);
            //wp_die('done');
        }

        //is customer look the site from mobile device
        public static function isMobile()
        {
            if (isset($_SERVER["HTTP_USER_AGENT"]))
            {
                return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
            }

            return false;
        }

        //Display Product for WooCommerce compatibility
        public function woof_modify_query_args($query_args)
        {

            if (isset($_REQUEST[$this->get_swoof_search_slug()]))
            {
                if (isset($_REQUEST['woof_wp_query_args']))
                {
                    $query_args['meta_query'] = $_REQUEST['woof_wp_query_args']['meta_query'];
                    $query_args['tax_query'] = $_REQUEST['woof_wp_query_args']['tax_query'];
                    $query_args['paged'] = $_REQUEST['woof_wp_query_args']['paged'];
                }
            }

            return $query_args;
        }

        public function render_html($pagepath, $data = array())
        {
            @extract($data);
            ob_start();
            include($pagepath);
            return ob_get_clean();
        }

    }

//***

    $WOOF = new WOOF();
    $GLOBALS['WOOF'] = $WOOF;
    add_action('init', array($WOOF, 'init'), 1);

//***


    class WP_QueryWoofCounter
    {

        public $post_count = 0;
        public $found_posts = 0;
        public $key_string = "";
        public $table = "";

        //public static $collector = array();

        public function __construct($query)
        {
            global $wpdb;
            global $WOOF;
            $query = (array) $query;
            $key = md5(json_encode($query));
            //***
            $this->key_string = 'woof_count_cache_' . $key;
            $this->table = WOOF::$query_cache_table;
            //***
            $woof_settings = get_option('woof_settings', array());

            $_REQUEST['woof_before_recount_query'] = 1;
            if ($woof_settings['cache_count_data'])
            {
                $value = $this->get_value();
                if ($value != -1)
                {
                    $this->post_count = $this->found_posts = $value;
                } else
                {
                    $q = new WP_QueryWOOFCounterIn($query);
                    $this->post_count = $this->found_posts = $q->post_count;
                    unset($q);
                    $this->set_value();
                }
            } else
            {
                $q = new WP_QueryWOOFCounterIn($query);
                $this->post_count = $this->found_posts = $q->post_count;
                unset($q);
            }
            unset($_REQUEST['woof_before_recount_query']);
        }

        private function set_value()
        {
            global $wpdb;
            $wpdb->query($wpdb->prepare("INSERT INTO {$this->table} (mkey, mvalue) VALUES (%s, %d)", $this->key_string, $this->post_count));
        }

        private function get_value()
        {
            global $wpdb;
            $result = -1;
            $sql = $wpdb->prepare("SELECT mkey,mvalue FROM {$this->table} WHERE mkey=%s", $this->key_string);
            $value = $wpdb->get_results($sql);

            if (!empty($value))
            {
                $value = end($value);
                if (isset($value->mkey))
                {
                    $result = $value->mvalue;
                }
            }

            return $result;
        }

    }

    class WP_QueryWOOFCounterIn extends WP_Query
    {

        function __construct($query = '')
        {
            parent::__construct($query);
        }

        function set_found_posts($q, $limits)
        {
            return false;
        }

        function setup_postdata($post)
        {
            return false;
        }

        function the_post()
        {
            return FALSE;
        }

        function have_posts()
        {
            return FALSE;
        }

    }

    class WOOF_Widget extends WP_Widget
    {

//Widget Setup
        function __construct()
        {
//Basic settings
            $settings = array('classname' => __CLASS__, 'description' => __('WooCommerce Products Filter by realmag777', 'woocommerce-products-filter'));

//Creation
            $this->WP_Widget(__CLASS__, __('WooCommerce Products Filter', 'woocommerce-products-filter'), $settings);
        }

//Widget view
        function widget($args, $instance)
        {
            $args['instance'] = $instance;
            $args['sidebar_id'] = $args['id'];
            $args['sidebar_name'] = $args['name'];
            //+++
            $price_filter = (int) get_option('woof_show_price_search');
            ?>
            <div class="widget widget-woof">
                <?php if (!empty($instance['title'])): ?>
                    <h3 class="widget-title"><?php echo $instance['title'] ?></h3>
                <?php endif; ?>
                <div class="woof_products_top_panel"></div>
                <?php echo do_shortcode('[woof sid="widget"  price_filter=' . $price_filter . ']'); ?>
            </div>
            <?php
        }

//Update widget
        function update($new_instance, $old_instance)
        {
            $instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
        }

//Widget form
        function form($instance)
        {
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
    