<?php
function Accounts_CRUD() {
    echo '<h3>Contents of the POST data</h3>';
    pr($_POST);
    echo '<h3>Contents of the REQUEST data</h3>';
    pr($_REQUEST);
    echo '<div id="msg" style="overflow: auto"></div>
        <div class="wrap">
        <h2>Accounts <a href="?page=accounts&command=new" class="add-new-h2">Add New</a></h2>
        <div style="clear: both"></div>';

    $accountdata = $_POST;

    if (isset($_REQUEST['id']))
        $accountid = $_REQUEST['id'];
    else
        $accountid = '';

    if (isset($_REQUEST["command"]))
        $command = $_REQUEST["command"];
    else
        $command = '';

    switch ($command) {
        case 'view':
            Accounts_view($accountid);
        break;

        case 'edit':
            $msg = Account_form('update', $accountid);
        break;

        case 'new':
            $msg = Account_form('insert',null);
        break;

        case 'delete':
            $msg = Account_delete($accountid);
            $command = '';
        break;

        case 'update':
            $msg = Account_update($accountdata);
            $command = '';
        break;

        case 'insert':
            $msg = Account_insert($accountdata);
            $command = '';
        break;
    }

    if (empty($command)) Account_list();

	if (!empty($msg)) {
      echo 'Message: '.$msg;      
	}
	echo '</div>';
}

function Accounts_view($accountid) {
    global $wpdb;
    $qry = $wpdb->prepare("SELECT * FROM ACCOUNTS_TABLE");
    $row = $wpdb->get_row($qry);
    echo '<p>';
    echo 'First Name:';
    echo '<br />';
    echo $row->first_name;
    echo '<p>';
    echo 'Last Name:';
    echo '<br />';
    echo $row->last_name;
    echo '<p><a href="?page=accounts">&laquo; Back to List</p>';
}

function Account_delete($accountid) {
    global $wpdb;
    $results = $wpdb->query($wpdb->prepare("DELETE FROM ACCOUNTS_TABLE WHERE id=%s",$accountid));
    if ($results) {
        $msg = "Account $accountid was successfully deleted.";
    }
    return $msg;
}


function Account_update($account_data) {
    global $wpdb, $current_user;

    if(preg_match('/^\d+$/',$account_data['phone_number'])) {
        echo "Phone Number is valid";
    } else {
        return "Phone Number is not valid";
    }

    $wpdb->update('ACCOUNTS_TABLE',
        array( 'first_name' => stripslashes_deep($account_data['first_name']),
            'last_name' => stripslashes_deep($account_data['last_name']),
            'address' => stripslashes_deep($account_data['address']),
            'phone_number' => stripslashes_deep($account_data['phone_number'])),
        array( 'id' => $account_data['hid']));
    $msg = "Account ".$accountdata['hid']. "has been updated";
    return $msg;
}


function Account_insert($account_data) {
    global $wpdb, $current_user;

    if(preg_match('/^\d+$/',$account_data['phone_number'])) {
        echo "Phone Number is vadlid";
    } else {
        return "Phone Number is not valid";
    }

    $wpdb->insert( 'ACCOUNTS_TABLE',
    array(
        'first_name' => stripslashes_deep($account_data['first_name']),
        'last_name' => stripslashes_deep($account_data['last_name']),
        'address' => stripslashes_deep($account_data['address']),
        'phone_number' => $account_data['phone_number']));
        $msg = "Account for ".$account_data['first_name']." has been created";
        return $msg;
}



function Account_list() {
    global $wpdb, $current_user;
    $query = "SELECT id, first_name, last_name, phone_number FROM ACCOUNTS_TABLE ORDER BY last_name DESC";
    $allaccounts = $wpdb->get_results($query);
    echo '<table class="wp-list-table widefat">
		<thead>
		<tr>
			<th scope="col" class="manage-column">ID</th>
			<th scope="col" class="manage-column">First Name</th>
			<th scope="col" class="manage-column">Last Name</th>
			<th scope="col" class="manage-column">Phone Number</th>
		</tr>
		</thead>
		<tbody>';
        foreach ($allaccounts as $account) {
            $edit_link = '?page=accounts&id=' . $account->id . '&command=edit';
            $view_link = '?page=accounts&id=' . $account->id . '&command=view';
            $delete_link = '?page=accounts&id=' . $account->id . '&command=delete';

            echo '<tr>';
            echo '<td>' . $account->id . '</td>';
            echo '<td><strong><a href="'.$edit_link.'" title="Edit This Account">' . $account->first_name . '</a></strong>';
            echo '<div class="row-actions">';
            echo '<span class="edit"><a href="'.$edit_link.'" title="Edit this item">Edit</a></span> | ';
            echo '<span class="view"><a href="'.$view_link.'" title="View this Item">View</a></span> | ';
            echo '<span class="trash"><a href="'.$delete_link.'" title="Delete This Item" onclick="return doDelete();">Trash</a></span>';
            echo'</div>';
            echo '</td>';
            echo '<td>' . $account->last_name . '</td>';
            echo '<td>' . $account->phone_number . '</td></tr>';
        }
        echo '</tbody></table>';

	echo "<script type='text/javascript'>
			function doDelete() { if (!confirm('Are you sure?')) return false; }
		  </script>";
}

function Account_form($command, $accountid = null) {
    global $wpdb;
    if ($command == 'insert') {
        $account->first_name = '';
        $account->last_name = '';
        $account->address = '';
        $account->phone_number = '';
    }

    if ($command == 'update') {
        $account = $wpdb->get_row("SELECT * FROM ACCOUNTS_TABLE WHERE id = '$accountid'");
    }
    echo '<form name="Account_form" method="post" action="?page=accounts">
    <input type="hidden" name="hid" value="'.$accountid.'"/>
    <input type="hidden" name="command" value="'.$command.'"/>

    <p>First Name:<br/>
    <input type="text" name="first_name" value="'.$account->first_name.'" size="20" class="large-text" />
    <p>Last Name:<br/>
    <input type="text" name="last_name" value="'.$account->last_name.'" size="20" class="large-text" />
    </p>
    <p>Address:<br/>
    <textarea name="address" rows="10" cols="30" class="large-text">'.$account->address.'</textarea>
    <p>Phone Number:<br/>
    <input type="text" name="phone_number" value="'.$account->phone_number.'" size="60" class="large-text" />
    <hr/>
    <p class="submit"><input type="submit" name="submit" value="Save Changes" class="button-primary" /></p>
    </form>';
    echo '<p> <a href="?page=accounts">&laquo; Back To Accounts List</p>';
}
Accounts_CRUD();

?>