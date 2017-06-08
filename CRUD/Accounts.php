<?php
//=======================================================================================
function Accounts_CRUD() {// the CRUD function for the accounts including a switch statement for the different commands
    echo '<div id="msg" style="overflow: auto"></div>
        <div class="wrap">
        <h2>Accounts <a href="?page=Accounts&command=new" class="add-new-h2">Add New</a></h2>
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
            $msg = Account_form('update', $accountid,null);
        break;

        case 'new':
            $msg = Account_form('insert',null,null);
        break;

        case 'delete':
            $msg = Account_delete($accountid);
            $command = '';
        break;

        case 'update':
            $msg = Account_update($accountdata);
            if(is_array($msg)){        
                $msg = Account_form('errorUpdate',$_REQUEST['hid'],$msg);
            }else{
                $command = '';
                break;
            }
        break;

        case 'insert':
            $msg = Account_insert($accountdata);
            if(is_array($msg)){        
                $msg = Account_form('errorNew',null,$msg);
            }else{
                $command = '';
                break;
            }
    }

    if (empty($command)) Account_list();

	if (!empty($msg)) {
      echo 'Message: '.$msg;      
	}
	echo '</div>';
}
//=======================================================================================
function Accounts_view($accountid) {// for when the administrator want to view a specific account
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
    echo '</p><p>Address:<br/>';
    echo $row->address;
    echo '</p><p>Phone Number:<br/>';
    echo $row->phone_number;
    echo '</p><p><a href="?page=Accounts">&laquo; Back to List</p>';
}
//=======================================================================================
function Account_delete($accountid) { // function for when the administrator wants to delete a specific account
    global $wpdb;
    $results = $wpdb->query($wpdb->prepare("DELETE FROM ACCOUNTS_TABLE WHERE id=%s",$accountid));
    if ($results) {
        $msg = "Account $accountid was successfully deleted.";
    }
    return $msg;
}
//=======================================================================================
function Account_update($account_data) {
    global $wpdb, $current_user;
    $error;
    
    if(!preg_match('/^[A-z]+$/',$account_data['first_name'])){
        $error[]="first_name";
    }
    if(!preg_match('/^[A-z]+$/',$account_data['last_name'])){
        $error[]="last_name";
    }
    if(!preg_match('/^\d{7,15}$/',$account_data['phone_number'])) {
        $error[]="phone_number";    
    }
    if(!preg_match('/^\d{1,5}[A-z]?\s[A-z]+\s[A-z]+$/',$account_data['address'])) {
        $error[]="address";
    }
    if(!empty($error)){
        return $error;
    }

    $wpdb->update('ACCOUNTS_TABLE',
        array( 'first_name' => stripslashes_deep($account_data['first_name']),
            'last_name' => stripslashes_deep($account_data['last_name']),
            'address' => stripslashes_deep($account_data['address']),
            'phone_number' => stripslashes_deep($account_data['phone_number'])),
        array( 'id' => $account_data['hid']));
        $msg = "Account ".$account_data['hid']. " has been updated";
    return $msg;
}
//=======================================================================================
function Account_insert($account_data) {
    global $wpdb, $current_user;
    $error;
    
    if(!preg_match('/^[A-z]+$/',$account_data['first_name'])){
        $error[]="first_name";
    }
    if(!preg_match('/^[A-z]+$/',$account_data['last_name'])){
        $error[]="last_name";
    }
    if(!preg_match('/^\d{7,15}$/',$account_data['phone_number'])) {
        $error[]="phone_number";    
    }
    if(!preg_match('/^\d{1,5}[A-z]?\s[A-z]+\s[A-z]+$/',$account_data['address'])) {
        $error[]="address";
    }
    if(!empty($error)){
        return $error;
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
//=======================================================================================
function Account_list() {//function for showing an administrator all the accounts
    global $wpdb, $current_user;
    $query = "SELECT id, first_name, last_name, phone_number FROM ACCOUNTS_TABLE ORDER BY id ASC";
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
            $edit_link = '?page=Accounts&id=' . $account->id . '&command=edit';
            $view_link = '?page=Accounts&id=' . $account->id . '&command=view';
            $delete_link = '?page=Accounts&id=' . $account->id . '&command=delete';

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
//=======================================================================================
function Account_form($command, $accountid = null, $errorArray = null) {// the form used for updating or adding an account
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

    if ($command == 'errorNew'||$command == 'errorUpdate') {
        $account_data = $_POST;
        $account->first_name = stripslashes_deep($account_data['first_name']);
        $account->last_name = stripslashes_deep($account_data['last_name']);
        $account->address = stripslashes_deep($account_data['address']);
        $account->phone_number = stripslashes_deep($account_data['phone_number']);
        if ($command == 'errorUpdate'){
            $command = 'update';
            
        }
        else $command = 'insert';
    }

    echo '<form name="Account_form" method="post" action="?page=Accounts">
    <input type="hidden" name="hid" value="'.$accountid.'"/>
    <input type="hidden" name="command" value="'.$command.'"/>';

    if(is_array($errorArray)){
        echo '<font color="FF0000"> There were errors in your details</font>';
    }

    echo'<p>First Name';
    if(is_array($errorArray)){
        foreach($errorArray as $error){
            if($error == 'first_name'){
                echo '<font color="FF0000"> First Name is invalid</font>';
            }
        }
    }

    echo 
    '<br/>
    <input type="text" name="first_name" value="'.$account->first_name.'" size="20" class="large-text" />

    <p>Last Name';
    if(is_array($errorArray)){
        foreach($errorArray as $error){
            if($error == 'last_name'){
                echo '<font color="FF0000"> Last Name is invalid</font>';
            }
        }
    }
    echo 
    '<br/>
    <input type="text" name="last_name" value="'.$account->last_name.'" size="20" class="large-text" />

    <p>Address';
    if(is_array($errorArray)){
        foreach($errorArray as $error){
            if($error == 'address'){
                echo '<font color="FF0000"> Address is invalid</font>';
            }
        }
    }
    echo
    '<br/>
    <input type="text" name="address" value="'.$account->address.'" size="60" class="large-text" />

    <p>Phone Number';
    if(is_array($errorArray)){
        foreach($errorArray as $error){
            if($error == 'phone_number'){
                echo '<font color="FF0000"> Phone number is invalid</font>';
            }
        }
    }
    echo
    '<br/>
    <input type="text" name="phone_number" value="'.$account->phone_number.'" size="60" class="large-text" />

    <hr/>
    <p class="submit"><input type="submit" name="submit" value="Save Changes" class="button-primary" /></p>
    </form>';
    echo'<p><a href="?page=Accounts">&laquo; Back To Accounts List</p>';
}
//=======================================================================================
?>