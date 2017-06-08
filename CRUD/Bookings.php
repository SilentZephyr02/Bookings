<?php
//=======================================================================================
function Bookings_CRUD(){ // the CRUD function for the bookings including a switch statement for the different commands
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
function Bookings_view($bookingaccount_number) {//this function is used when the user want to view a specific booking
    global $wpdb;
    $qry = $wpdb->prepare("SELECT * FROM BOOKINGS_TABLE");
    $row = $wpdb->get_row($qry);
    echo '<p>Date Made:';
    echo $row->date_made;
    echo '</p><p>';
    echo 'Room Type:';
    echo '<br />';
    echo $row->room_type;
    echo '</p>';
    echo '<p>Date Of Arrival';
    echo '<br />';
    echo $row->date_of_arrival;
    echo '</p><p>Date of Departure:<br/>';
    echo $row->date_of_departure;
    echo '</p><p>Reservation or Booking:';
    echo $row->reservation_or_booking;
    echo '</p><p>List Of Extras:<br/>';
    echo $row->list_of_extras;

    echo '</p><p><a href="?page=Bookings">&laquo; Back to List</p>';
}





//=======================================================================================
function Booking_delete($bookingaccount_number, $bookingdate_of_arrival) {//function for deleting a booking in the database
    global $wpdb;
    
    $results = $wpdb->query($wpdb->prepare("DELETE FROM BOOKINGS_TABLE WHERE account_number = $bookingaccount_number AND date_of_arrival = $bookingdate_of_arrival"));
    if ($results) {
        $msg = "Booking on $bookingdate_of_arrival by Account $bookingaccount_number was successfully deleted.";
    }
    return $msg;
}



//=======================================================================================
function Booking_update($bookingdata) {// function for updating a current booking in the database
    global $wpdb, $current_user;

    $wpdb->update('BOOKINGS_TABLE',
        array( 
            'date_of_arrival' => $bookingdata['date_of_arrival'],
            'date_of_departure' => $bookingdata['date_of_departure'],
            'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
            'room_type' => stripslashes_deep($bookingdata['room_type']),
            'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));
        $msg = "Booking on ".$bookingdata['date_of_arrival']. "has been updated.";
        return $msg;
}



//=======================================================================================
function Booking_insert($bookingdata) { //function for adding a new booking into the database
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
 
    pr($bookingdata);
    $date = date('Y-m-d');

    $wpdb->insert( 'BOOKINGS_TABLE',
    array(
        'account_number' => $current_userID,
        'date_made' => $date,
        'date_of_arrival' => $bookingdata['date_of_arrival'],
        'date_of_departure' => $bookingdata['date_of_departure'],
        'reservation_or_booking' => stripslashes_deep($bookingdata['reservation_or_booking']),
        'room_type' => stripslashes_deep($bookingdata['room_type']),
        'list_of_extras' => stripslashes_deep($bookingdata['list_of_extras'])));

        $wpdb->show_errors();
        $msg = "Booking for ".$current_userID." has been made.";
        return $msg;
}




//======================================================================================
function Bookings_list() { //The function for displaying all the users bookings
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
        $edit_link = '?page=Bookings&id='. $booking->id.'&command=edit';
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
function Booking_form($command, $bookingaccount_number = null) {//The function/form for entering bookings into the database
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
        $booking->date_made = date("Y/m/d");
        $booking->date_of_arrival = '';
        $booking->date_of_departure = '';
        $booking->reservation_or_booking = '';
        $booking->room_type = '';
        $booking->list_of_extras = '';
    }
    if ($command == 'update') {
        $booking = $wpdb->get_row("SELECT * FROM BOOKINGS_TABLE WHERE account_number = '$bookingaccount_number'");
    }
    $roomqry = "SELECT room_type FROM ROOMS_TABLE";
    $allrooms = $wpdb->get_results($roomqry);
    echo '<form name="Booking_form" method="post" action="?page=Bookings">
    <input type="hidden" name="command" value="'.$command.'"/>
    <p>Date of Arrival (YYYY-MM-DD)<br/>
    <input type="text" name="date_of_arrival" value="'.$booking->date_of_arrival.'" size="20" class="large-text" id="date_of_arrival"/>
    <p>Date of Departure (YYYY-MM-DD)<br/>
    <input type="text" name="date_of_departure" value="'.$booking->date_of_departure.'" size="20" class="large-text" id="date_of_departure"/>
    <p>Reservation Or Booking<br/>
    <p>Reservation</p>
    <input type="radio" name="reservation_or_booking" value="reservation" /> 
    <p>Booking</p>

    <input type="radio" name="reservation_or_booking" value="booking" />
    <p>Room Type<br/>
    
     <select name="room_type" value="'.$booking->room_type.'">';
     foreach ($allrooms as $room) {//to show the different types of rooms available for booking
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