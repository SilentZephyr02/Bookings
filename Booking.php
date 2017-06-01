
<?php
/*
Plugin Name: Booking_Assignment
Plugin URI: http://farcue.com
Description: a failed attempt at a booking calender
Author: Mitch and John
Version: 1.0
Author URI: http://fbgm.com
Last update: 9th May 2017
*/

//=======================================================================================

$Assignment2_dbversion = "0.1";

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
//=======================================================================================

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
            PRIMARY KEY (id)
            )ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            $makebookingstable = 'CREATE TABLE BOOKINGS_TABLE (
            account_number int(11) NOT NULL,
            date_made date NOT NULL,
            date_of_arrival date NOT NULL,
            length_of_stay int(11) NOT NULL,
            reservation_or_booking text NOT NULL,
            room_number int NOT NULL,
            list_of_extras text NOT NULL,
            PRIMARY KEY (account_number)
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

    add_submenu_page( 'Bookings', 'Manage Account'   , 'Manage Account'  , 'read'            , 'accounts', 'WAD_plugin_menu_includes'   );
    add_submenu_page( 'Bookings', 'Manage Rooms'     , 'Manage Rooms'    , 'manage_options'  , 'rooms'   , 'WAD_plugin_menu_includes'      );
}

//=======================================================================================

function WAD_plugin_menu_includes() {
        $current_page = isset($_REQUEST['page']) ? esc_html($_REQUEST['page']) : 'Bookings';
        switch ($current_page) {
            case 'Assignment2': Bookings_CRUD();  //default
                break;
            case 'accounts': include('CRUD/Accounts.php');
                break;
            case 'rooms': include('CRUD/Room.php');
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
        $buffer .= '<li>'.$booking->account_number.'<br />'.$booking->room_number.'</li>';
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

function Bookings_CRUD(){
    echo '<h3>Bookings Page</h3>';
    echo '<div id="msg" style="overflow: auto"></div>
        <div class="wrap">
        <h2>Bookings <a href="?page=bookings&command=new" class="add-new">Add New</a></h2>
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
        echo '<p><a href="?page=bookings">Back to the Bookings list</a></p> Message: ' .$msg;   
    }
    echo '</div>';
}




function Bookings_view($bookingaccount_number) {
    global $wpdb;
    $qry = $wpdb->prepare("SELECT * FROM BOOKINGS_TABLE");
    $row = $wpdb->get_row($qry);
    echo '<p>';
    echo 'Room Number:';
    echo '<br />';
    echo $row->room_number;
    echo '<p>';
    echo 'Date Of Arrival';
    echo '<br />';
    echo $row->date_of_arrival;
    echo '<p><a href="?page=bookings">&laquo; Back to List</p>';
}





function Booking_delete($bookingaccount_number, $bookingdate_of_arrival) {
    global $wpdb;
    
    $results = $wpdb->query($wpdb->prepare("DELETE FROM BOOKINGS_TABLE WHERE account_number=$bookingaccount_number AND date_of_arrival = $bookingdate_of_arrival"));
    if ($results) {
        $msg = "Booking on $bookingdate_of_arrival by Account $bookingaccount_number was successfully deleted.";
    }
    return $msg;
}



function Booking_update($bookingdata) {
    global $wpdb, $current_user;
    $wpdb->update('BOOKINGS_TABLE',
        array( 'date_of_arrival' => date("d-m-Y"),
        'length_of_stay' => stripslashes_deep($bookingdata['length_of_stay']),
        'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
        'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));
        $msg = "Booking on ".$bookingdata['date_of_arrival']. "has been updated.";
        return $msg;
}




function Booking_insert($bookingdata) {
    global $wpdb;
    $wpdb->insert( 'BOOKINGS_TABLE',
    array(
        'account_number' => $bookingdata['account_number'],
        'date_made' => date("d-m-y"),
        'date_of_arrival' => date("d-m-y"),
        'length_of_stay' => $bookingdata['length_of_stay'],
        'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
        'room_number' => $bookingdata['room_number'],
        'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));
        $msg = "Booking for ".$bookingdata['date_of_arrival']."has been made.";
        return $msg;
}




function Bookings_list() {
    global $wpdb;
    $query = "SELECT account_number, date_of_arrival, length_of_stay, room_number FROM BOOKINGS_TABLE ORDER BY account_number DESC";
    $allbookings = $wpdb->get_results($query);
    echo '<table class="wp-list-table widefat">
		<thead>
		<tr>
			<th scope="col" class="manage-column">Account Number</th>
			<th scope="col" class="manage-column">Date Of Arrival</th>
			<th scope="col" class="manage-column">Length Of Stay</th>
			<th scope="col" class="manage-column">Room Number</th>
		</tr>
		</thead>
		<tbody>';
        foreach ($allbookings as $booking) {
            $edit_link = '?page=Bookings&command=edit';
            $view_link = '?page=Bookings&command=view';
            $delete_link = '?page=Bookings&command=delete';

            echo '<tr>';
            echo '<td>' . $booking->account_number . '</td>';
            echo '<td><strong><a href="'.$edit_link.'" title="Edit This Booking">' . $room->date_of_arrival . '</a></strong>';
            echo '<div class="row-actions">';
            echo '<span class="edit"><a href="'.$edit_link.'" title="Edit this item">Edit</a></span> | ';
            echo '<span class="view"><a href="'.$view_link.'" title="View this Item">View</a></span> | ';
            echo '<span class="trash"><a href="'.$delete_link.'" title="Delete This Item" onclick=return doDelete();">Trash</a></span>';
            echo'</div>';
            echo '</td>';
            echo '<td>' . $booking->length_of_stay . '</td>';
            echo '<td>' . $booking->room_number . '</td>';
            echo "<script type='text/javascript'>
                        function doDelete() { if (!confirm('Are you sure?')) return false; }
                    </script>";   
        }
        echo '</tbody></table>';
}



function Booking_form($command, $bookingaccount_number = null) {
    global $wpdb;
    if ($command == 'insert') {
        $booking->date_made = '';
        $booking->date_of_arrival = '';
        $booking->length_of_stay = '';
        $booking->reservation_or_booking = '';
        $booking->room_number = '';
        $booking->list_of_extras = '';
    }
    if ($command == 'update') {
        $booking = $wpdb->get_row("SELECT * FROM BOOKINGS_TABLE WHERE account_number = '.$bookingaccount_number");
    }
    echo '<form name="Booking_form" method="post" action="?page=Bookings">
    <input type="hidden" name="command" value="'.$command.'"/>
    <input type="date" name="date_made" value="'.$booking->date_made.'"/>
    <p>Date of Arrival<br />
    <input type="text" name="date_of_arrival" value="'.$booking->date_of_arrival.'" size="20" class="large-text"/>
    <p>Length Of Stay<br/>
    <input type="text" name="length_of_stay" value="'.$booking->length_of_stay.'" size="20" class="large-text"/>
    <p>Reservation Of Booking<br/>
    <input type="text" name="reservation_or_booking" value="'.$booking->reservation_or_booking.'" size="20" class="large-text"/>
    <p>List Of Extras<br/>
    <textarea name="list_of_extras" rows="10" cols="30" class="large-text">'.$booking->list_of_extras.'</textarea></p>
    </form>';
    echo '<p><a href="?page=Bookings">&laquo Back To List</p>';
}

?>