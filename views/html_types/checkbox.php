<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!function_exists('woof_draw_checkbox_childs')) {

    function woof_draw_checkbox_childs($tax_slug, $childs,$show_count) {
        $current_request = array();
        if (isset($_REQUEST[$tax_slug])) {
            $current_request = $_REQUEST[$tax_slug];
            $current_request = explode(',', $current_request);
        }
        ?>
        <ul class="woof_childs_list">
            <?php foreach ($childs as $term) : $inique_id=uniqid(); ?>
            <li><input type="checkbox" id="<?php echo 'woof_'.$inique_id ?>" class="woof_checkbox_term" data-tax="<?php echo $tax_slug ?>" name="<?php echo $term['slug'] ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_'.$inique_id ?>" <?php if(checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></label><br />
                    <?php
                    if (!empty($term['childs'])) {
                        woof_draw_checkbox_childs($term['childs'],$show_count);
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
    <?php foreach ($terms as $term) : $inique_id=uniqid(); ?>
    <li><input type="checkbox" id="<?php echo 'woof_'.$inique_id ?>" class="woof_checkbox_term" data-tax="<?php echo $tax_slug ?>" name="<?php echo $term['slug'] ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_'.$inique_id ?>" <?php if(checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></label><br />
            <?php
            if (!empty($term['childs'])) {
                woof_draw_checkbox_childs($tax_slug, $term['childs'],$show_count);
            }
            ?>
        </li>
    <?php endforeach; ?>
</ul>
