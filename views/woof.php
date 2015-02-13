<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="woof">
    <?php
    global $wp_query;
    //print_r($wp_query);
    //+++
    if (!empty($taxonomies))
    {
        $exclude_tax_key = '';


        /*
          if (is_product_taxonomy()) {
          $exclude_tax_key = $wp_query->queried_object->taxonomy;
          }
         */

        //if we are on product taxonimies page
        /*
          if (isset($wp_query->query_vars['taxonomy'])) {
          print_r($wp_query);
          if (in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product'))) {
          $exclude_tax_key = $wp_query->query_vars['taxonomy'];
          if (isset($_GET[$exclude_tax_key])) {
          $exclude_tax_key = '';
          }
          }
          }
         */

        if (!empty($wp_query->query))
        {
            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product')))
            {
                $taxes = $wp_query->query;
                if (isset($taxes['paged']))
                {
                    unset($taxes['paged']);
                }

                foreach ($taxes as $key => $value)
                {
                    if (in_array($key, array_keys($_GET)))
                    {
                        unset($taxes[$key]);
                    }
                }
                //***
                if (!empty($taxes))
                {
                    $t = array_keys($taxes);
                    $v = array_values($taxes);
                    //***
                    $exclude_tax_key = $t[0];
                    $_REQUEST['WOOF_IS_TAX_PAGE'] = 1;
                }
            }
        }

        //***
        $taxonomies_tmp = $taxonomies;
        $taxonomies = array();
        //sort them as in options
        foreach ($this->settings['tax'] as $key => $value)
        {
            $taxonomies[$key] = $taxonomies_tmp[$key];
        }
        //check for absent
        foreach ($taxonomies_tmp as $key => $value)
        {
            if (!in_array(@$taxonomies[$key], $taxonomies_tmp))
            {
                $taxonomies[$key] = $taxonomies_tmp[$key];
            }
        }
        //+++

        foreach ($taxonomies as $tax_slug => $terms)
        {
            if ($exclude_tax_key == $tax_slug)
            {
                continue;
            }
            //+++
            $args = array();
            $args['taxonomy_info'] = $taxonomies_info[$tax_slug];
            $args['tax_slug'] = $tax_slug;
            $args['terms'] = $terms;
            $args['show_count'] = get_option('woof_show_count');
            $args['show_count_dynamic'] = get_option('woof_show_count_dynamic');
            $args['hide_dynamic_empty_pos'] = get_option('woof_hide_dynamic_empty_pos');
            //***
            $woof_container_styles = "";
            if ($woof_settings['tax_type'][$tax_slug] == 'radio' OR $woof_settings['tax_type'][$tax_slug] == 'checkbox')
            {
                if ($this->settings['tax_block_height'][$tax_slug] > 0)
                {
                    $woof_container_styles = "max-height:{$this->settings['tax_block_height'][$tax_slug]}px; overflow-y: auto;";
                }
            }
            ?>
            <div class="woof_container" style="<?php echo $woof_container_styles ?>">
                <div class="woof_container_inner">
                    <?php
                    switch ($woof_settings['tax_type'][$tax_slug])
                    {
                        case 'checkbox':
                            ?>
                            <h3><?php echo $taxonomies_info[$tax_slug]->label ?></h3>            
                            <?php
                            echo $this->render_html(WOOF_PATH . 'views/html_types/checkbox.php', $args);
                            break;
                        case 'select':
                            echo $this->render_html(WOOF_PATH . 'views/html_types/select.php', $args);
                            break;
                        case 'mselect':
                            echo $this->render_html(WOOF_PATH . 'views/html_types/mselect.php', $args);
                            break;

                        default:
                            ?>
                            <h3><?php echo $taxonomies_info[$tax_slug]->label ?></h3>            
                            <?php
                            echo $this->render_html(WOOF_PATH . 'views/html_types/radio.php', $args);
                            break;
                    }
                    ?>
                    <br />

                </div>
            </div>
            <?php
        }
    }

//***
    ?>
    <?php if (isset($_GET['swoof'])): global $woof_link; ?>
        <div style="overflow: hidden;">
            <input style="float: right;" type="button" class="button" onclick="window.location = '<?php echo $woof_link ?>'" value="<?php _e('Reset', 'woocommerce-products-filter') ?>" /><br />
        </div>
    <?php endif; ?>
</div>
<script type="text/javascript">
    try {
        jQuery(function () {
            var containers = jQuery('.woof_container');
            jQuery.each(containers, function (index, value) {

                var remove = false;

                if (jQuery(value).find('ul.woof_list_radio').size() === 1) {
                    remove = true;
                }

                if (jQuery(value).find('ul.woof_list_checkbox').size() === 1) {
                    remove = true;
                }

                if (remove) {
                    if (jQuery(value).find('ul.woof_list li').size() === 0) {
                        jQuery(value).remove();
                    }
                }

            });

        });
    } catch (e) {

    }
</script>

