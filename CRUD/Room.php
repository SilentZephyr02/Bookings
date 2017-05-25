<?php
//=======================================================================================
function Rooms_CRUD() {
    echo '<div id="msg" style="overflow: auto"></div>
        <div class="wrap">
        <h2>Rooms <a href="?page=rooms&command=new" class="add-new-h2">Add New</a></h2>
        <div style="clear: both"></div>';
    
    $roomdata = $_POST;

    if (isset($_REQUEST['room_number']))
        $room_number = $_REQUEST['room_number'];
    else
        $room_number = '';

    if (isset($_REQUEST["command"]))
        $command = $_REQUEST["command"];
    else
        $command = '';

    switch ($command) {
        case 'view':
            Rooms_view($room_number);
        break;

        case 'edit':
            $msg = Room_form('update', $room_number);
        break;

        case 'new':
            $msg = Room_form('insert',null);
        break;

        case 'delete':
            $msg = Room_delete($room_number);
            $command = '';
        break;

        case 'update':
            $msg = Room_update($roomdata);
            $command = '';
        break;

        case 'insert':
            $msg = Room_insert($roomdata);
            $command = '';
        break;
        }
        if (empty($command)) Room_list();
        if (!empty($msg)) {
            echo '<p><a href="?page=rooms">Back to the Rooms List </a></p> Message: '.$msg;
        }
        echo '</div>';
}
//=======================================================================================
function Rooms_view($room_number) {
    global $wpdb;
    $qry = $wpdb->prepare("SELECT * FROM ROOMS_TABLE");
    $row = $wpdb->get_row($qry);
    echo '<p>';
    echo 'Room Number:';
    echo '<br />';
    echo $row->room_number;
    echo '<p>';
    echo 'Room Type:';
    echo '<br />';
    echo $row->room_type;
    echo '<p><a href="?page=rooms">&laquo; Back to the List</p>';
}
//=======================================================================================
function Room_delete($room_number) {
    global $wpdb;

    $results = $wpdb->query($wpdb->prepare("DELETE FROM ROOMS_TABLE WHERE room_number = $room_number"));
    if ($results){
        $msg = "Room Number $room_number successfully deleted";
    }
    return $msg;
}
//=======================================================================================
function Room_insert($roomdata) {
    global $wpdb;
    $wpdb->insert( 'ROOMS_TABLE',
    array(
        'room_number' => $roomdata['room_number'],
        'room_type' => stripslashes_deep($roomdata['room_type']),
        'features' => stripslashes_deep($roomdata['features']),
        'berth' => $roomdata['berth'],
        'price' => $roomdata['price']));
        $msg = "Room ".$roomdata['room_number']."has been made";
        return $msg;
}
//=======================================================================================
function Room_update($roomdata) {
    global $wpdb, $current_user;
    $wpdb->update('ROOMS_TABLE',
    array( 'room_type' => stripslashes_deep($roomdata['room_type']),
    'features' => stripslashes_deep($roomdata['features']),
    'berth' => stripslashes_deep($roomdata['berth']),
    'price' => stripslashes_deep($roomdata['price'])));
    $msg = "Room Number ".$roomdata['room_number']."has been updated";
    return $msg;
}
//=======================================================================================
function Room_list() {
    global $wpdb;
    $query = "SELECT room_number, room_type, berth, price FROM ROOMS_TABLE ORDER BY room_number DESC";
    $allrooms = $wpdb->get_results($query);
    echo '<table class="wp-list-table widefat">
		<thead>
		<tr>
			<th scope="col" class="manage-column">Room Number</th>
			<th scope="col" class="manage-column">Room Type</th>
			<th scope="col" class="manage-column">Berth</th>
			<th scope="col" class="manage-column">Price</th>
		</tr>
		</thead>
		<tbody>';
        foreach ($allrooms as $rooms) {
            $edit_link = '?page=Rooms&command=edit';
            $view_link = '?page=Rooms&command=edit';
            $delete_link = '?page=Rooms&command=delete';

            echo '<tr>';
            echo '<td>' . $room->room_number . '</td>';
            echo '<td><strong><a href="'.$edit_link.'" title="Edit This Room">' . $room->room_type . '</a></strong>';
            echo '<span class="edit"><a href="'.$edit_link.'" title="Edit this item">Edit</a></span> | ';
            echo '<span class="view"><a href="'.$view_link.'" title="View this Item">View</a></span> | ';
            echo '<span class="trash"><a href="'.$delete_link.'" title="Delete This Item" onclick=return doDelete();">Trash</a></span>';
            echo'</div>';
            echo '</td>';
            echo '<td>' . $room->berth . '</td>';
            echo '<td>$' . $booking->price . '</td>';
            echo '</tbody></table>';
            echo "<script type='text/javascript'>
                        function doDelete() { if (!confirm('Are you sure?')) return false; }
                    </script>";
            }
            echo '<div class="row-actions">';
}
//=======================================================================================
function Room_form($command, $room_number = null) {
    global $wpdb;
    if ($command == 'insert') {
      $room->room_number = '';
      $room->room_type   = '';
      $room->features = '';
      $room->berth = '';
	  $room->price   = '';
    }
    if ($command =='update') {
        $account = $wpdb->get_row("SELECT * FROM ROOMS_TABLE ORDER BY room_number DESC");
    }
    echo '<form name="rooms_form" method="post" action="?page=Rooms">
		<input type="hidden" name="command" value="'.$command.'"/>

		<p>Room Number:<br/>
		<input type="text" name="room_number" value="'.$room->room_number.'" size="20" class="large-text"/>
		<p>Room Type:<br/>
		<input type="text" name="room_type" value="'.$room->room_type.'" size="20" class="large-text"/>
        <p>Features: <br/>
        <textarea name="features" rows="10" cols="30" class="large-text">'.$room->features.'</textarea>
        <p>Berth: <br/>
        <input type="text" name="berth" value="'.$room->berth.'" size="20" class="large-text"/>
        <p>Price: <br/>
        <input type="text" name="price" value="'.$room->price.'" size="20" class="large-text"/>
		<p class="submit"><input type="submit" name="Submit" value="Save Changes" class="button-primary" /></p>
		</form>';
   echo '<p><a href="?page=ASS2simple">&laquo; back to list</p>';	
}
//========================================================================================

Rooms_CRUD();

?>