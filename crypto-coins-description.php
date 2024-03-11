<?php
/*
Plugin Name: Crypto Coins Description
Description: This is a custom plugin to add and manage descriptions for different crypto coins.
Author: Your Name
Version: 1.0
*/


define('CRYPTO_PLUGIN_DIR_PATH',plugin_dir_path( __FILE__ ));
define('CRYPTO_PLUGIN_URL',plugin_dir_url(__FILE__ ));
define('TABLE_NAME','crypto_coins_description');

//Enqueue css and js file
function add_plugin_script()
{
    wp_enqueue_style(
        'quiz_fontawesome_style', //unique name
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', //path url
    );
    
    wp_enqueue_style(
        'crypto_style', //unique name
        CRYPTO_PLUGIN_URL.'assets/css/style.css', //path url
        '', //dependencies
        time() //ver
    );

    wp_enqueue_script(
        'quiz_jquery_script', //unique name
        CRYPTO_PLUGIN_URL.'assets/js/jquery-min.js', // path url
        '',
        time(), //ver
        
    );
  
    wp_enqueue_script(
        'ajax_script', //unique name
        CRYPTO_PLUGIN_URL.'assets/js/ajax-script.js', // path url
         '',
         time(),
        
    );
    wp_localize_script('ajax_script','crypto_desc_url', array('ajax_url' => admin_url( 'admin-ajax.php' )));
}
add_action('init','add_plugin_script');


function register_admin_page_menu()
{
    add_menu_page(
        "coins_description", // menu name
        'Coins Description', // menu title
        'manage_options', // menu level
        'coins-description', // slug
        'AllDescription', // callback function
        'dashicons-media-document', // wordpress built icon
        11 //position 
    );
    add_submenu_page(
        'coins-description', // parent slug
        'All Description',        // page title
        'All Description',        // menu title
        'manage_options', // capability
        'coins-description',        // current slug
        'AllDescription', // callback       
    );
   
    

}
function AllDescription()
{
    //include menu page
    include_once(CRYPTO_PLUGIN_DIR_PATH.'view/all_description.php');
}

// add admin menu ans submenu page
add_action( 'admin_menu', 'register_admin_page_menu' );


// Then we'll set up activation and deactivation hooks for when the plugin is activated/deactivated.
register_activation_hook(__FILE__, 'crypto_coins_description_activation');
register_deactivation_hook(__FILE__, 'crypto_coins_description_deactivation');

function crypto_coins_description_activation() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . TABLE_NAME;

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        coin_id varchar(100) NOT NULL,
        coin_name varchar(100) NOT NULL,
        coin_description text NOT NULL,
        coin_status tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    add_option("crypto_plugin_db_version", "1.0");
}

function crypto_coins_description_deactivation() {
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME;

    $sql=" DROP TABLE IF EXISTS ".$table_name.";";
    $wpdb->query($sql);
    delete_option("crypto_plugin_db_version");
}

add_action('wp_ajax_sync_coins_list', 'sync_coins_list');
add_action('wp_ajax_nopriv_sync_coins_list', 'sync_coins_list');

function sync_coins_list(){

    $api_url = 'https://pro-api.coingecko.com/api/v3/coins/list';
    $parameters = array(
        'x_cg_pro_api_key' => 'CG-9mvqVvqWn8zXkdswxCdrsseF'
    );
    $request_url = add_query_arg($parameters, $api_url);
    $response = wp_remote_get($request_url);

    if (is_wp_error($response)) {
        // Handle error
        echo 'API request failed.';
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        echo insert_crypto_list($data);
    }
    wp_die();
}

function insert_crypto_list($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crypto_coins_description'; 

    // Format of each of the values in $data
    $data_format = array(
        '%s',  // 'coin_id'
        '%s',  // 'coin_name'
        '%s'   // 'coin_description'
    );

    foreach($data as $item) {
        // Check if the coin_id already exists in the table
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE coin_id = %s", $item['id']));

        // If it does not exist, insert it
        if (null === $existing) {
            $insertData = array(
                'coin_id' => $item['id'], 
                'coin_name' => $item['name'], 
                'coin_description' => ""  // or some value if you have
            );

            // Insert the data into the table
            $wpdb->insert($table_name, $insertData, $data_format);
        }
    }

      // Number of rows inserted
      $rows_inserted = $wpdb->rows_affected;
      return "Number of rows inserted: " . $rows_inserted;
}

add_action('wp_ajax_get_coins_list', 'get_coins_list');
add_action('wp_ajax_nopriv_get_coins_list', 'get_coins_list');

function get_coins_list(){

    global $wpdb;
    $table_name = $wpdb->prefix . 'crypto_coins_description';
    $page_num = $_POST['page_num'];
    $items_per_page = $_POST['items_per_page'];

    $offset = ( $page_num - 1 ) * $items_per_page;


    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY id ASC LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    ), ARRAY_A );
    echo  json_encode($results);
    wp_die();

}
add_action('wp_ajax_get_search_list', 'get_search_list');
add_action('wp_ajax_nopriv_get_search_list', 'get_search_list');
function get_search_list(){

    global $wpdb;
    $table_name = $wpdb->prefix . 'crypto_coins_description';
    $search_data = $_POST['search_data'];
    $search_like = '%' . $wpdb->esc_like($search_data) . '%';

    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE coin_name LIKE %s",
        $search_like
    ), ARRAY_A );
    
    echo json_encode($results);
    wp_die();

}

add_action('wp_ajax_get_coins_decription', 'get_coins_decription');
add_action('wp_ajax_nopriv_get_coins_decription', 'get_coins_decription');

function get_coins_decription()
{

    $coin_id = $_POST['coin_id'];
    $api_url = 'https://pro-api.coingecko.com/api/v3/coins/' . $coin_id;
    $parameters = array(
        'x_cg_pro_api_key' => 'CG-9mvqVvqWn8zXkdswxCdrsseF'

    );
    $request_url = add_query_arg($parameters, $api_url);
    $response = wp_remote_get($request_url);
    if (is_wp_error($response)) {
        echo 'API request failed.';
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        echo json_encode($data['description']['en']);
      
    }
    wp_die();
}


add_action('wp_ajax_update_coins_decription', 'update_coins_decription');
add_action('wp_ajax_nopriv_update_coins_decription', 'update_coins_decription');

function update_coins_decription()
{

    $coin_id = sanitize_text_field($_POST['coin_id']);
    $coin_description = sanitize_text_field($_POST['coin_description']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . TABLE_NAME; 
    $data = array(
        'coin_description' => $coin_description,
        'coin_status' => 1,
        'updated_at' => current_time('mysql', 1)
    );
    
    $where = array('coin_id' => $coin_id);
    
    $format = array('%s', '%d', '%s');
    $where_format = array('%s'); 
    
    $result = $wpdb->update($table_name, $data, $where, $format, $where_format);
    if($result) {
       echo json_encode( array('status'=>'success'));
    }else{
        echo json_encode( array('status'=>'failed'));
    }
    


    wp_die();
}


?>
