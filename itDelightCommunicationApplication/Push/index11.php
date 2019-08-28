<html>
<head>
	<title></title>
</head>
<body>
<form action="https://teleropa.de/custom/plugins/itDelightCommunicationApplication/Push/index11.php" method="post">
  title: <input type="text" name="title"><br>
  body: <input type="text" name="body"><br>
  <input type="submit" value="Submit">
</form>
</body>
</html>





<?php
if(isset($_POST["title"])){

// var_dump($_POST["title"], $_POST["body"]);die();
$url = 'https://fcm.googleapis.com/fcm/send';
$YOUR_API_KEY = 'AIzaSyDOcRI-bqi4KxuxLR6HkjFhAc3J2L4-i30'; // Server key

//$YOUR_TOKEN_ID = array('dDIXR82vEHs:APA91bEkf44G7EOxEEvnQJ_QavHrxldGKxpjGx0bK8BoCGL2RnhJ9AYd7wP9zl-SIB09kiDLSB0yttIaT-_HRLMY48YRTIiipVx-9w10rzPVIk_-VP0TIefTml-cxd9sQjphwf5rs0QB');
$YOUR_TOKEN_ID = array('dQN-kKWrKQM:APA91bEeY7l6orxQV3KOhQehqyxCFn3tk-f-1rc6kph7d_xrqqS4Joxi2hkO8D2Tdh79-rjAoRkZEop6zjskx_sgd8TeSc8hxcw-3YmKAgpidGY-M2c4HawHF2Fiirs4ED4TnWR4ZyNr');
// $YOUR_TOKEN_ID = "/topics/all";

$request_body = [
    'registration_ids' => $YOUR_TOKEN_ID,
    // 'to' =>$YOUR_TOKEN_ID,
    'notification' => [
        'title' => $_POST["title"],
        'body' => $_POST["body"],
        'icon' => 'https://static2.clutch.co/s3fs-public/logos/logo_256x256.png?width=192&height=192',
        'click_action' => 'http://www.itDelight.de/',
    ],
];
$fields = json_encode($request_body);
// var_dump($fields);die();
$request_headers = [
    'Content-Type: application/json',
    'Authorization: key=' . $YOUR_API_KEY,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
}