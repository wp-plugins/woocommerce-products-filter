<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<?php
if (!function_exists('woof_draw_radio_childs')) {

    function woof_draw_radio_childs($tax_slug, $childs,$show_count) {
        $current_request = array();
        if (isset($_REQUEST[$tax_slug])) {
            $current_request = $_REQUEST[$tax_slug];
            $current_request = explode(',', $current_request);
        }
        ?>
        <ul class="woof_childs_list">
            <?php foreach ($childs as $term) : $inique_id = uniqid(); ?>
                <li><input type="radio" id="<?php echo 'woof_' . $inique_id ?>" class="woof_radio_term" data-slug="<?php echo $term['slug'] ?>" name="<?php echo $tax_slug ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_' . $inique_id ?>" <?php if(checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></label><br />
                    <?php
                    if (!empty($term['childs'])) {
                        woof_draw_radio_childs($term['childs'],$show_count);
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

}
?>

<ul class="woof_list woof_list_radio">
    <?php
    $current_request = array();
    if (isset($_REQUEST[$tax_slug])) {
        $current_request = $_REQUEST[$tax_slug];
        $current_request = explode(',', $current_request);
    }
    ?>
    <?php foreach ($terms as $term) : $inique_id = uniqid(); ?>
        <li>
            <input type="radio" id="<?php echo 'woof_' . $inique_id ?>" class="woof_radio_term" data-slug="<?php echo $term['slug'] ?>" name="<?php echo $tax_slug ?>" value="<?php echo $term['term_id'] ?>" <?php echo checked(in_array($term['slug'], $current_request)) ?> />&nbsp;<label for="<?php echo 'woof_' . $inique_id ?>" <?php if(checked(in_array($term['slug'], $current_request))): ?>style="font-weight: bold;"<?php endif; ?>><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></label>
            <?php if (in_array($term['slug'], $current_request)): ?>
            <a href="#" name="<?php echo $tax_slug ?>" class="woof_radio_term_reset"><img src="<?php echo WOOF_LINK ?>/img/delete.png" height="12" width="12" /></a>
            <?php endif; ?>
            <br />
            <?php
            if (!empty($term['childs'])) {
                woof_draw_radio_childs($tax_slug, $term['childs'],$show_count);
            }
            ?>
        </li>
    <?php endforeach; ?>
</ul>
