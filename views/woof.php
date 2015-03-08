<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="woof">
    <div class="woof_checkbox_instock_container">
        <input type="checkbox" class="woof_checkbox_instock" id="woof_checkbox_instock" name="stock" value="0" <?php checked('instock', isset($_REQUEST['stock']) ? 'instock' : '', true) ?> />&nbsp;&nbsp;<label for="woof_checkbox_instock"><?php _e('In stock only', 'woocommerce-products-filter') ?></label><br />
    </div>
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
        $counter = 0;
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
            $args['woof_autosubmit'] = get_option('woof_autosubmit');
            //***
            $woof_container_styles = "";
            if ($woof_settings['tax_type'][$tax_slug] == 'radio' OR $woof_settings['tax_type'][$tax_slug] == 'checkbox')
            {
                if ($this->settings['tax_block_height'][$tax_slug] > 0)
                {
                    $woof_container_styles = "max-height:{$this->settings['tax_block_height'][$tax_slug]}px; overflow-y: auto;";
                }
            }
            //***
            //https://wordpress.org/support/topic/adding-classes-woof_container-div
            $primax_class = sanitize_key(WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]->label));
            ?>
            <div class="woof_container woof_container_<?php echo $counter++ ?> woof_container_<?php echo $primax_class ?>" <?php if (!empty($woof_container_styles)): ?>style="<?php echo $woof_container_styles ?>"<?php endif; ?>>
                <div class="woof_container_inner woof_container_inner_<?php echo $primax_class ?>">
                    <?php
                    switch ($woof_settings['tax_type'][$tax_slug])
                    {
                        case 'checkbox':
                            ?>
                            <h3><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]->label) ?></h3>
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
                            <h3><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]->label) ?></h3>
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
    $woof_autosubmit = (int) get_option('woof_autosubmit');
    ?>

    <div class="woo_submit_search_form_container">

        <?php if (isset($_GET['swoof'])): global $woof_link; ?>
            <input style="float: right;" type="button" class="button woo_reset_search_form" onclick="window.location = '<?php echo $woof_link ?>'" value="<?php _e('Reset', 'woocommerce-products-filter') ?>" />
        <?php endif; ?>

        <?php if (!$woof_autosubmit): ?>
            <input style="float: left;" type="button" class="button woo_submit_search_form" onclick="" value="<?php _e('Filter', 'woocommerce-products-filter') ?>" />
        <?php endif; ?>

    </div>


</div>

