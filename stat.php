<!DOCTYPE html>
<html lang="en" style="height: 100%">
<head>
    <meta charset="UTF-8">
    <title>Statistics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/w3style.css">
    <style>
        button
        {
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            -webkit-transition-duration: 0.4s;
            transition-duration: 0.4s;
            cursor: pointer;
            background-color: white;
            color: black;
        }

        button:hover {
            background-color: black;
            color: white;
        }
    </style>
</head>
<body style="
  height: 100%;
  margin: 0;
  background-image: url(coreImages/tree.jpg);
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  width: 100%;
">

<!--- top navigation bar_______________________________________________ --->
<div class="w3-top">
    <div class="w3-bar w3-black w3-opacity w3-animate-opacity">
        <img src="coreImages/pickupLogo.png" class="w3-bar-item w3-button"
             width="120" height="40" style="margin-left: 100px; margin-right: 25px">
    </div>
</div>
<form class="w3-section w3-padding" action="" method="post" enctype="multipart/form-data">
    <div class="w3-center" style="padding-top: 20%;padding-left: 40%; padding-right: 40%" >
        <label><b>Enter hour</b></label>
        <br>
        <input name="hour" class="w3-input w3-border w3-margin-bottom" type="text"
               placeholder="Enter hour" required>
        <button class="w3-button w3-block w3-green w3-section w3-padding"
                type="submit" >Confirm</button>
    </div>
</form>
</body>
</html>
<?php
session_start();
if(isset($_POST["hour"])){
    $_SESSION["hour"]=$_POST["hour"];
    header('Location: statresult.php');
}
?>