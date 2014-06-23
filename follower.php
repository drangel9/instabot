<?php
set_time_limit(100000000000);
error_reporting(E_ALL);

//open up database
	$conn = mysql_connect("HOST","USER","PASSWORD");

    $db = mysql_select_db("instagram",$conn);

//Get data from instagram api

//hashtag to search photos by
$hashtag = 'instagood';

//$mediaID = "367252842664714004_27734630";

$limit=5;
$token='1146950746.1fb234f.c976492e54924c79b90ae3d7c152511b';
$url=  'https://api.instagram.com/v1/tags/'.$hashtag.'/media/recent?access_token='.$token;

function getphotos($url){
try {
	$curl_connection = curl_init($url);
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	

	//Data are stored in $data
	$tags = json_decode(curl_exec($curl_connection), true);
	curl_close($curl_connection);
	return $tags;
 	
} catch(Exception $e) {
	return $e->getMessage();
}
}

function likephoto($mediaID){

	$token = '1146950746.1fb234f.c976492e54924c79b90ae3d7c152511b';

	try {

		$url = 'https://api.instagram.com/v1/media/'.$mediaID.'/likes?access_token='.$token;

		$curl_connection = curl_init($url);

    	curl_setopt($curl_connection, CURLOPT_HTTPHEADER, array('Accept: application/json'));

		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 5);

		curl_setopt($curl_connection, CURLOPT_POST, null);

    	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, null);

		//Data are stored in $data

		$tags = json_decode(curl_exec($curl_connection), true);

		curl_close($curl_connection);

		return $tags;

 	

	} catch(Exception $e) {

		return $e->getMessage();

	}

}

function followuser($userid){

	$token ='1146950746.1fb234f.c976492e54924c79b90ae3d7c152511b';

	try {
 
    $url = 'https://api.instagram.com/v1/users/'.$userid.'/relationship';
 
    $access_token_parameters = array(
        'access_token'       =>      $token,
        'action'             =>      'follow'
    ); 
 
    $curl = curl_init($url); 
 
 	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));

    curl_setopt($curl,CURLOPT_POST,true);
 
    curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
    
    echo "following user " . $userid;
 	
	$tags = json_decode(curl_exec($curl), true);
 	return $tags;


	} catch(Exception $e) {

		return $e->getMessage();

	}

}

function getusermedia($userid){

		$token = '1146950746.1fb234f.c976492e54924c79b90ae3d7c152511b';

	try {

		$url = 'https://api.instagram.com/v1/users/'.$userid.'/media/recent/?access_token='.$token;

		$curl_connection = curl_init($url);

    	curl_setopt($curl_connection, CURLOPT_HTTPHEADER, array('Accept: application/json'));

		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 5);



		//Data are stored in $data

		$tags = json_decode(curl_exec($curl_connection), true);

		curl_close($curl_connection);

		return $tags;

 	

	} catch(Exception $e) {

		return $e->getMessage();

	}
}

$tags=getphotos($url);

$i=0;
$count=0;

while(isset($tags['pagination']['next_url'])){
    
	$tags=getphotos($url);
	$interval=rand(14,36);
	$followinterval=rand(3,6);
	$likeinterval=rand(9,14);
	
	//Like photo
	$like = likephoto($tags['data'][$i]['id']);

    //wait
	sleep($followinterval);

	//Follow user
	$follow = followuser($tags['data'][$i]['user']['id']);

	//Store user id/get user media
	$username = $tags['data'][$i]['user']['username'];
	$userid = $tags['data'][$i]['user']['id'];

  echo ": " . $tags['data'][$i]['user']['username'] . "\n\n";
	$usermedia = getusermedia($tags['data'][$i]['user']['id']);

	//choose random photo out of user media
	$randomphoto = rand(0,count($usermedia['data']));
	if(count($usermedia['data'])>1){
		while ($usermedia['data'][$randomphoto]['id'] == $tags['data'][$i]['id']){
			$randomphoto = rand(0,count($usermedia['data']));
		}
	}
	//wait
	sleep($likeinterval);

	//like random photo
	$like = likephoto($usermedia['data'][$randomphoto]['id']);
	
    $i=$i+1;
	print_r($tags['data'][$i]['id']);
	
	if($i >= count($tags['data'])-1){
		$i=0;
		$url = $tags['pagination']['next_url'];
	}
	$count++;
	$time = time();

    mysql_query("INSERT INTO follows(`userid`,`username`,`epoch`) VALUES('$userid','$username','$time')") or die(mysql_error());

    //wait before looping again
    sleep($interval);
    print_r($tags['pagination']);
    die();
}
echo "no more urls";
print_r($tags['pagination']);

?>
