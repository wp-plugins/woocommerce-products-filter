<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="subsubsub_section">
    <br class="clear" />
    <div class="section">
        <h3><?php printf(__('WOOF - Products Filter Options v.%s', 'woocommerce-currency-switcher'), $this->version) ?></h3>
        <input type="hidden" name="woof_settings" value="" />
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?php _e("Taxonomies", 'woocommerce-currency-switcher') ?></a></li>
                <li><a href="#tabs-2"><?php _e("Options", 'woocommerce-currency-switcher') ?></a></li>
                <!-- <li><a href="#tabs-3"><?php _e("Auto show layout", 'woocommerce-currency-switcher') ?></a></li> -->
                <li><a href="#tabs-4"><?php _e("Miscellaneous", 'woocommerce-currency-switcher') ?></a></li>
                <li><a href="#tabs-5"><?php _e("Advanced", 'woocommerce-currency-switcher') ?></a></li>
                <li><a href="#tabs-6"><?php _e("Info", 'woocommerce-currency-switcher') ?></a></li>
            </ul>

            <div id="tabs-1">
                <ul id="woof_options">
                    <?php
                    $taxonomies_tmp = $this->get_taxonomies();
                    $taxonomies = array();
                    //sort them as in options
                    if (!empty($this->settings['tax']))
                    {
                        foreach ($this->settings['tax'] as $key => $value)
                        {
                            $taxonomies[$key] = $taxonomies_tmp[$key];
                        }
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
                    foreach ($taxonomies as $key => $tax):
                        ?>
                        <li data-key="<?php echo $key ?>">
                            <a href="#" class="help_tip" data-tip="<?php _e("drag and drope", 'woocommerce-products-filter'); ?>"><img style="width: 22px; vertical-align: middle;" src="<?php echo WOOF_LINK ?>img/move.png" alt="<?php _e("move", 'woocommerce-products-filter'); ?>" /></a>&nbsp;
                            <select name="woof_settings[tax_type][<?php echo $key ?>]">
                                <?php foreach ($this->html_types as $type => $type_text) : ?>
                                    <option <?php if ($type == 'color'): ?>disabled=""<?php endif; ?> value="<?php echo $type ?>" <?php if (isset($woof_settings['tax_type'][$key])) echo selected($woof_settings['tax_type'][$key], $type) ?>><?php echo $type_text ?></option>
                                <?php endforeach; ?>
                            </select><img class="help_tip" data-tip="<?php _e('View of the taxonomies terms on the front', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" />&nbsp;
                            <?php
                            //+++
                            $excluded_terms = '';
                            if (isset($woof_settings['excluded_terms'][$key]))
                            {
                                $excluded_terms = $woof_settings['excluded_terms'][$key];
                            }
                            ?>
                            <input type="text" style="width: 300px;" name="woof_settings[excluded_terms][<?php echo $key ?>]" placeholder="<?php _e('excluded terms ids', 'woocommerce-products-filter') ?>" value="<?php echo $excluded_terms ?>" />&nbsp;<img class="help_tip" data-tip="<?php _e('If you want to exclude some current taxonomies terms from the searching at all! Example: 11,23,77', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" />&nbsp;
                            <input type="button" value="<?php _e('additional options', 'woocommerce-products-filter') ?>" data-taxonomy="<?php echo $key ?>" data-taxonomy-name="<?php echo $tax->labels->name ?>" class="button js_woof_add_options" />&nbsp;

                            <div style="display: none;">
                                <?php
                                $max_height = 0;
                                ?>
                                <input type="text" name="woof_settings[tax_block_height][<?php echo $key ?>]" placeholder="" value="<?php echo $max_height ?>" />
                                <?php
                                $show_title_label = 0;
                                if (isset($woof_settings['show_title_label'][$key]))
                                {
                                    $show_title_label = $woof_settings['show_title_label'][$key];
                                }
                                ?>
                                <input type="text" name="woof_settings[show_title_label][<?php echo $key ?>]" placeholder="" value="<?php echo $show_title_label ?>" />

                                <?php
                                $dispay_in_row = 0;
                                if (isset($woof_settings['dispay_in_row'][$key]))
                                {
                                    $dispay_in_row = $woof_settings['dispay_in_row'][$key];
                                }
                                ?>
                                <input type="text" name="woof_settings[dispay_in_row][<?php echo $key ?>]" placeholder="" value="<?php echo $dispay_in_row ?>" />

                                <?php
                                $custom_tax_label = '';
                                ?>
                                <input type="text" name="woof_settings[custom_tax_label][<?php echo $key ?>]" placeholder="" value="<?php echo $custom_tax_label ?>" />


                            </div>



                            <input <?php echo(@in_array($key, @array_keys($this->settings['tax'])) ? 'checked="checked"' : '') ?> type="checkbox" name="woof_settings[tax][<?php echo $key ?>]" value="1" />&nbsp;
                            <?php echo $tax->labels->name ?>&nbsp;


                        </li>
                    <?php endforeach; ?>
                </ul><br />
            </div>

            <div id="tabs-2">

                <?php woocommerce_admin_fields($this->get_options()); ?>


            </div>

            <div id="tabs-3" style="display: none;">
                <?php $_REQUEST['woof_layout_edit'] = 1; ?>
                <link rel='stylesheet' id='woof-css' href='<?php echo WOOF_LINK ?>css/front.css' type='text/css' media='all' />
                <div class="woof_auto_show">
                    <?php //echo do_shortcode('[woof]')  ?>
                </div>

            </div>

            <div id="tabs-4">


                <?php
                $skins = array(
                    'flat' => array(
                        'flat_aero',
                        'flat_blue',
                        'flat_flat',
                        'flat_green',
                        'flat_grey',
                        'flat_orange',
                        'flat_pink',
                        'flat_purple',
                        'flat_red',
                        'flat_yellow'
                    ),
                    'minimal' => array(
                        'minimal_aero',
                        'minimal_blue',
                        'minimal_green',
                        'minimal_grey',
                        'minimal_minimal',
                        'minimal_orange',
                        'minimal_pink',
                        'minimal_purple',
                        'minimal_red',
                        'minimal_yellow'
                    ),
                    'square' => array(
                        'square_aero',
                        'square_blue',
                        'square_green',
                        'square_grey',
                        'square_orange',
                        'square_pink',
                        'square_purple',
                        'square_red',
                        'square_yellow',
                        'square_square'
                    )
                );
                $skin = $woof_settings['icheck_skin'];
                ?>
                <h4 style="margin-bottom: 5px;"><?php _e('Radio and checkboxes skin', 'woocommerce-products-filter') ?></h4>
                <select name="woof_settings[icheck_skin]" class="chosen_select" style="width: 300px;">
                    <?php foreach ($skins as $key => $schemes) : ?>
                        <optgroup label="<?php echo $key ?>">
                            <?php foreach ($schemes as $scheme) : ?>
                                <option value="<?php echo $scheme; ?>" <?php if ($skin == $scheme): ?>selected="selected"<?php endif; ?>><?php echo $scheme; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>&nbsp;<br />

                <?php
                $skins = array(
                    'default',
                        //'plainoverlay',
                        /*
                          'loading-balls',
                          'loading-bars',
                          'loading-bubbles',
                          'loading-cubes',
                          'loading-cylon',
                          'loading-spin',
                          'loading-spinning-bubbles',
                          'loading-spokes',
                         *
                         */
                );
                if (!isset($woof_settings['overlay_skin']))
                {
                    $woof_settings['overlay_skin'] = 'default';
                }
                $skin = $woof_settings['overlay_skin'];
                ?>
                <h4 style="margin-bottom: 5px;"><?php _e('Overlay skin', 'woocommerce-products-filter') ?></h4>
                <select name="woof_settings[overlay_skin]" class="chosen_select" style="width: 300px;">
                    <?php foreach ($skins as $scheme) : ?>
                        <option value="<?php echo $scheme; ?>" <?php if ($skin == $scheme): ?>selected="selected"<?php endif; ?>><?php echo $scheme; ?></option>
                    <?php endforeach; ?>
                </select>&nbsp;<br />
                <?php
                if (!isset($woof_settings['overlay_skin_bg_img']))
                {
                    $woof_settings['overlay_skin_bg_img'] = '';
                }
                $overlay_skin_bg_img = $woof_settings['overlay_skin_bg_img'];
                ?>
                <div <?php if ($skin == 'default'): ?>style="display: none;"<?php endif; ?>>

                    <h4 style="margin-bottom: 5px;"><?php _e('Overlay image background', 'woocommerce-products-filter') ?></h4>
                    <input type="text" style="width: 80%;" name="woof_settings[overlay_skin_bg_img]" value="<?php echo $overlay_skin_bg_img ?>" /><br />
                    <i><?php _e('Example', 'woocommerce-products-filter') ?>: <?php echo WOOF_LINK ?>img/overlay_bg.png</i><br />

                    <div <?php if ($skin != 'plainoverlay'): ?>style="display: none;"<?php endif; ?>>
                        <br />

                        <?php
                        if (!isset($woof_settings['plainoverlay_color']))
                        {
                            $woof_settings['plainoverlay_color'] = '';
                        }
                        $plainoverlay_color = $woof_settings['plainoverlay_color'];
                        ?>

                        <h4 style="margin-bottom: 5px;"><?php _e('Plainoverlay color', 'woocommerce-products-filter') ?></h4>
                        <input type="text" name="woof_settings[plainoverlay_color]" value="<?php echo $plainoverlay_color ?>" id="woof_color_picker_plainoverlay_color" class="woof-color-picker" >

                    </div>

                </div>







                <?php if (get_option('woof_set_automatically')): ?>



                    <?php
                    $woof_auto_hide_button = array(
                        0 => __('No', 'woocommerce-products-filter'),
                        1 => __('Yes', 'woocommerce-products-filter')
                    );
                    if (!isset($woof_settings['woof_auto_hide_button']))
                    {
                        $woof_settings['woof_auto_hide_button'] = 0;
                    }
                    $woof_auto_hide_button_val = $woof_settings['woof_auto_hide_button'];
                    ?>
                    <h4 style="margin-bottom: 5px;"><?php _e('Hide auto filter by default', 'woocommerce-products-filter') ?></h4>
                    <select name="woof_settings[woof_auto_hide_button]" class="chosen_select" style="width: 300px;">
                        <?php foreach ($woof_auto_hide_button as $v => $n) : ?>
                            <option value="<?php echo $v; ?>" <?php if ($woof_auto_hide_button_val == $v): ?>selected="selected"<?php endif; ?>><?php echo $n; ?></option>
                        <?php endforeach; ?>
                    </select>&nbsp;<br />
                    <i><?php _e('If in options tab option "Set filter automatically" is "Yes" you can hide filter and show hide/show button instead of it.', 'woocommerce-products-filter') ?></i>
                <?php endif; ?>


                <?php
                if (!isset($woof_settings['woof_auto_hide_button_img']))
                {
                    $woof_settings['woof_auto_hide_button_img'] = '';
                }

                if (!isset($woof_settings['woof_auto_hide_button_txt']))
                {
                    $woof_settings['woof_auto_hide_button_txt'] = '';
                }
                ?>


                <h4 style="margin-bottom: 5px;"><?php _e('Auto filter close/open image', 'woocommerce-products-filter') ?></h4>
                <input style="width: 80%;" type="text" name="woof_settings[woof_auto_hide_button_img]" value="<?php echo $woof_settings['woof_auto_hide_button_img'] ?>" /><br />
                <i><?php _e('Image which displayed instead filter while it is closed if selected. Write "none" here if you want to use text only!', 'woocommerce-products-filter') ?></i><br />
                <br />
                <h4 style="margin-bottom: 5px;"><?php _e('Auto filter close/open text', 'woocommerce-products-filter') ?></h4>
                <input style="width: 80%;" type="text" name="woof_settings[woof_auto_hide_button_txt]" value="<?php echo $woof_settings['woof_auto_hide_button_txt'] ?>" /><br />
                <i><?php _e('Text which displayed instead filter while it is closed if selected.', 'woocommerce-products-filter') ?></i><br />
                <br />




                <?php if (class_exists('SitePress')): ?>
                    <br />
                    <?php
                    $wpml_tax_labels = "";
                    if (isset($woof_settings['wpml_tax_labels']) AND is_array($woof_settings['wpml_tax_labels']))
                    {
                        foreach ($woof_settings['wpml_tax_labels'] as $lang => $words)
                        {
                            if (!empty($words) AND is_array($words))
                            {
                                foreach ($words as $key_word => $translation)
                                {
                                    $wpml_tax_labels.=$lang . ':' . $key_word . '^' . $translation . PHP_EOL;
                                }
                            }
                            //$first_value = reset($value); // First Element's Value
                            //$first_key = key($value); // First Element's Key
                        }
                    }
                    ?>

                    <h4 style="margin-bottom: 5px;"><?php _e('WPML taxonomies labels translations', 'woocommerce-products-filter') ?> <img class="help_tip" data-tip="Syntax:
                                                                                                                                           es:Locations^Ubicaciones
                                                                                                                                           es:Size^Tamaño
                                                                                                                                           de:Locations^Lage
                                                                                                                                           de:Size^Größe" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" /></h4>
                    <textarea class="wide" id="wpml_tax_labels" style="height: 300px; width: 50%;" name="woof_settings[wpml_tax_labels]"><?php echo $wpml_tax_labels ?></textarea><br />
                    <i><?php _e('Use it if you can not translate your custom taxonomies labels and attributes labels by another plugins.', 'woocommerce-products-filter') ?></i>
                <?php endif; ?>
                <br /><br />


                <h4 style="margin-bottom: 5px;"><?php _e('Custom CSS code', 'woocommerce-products-filter') ?></h4>
                <textarea class="wide" id="custom_css_code" style="height: 300px; width: 50%;" name="woof_settings[custom_css_code]"><?php echo stripcslashes(@$this->settings['custom_css_code']) ?></textarea><br />
                <i><?php _e("If you are need to customize something and you don't want to lose your changes after update", 'woocommerce-products-filter') ?></i>
                <br /><br />




            </div>

            <div id="tabs-5">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="title_search"><?php _e("Search by title behaviour. Premium only!", 'woocommerce-products-filter') ?></label></th>
                        <td>
                            <fieldset>
                                <label>

                                    <?php
                                    $behaviour = array(
                                        'title' => __("Search by title", 'woocommerce-products-filter'),
                                        'content' => __("Search by content", 'woocommerce-products-filter'),
                                        'title_or_content' => __("Search by title OR content", 'woocommerce-products-filter'),
                                        'title_and_content' => __("Search by title AND content", 'woocommerce-products-filter'),
                                    );
                                    ?>

                                    <?php
                                    if (!isset($woof_settings['search_by_title_behaviour']) OR empty($woof_settings['search_by_title_behaviour']))
                                    {
                                        $woof_settings['search_by_title_behaviour'] = 'title';
                                    }
                                    ?>
                                    <select name="woof_settings[search_by_title_behaviour]" disabled="">
                                        <?php foreach ($behaviour as $key => $value) : ?>
                                            <option value="<?php echo $key; ?>" <?php if ($woof_settings['search_by_title_behaviour'] == $key): ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </label>
                            </fieldset>

                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="cache_count_data"><?php _e("Cache dynamic recount number for each item in filter", 'woocommerce-products-filter') ?></label></th>
                        <td>
                            <fieldset>


                                <?php
                                $cache_count_data = array(
                                    0 => __("No", 'woocommerce-products-filter'),
                                    1 => __("Yes", 'woocommerce-products-filter')
                                );
                                ?>

                                <?php
                                if (!isset($woof_settings['cache_count_data']) OR empty($woof_settings['cache_count_data']))
                                {
                                    $woof_settings['cache_count_data'] = 0;
                                }
                                ?>
                                <select name="woof_settings[cache_count_data]">
                                    <?php foreach ($cache_count_data as $key => $value) : ?>
                                        <option value="<?php echo $key; ?>" <?php if ($woof_settings['cache_count_data'] == $key): ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <p class="description"><?php _e("Useful thing when you already started your site and use dynamic recount -> it make recount very fast! Of course if you added new posts which have to be in search results you have to clean this cache OR you can set time period for auto cleaning!", 'woocommerce-products-filter') ?></p>

                                <?php if ($woof_settings['cache_count_data']): ?>
                                    &nbsp;<a href="#" class="button js_cache_count_data_clear"><?php _e("clear cache", 'woocommerce-products-filter') ?></a>&nbsp;<span style="color: green; font-weight: bold;"></span>
                                    &nbsp;
                                    <?php
                                    $clean_period = 0;
                                    if (isset($this->settings['cache_count_data_auto_clean']))
                                    {
                                        $clean_period = $this->settings['cache_count_data_auto_clean'];
                                    }
                                    ?>
                                    <select name="woof_settings[cache_count_data_auto_clean]">
                                        <option <?php if ($clean_period == 0): ?>selected=""<?php endif; ?> value="0"><?php _e("do not clean cache automatically", 'woocommerce-products-filter') ?></option>
                                        <option <?php if ($clean_period == 'hourly'): ?>selected=""<?php endif; ?> value="hourly"><?php _e("clean cache automatically hourly", 'woocommerce-products-filter') ?></option>
                                        <option <?php if ($clean_period == 'twicedaily'): ?>selected=""<?php endif; ?> value="twicedaily"><?php _e("clean cache automatically twicedaily", 'woocommerce-products-filter') ?></option>
                                        <option <?php if ($clean_period == 'daily'): ?>selected=""<?php endif; ?> value="daily"><?php _e("clean cache automatically daily", 'woocommerce-products-filter') ?></option>
                                    </select>

                                <?php endif; ?>


                            </fieldset>


                            <?php
                            global $wpdb;

                            $charset_collate = '';
                            if (method_exists($wpdb, 'has_cap') AND $wpdb->has_cap('collation'))
                            {
                                if (!empty($wpdb->charset))
                                {
                                    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                                }
                                if (!empty($wpdb->collate))
                                {
                                    $charset_collate .= " COLLATE $wpdb->collate";
                                }
                            }
//***
                            $sql = "CREATE TABLE IF NOT EXISTS `" . WOOF::$query_cache_table . "` (
                                    `mkey` text NOT NULL,
                                    `mvalue` text NOT NULL
                                  ){$charset_collate}";

                            if ($wpdb->query($sql) === false)
                            {
                                ?>
                                <p class="description"><?php _e("WOOF cannot create the database table! Make sure that your mysql user has the CREATE privilege! Do it manually using your host panel&phpmyadmin!", 'woocommerce-products-filter') ?></p>
                                <code><?php echo $sql; ?></code>
                                <input type="hidden" name="woof_settings[cache_count_data]" value="0" />
                                <?php
                                echo $wpdb->last_error;
                            }
                            ?>
                        </td>
                    </tr>

                </table>
            </div>

            <div id="tabs-6">
                <p>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label><?php _e("In the premium version of the plugin you will get", 'woocommerce-products-filter') ?></label></th>
                            <td>

                                <ul style="margin: 6px;">
                                    <li>Color term type</li>
                                    <li>Taxonomy custom label in the terms additional options</li>
                                    <li>Max height of the block in the terms additional options</li>
                                    <li>Shortcode [woof_products]</li>
                                    <li>AJAX filtering with shortcode, or even in your default shop (not 100%)</li>
                                    <li>Search by title</li>
                                    <li>More beauty overlay skin/skins</li>
                                    <li>Inbuilt into the plugin native woocommerce price-range filter</li>
                                    <li>Support</li>
                                </ul>
                                <br />

                                <a href="http://codecanyon.net/item/woocommerce-products-filter-light/11498469?ref=realmag777" target="_blank" class="button button-primary button-large">GET IT!!</a><br />

                                <br />
                                <b>You will not get:</b><br />
                                <ul>
                                    <li>Any CSS or layout fixing/customization</li>
                                </ul>

                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label><?php _e("Docs", 'woocommerce-products-filter') ?></label></th>
                            <td>

                                <ul style="margin: 6px;">

                                    <li>
                                        <a href="http://woocommerce-filter.com/documentation/" target="_blank">WOOF documentation</a>
                                    </li>

                                </ul>

                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label><?php _e("Demo site", 'woocommerce-products-filter') ?></label></th>
                            <td>

                                <ul style="margin: 6px;">

                                    <li>
                                        <a href="http://www.demo.woocommerce-filter.com/" target="_blank">WooCommerce Products Filter</a>
                                    </li>

                                </ul>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label><?php _e("Recommended plugins for your site flexibility and features", 'woocommerce-products-filter') ?></label></th>
                            <td>

                                <ul style="margin: 6px;">
                                    
                                    <li>
                                        <a href="https://wordpress.org/plugins/woocommerce-currency-switcher/" target="_blank">WooCommerce Currency Switcher</a><br />
                                        <p class="description"><?php _e("WooCommerce Currency Switcher – is the plugin that allows you to switch to different currencies and get their rates converted in the real time! Compatible with WOOF!!", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/woocommerce-currency-switcher/" target="_blank">WooCommerce Currency Switcher</a><br />
                                        <p class="description"><?php _e("WooCommerce Currency Switcher – is the plugin that allows you to switch to different currencies and get their rates converted in the real time!", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/taxonomy-terms-order/" target="_blank">Category Order and Taxonomy Terms Order</a><br />
                                        <p class="description"><?php _e("Order Categories and all custom taxonomies terms (hierarchically) and child terms using a Drag and Drop Sortable javascript capability", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/custom-taxonomy-order-ne/" target="_blank">Custom Taxonomy Order NE</a><br />
                                        <p class="description"><?php _e("Allows for the ordering of categories and custom taxonomy terms through a simple drag-and-drop interface", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    
                                    <li>
                                        <a href="https://wordpress.org/plugins/inpost-gallery/" target="_blank">InPost Gallery</a><br />
                                        <p class="description"><?php _e("Insert Gallery in post, page and custom post types just in two clicks ", 'woocommerce-products-filter') ?></p>
                                    </li>


                                    <li>
                                        <a href="https://wordpress.org/plugins/autoptimize/" target="_blank">Autoptimize</a><br />
                                        <p class="description"><?php _e("It concatenates all scripts and styles, minifies and compresses them, adds expires headers, caches them, and moves styles to the page head, and scripts to the footer", 'woocommerce-products-filter') ?></p>
                                    </li>


                                    <li>
                                        <a href="https://wordpress.org/plugins/pretty-link/" target="_blank">Pretty Link Lite</a><br />
                                        <p class="description"><?php _e("Shrink, beautify, track, manage and share any URL on or off of your WordPress website. Create links that look how you want using your own domain name!", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/custom-post-type-ui/" target="_blank">Custom Post Type UI</a><br />
                                        <p class="description"><?php _e("This plugin provides an easy to use interface to create and administer custom post types and taxonomies in WordPress.", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/widget-logic/other_notes/" target="_blank">Widget Logic</a><br />
                                        <p class="description"><?php _e("Widget Logic lets you control on which pages widgets appear using", 'woocommerce-products-filter') ?></p>
                                    </li>

                                    <li>
                                        <a href="https://wordpress.org/plugins/wp-super-cache/" target="_blank">WP Super Cache</a><br />
                                        <p class="description"><?php _e("Cache pages, allow to make a lot of search queries on your site without high load on your server!", 'woocommerce-products-filter') ?></p>
                                    </li>


                                    <li>
                                        <a href="https://wordpress.org/plugins/wp-migrate-db/" target="_blank">WP Migrate DB</a><br />
                                        <p class="description"><?php _e("Exports your database, does a find and replace on URLs and file paths, then allows you to save it to your computer.", 'woocommerce-products-filter') ?></p>
                                    </li>

                                </ul>

                            </td>
                        </tr>

                    </tbody>
                </table>
                </p>
            </div>
        </div>

        <style type="text/css">
            .form-table th{
                width: 300px;
            }
        </style>

        <script type="text/javascript">
            //code syntax highlight here
        </script>

    </div>

    <div id="woof-modal-content" style="display: none;">
        <h4 style="margin: 0.5em 0 !important;"><?php _e('Taxonomy custom label', 'woocommerce-products-filter') ?>&nbsp;<img class="help_tip" data-tip="<?php _e('For example you want to show title of Product Categories as "My Products". Just for your conveniencing.', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" /></h4>
        <input type="text" disabled="" class="woof_popup_option" data-option="custom_tax_label" placeholder="<?php _e('in the premium version', 'woocommerce-products-filter') ?>" value="0" />px&nbsp;
        <br />

        <h4 style="margin: 0.5em 0 !important;"><?php _e('Max height of the block', 'woocommerce-products-filter') ?>&nbsp;<img class="help_tip" data-tip="<?php _e('Max-height (px). Works if the taxonomy view is radio or checkbox. 0 means no max-height. In premium version ONLY!', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" /></h4>
        <input type="text" disabled="" class="woof_popup_option" data-option="tax_block_height" placeholder="<?php _e('Max height of  the block', 'woocommerce-products-filter') ?>" value="0" />px&nbsp;
        <br />

        <h4 style="margin: 0.5em 0 !important;"><?php _e('Show title label', 'woocommerce-products-filter') ?> &nbsp;<img class="help_tip" data-tip="<?php _e('Show/Hide taxonomy block title on the front', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" /></h4>
        <select style="width: 100%;" class="woof_popup_option" data-option="show_title_label">
            <option value="0"><?php _e('No', 'woocommerce-products-filter') ?></option>
            <option value="1"><?php _e('Yes', 'woocommerce-products-filter') ?></option>
        </select><br />


        <h4 style="margin: 0.5em 0 !important;"><?php _e('Dispaly items in a row', 'woocommerce-products-filter') ?> &nbsp;<img class="help_tip" data-tip="<?php _e('Works for radio and checkboxes only. Allows show radio/checkboxes in 1 row!', 'woocommerce-products-filter') ?>" src="<?php echo WP_PLUGIN_URL ?>/woocommerce/assets/images/help.png" height="16" width="16" /></h4>
        <select style="width: 100%;" class="woof_popup_option" data-option="dispay_in_row">
            <option value="0"><?php _e('No', 'woocommerce-products-filter') ?></option>
            <option value="1"><?php _e('Yes', 'woocommerce-products-filter') ?></option>
        </select><br />


    </div>

</div>

<br />
<a href="http://codecanyon.net/item/woocommerce-products-filter-light/11498469?ref=realmag777" target="_blank"><img src="<?php echo WOOF_LINK ?>/img/woof_banner.jpg" alt="" /></a>
&nbsp;<a href="http://codecanyon.net/item/woocommerce-currency-switcher/8085217?ref=realmag777" target="_blank"><img src="<?php echo WOOF_LINK ?>/img/woocs_banner.jpg" alt="" /></a>

