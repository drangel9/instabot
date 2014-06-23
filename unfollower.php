<?php
set_time_limit(100000000000);
error_reporting(E_ALL);
	$conn = mysql_connect("HOST","USER","PASSWORD");
	//$conn = mysql_connect("localhost","root","");
    $db = mysql_select_db("instagram",$conn);

function unfollowuser($userid){

	$token = '986612009.1fb234f.abd8b100316d408bb69c345db217bf3c';

	try {
 
    $url = 'https://api.instagram.com/v1/users/'.$userid.'/relationship';
 
    $access_token_parameters = array(
        'access_token'       =>      $token,
        'action'             =>      'unfollow'
    ); 
 
    $curl = curl_init($url); 
 
 	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));

    curl_setopt($curl,CURLOPT_POST,true);
 
    curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
    
    
 	
	$tags = json_decode(curl_exec($curl), true);
 	return $tags;


	} catch(Exception $e) {

		return $e->getMessage();

	}

}
function getuserfollows($userid){

		$token = '986612009.1fb234f.abd8b100316d408bb69c345db217bf3c';

	try {

		$url = 'https://api.instagram.com/v1/users/'.$userid.'/follows?access_token='.$token;

		$curl_connection = curl_init($url);

    	curl_setopt($curl_connection, CURLOPT_HTTPHEADER, array('Accept: application/json'));

		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 5);



		//Data are stored in $data

		$userfollows = json_decode(curl_exec($curl_connection), true);

		curl_close($curl_connection);

		return $userfollows;

 	

	} catch(Exception $e) {

		return $e->getMessage();

	}
}

function findme($array){
	
	for($i=0; $i<count($array); $i++){
		if ($array[$i]["username"] == "ouadventurers"){
			return TRUE;
		}
	}
	return FALSE;
}


$firstrow = mysql_query("SELECT `id` FROM follows LIMIT 1");
$first = mysql_fetch_row($firstrow);
$i = $first[0];
echo $i;
while(true){  
	$firstrow = mysql_query("SELECT `id` FROM follows LIMIT 1");
	$first = mysql_fetch_row($firstrow);
	$result = mysql_query("SELECT `id`, `userid`, `username`, `epoch`, `follow` FROM follows WHERE `id` = '$i'");
	$rows = mysql_query("SELECT * FROM follows");
	if (!$result) {
    	echo 'Could not run query: ' . mysql_error();
    	exit;
	}
	$numrows = mysql_num_rows($rows);
	$row = mysql_fetch_row($result);
	$val=time()-12000; //1/2 hour
	
	$user = $row[1];
	$time = $row[3];
	$follow = $row[4];

	if($time < $val && $follow=='NO'){
		$follows=getuserfollows($user);

	
		$findme=findme($follows['data']);
		if(!$findme){
		
			echo "Unfollowing user: " . $user . "\n\n";
			$unfollow = unfollowuser($user);
			mysql_query("DELETE FROM follows WHERE id = '$i'");
		}else{
			echo "Setting user: " . $user . " follows to YES \n\n";
			mysql_query("UPDATE follows SET follow = 'YES' WHERE id = '$i'");
		}

	}
	
	if($numrows<=$i-$first[0]){
		$i=$first[0];
	}
	else{
		$i++;
	}
	
	
}




?>
