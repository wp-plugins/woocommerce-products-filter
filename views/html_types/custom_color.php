<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<ul class="woof_list woof_list_color">
    <?php
    $woof_tax_values = array();
    $current_request = array();
    $_REQUEST['additional_taxes'] = $additional_taxes;
    if (isset($_GET[$tax_slug]))
    {
        $current_request = $_GET[$tax_slug];
        $current_request = explode(',', urldecode($current_request));
    }
    //excluding hidden terms
    $hidden_terms = array();
    if (isset($this->settings['excluded_terms'][$tax_slug]))
    {
        $hidden_terms = explode(',', $this->settings['excluded_terms'][$tax_slug]);
    }
    //CUSTOM term CODE
    $terms = $terms[17]['childs'];
    ?>
    <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
        <?php
        $count_string = "";
        $count = 0;
        if (!in_array($term['slug'], $current_request))
        {
            if ($show_count)
            {
                if ($show_count_dynamic)
                {
                    $count = $this->dynamic_count($term, 'color', $_REQUEST['additional_taxes']);
                } else
                {
                    $count = $term['count'];
                }
                $count_string = '(' . $count . ')';
            }
            //+++
            if ( $hide_dynamic_empty_pos AND $count == 0)
            {
                continue;
            }
        }
        $color = '#000000';
        //if (isset($colors[$term['slug']]))
        {
            //$color = $colors[$term['slug']];
            $color = term_description($term['term_id'], $term['taxonomy']);
        }

        //excluding hidden terms
        if (in_array($term['term_id'], $hidden_terms))
        {
            continue;
        }
        ?>
        <li>
            <?php
            $category_thumbnail = get_woocommerce_term_meta($term['term_id'], 'thumbnail_id', true);
            $image = wp_get_attachment_url($category_thumbnail);
            ?>
            <table>
                <tr>
                    <td width="60">
                        <img width="50" height="50" src="<?php echo $image ?>" alt="<?php echo $term['name'] ?>" />&nbsp;
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" id="<?php echo 'woof_' . $term['term_id'] . '_' . $inique_id ?>" class="woof_color_term <?php if (checked(in_array($term['slug'], $current_request))): ?>checked<?php endif; ?>" data-color="<?php echo $color ?>" data-tax="<?php echo $tax_slug ?>" name="<?php echo $term['slug'] ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> /><br />
                        <?php echo $term['name'] ?> <?php echo $count_string ?>
                        <input type="hidden" value="<?php echo $term['name'] ?>" class="woof_n_<?php echo $tax_slug ?>_<?php echo $term['slug'] ?>" />
                    </td>
                </tr>
            </table>
        </li>
    <?php endforeach; ?>
</ul>
<div style="clear: both;"></div>
<?php
//we need it only here, and keep it in $_REQUEST for using in function for child items
unset($_REQUEST['additional_taxes']);
