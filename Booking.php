
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

$Assignment2_dbversion = "0.6";

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
//1. Add a new form element...
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

    //2. Add validation. In this case, we make sure first_name is required.
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

    //3. Finally, save our extra registration user meta.
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

//=======================================================
function Assignment2install () {
    global $wpdb;
    global $Assignment2_dbversion;

    $currentversion = get_option( "Assignment2_dbversion");
    if ($Assignment2_dbversion > $currentversion) {
        if ($wpdb->get_var("show tables like 'ACCOUNTS_TABLE'") != 'ACCOUNTS TABLE') {

            $makeaccounttable = 'CREATE TABLE ACCOUNTS_TABLE (
            id int(11) NOT NULL auto_increment,
            account_created_date date NOT NULL,
            first_name text NOT NULL,
            last_name text NOT NULL,
            address text NOT NULL,
            phone_number int NOT NULL,
            wp_id text NOT NULL,
            PRIMARY KEY (id)
            )ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $makebookingstable = 'CREATE TABLE BOOKINGS_TABLE (
            account_number int(11) NOT NULL,
            date_made date NOT NULL,
            date_of_arrival date NOT NULL,
            date_of_departure date NOT NULL,
            reservation_or_booking text NOT NULL,
            room_type text NOT NULL,
            list_of_extras text NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $makeroomstable = 'CREATE TABLE ROOMS_TABLE (
            room_number int NOT NULL,
            room_type text NOT NULL,
            features text NOT NULL,
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
    add_menu_page( 'Assignment2Settings', 'Bookings' , 'read' , 'Bookings','Bookings_CRUD' );

    add_submenu_page( 'Bookings', 'Manage Account'   , 'Manage Account'  , 'manage_options'  , 'Accounts', 'WAD_plugin_menu_includes'   );
    add_submenu_page( 'Bookings', 'Manage Rooms'     , 'Manage Rooms'    , 'manage_options'  , 'Rooms'   , 'WAD_plugin_menu_includes'   );
}

//=======================================================================================

function WAD_plugin_menu_includes() {
        $current_page = isset($_REQUEST['page']) ? esc_html($_REQUEST['page']) : 'Bookings';
        switch ($current_page) {
            case 'Assignment2': Bookings_CRUD();  //default
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

//=======================================================================================
function Bookings_CRUD(){
    echo '<h3>Bookings Page</h3>';
    echo '<h3>Contents of the POST data</h3>';
    pr($_POST);
    echo '<h3>Contents of the REQUEST data</h3>';
    pr($_REQUEST);
    echo '<div id="msg" style="overflow: auto"></div>
        <div class="wrap">
        <h2>Bookings <a href="?page=Bookings&command=new" class="add-new-h2">Add New</a></h2>
        <div style="clear: both"></div>';

    $bookingdata = $_POST;

    if (isset($_REQUEST['account_number'])) {
        $bookingaccount_number = $_REQUEST['account_number'];
        $bookingdate_of_arrival = $_REQUEST['date_of_arrival'];
    }
    else
        $bookingaccount_number = '';
        $bookingdate_of_arrival = '';

    if (isset($_REQUEST["command"]))
        $command = $_REQUEST["command"];
    else
        $command = '';
    
    switch ($command) {
        case 'view':
            Bookings_view($bookingaccount_number);
        break;

        case 'edit':
            $msg = Booking_form('update', $bookingaccount_number);
        break;

        case 'new':
            $msg = Booking_form('insert',null);
        break;

        case 'delete':
            $msg = Booking_delete($bookingaccount_number, $bookingdate_of_arrival);
            $command = '';
        break;

        case 'update':
            $msg = Booking_update($bookingdata);
            $command = '';
        break;

        case 'insert':
            $msg = Booking_insert($bookingdata);
            $command = '';
        break;
    }
    
    if(empty($command)) Bookings_list();
    if (!empty($msg)) {
        echo '<p><a href="?page=Bookings">Back to the Bookings list</a></p> Message: ' .$msg;   
    }
    echo '</div>';
}




//=======================================================================================
function Bookings_view($bookingaccount_number) {
    global $wpdb;
    $qry = $wpdb->prepare("SELECT * FROM BOOKINGS_TABLE");
    $row = $wpdb->get_row($qry);
    echo '<p>';
    echo 'Room Type:';
    echo '<br />';
    echo $row->room_type;
    echo '<p>';
    echo 'Date Of Arrival';
    echo '<br />';
    echo $row->date_of_arrival;
    echo '<p><a href="?page=Bookings">&laquo; Back to List</p>';
}





//=======================================================================================
function Booking_delete($bookingaccount_number, $bookingdate_of_arrival) {
    global $wpdb;
    
    $results = $wpdb->query($wpdb->prepare("DELETE FROM BOOKINGS_TABLE WHERE account_number=$bookingaccount_number AND date_of_arrival = $bookingdate_of_arrival"));
    if ($results) {
        $msg = "Booking on $bookingdate_of_arrival by Account $bookingaccount_number was successfully deleted.";
    }
    return $msg;
}



//=======================================================================================
function Booking_update($bookingdata) {
    global $wpdb, $current_user;
    $wpdb->update('BOOKINGS_TABLE',
        array( 'date_of_arrival' => date("d-m-Y"),
        'date_of_departure' => date("d-m-Y"),
        'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
        'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));
        $msg = "Booking on ".$bookingdata['date_of_arrival']. "has been updated.";
        return $msg;
}



//=======================================================================================
function Booking_insert($bookingdata) {
    global $wpdb, $current_user;

    $query = "SELECT id, wp_id  FROM ACCOUNTS_TABLE";
    $usersID = $wpdb->get_results($query);
    foreach ($usersID as $wpID){
        pr($current_user);
                pr($wpID);
        if($wpID->wp_id==$current_user->ID){
            $current_userID = $wpID->id;
               pr($current_userID);
                
        }
    }
 


    $wpdb->insert( 'BOOKINGS_TABLE',
    array(
        'account_number' => $current_userID,
        'date_made' => $bookingdata['date_made'],
        'date_of_arrival' => $bookingdata['date_of_arrival'],
        'date_of_departure' => $bookingdata['date_of_departure'],
        'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
        'room_type' => stripslashes_deep($bookingdata['room_type']),
        'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));
        $msg = "Booking for ".$current_userID." has been made.";
        return $msg;
}




//=======================================================================================
function Bookings_list() {
    global $wpdb, $current_user;
    $query = "SELECT account_number, date_of_arrival, date_of_departure, room_type FROM BOOKINGS_TABLE ORDER BY account_number DESC";
    $allbookings = $wpdb->get_results($query);
    echo '<table class="wp-list-table widefat">
		<thead>
		<tr>
			<th scope="col" class="manage-column">Account Number</th>
			<th scope="col" class="manage-column">Date Of Arrival</th>
			<th scope="col" class="manage-column">Date of Departure</th>
			<th scope="col" class="manage-column">Room Type</th>
		</tr>
		</thead>
		<tbody>';

    $query = "SELECT id, wp_id  FROM ACCOUNTS_TABLE";
    $usersID = $wpdb->get_results($query);
    foreach ($usersID as $wpID){
        if($wpID->wp_id==$current_user->ID){
            $current_userID = $wpID->id;
        }
    }
    foreach ($allbookings as $booking) {
        if (current_user_can( 'manage_options' ) || $current_userID == $booking->account_number){
        $edit_link = '?page=Bookings&command=edit';
        $view_link = '?page=Bookings&command=view';
        $delete_link = '?page=Bookings&command=delete';

        echo '<tr>';
        echo '<td>' . $booking->account_number . '</td>';
        echo '<td><strong><a href="'.$edit_link.'" title="Edit This Booking">' . $booking->date_of_arrival . '</a></strong>';
        echo '<div class="row-actions">';
        echo '<span class="edit"><a href="'.$edit_link.'" title="Edit this item">Edit</a></span> | ';
        echo '<span class="view"><a href="'.$view_link.'" title="View this Item">View</a></span> | ';
        echo '<span class="trash"><a href="'.$delete_link.'" title="Delete This Item" onclick=return doDelete();">Trash</a></span>';
        echo'</div>';
        echo '</td>';
        echo '<td>' . $booking->date_of_departure . '</td>';
        echo '<td>' . $booking->room_type . '</td>';
        echo "<script type='text/javascript'>
                    function doDelete() { if (!confirm('Are you sure?')) return false; }
                </script>";   
        }
    }
        echo '</tbody></table>';
    
}



//=======================================================================================
function Booking_form($command, $bookingaccount_number = null) {
    global $wpdb;
    ?> <script>
    jQuery(function() {
		jQuery( "#date_of_arrival" ).datepicker({ dateFormat: 'yy-m-d' });
		jQuery( "#date_of_departure" ).datepicker({ dateFormat: 'yy-m-d' });
        jQuery( "#date_made" ).datepicker({ dateFormat: 'yy-m-d' });
    });
    </script>
    <?php
    if ($command == 'insert') {
        $booking->date_made = '';
        $booking->date_of_arrival = '';
        $booking->date_of_departure = '';
        $booking->reservation_or_booking = '';
        $booking->room_type = '';
        $booking->list_of_extras = '';
    }
    if ($command == 'update') {
        $booking = $wpdb->get_row("SELECT * FROM BOOKINGS_TABLE WHERE account_number = '.$bookingaccount_number");
    }
    $roomqry = "SELECT room_type FROM ROOMS_TABLE";
    $allrooms = $wpdb->get_results($roomqry);

    echo '<form name="Booking_form" method="post" action="?page=Bookings">
    <input type="hidden" name="command" value="'.$command.'"/>
     <input type="text" name="date_made" value="'.$booking->date_made.'" size="20" class="large-text" id="date_made"/>
    <p>Date of Arrival<br />
    <input type="text" name="date_of_arrival" value="'.$booking->date_of_arrival.'" size="20" class="large-text" id="date_of_arrival"/>
    <p>Date of Departure<br/>
    <input type="text" name="date_of_departure" value="'.$booking->date_of_departure.'" size="20" class="large-text" id="date_of_departure"/>
    <p>Reservation Or Booking<br/>
    <p>Reservation</p><input type="radio" name="reservation_or_booking" value="reservation" /> <p>Booking</p><input type="radio" name="reservation_or_booking" value="booking" />
    <p>Room Type<br/>
    
     <select name="Room_type" value="'.$booking->room_type.'">';
     foreach ($allrooms as $room) {
         echo '<option value="'.$room->room_type.'" name="'.$room->room_type.'">'.$room->room_type.'</option>';
     }
    echo '</select>
    <p>List Of Extras<br/>
    <textarea name="list_of_extras" rows="10" cols="30" class="large-text">'.$booking->list_of_extras.'</textarea></p>
     <p class="submit"><input type="submit" name="submit" value="Save Changes" class="button-primary" /></p>
    </form>';
    echo '<p><a href="?page=Bookings">&laquo Back To List</p>';

}
?>