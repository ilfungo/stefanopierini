<?php
/*
  Plugin Name: WPML MarketPress
  Plugin URI: http://www.wpml.org/
  Description: WPML MarketPress plugin. <a href="http://www.wpml.org/">Documentation</a>.
  Author: ICanLocalize
  Author URI: http://www.wpml.org/
  Version: 1.1.1
 */
add_action('plugins_loaded', 'wpml_marketpress_init', 2);

/**
 * Init function. 
 */
function wpml_marketpress_init() {
    if (!defined('ICL_SITEPRESS_VERSION') || !class_exists('MarketPress')) {
        return '';
    }
    if (!is_admin()) {
        global $wpml_marketpress_settings, $wpml_marketpress_old_settings;
        $wpml_marketpress_old_settings = get_option('mp_settings');
        $wpml_marketpress_settings = wpml_marketpress_settings_option_filter($wpml_marketpress_old_settings);

        // Filter WPML language switcher
        add_filter('icl_ls_languages', 'wpml_marketpress_ls_filter');

        global $sitepress, $sitepress_settings;
        if ($sitepress->get_current_language() != $sitepress_settings['st']['strings_language']) {
            add_filter('option_mp_settings',
                    create_function('$a',
                            'global $wpml_marketpress_settings; return $wpml_marketpress_settings;'));

            add_filter('option_mp_store_page',
                    'wpml_marketpress_store_page_option_filter', 0);
            add_filter('mp_cart_link', 'wpml_marketpress_mp_cart_link_filter');
            add_filter('mp_store_link', 'wpml_marketpress_mp_store_link_filter');
            add_filter('mp_products_link',
                    'wpml_marketpress_mp_products_link_filter');
            add_filter('mp_orderstatus_link',
                    'wpml_marketpress_mp_orderstatus_link_filter');
            add_filter('mp_checkout_step_url',
                    'wpml_marketpress_mp_checkout_step_url_filter');
            add_filter('mp_product_image_id',
                    'wpml_marketpress_mp_product_image_id_filter');
            add_filter('mp_product_url_display_in_cart',
                    'wpml_marketpress_mp_product_url_display_in_cart_filter',
                    10, 2);
            add_filter('mp_product_name_display_in_cart',
                    'wpml_marketpress_mp_product_name_display_in_cart_filter',
                    10, 2);
            add_filter('option_rewrite_rules',
                    'wpml_marketpress_option_rewrite_rules_filter', 9); // Do this before WPML (10)
        }
    }
    add_filter('mp_product_id_add_to_cart',
            'wpml_marketpress_mp_product_id_add_to_cart_filter');
    add_action("updated_post_meta", 'wpml_marketpress_updated_post_meta_hook',
            10, 4);
    add_action('template_redirect', 'wpml_marketpress_redirect_order', 11);
    add_action('mp_new_order', 'wpml_marketpress_mp_new_order_hook');
    if (function_exists('icl_t')) {
        add_filter('mp_shipped_order_notification_body',
                'wpml_marketpress_mp_shipped_order_notification_body_filter', 0,
                2);
        add_filter('mp_shipped_order_notification_subject',
                'wpml_marketpress_mp_shipped_order_notification_subject_filter',
                0, 2);
    }
}

/**
 * Filters shipped notification mail body.
 * 
 * @param type $string
 * @param type $order
 * @return type 
 */
function wpml_marketpress_mp_shipped_order_notification_body_filter($string,
        $order) {
    $meta = get_post_meta($order->ID, 'mp_wpml', true);
    $language = isset($meta['language']) ? $meta['language'] : icl_get_default_language();
    $string = wpml_marketpress_get_translated_string('plugin marketpress emails',
            'shipped_order_txt', $language, $string);
    return $string;
}

/**
 * Filters shipped notification mail subject.
 * 
 * @param type $string
 * @param type $order
 * @return type 
 */
function wpml_marketpress_mp_shipped_order_notification_subject_filter($string,
        $order) {
    $meta = get_post_meta($order->ID, 'mp_wpml', true);
    $language = isset($meta['language']) ? $meta['language'] : icl_get_default_language();
    $string = wpml_marketpress_get_translated_string('plugin marketpress emails',
            'shipped_order_subject', $language, $string);
    return $string;
}

/**
 * Adds language to order post type.
 * 
 * Language was stored before in cookie created on cart page.
 * See wpml_marketpress_redirect_order().
 * 
 * @param type $order 
 */
function wpml_marketpress_mp_new_order_hook($order) {
    $cookie_id = 'mp_globalcart_language_' . COOKIEHASH;
    $language = isset($_COOKIE[$cookie_id]) ? $_COOKIE[$cookie_id] : ICL_LANGUAGE_CODE;
    update_post_meta($order->ID, 'mp_wpml', array('language' => $language));
}

/**
 * Gets string in required language.
 * 
 * WPML lacks function for getting string in any language specified.
 * 
 * @global type $wpdb
 * @param type $context
 * @param type $key
 * @param type $language
 * @param type $string
 * @return type 
 */
function wpml_marketpress_get_translated_string($context, $key, $language,
        $string = '') {
    $string_id = icl_st_is_registered_string($context, $key);
    global $wpdb;
    $sql = "SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE string_id=" . $string_id . " AND language='" . $language . "'";
    $translated = $wpdb->get_var($sql);
    if (empty($translated) && !empty($string)) {
        return $string;
    }
    return $translated;
}

/**
 * Redirects customer when back from purchasing or saves language cookie
 * when on cart page.
 * 
 * @global type $wp_query
 * @global type $mp
 * @global type $sitepress
 * @global type $wpdb
 * @return type 
 */
function wpml_marketpress_redirect_order() {
    global $wp_query, $mp, $sitepress;
    if (isset($wp_query->query_vars['pagename'])
            && $wp_query->query_vars['pagename'] == 'cart') {
        if ($wp_query->query_vars['checkoutstep'] == 'confirm-checkout') {

            $cookie_id = 'mp_globalcart_language_' . COOKIEHASH;
            
            if (!isset($_COOKIE[$cookie_id])) {
                return '';
            }
            
            $language = !empty($_COOKIE[$cookie_id]) ? $_COOKIE[$cookie_id] : ICL_LANGUAGE_CODE;
            $active_languages = $sitepress->get_active_languages();
            if ($language == ICL_LANGUAGE_CODE || !array_key_exists($language,
                            $active_languages)) {
                return '';
            }
            $s = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '';
            $request_url = 'http' . $s . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $request_url = wpml_marketpress_translate_url($request_url,
                    $language, 'cart');
            // Delete cookie so user can switch languages
            setcookie($cookie_id, "", time() - 3600, COOKIEPATH);
            wp_redirect($request_url);
            die();
        } else {
            // Store cart language
            $cookie_id = 'mp_globalcart_language_' . COOKIEHASH;
            //set cookie
            $expire = time() + 2592000; //1 month expire
            setcookie($cookie_id, ICL_LANGUAGE_CODE, $expire, COOKIEPATH);
        }
    }
}

/**
 * Synchronizes post meta 'mp_sales_count' betweeen translated posts.
 * 
 * This is used by MarketPress to e.g. get most popular products.
 * 
 * @global type $sitepress
 * @param type $meta_id
 * @param type $object_id
 * @param type $meta_key
 * @param type $_meta_value 
 */
function wpml_marketpress_updated_post_meta_hook($meta_id, $object_id,
        $meta_key, $_meta_value) {
    if ($meta_key == 'mp_sales_count') {
        global $sitepress;
        $languages = $sitepress->get_active_languages();
        foreach ($languages as $code => $language) {
            $product_id = icl_object_id($object_id, 'product', true, $code);
            echo $object_id;
            update_post_meta($product_id, 'mp_sales_count', $_meta_value);
        }
    }
}

/**
 * Changes rewrite rules.
 * 
 * This is needed to match to match MarketPress virtual pages translations
 * and to match 'products', 'product category' and product tag' rewrite rules.
 * 
 * @param type $rules
 * @return type 
 */
function wpml_marketpress_option_rewrite_rules_filter($rules) {
//    global $sitepress_settings;
//    $prefix = $sitepress_settings['language_negotiation_type'] == 1 ? ICL_LANGUAGE_CODE . '/' : '';
    global $wpml_marketpress_settings, $wpml_marketpress_old_settings;
    $prefix = '';
    $settings = $wpml_marketpress_settings;
    $old_settings = $wpml_marketpress_old_settings;
    if (empty($old_settings)) {
        return $rules;
    }
    $checks = array(
        $prefix . $old_settings['slugs']['store'] . '/' . $old_settings['slugs']['products'] . '/' . $old_settings['slugs']['category'] . '/',
        $prefix . $old_settings['slugs']['store'] . '/' . $old_settings['slugs']['products'] . '/' . $old_settings['slugs']['tag'] . '/',
        $prefix . $old_settings['slugs']['store'] . '/' . $old_settings['slugs']['products'] . '/',
        $prefix . $old_settings['slugs']['store'] . '/' . $old_settings['slugs']['cart'] . '/',
        $prefix . $old_settings['slugs']['store'] . '/' . $old_settings['slugs']['orderstatus'] . '/',
        $prefix . $old_settings['slugs']['store'] . '/payment-return/',
    );
    $replaces = array(
        $prefix . $settings['slugs']['store'] . '/' . $settings['slugs']['products'] . '/' . $settings['slugs']['category'] . '/',
        $prefix . $settings['slugs']['store'] . '/' . $settings['slugs']['products'] . '/' . $settings['slugs']['tag'] . '/',
        $prefix . $settings['slugs']['store'] . '/' . $settings['slugs']['products'] . '/',
        $prefix . $settings['slugs']['store'] . '/' . $settings['slugs']['cart'] . '/',
        $prefix . $settings['slugs']['store'] . '/' . $settings['slugs']['orderstatus'] . '/',
        $prefix . $settings['slugs']['store'] . '/payment-return/',
    );
    $new_rules = array();
    foreach ($rules as $k => $v) {
        foreach ($checks as $ck => $check) {
            if (strpos($k, $check) === 0) {
                unset($rules[$k]);
                $k_translated = str_replace($check, $replaces[$ck], $k);
                $new_rules[$k_translated] = $v;
                break;
            }
        }
    }
    return $new_rules + $rules;
}

/**
 * Filters MarketPress product link in cart.
 * 
 * @param type $url
 * @param type $product_id
 * @return type 
 */
function wpml_marketpress_mp_product_url_display_in_cart_filter($url,
        $product_id) {
    $product_id = icl_object_id($product_id, 'product', true);
    return get_permalink($product_id);
}

/**
 * Filters MarketPress product name in cart.
 * 
 * @param type $name
 * @param type $product_id
 * @return type 
 */
function wpml_marketpress_mp_product_name_display_in_cart_filter($name,
        $product_id) {
    $product_id = icl_object_id($product_id, 'product', true);
    $post = get_post($product_id);
    if (!empty($post)) {
        $name = $post->post_title;
    }
    return $name;
}

/**
 * Adjusts MarketPress product ID to be added in cart (original product ID).
 * 
 * @param type $product_id
 * @return type 
 */
function wpml_marketpress_mp_product_id_add_to_cart_filter($product_id) {
    $product_id = icl_object_id($product_id, 'product', true,
            icl_get_default_language());
    return $product_id;
}

/**
 * Filters MarketPress product ID for thumbnail
 * .
 * @param type $product_id
 * @return type 
 */
function wpml_marketpress_mp_product_image_id_filter($product_id) {
    $product_id = icl_object_id($product_id, 'product', true);
    return $product_id;
}

/**
 * Filters MarketPress cart link.
 * 
 * @global type $sitepress
 * @param type $link
 * @return type 
 */
function wpml_marketpress_mp_cart_link_filter($link) {
    global $sitepress;
    // Don't convert if it's called from mp_checkout_step_url()
    $backtrace = debug_backtrace();
    if (isset($backtrace[4]['function']) && $backtrace[4]['function'] == 'mp_checkout_step_url') {
        return $link;
    }
    return $sitepress->convert_url($link);
}

/**
 * Filters MarketPress store link.
 * 
 * @global type $sitepress
 * @param type $link
 * @return type 
 */
function wpml_marketpress_mp_store_link_filter($link) {
    global $sitepress;
    return $sitepress->convert_url($link);
}

/**
 * Filters MarketPress pruducts link.
 * 
 * @global type $sitepress
 * @param type $link
 * @return type 
 */
function wpml_marketpress_mp_products_link_filter($link) {
    global $sitepress;
    return $sitepress->convert_url($link);
}

/**
 * Filters MarketPress order status link.
 * 
 * @global type $sitepress
 * @param type $link
 * @return type 
 */
function wpml_marketpress_mp_orderstatus_link_filter($link) {
    global $sitepress;
    return $sitepress->convert_url($link);
}

function wpml_marketpress_mp_checkout_step_url_filter($link) {
    global $sitepress;
    return $sitepress->convert_url($link);
}

/**
 * Filters MarketPress options.
 * 
 * @staticvar string $original
 * @param type $settings
 * @return type 
 */
function wpml_marketpress_settings_option_filter($settings) {
    foreach ($settings['slugs'] as $key => $slug) {
        if ($key == 'store') {
            $translated_store_page_id = wpml_marketpress_store_page_option_filter(get_option('mp_store_page'));
            $translated_store_page = get_post($translated_store_page_id);
            if (!empty($translated_store_page)) {
                $settings['slugs']['store'] = $translated_store_page->post_name;
            }
        } else {
            if (function_exists('icl_translate')) {
                $settings['slugs'][$key] = strtolower(sanitize_title(icl_translate('plugin marketpress slugs',
                                        $key, $slug, false)));
            }
        }
    }
    foreach ($settings['msg'] as $key => $text) {
        if (function_exists('icl_translate')) {
            $settings['msg'][$key] = icl_translate('plugin marketpress messages',
                    $key, $text, false);
        }
    }
    foreach ($settings['email'] as $key => $text) {
        if (function_exists('icl_translate')) {
            $settings['email'][$key] = icl_translate('plugin marketpress emails',
                    $key, $text, false);
        }
    }               
    
    return $settings;
}

/**
 * Filters MarketPress store page ID.
 * 
 * @param type $page_id
 * @return type 
 */
function wpml_marketpress_store_page_option_filter($page_id) {
    return icl_object_id($page_id, 'page', true);
}

/**
 * Filters WPML language switcher.
 * 
 * @global type $wp_query
 * @param type $languages
 * @return type 
 */
function wpml_marketpress_ls_filter($languages) {
    global $wp_query;
    foreach ($languages as $k => $language) {
        $page = false;
        if (isset($wp_query->query_vars['pagename'])) {
            switch ($wp_query->query_vars['pagename']) {

                case 'product_list':
                    $page = 'products';
                    break;

                case 'cart':
                    $page = 'cart';
                    break;

                case 'orderstatus':
                    $page = 'orderstatus';
                    break;
            }
        }
        if (isset($wp_query->query_vars['product_category'])) {
            $page = 'category';
        } else if (isset($wp_query->query_vars['product_tag'])) {
            $page = 'tag';
        } else if (isset($wp_query->query_vars['product'])
                || (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == 'product')) {
            $page = 'product';
        }
        if ($page) {
            $languages[$k]['url'] = wpml_marketpress_translate_url($language['url'],
                    $language['language_code'], $page);
        }
    }
    return $languages;
}

/**
 * Translates URL by Marketpress slug settings.
 * 
 * @global type $wpml_marketpress_old_settings
 * @global type $wpdb
 * @global type $sitepress
 * @global type $wp_query
 * @global type $icl_adjust_id_url_filter_off
 * @global type $icl_adjust_id_url_filter_off
 * @global type $icl_adjust_id_url_filter_off
 * @staticvar array $cache
 * @param type $url
 * @param type $language
 * @param type $page
 * @return array 
 */
function wpml_marketpress_translate_url($url, $language, $page = 'products') {

    static $cache = array();
    if (isset($cache[$language][$page])) {
        return $cache[$language][$page];
    }

    global $wpml_marketpress_settings, $wpml_marketpress_old_settings, $wpdb, $sitepress, $wp_query;
    $old_settings = $wpml_marketpress_old_settings;
    $slugs = $wpml_marketpress_old_settings['slugs'];
    $slugs_current = $wpml_marketpress_settings['slugs'];
    $translated_slugs = array();

    if (!in_array($page,
                    array('category', 'tag', 'products', 'cart', 'orderstatus', 'payment_return', 'product'))
            || !function_exists('icl_t')) {
        return $url;
    }

    foreach ($slugs as $key => $slug) {
        if ($key == 'store') {
            continue;
        }
        $string_id = icl_st_is_registered_string('plugin marketpress slugs',
                $key);
        $sql = "SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE string_id=" . $string_id . " AND language='" . $language . "'";
        $translated_slug = $wpdb->get_var($sql);
        if (!empty($translated_slug)) {
            $translated_slug = strtolower(sanitize_title($translated_slug));
            $translated_slugs[$key] = $translated_slug;
        } else {
            $translated_slugs[$key] = $slug;
        }
    }

    $store_page_id = get_option('mp_store_page');
    $translated_store_page_id = icl_object_id($store_page_id, 'page', true,
            $language);
    $translated_store_page = get_post($translated_store_page_id);
    $translated_store_page = $translated_store_page->post_name;
    $translated_slugs['store'] = $translated_store_page;

    switch ($page) {
        case 'category':
            $category = '';
            if (!empty($wp_query->query_vars['product_category'])) {
                global $icl_adjust_id_url_filter_off;
                $icl_adjust_id_url_filter_off = true;
                $category = get_term_by('slug',
                        $wp_query->query_vars['product_category'],
                        'product_category');
                $translated_id = icl_object_id($category->term_id,
                        'product_category', true, $language);
                $translated_category = get_term_by('id', $translated_id,
                        'product_category');
                if (!empty($translated_category)) {
                    $category = $translated_category->slug . '/';
                }
                $icl_adjust_id_url_filter_off = false;
            }
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['products'] . '/' . $translated_slugs['category'] . '/' . $category;
            break;

        case 'tag':
            $tag = '';
            if (!empty($wp_query->query_vars['product_tag'])) {
                global $icl_adjust_id_url_filter_off;
                $icl_adjust_id_url_filter_off = true;
                $tag = get_term_by('slug', $wp_query->query_vars['product_tag'],
                        'product_tag');
                $translated_id = icl_object_id($tag->term_id, 'product_tag',
                        true, $language);
                $translated_tag = get_term_by('id', $translated_id,
                        'product_tag');
                if (!empty($translated_tag)) {
                    $tag = $translated_tag->slug . '/';
                }
                $icl_adjust_id_url_filter_off = false;
            }
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['products'] . '/' . $translated_slugs['tag'] . '/' . $tag;
            break;

        case 'products':
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['products'] . '/';
            break;

        case 'product':
            $product = '';
            if (!empty($wp_query->queried_object_id)) {
                global $icl_adjust_id_url_filter_off;
                $icl_adjust_id_url_filter_off = true;
                $translated_id = icl_object_id($wp_query->queried_object_id,
                        'product', true, $language);
                $translated_product = get_post($translated_id);
                if (!empty($translated_product)) {
                    $product = $translated_product->post_name . '/';
                }
                $icl_adjust_id_url_filter_off = false;
            }
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['products'] . '/' . $product;
            break;

        case 'cart':
            $add = '';
            $parts = array_values(explode($slugs_current['cart'],
                            trim($_SERVER['REQUEST_URI'], '/')));
            if (!empty($parts[1])) {
                $parts = explode('?', trim($parts[1], '/'));
                $add = trim($parts[0], '/') . '/';
            }
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['cart'] . '/' . $add;
            break;

        case 'orderstatus':
            $add = '';
            $parts = array_values(explode('/',
                            trim($_SERVER['REQUEST_URI'], '/')));
            $order = end($parts);
            if ($order != $slugs_current['orderstatus']) {
                $add = $order . '/';
            }
            $url = '/' . $translated_slugs['store'] . '/' . $translated_slugs['orderstatus'] . '/' . $add;
            break;

        case 'payment_return':
            $url = '/' . $translated_slugs['store'] . '/payment-return/';
            break;
    }

    $query_string = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    $url = $sitepress->convert_url(site_url() . $url . $query_string, $language);
    $cache[$language][$page] = $url;
    return $url;
}