<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!function_exists('woof_draw_select_childs'))
{

    function woof_draw_select_childs($tax_slug, $childs, $level, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos)
    {
        $current_request = array();
        if (isset($_REQUEST[$tax_slug]))
        {
            $current_request = $_REQUEST[$tax_slug];
            $current_request = explode(',', $current_request);
        }
        ?>
        <?php foreach ($childs as $term) : ?>
            <?php
            $count_string = "";
            if ($show_count)
            {
                if ($show_count_dynamic)
                {
                    $count = WOOF::dynamic_count($term, 'select');
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
            ?>
            <option value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php echo str_repeat('&nbsp;&nbsp;&nbsp;', $level) ?><?php echo $term['name'] ?> <?php echo $count_string ?></option>
            <?php
            if (!empty($term['childs']))
            {
                woof_draw_select_childs($tax_slug, $term['childs'], $level + 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
            }
            ?>
        <?php endforeach; ?>
        <?php
    }

}
?>
<select class="woof_select" name="<?php echo $tax_slug ?>">
    <option value="0"><?php echo WOOF_HELPER::wpml_translate($taxonomy_info->label) ?></option>
    <?php
    $woof_tax_values = array();
    $current_request = array();
    if (isset($_REQUEST[$tax_slug]))
    {
        $current_request = $_REQUEST[$tax_slug];
        $current_request = explode(',', $current_request);
    }
    ?>
    <?php foreach ($terms as $term) : ?>
        <?php
        $count_string = "";
        if ($show_count)
        {
            if ($show_count_dynamic)
            {
                $count = self::dynamic_count($term, 'select');
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
        ?>
        <option value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php echo $term['name'] ?> <?php echo $count_string ?></option>
        <?php
        if (!empty($term['childs']))
        {
            woof_draw_select_childs($tax_slug, $term['childs'], 1, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
        }
        ?>
    <?php endforeach; ?>
</select>
