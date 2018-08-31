<?php
/*
Plugin Name:Wordpress Firebase Push Notification
Description:Wordpress Firebase Push Notification
Version:3.5
Author:sony7596, miraclewebssoft, reach.baljit, germanramos
Author URI:http://www.miraclewebsoft.com
License:GPL2
License URI:https://www.gnu.org/licenses/gpl-2.0.html
*/
if (!defined('ABSPATH')) {
    exit;
}

if (!defined("FCM_VERSION_CURRENT")) define("FCM_VERSION_CURRENT", '1');
if (!defined("FCM_URL")) define("FCM_URL", plugin_dir_url( __FILE__ ) );
if (!defined("FCM_PLUGIN_DIR")) define("FCM_PLUGIN_DIR", plugin_dir_path(__FILE__));
if (!defined("FCM_PLUGIN_NM")) define("FCM_PLUGIN_NM", 'Wordpress Firebase Push Notification');
if (!defined("FCM_TD")) define("FCM_TD", 'fcm_td');


Class Firebase_Push_Notification
{
    public $pre_name = 'fcm';

    public function __construct()
    {
        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array($this, $this->pre_name . '_activate'));
        register_deactivation_hook(__FILE__, array($this, $this->pre_name . '_deactivate'));
        add_action('admin_menu', array($this, $this->pre_name . '_setup_admin_menu'));
        add_action("admin_init", array($this, $this->pre_name . '_backend_plugin_js_scripts_filter_table'));
        add_action("admin_init", array($this, $this->pre_name . '_backend_plugin_css_scripts_filter_table'));
        add_action('admin_init', array($this, $this->pre_name . '_settings'));
        add_action('save_post', array($this, $this->pre_name . '_on_post_save'),10, 3);
        //add_action('init', array($this, $this->pre_name . '_custom_post_type'));

    }

    public function fcm_setup_admin_menu()
    {
        add_submenu_page('options-general.php', __('Firebase Push Notification', FCM_TD), FCM_PLUGIN_NM, 'manage_options', 'fcm_slug', array($this, 'fcm_admin_page'));

        add_submenu_page(null            // -> Set to null - will hide menu link
            , __('Test Notification', FCM_TD)// -> Page Title
            , 'Test Notification'    // -> Title that would otherwise appear in the menu
            , 'administrator' // -> Capability level
            , 'test_notification'   // -> Still accessible via admin.php?page=menu_handle
            , array($this, 'fcm_test_notification') // -> To render the page
        );
    }

    public function fcm_admin_page()
    {
        include(plugin_dir_path(__FILE__) . 'views/dashboard.php');
    }

    function fcm_backend_plugin_js_scripts_filter_table()
    {
        wp_enqueue_script("jquery");
        wp_enqueue_script("fcm.js", FCM_URL . "assets/js/fcm.js");
    }

    function fcm_backend_plugin_css_scripts_filter_table()
    {
        wp_enqueue_style("fcm.css", FCM_URL . "assets/css/fcm.css");
    }

    public function fcm_activate()
    {

    }

    public function fcm_deactivate()
    {
    }


    function fcm_settings()
    {    //register our settings
        register_setting('fcm_group', 'stf_fcm_api');
        register_setting('fcm_group', 'fcm_disable');
        register_setting('fcm_group', 'fcm_update_disable');
        register_setting('fcm_group', 'fcm_page_disable');
        register_setting('fcm_group', 'fcm_update_page_disable');

    }

    function fcm_custom_post_type()
    {
        register_post_type('device_tokens',
            [
                'labels'      => [
                    'name'          => __('Device Tokens'),
                    'singular_name' => __('Device Token'),
                ],
                'public'      => true,
                'has_archive' => true,
            ]
        );
    }

    function getNamesOfCategory($category)
    {
      return($category->cat_name);
    }

    function fcm_on_post_save($post_id, $post, $update) {
        //error_log( "Firebase fcm_on_post_save: post_id {$post_id} update {$update}" );
        $title = $post->post_title;
        $content = substr(strip_tags($post->post_content), 0, 50) . "...";
        if ( ! function_exists('getSlugOfCategory')) {
          function getSlugOfCategory($category) { return($category->slug); }
        }
        $topics = array_map("getSlugOfCategory", get_the_category($post_id));
        $extra = array(
            'title'       => array('rendered' => $post->post_title),
            'content'     => array('rendered' => $post->post_content),
            'date'        => str_replace(' ','T',$post->post_date),
            'author'      => $post->post_author,
            'id'          => $post_id,
            'categories'  => wp_get_post_categories($post_id)
        );
        if(get_option('stf_fcm_api')) {
            //new post/page
            if (isset($post->post_status)) {

                if (!$update) {
                    if ($post->post_status == 'publish') {

                        if ($post->post_type == 'post' && get_option('fcm_disable') != 1) {
                            $this->fcm_notification($title, $content, $topics, $extra);

                        } elseif ($post->post_type == 'page' && get_option('fcm_page_disable') != 1) {
                            $this->fcm_notification($title, $content, $topics, $extra);
                        }


                    }

                } else {
                    //updated post/page
                    if ($post->post_status == 'publish') {
                        if ($post->post_type == 'post' && get_option('fcm_update_disable') != 1) {
                            $this->fcm_notification($title, $content, $topics, $extra);
                        } elseif ($post->post_type == 'page' && get_option('fcm_update_page_disable') != 1) {
                            $this->fcm_notification($title, $content, $topics, $extra);
                        }

                    }
                }
            }
        }

    }

    function fcm_test_notification(){
        $title = 'Test title from FCM Plugin';
        $content = 'Test content from FCM Plugin';
        $topics = array("test");
        $extra = array(
            'title'       => array('rendered' => 'Test title from FCM Plugin'),
            'content'     => array('rendered' => 'Test content from FCM Plugin'),
            'date'        => "2018-08-20T15:01:44",
            'author'      => 1,
            'id'          => 1000,
            'categories'  => [1,2]
        );

        $result = $this->fcm_notification($title, $content, $topics, $extra);

        echo '<div class="row">';
        echo '<div><h2>Debug Information</h2>';

        echo '<pre>';
        printf($result);
        echo '</pre>';

        echo '<p><a href="'. admin_url('admin.php').'?page=test_notification">Retry</a></p>';
        echo '<p><a href="'. admin_url('admin.php').'?page=fcm_slug">Home</a></p>';

        echo '</div>';
    }

    function fcm_notification($title, $content, $topics, $extra){
        $condition =  "'".$topics[0]."' in topics";
        if (count($topics) > 1) $condition = $condition . " || '".$topics[1]."' in topics";
        if (count($topics) > 2) $condition = $condition . " || '".$topics[2]."' in topics";
        //error_log( "Firebase fcm_notification condition: {$condition}" );
        $apiKey = get_option('stf_fcm_api');
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );
        $notification_data = array(    //// when application open then post field 'data' parameter work so 'message' and 'body' key should have same text or value
            //'message'        => $content,
            'extra'          => $extra,
            //'category'       => $category
        );

        $notification = array(       //// when application close then post field 'notification' parameter work
            'title'      => $title,
            'body'       => $content,
            //'extra'      => $extra,
            //'category'   => $category,
            'sound'      => 'default'
        );

        $post = array(
            'condition'         => $condition,
            'notification'      => $notification,
            "content_available" => true,
            'priority'          => 'high',
            'data'              => $notification_data
        );
        //echo '<pre>';
        //var_dump($post);
        // Initialize curl handle
        $ch = curl_init();

        // Set URL to GCM endpoint
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set request method to POST
        curl_setopt($ch, CURLOPT_POST, true);

        // Set our custom headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Get the response back as string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set JSON post data
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        // Actually send the push
        $result = curl_exec($ch);

        // Close curl handle
        curl_close($ch);

        // Debug GCM response

        $result_de = json_decode($result);

        return $result;

        //var_dump($result); die;

    }


}

$Firebase_Push_Notification_OBJ = new Firebase_Push_Notification();
