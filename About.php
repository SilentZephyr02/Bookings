
<?php
/*
Plugin Name: Booking_Assignment
Plugin URI: http://google.com
Description: A Booking Calender
Author: Mitch and John
Version: 1.0
Author URI: http://google.com
Last update: 9th May 2017
*/

//=======================================================================================

$Assignment2_dbversion = "1.28";

if (!function_exists('pr')) {
    function pr($var) {echo '<pre>'; var_dump($var); echo '</pre>';}
}

//=======================================================================================
register_activation_hook( __FILE__, 'Assignment2install' );
register_uninstall_hook( __FILE__, 'Assignment2uninstall' );

add_action('plugins_loaded', 'Assignment2_update_dbcheck');
add_action('plugin_action_links_'.plugin_basename(__FILE__), 'Assignment2settingslink');

add_shortcode('displayAccountsTable', 'display_accounts_table');
add_shortcode('displayBookingTable', 'display_booking_table');
add_shortcode('displayRoomsTable', 'display_rooms_table');
add_action('admin_menu' , 'Assignment2_menu');
add_action( 'wp_enqueue_scripts', 'load_scripts' );

//=======================================================================================
function load_scripts() {
     wp_enqueue_style( 'WAD11', plugins_url('css/WAD11.css',__FILE__));
     wp_enqueue_script( 'jquery-ui-datepicker');
     wp_enqueue_script( 'json2' );
}

//=======================================================================================
add_action( 'register_form', 'myplugin_register_form' );
function myplugin_register_form() {


    $first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
    $last_name = ( ! empty( $_POST['last_name'] ) ) ? trim( $_POST['last_name'] ) : '';
    $address = ( ! empty( $_POST['address'] ) ) ? trim( $_POST['address'] ) : '';
    $phone_number = ( ! empty( $_POST['phone_number'] ) ) ? trim( $_POST['phone_number'] ) : '';
        
        ?>
        <p>
            <label for="first_name"><?php _e( 'First Name', 'mydomain' ) ?><br />
                <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" size="25" /></label>
            <label for="last_name"><?php _e( 'Last Name', 'mydomain' ) ?><br />
                <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr( wp_unslash( $last_name ) ); ?>" size="25" /></label>
            <label for="address"><?php _e( 'Address', 'mydomain' ) ?><br />
                <input type="text" name="address" id="address" class="input" value="<?php echo esc_attr( wp_unslash( $address ) ); ?>" size="25" /></label>
            <label for="phone_number"><?php _e( 'Phone Number', 'mydomain' ) ?><br />
                <input type="text" name="phone_number" id="phone_number" class="input" value="<?php echo esc_attr( wp_unslash( $phone_number ) ); ?>" size="25" /></label>
        </p>
        <?php
    }

add_filter( 'registration_errors', 'myplugin_registration_errors', 10, 3 );
function myplugin_registration_errors( $errors, $sanitized_user_login, $user_email ) { 
    if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
        $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'mydomain' ) );
    }
    else if(!preg_match('/^[A-z]+$/',$_POST['first_name'])){
        $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: First Name is invaild.', 'mydomain' ) );
    }


    if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
        $errors->add( 'last_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'mydomain' ) );
    }
    else if(!preg_match('/^[A-z]+$/',$_POST['last_name'])){
        $errors->add( 'last_name_error', __( '<strong>ERROR</strong>: Last Name is invaild.', 'mydomain' ) );
    }

    if ( empty( $_POST['address'] ) || ! empty( $_POST['address'] ) && trim( $_POST['address'] ) == '' ) {
        $errors->add( 'address_error', __( '<strong>ERROR</strong>: You must include an Address.', 'mydomain' ) );
    }
    else if(!preg_match('/^\d{1,5}[A-z]?\s[A-z]+\s[A-z]+$/',$_POST['address'])) {
        $errors->add( 'address_error', __( '<strong>ERROR</strong>: Address is invaild.', 'mydomain' ) );
    }
    
    if ( empty( $_POST['phone_number'] ) || ! empty( $_POST['phone_number'] ) && trim( $_POST['phone_number'] ) == '' ) {
        $errors->add( 'phone_number_error', __( '<strong>ERROR</strong>: You must include a Phone number.', 'mydomain' ) );
    }
    else if(!preg_match('/^\d{7,15}$/',$_POST['phone_number'])) {
        $errors->add( 'phone_number_error', __( '<strong>ERROR</strong>: The phone number is not valid.', 'mydomain' ) );    
    }

    return $errors;
}


add_action( 'user_register', 'myplugin_user_register' );
function myplugin_user_register( $user_id ) {
    global $wpdb;
    if ( ! empty( $_POST['first_name'] ) ) {
        update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
    }
    if ( ! empty( $_POST['last_name'] ) ) {
        update_user_meta( $user_id, 'last_name', trim( $_POST['last_name'] ) );
    }

    $date = date('Y-m-d');
    $wpdb->insert( 'ACCOUNTS_TABLE',
        array(
            'account_created_date' => $date,
            'first_name' => stripslashes_deep($_POST['first_name']),
            'last_name' => stripslashes_deep($_POST['last_name']),
            'address' => stripslashes_deep($_POST['address']),
            'phone_number' => $_POST['phone_number'],
            'wp_id' => $user_id));
            }

function auto_login_new_user( $user_id ) {
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    wp_redirect( home_url( 'wp-admin/admin.php?page=Bookings' ));
    exit;
}
add_action( 'user_register', 'auto_login_new_user' );

//=======================================================
function Assignment2install () {
    global $wpdb;
    global $Assignment2_dbversion;

    update_option( "users_can_register", 1 );
    
    $currentversion = get_option( "Assignment2_dbversion");
    if ($Assignment2_dbversion > $currentversion) {
        if ($wpdb->get_var("show tables like 'ACCOUNTS_TABLE'") != 'ACCOUNTS TABLE') {

            $makeaccounttable = 'CREATE TABLE ACCOUNTS_TABLE (
            id int(11) NOT NULL auto_increment,
            account_created_date date NOT NULL,
            first_name CHAR(30) NOT NULL,
            last_name CHAR(30) NOT NULL,
            address CHAR(100) NOT NULL,
            phone_number int NOT NULL,
            wp_id CHAR(50) NOT NULL,
            PRIMARY KEY (id)
            )ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $makebookingstable = 'CREATE TABLE BOOKINGS_TABLE (
            account_number int(11) NOT NULL,
            date_made date NOT NULL ,
            date_of_arrival date NOT NULL,
            date_of_departure date NOT NULL,
            reservation_or_booking CHAR(30) NOT NULL,
            room_type CHAR(20) NOT NULL,
            list_of_extras CHAR(120) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $makeroomstable = 'CREATE TABLE ROOMS_TABLE (
            room_number int NOT NULL,
            room_type CHAR(30) NOT NULL,
            features CHAR(200) NOT NULL,
            berth int NOT NULL,
            price int NOT NULL,
            PRIMARY KEY (room_number)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($makeaccounttable);
            dbDelta($makebookingstable);
            dbDelta($makeroomstable);
            update_option( "Assignment2_dbversion", $Assignment2_dbversion );
            add_option("Assignment2_dbversion", $Assignment2_dbversion );
        }
    }
}
//=======================================================================================
function Assignment2_update_dbcheck() { 
    global $Assignment2_dbversion;
    if (get_site_option('Assignment2_dbversion') != $Assignment2_dbversion) Assignment2install();
}
//=======================================================================================
function Assignment2uninstall() {
    delete_site_option( $Assignment2_dbversion );
    delete_option( $Assignment2_dbversion );
}
//=======================================================================================
function Assignment2settingslink($links) {
    array_unshift($links, '<a href="',admin_url('plugins.php?page=Assignment2simple').'">Settings</a>');
    return $links;
}

//=======================================================================================

function Assignment2_menu() {
    add_menu_page( 'Bookings App', 'About' , 'read' , 'About','aboutPage' );
  //add_menu_page( 'Assignment2Settings', 'Bookings' , 'read' , 'Bookings','Bookings_CRUD' );

    add_submenu_page( 'About', 'Manage Bookings'  , 'Manage Bookings'  , 'read'            , 'Bookings','WAD_plugin_menu_includes' );
    add_submenu_page( 'About', 'Manage Account'   , 'Manage Account'  , 'manage_options'  , 'Accounts', 'WAD_plugin_menu_includes'   );
    add_submenu_page( 'About', 'Manage Rooms'     , 'Manage Rooms'    , 'manage_options'  , 'Rooms'   , 'WAD_plugin_menu_includes'   );
}

//=======================================================================================

function WAD_plugin_menu_includes() {
        $current_page = isset($_REQUEST['page']) ? esc_html($_REQUEST['page']) : 'Bookings';
        switch ($current_page) {
            case 'Assignment2': aboutPage();  //default
                break;
            case 'Bookings': include('CRUD/Bookings.php');
                break;
            case 'Accounts': include('CRUD/Accounts.php');
                break;
            case 'Rooms': include('CRUD/Room.php');
        }
}

/*
function display_accounts_table() {
    global $wpdb;

    $query = "SELECT * FROM ACCOUNTS_TABLE ORDER BY id DESC";
    $allaccounts = $wpdb->get_results($query);
    $buffer = '<ol>';
    foreach ($allaccounts as $account) {
        $buffer .= '<li>'.$account->first_name.'<br />'.$account->last_name.'</li>';   
    }
    $buffer .='</ol>';
    return $buffer;
}

function display_booking_table() {
    global $wpdb;

    $query = " SELECT * FROM BOOKINGS_TABLE ORDER BY account_number DESC";
    $allbookings = $wpdb->get_results($query);
    $buffer = '<ol>';
    foreach ($allbookings as $booking) {
        $buffer .= '<li>'.$booking->account_number.'<br />'.$booking->room_type.'</li>';
    }
    $buffer .= '</ol>';
    return $buffer;
}


function display_rooms_table() {
    global $Assignment2_db;

    $query = "SELECT * FROM ROOMS_TABLE ORDER BY room_number DESC";
    $allrooms = $Assignment2_db->get_results($query);
    $buffer = '<ol>';
    foreach ($allrooms as $room) {
        $buffer .= '<li>'.$room->room_number.'<br />'.$room->room_type.'</li>';
    }
    $buffer .= '</ol>';
    return $buffer;
}
*/
 function aboutPage(){

 }

?>