<?php
$apiKey = "6dd74ef468144815ae3b91ca7432cd67"; //client id
$apiReturnback = "http://dev2.spaceo.in/project/socialapp/success.php";
$api_auth_url = 'https://api.instagram.com/oauth/authorize';

$loginUrl = $api_auth_url . '?client_id=' . $apiKey . '&redirect_uri=' . urlencode($apiReturnback) . '&scope=' . implode('+',array('basic')) . '&response_type=code';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Signup with Instagram</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    </head>
<body>
    <div class="container">
        <div class="starter-template" style="margin-top: 300px;">
            <center><a href="<?php echo $loginUrl; ?>" class="btn btn-primary"><i class="fa fa-instagram"></i> Signup with instagram</a></center>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
