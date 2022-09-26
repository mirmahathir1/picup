<?php
session_start();
ob_start();
include_once("query.php");
include_once("functions.php");

///////////////////////////////////////////////////////////////////////////////////////////
/// connecting to database
$db=connectToDatabase();

///////////////////////////////////////////////////////////////////////////////////////////
///getting ip address of user
$ip=$_SERVER['REMOTE_ADDR'];

///////////////////////////////////////////////////////////////////////////////////////////
/// html head
echo <<<LINE
<!DOCTYPE html>
<html lang="en" style="height: 100%">
<head>
    <meta charset="UTF-8">
    <title>PicUp</title>
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
        <img src="coreImages/pickupLogo.png" class="w3-bar-item" 
            width="120" height="40" style="margin-left: 100px; margin-right: 25px">
    </div>
</div>

<!-- flickr introductions-->
<div class="w3-container" style="padding-top: 10%">
    <h1 align="center" class="w3-xxlarge w3-animate-zoom">Want to go up?</h1>
    <h2 align="center" class="w3-xlarge w3-animate-zoom">Join the PicUp</h2>
</div>

<!-- sign up and sign in buttons-->
<div class="w3-container" style="padding-top: 5%" align="center">
    <button onclick="document.getElementById('id02').style.display='block'" 
        class="w3-btn w3-large w3-white w3-hover-black w3-hover-shadow w3-animate-opacity">Sign Up</button>
    <br>
    <br>
    <button onclick="document.getElementById('id01').style.display='block'" 
        class="w3-btn w3-large w3-white w3-hover-black w3-hover-shadow w3-animate-opacity">Sign In</button>
</div>
LINE;

///////////////////////////////////////////////////////////////////////////////////////////
/// check if the sign in fields have been filled
if(isset($_POST['email1']) && isset($_POST['password1']))
{
    signIn($db,$ip);
}

///////////////////////////////////////////////////////////////////////////////////////////
/// sign-in modal
echo <<<LINE
<div id="id01" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-opacity" style="max-width:600px">

        <!-- close button of modal-->
        <div class="w3-center"><br>
            <span onclick="document.getElementById('id01').style.display='none'" 
                class="w3-button w3-xlarge w3-hover-red w3-display-topright" 
                title="Close Modal">&times;</span>
        </div>

        <!-- form section in the modal-->
        <form method="post"  class="w3-container" enctype="multipart/form-data">
            <div class="w3-section">

                <!-- Email field in the modal-->
                <label><b>Email</b></label>
                <input name="email1" id="em2" class="w3-input w3-border w3-margin-bottom" 
                    type="text" placeholder="Enter Email" name="email" required>

                <!-- Password field in the modal-->
                <label><b>Password</b></label>
                <input name="password1" class="w3-input w3-border" type="password" 
                    placeholder="Enter Password" required>

                <!-- Login button in the modal -->
                <button class="w3-button w3-block w3-green w3-section w3-padding" 
                    type="submit">Login</button>
            </div>
        </form>

        <!-- cancel button of modal-->
        <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
            <button onclick="document.getElementById('id01').style.display='none'" 
                type="button" class="w3-button w3-red">Cancel</button>
        </div>

    </div>
</div>
<script>

    //this function validates the format of the inserted email addresses in the signIn/ signUp fields
    function validateEmail2() {
        var x = document.getElementById('em2').value;
        var atpos = x.indexOf("@");
        var dotpos = x.lastIndexOf(".");
        if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
            document.getElementById('em2').setCustomValidity('Email was not valid');
        }
        else{
            document.getElementById('em2').setCustomValidity('');
        }
    }
    
    //registers a listener for the 
    document.getElementById('em2').onkeyup = validateEmail2;
</script>
LINE;
/////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['fullName'])
    && isset($_FILES['imageupload']))
{
    signUp($db,$ip);
}
/////////////////////////////////////////////////////////////////////////////////////////////
/// sign-up modal and end of html
echo <<<LINE
<!-- sign-up modal -->
<div id="id02" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-opacity" style="max-width:600px">
        <!-- close button-->
        
        <div class="w3-center"><br>
                <span onclick="document.getElementById('id02').style.display='none'" class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
        </div>
        <form class="w3-section w3-padding" action="" method="post" enctype="multipart/form-data">
            <!-- form section in the modal-->
            <div >
                <br>
                <label><b>Choose a profile photo</b></label><br>
                <!-- image input for signup profile image -->
                <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                    name="imageupload" required>
                <!-- full name field in the modal-->
                <label><b>Full Name</b></label>
                <input name="fullName" class="w3-input w3-border w3-margin-bottom" type="text"
                    placeholder="Enter Full Name" required>

                <!-- Email field in the modal-->
                <label><b>Email</b></label>
                <input id="em" name="email" class="w3-input w3-border w3-margin-bottom" type="text"
                    placeholder="Enter Email" required>

                <!-- Password field in the modal-->
                <label><b>Password</b></label>
                <input id="p1" name="password" class="w3-input w3-border" type="password" 
                    placeholder="Enter Password" required>

                <!-- Confirm Password field in the modal-->
                <label><b>Confirm Password</b></label>
                <input id="p2" name="password2" class="w3-input w3-border" type="password"
                    placeholder="Confirm Password" required>
                
                <!-- signup button in the modal-->
                <button class="w3-button w3-block w3-green w3-section w3-padding"
                    type="submit" >Sign Up</button>              
            </div>
        </form>
        <!-- cancel button in the modal-->
        <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
            <button onclick="document.getElementById('id02').style.display='none'" type="button" class="w3-button w3-red">Cancel</button>
        </div>

    </div>
</div>
<div class="w3-container w3-third w3-btn" >
    <div class="w3-display-container w3-card" onclick=
                "document.getElementById('popup').style.display='block'">
        <img class="w3-animate-fading" src="coreImages/heart.png" alt="Avatar" style="position: fixed; top: 90%; left: 95%; width: 20px; height: 20px">
    </div>
    <div id="popup" class="w3-modal w3-animate-zoom w3-center" onclick="this.style.display='none'">
          
          <div class="w3-panel w3-light-grey">
          <h3>Acknowledgement</h3>
          <span style="font-size:150px;line-height:0.6em;opacity:0.2">❝</span>
          <p><i>
              "Is that a piece of lightning shredding through my dark clouds?
              Is that an angel thrown into an exodus from heaven to Earth?
              Oh angel, Lay your wings upon me."</i></p>
          <p><i>
              Dedicated to the girl who paralyzed me with her beauty... Taaha/ Bhootuu"</i></p>
          <p>-Mahathir</p>
    </div>
    </div>
</div>
<script>
    function validatePassword() {
        if(document.getElementById('p1').value==document.getElementById('p2').value){
            
            document.getElementById('p2').setCustomValidity('');
        }
        else{
            document.getElementById('p2').setCustomValidity("Passwords do not Match");
        }
    }
    document.getElementById('p1').onchange = validatePassword;
    document.getElementById('p2').onkeyup = validatePassword;
    function validateEmail() {
        var x = document.getElementById('em').value;
        var atpos = x.indexOf("@");
        var dotpos = x.lastIndexOf(".");
        if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
            document.getElementById('em').setCustomValidity('Email was not valid');
        }
        else{
            document.getElementById('em').setCustomValidity('');
        }
    }
    document.getElementById('em').onkeyup = validateEmail;
</script>
</body>
</html>
LINE;
ob_end_flush();
?>