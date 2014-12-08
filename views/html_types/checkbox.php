<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!function_exists('woof_draw_checkbox_childs')) {

    function woof_draw_checkbox_childs($tax_slug, $childs, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos) {
        $current_request = array();
        if (isset($_REQUEST[$tax_slug])) {
            $current_request = $_REQUEST[$tax_slug];
            $current_request = explode(',', $current_request);
        }
        ?>
        <ul class="woof_childs_list">
            <?php foreach ($childs as $term) : $inique_id = uniqid(); ?>
                <?php
                $count_string = "";
                if ($show_count) {
                    if ($show_count_dynamic) {
                        $count = WOOF::dynamic_count($term, 'checkbox');
                    } else {
                        $count = $term['count'];
                    }
                    $count_string = '(' . $count . ')';
                }
                //+++
        if ($hide_dynamic_empty_pos AND $count == 0) {
            continue;
        }
                ?>
                <li><input type="checkbox" id="<?php echo 'woof_' . $inique_id ?>" class="woof_checkbox_term" data-tax="<?php echo $tax_slug ?>" name="<?php echo $term['slug'] ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_' . $inique_id ?>" <?php if (checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php echo $count_string ?></label><br />
                    <?php
                    if (!empty($term['childs'])) {
                        woof_draw_checkbox_childs($term['childs'], $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

}
?>
<ul class="woof_list woof_list_checkbox">
    <?php
    $woof_tax_values = array();
    $current_request = array();
    if (isset($_REQUEST[$tax_slug])) {
        $current_request = $_REQUEST[$tax_slug];
        $current_request = explode(',', $current_request);
    }
    ?>
    <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
        <?php
        $count_string = "";
        if ($show_count) {
            if ($show_count_dynamic) {
                $count = self::dynamic_count($term, 'checkbox');
            } else {
                $count = $term['count'];
            }
            $count_string = '(' . $count . ')';
        }
        //+++
        if ($hide_dynamic_empty_pos AND $count == 0) {
            continue;
        }
        ?>
        <li><input type="checkbox" id="<?php echo 'woof_' . $inique_id ?>" class="woof_checkbox_term" data-tax="<?php echo $tax_slug ?>" name="<?php echo $term['slug'] ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_' . $inique_id ?>" <?php if (checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php echo $count_string ?></label><br />
            <?php
            if (!empty($term['childs'])) {
                woof_draw_checkbox_childs($tax_slug, $term['childs'], $show_count, $show_count_dynamic, $hide_dynamic_empty_pos);
            }
            ?>
        </li>
    <?php endforeach; ?>
</ul>
