<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
$collector = array();
$_REQUEST['additional_taxes'] = $additional_taxes;
$woof_hide_dynamic_empty_pos = get_option('woof_hide_dynamic_empty_pos');
if (!function_exists('woof_draw_mselect_childs'))
{

    function woof_draw_mselect_childs(&$collector, $taxonomy_info, $tax_slug, $childs, $level, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos)
    {
        global $WOOF;
        $woof_hide_dynamic_empty_pos = get_option('woof_hide_dynamic_empty_pos');

        $current_request = array();
        if (isset($_GET[$tax_slug]))
        {
            $current_request = $_GET[$tax_slug];
            $current_request = explode(',', urldecode($current_request));
        }

        //excluding hidden terms
        $hidden_terms = array();
        if (isset($WOOF->settings['excluded_terms'][$tax_slug]))
        {
            $hidden_terms = explode(',', $WOOF->settings['excluded_terms'][$tax_slug]);
        }
        ?>
        <?php foreach ($childs as $term) : ?>
            <?php
            $count_string = "";
            $count = 0;
            if (!in_array($term['slug'], $current_request))
            {
                if ($show_count)
                {
                    if ($show_count_dynamic)
                    {
                        $count = $WOOF->dynamic_count($term, 'mselect', $_REQUEST['additional_taxes']);
                    } else
                    {
                        $count = $term['count'];
                    }
                    $count_string = '(' . $count . ')';
                }
                //+++
                if ($hide_dynamic_empty_pos AND $count == 0)
                {
                    continue;
                }
            }


            //excluding hidden terms
            if (in_array($term['term_id'], $hidden_terms))
            {
                continue;
            }
            ?>
            <option <?php if ($woof_hide_dynamic_empty_pos == 0 AND $count == 0): ?>disabled=""<?php endif; ?> value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php echo str_repeat('&nbsp;&nbsp;&nbsp;', $level) ?><?php
                if (has_filter('woof_before_term_name'))
                    echo apply_filters('woof_before_term_name', $term, $taxonomy_info);
                else
                    echo $term['name'];
                ?> <?php echo $count_string ?></option>
            <?php
            if (!isset($collector[$tax_slug]))
            {
                $collector[$tax_slug] = array();
            }
            $collector[$tax_slug][] = array('name' => $term['name'], 'slug' => $term['slug']);

            if (!empty($term['childs']))
            {
                woof_draw_mselect_childs($collector, $taxonomy_info, $tax_slug, $term['childs'], $level + 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
            }
            ?>
        <?php endforeach; ?>
        <?php
    }

}
?>
<select class="woof_mselect" data-placeholder="<?php echo WOOF_HELPER::wpml_translate($taxonomy_info) ?>" multiple="" size="<?php echo($this->is_woof_use_chosen() ? 1 : '') ?>" name="<?php echo $tax_slug ?>">
    <option value="0"></option>
    <?php
    $woof_tax_values = array();
    $current_request = array();
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
    ?>
    <?php foreach ($terms as $term) : ?>
        <?php
        $count_string = "";
        $count = 0;
        if (!in_array($term['slug'], $current_request))
        {
            if ($show_count)
            {
                if ($show_count_dynamic)
                {
                    $count = $this->dynamic_count($term, 'mselect', $_REQUEST['additional_taxes']);
                } else
                {
                    $count = $term['count'];
                }
                $count_string = '(' . $count . ')';
            }
            //+++
            if ($hide_dynamic_empty_pos AND $count == 0)
            {
                continue;
            }
        }


        if (in_array($term['term_id'], $hidden_terms))
        {
            continue;
        }
        ?>
        <option <?php if ($woof_hide_dynamic_empty_pos == 0 AND $count == 0): ?>disabled=""<?php endif; ?> value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php
            if (has_filter('woof_before_term_name'))
                echo apply_filters('woof_before_term_name', $term, $taxonomy_info);
            else
                echo $term['name'];
            ?> <?php echo $count_string ?></option>
        <?php
        if (!isset($collector[$tax_slug]))
        {
            $collector[$tax_slug] = array();
        }

        $collector[$tax_slug][] = array('name' => $term['name'], 'slug' => $term['slug']);

        //+++

        if (!empty($term['childs']))
        {
            woof_draw_mselect_childs($collector, $taxonomy_info, $tax_slug, $term['childs'], 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
        }
        ?>
    <?php endforeach; ?>
</select>


<?php
//this is for woof_products_top_panel
if (!empty($collector))
{
    foreach ($collector as $ts => $values)
    {
        if (!empty($values))
        {
            foreach ($values as $value)
            {
                ?>
                <input type="hidden" value="<?php echo $value['name'] ?>" class="woof_n_<?php echo $ts ?>_<?php echo $value['slug'] ?>" />
                <?php
            }
        }
    }
}

//we need it only here, and keep it in $_REQUEST for using in function for child items
unset($_REQUEST['additional_taxes']);
