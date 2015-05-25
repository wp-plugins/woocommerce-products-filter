<?php

final class WOOF_HELPER
{

    //log test data while makes debbuging
    public static function log($string)
    {
        $handle = fopen(WOOF_PATH . 'log.txt', 'a+');
        $string.= PHP_EOL;
        fwrite($handle, $string);
        fclose($handle);
    }

    public static function get_terms($taxonomy, $hide_empty = true, $get_childs = true, $selected = 0, $category_parent = 0)
    {
        static $collector = array();

        if (isset($collector[$taxonomy]))
        {
            return $collector[$taxonomy];
        }

        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'style' => 'list',
            'show_count' => 0,
            'hide_empty' => $hide_empty,
            'use_desc_for_title' => 1,
            'child_of' => 0,
            'hierarchical' => true,
            'title_li' => '',
            'show_option_none' => '',
            'number' => '',
            'echo' => 0,
            'depth' => 0,
            'current_category' => $selected,
            'pad_counts' => 0,
            'taxonomy' => $taxonomy,
            'walker' => 'Walker_Category');


        //WPML compatibility
        if (class_exists('SitePress'))
        {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }

        $cats_objects = get_categories($args);

        $cats = array();
        if (!empty($cats_objects))
        {
            foreach ($cats_objects as $value)
            {
                if (is_object($value) AND $value->category_parent == $category_parent)
                {
                    $cats[$value->term_id] = array();
                    $cats[$value->term_id]['term_id'] = $value->term_id;
                    $cats[$value->term_id]['slug'] = $value->slug;
                    $cats[$value->term_id]['taxonomy'] = $value->taxonomy;
                    $cats[$value->term_id]['name'] = $value->name;
                    $cats[$value->term_id]['count'] = $value->count;
                    $cats[$value->term_id]['parent'] = $value->parent;
                    if ($get_childs)
                    {
                        $cats[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
                    }
                }
            }
        }

        $collector[$taxonomy] = $cats;
        return $cats;
    }

    //just for get_terms
    private static function assemble_terms_childs($cats_objects, $parent_id)
    {
        $res = array();
        foreach ($cats_objects as $value)
        {
            if ($value->category_parent == $parent_id)
            {
                $res[$value->term_id]['term_id'] = $value->term_id;
                $res[$value->term_id]['name'] = $value->name;
                $res[$value->term_id]['slug'] = $value->slug;
                $res[$value->term_id]['count'] = $value->count;
                $res[$value->term_id]['taxonomy'] = $value->taxonomy;
                $res[$value->term_id]['parent'] = $value->parent;
                $res[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
            }
        }

        return $res;
    }

    //https://wordpress.org/support/topic/translated-label-with-wpml
    //for taxonomies labels translations
    public static function wpml_translate($taxonomy_info)
    {
        $string = $taxonomy_info->label;
        $check_for_custom_label = false;
        if (class_exists('SitePress'))
        {
            $lang = ICL_LANGUAGE_CODE;
            $woof_settings = get_option('woof_settings');
            if (isset($woof_settings['wpml_tax_labels']) AND ! empty($woof_settings['wpml_tax_labels']))
            {
                $translations = $woof_settings['wpml_tax_labels'];
                //$translations = unserialize($translations);
                /*
                  $translations = array(
                  'es' => array(
                  'Locations' => 'Ubicaciones',
                  'Size' => 'Tamaño'
                  ),
                  'de' => array(
                  'Locations' => 'Lage',
                  'Size' => 'Größe'
                  ),
                  );
                 */

                if (isset($translations[$lang]))
                {
                    if (isset($translations[$lang][$string]))
                    {
                        $string = $translations[$lang][$string];
                    }
                }
            } else
            {
                $check_for_custom_label = TRUE;
            }
        } else
        {
            $check_for_custom_label = TRUE;
        }

        

        return $string;
    }

    public static function price_filter()
    {
        
    }

    //for dynamic recount - under dev
    public static function get_post_count($args)
    {
        global $wpdb;

        //get terms ids
        $sql1 = "SELECT term_id FROM wp_terms WHERE slug='clothing'";
        $term_id = $wpdb->get_var($sql1);

//$termchildren = get_term_children( $term_id, $taxonomy_name );


        $sql = "SELECT wp_posts.ID FROM wp_posts  
            INNER JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id)  
            INNER JOIN wp_term_relationships AS tt1 ON (wp_posts.ID = tt1.object_id)  
            INNER JOIN wp_term_relationships AS tt2 ON (wp_posts.ID = tt2.object_id) 
            INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )  
            INNER JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id ) 
            WHERE 1=1  AND ( 
  wp_term_relationships.term_taxonomy_id IN (9) 
  AND 
  tt1.term_taxonomy_id IN (24,34,35) 
  AND 
  tt2.term_taxonomy_id IN (27,38,39,40)
) AND ( 
  wp_postmeta.meta_key = '_price' 
  AND 
  ( 
    ( mt1.meta_key = '_visibility' AND CAST(mt1.meta_value AS CHAR) IN ('visible','catalog') )
  )
) AND wp_posts.post_type = 'product' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private') 
GROUP BY wp_posts.ID ORDER BY wp_posts.post_date DESC ";




        return count($wpdb->get_results($sql));
    }

}

final class WOOF_POST_COUNTER
{

    public $post_type = 'product';

    //$args in wp syntax
    public function __construct($args)
    {
        
    }

}

/*
 * Array
(
    [nopaging] => 1
    [fields] => ids
    [tax_query] => Array
        (
            [0] => Array
                (
                    [taxonomy] => product_cat
                    [terms] => Array
                        (
                            [0] => clothing
                        )

                    [field] => slug
                    [operator] => IN
                    [include_children] => 1
                )

            [1] => Array
                (
                    [taxonomy] => pa_color
                    [terms] => Array
                        (
                            [0] => red
                        )

                    [field] => slug
                    [operator] => IN
                    [include_children] => 1
                )

            [2] => Array
                (
                    [taxonomy] => pa_size
                    [terms] => Array
                        (
                            [0] => l
                        )

                    [include_children] => 1
                    [field] => slug
                    [operator] => IN
                )

        )

    [meta_query] => Array
        (
            [0] => Array
                (
                    [key] => _price
                )

            [1] => Array
                (
                    [0] => Array
                        (
                            [key] => _visibility
                            [value] => Array
                                (
                                    [0] => visible
                                    [1] => catalog
                                )

                            [compare] => IN
                        )

                    [relation] => OR
                )

            [relation] => AND
        )

)
 */