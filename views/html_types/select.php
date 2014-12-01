<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
if (!function_exists('woof_draw_select_childs')) {

    function woof_draw_select_childs($tax_slug, $childs, $level,$show_count) {
        $current_request = array();
        if (isset($_REQUEST[$tax_slug])) {
            $current_request = $_REQUEST[$tax_slug];
            $current_request = explode(',', $current_request);
        }
        ?>
        <?php foreach ($childs as $term) : ?>
            <option value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php echo str_repeat('&nbsp;&nbsp;&nbsp;', $level) ?><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></option>
            <?php
            if (!empty($term['childs'])) {
                woof_draw_select_childs($tax_slug, $term['childs'], $level + 1,$show_count);
            }
            ?>
        <?php endforeach; ?>
        <?php
    }

}
?>
<select class="woof_select" name="<?php echo $tax_slug ?>">
    <option value="0"><?php echo $taxonomy_info->label ?></option>
    <?php
    $woof_tax_values = array();
    $current_request = array();
    if (isset($_REQUEST[$tax_slug])) {
        $current_request = $_REQUEST[$tax_slug];
        $current_request = explode(',', $current_request);
    }
    ?>
    <?php foreach ($terms as $term) : ?>
        <option value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php echo $term['name'] ?> <?php if($show_count) echo '('.$term['count'].')' ?></option>
        <?php
        if (!empty($term['childs'])) {
            woof_draw_select_childs($tax_slug, $term['childs'], 1,$show_count);
        }
        ?>
    <?php endforeach; ?>
</select>
