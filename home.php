<?php
session_start();
ob_start();
include_once("cards.php");
include_once("query.php");
include_once("functions.php");
///////////////////////////////////////////////////////////////////////////////////////////
/// connecting to db
$db=connectToDatabase();
///////////////////////////////////////////////////////////////////////////////////////////
/// getting person id from database



/*
$ip=$_SERVER['REMOTE_ADDR'];
$personId=getUserInfo($ip,$db);
$personId=$personId[0];
$userId=$personId[0];
*/


$personId= $_SESSION["PERSON_ID"];
$userId= $_SESSION["PERSON_ID"];

////////////////////////////////////////////////////////////////////////////////////////////
/// getting User image and name
$user=getPersonInfo($personId,$db);
$userName=$user[0];
$userImage=$user[1];


////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['searchingText'])){
    goToSearchPage2($_POST['searchingText'],$db,$personId);
    //goToSearchPage($_POST['searchingText'],$db,$ip);
}
////////////////////////////////////////////////////////////////////////////////////////////
/// head for html
echo<<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/w3style.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/icon.css">
    <script src="functions.js"></script>
</head>
<body bgcolor="#d3d3d3">
<!--- top navigation bar--->
<div class="w3-top">
    <div class="w3-bar w3-black w3-animate-opacity">
        <!-- LOGO OF WEBSITE-->
        <img src="coreImages/pickupLogo.png" onclick="document.location.href='home.php'" class="w3-bar-item w3-button" 
            width="120" height="40" style="margin-left: 100px; margin-right: 25px">
        <!-- owner information-->
EOF;

////////////////////////////////////////////////////////////////////////////////////////
/// PAGE OWNER INFO
echo headerPersonCard();

echo<<<EOF
        <!-- SIGN OUT BUTTON-->
        <span class="w3-button w3-bar-item w3-right" onclick= 
            "document.location.href='index.php'"/>Sign Out</span>
        
        <!-- SEARCH BAR AND SEARCH BUTTON-->
        <form method="post" enctype="multipart/form-data">
            <div class="w3-right" style="padding: 5px">
                <input name="searchingText" type="text" class=
                    "w3-bar-item w3-input w3-white w3-mobile" placeholder="Search..">
                <button type="submit" class="w3-bar-item w3-button w3-gray w3-mobile">Go</button>
            </div>
        </form>
        
        <!-- NOTIFICATION DROPDOWN-->
        <div class="w3-dropdown-hover w3-right">
            <!-- NOTIFICATION ICON-->
            <img src="coreImages/notification-circle-blue-512.png" class="w3-bar-item w3-button w3-animate-fading"
                width="50" height="50">
            <!-- NOTIFICATION DROPDOWN CONTENTS-->
            <div class="w3-dropdown-content w3-card-4 w3-animate-opacity " style="width:300px;
                height: 400px;overflow: scroll">
                <p align="center">
                    <b>Notifications</b>
                </p>
                <hr>
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// getting notification and adding notification into notification bar
$notifications=getNotification($personId,$db);
if($notifications){
    while($row = pg_fetch_row($notifications)) {
        echo notificationCard($row[0]);
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Tab for home and setting-->
echo <<<EOF
            </div>
        </div>
        <!-- Tab for home and setting-->
        <div class="w3-bar w3-gray">
            <button class="w3-bar-item w3-button tablink w3-blue-gray" onclick="openTab(event,'Home')">Home</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Settings')">Settings</button>
        </div>
    </div>
</div>
<br><br>
<!-- PostCards------------------------------------------------------------------------ -->
<div id="Home" class="w3-container w3-border tabPage" style=
    "background-color: white;height: 90%; padding-top: 10%">
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// CREATING POSTS
/// first getting all posts from database then add post card for all of them
echo '<div class="w3-container" style="padding-left: 25%; padding-right: 25%">';
$allPosts=getAllPost($db);
$l=0;
while($post=pg_fetch_row($allPosts)){
    $flag=($post[6]==$personId);
    $postIds[$l]=$post[0];
    $l=$l+1;

    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
                                    $flag,$userName,$userImage,$personId,$db,"home.php",$post[6],isLiked($post[0],$personId,$db));
    setLikeComment($post[0],$db);


}
////////////////////////////////////////////////////////////////////////////////////////////
/// connecting button click with a php function and setting
for($i=0;$i<$l;$i=$i+1){


    ///for delete post
    if(isset($_REQUEST[$postIds[$i]."delete"])){
        deletePost($postIds[$i],$db);
        unset($_REQUEST[$postIds[$i]."delete"]);
    }
    ///for editting caption
    if(isset($_POST[$postIds[$i]."captionnew"])){
        editCaption($postIds[$i],$db,'Location: home.php');
    }
    ///for adding a like
    if(isset($_REQUEST[$postIds[$i]."like"])){
        addLike($postIds[$i],$personId,$db);

        unset($_REQUEST[$postIds[$i]."like"]);
    }
    ///for adding a comment
    if(isset($_POST[$postIds[$i]."editcomment"])){
        addComment($postIds[$i],$personId,$db,'Location: home.php');
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// connecting comment delete button with a php function deleteComment
$comments=getAllCommentId($db);
while ($comment=pg_fetch_row($comments)){
    $commentId=$comment[0];
    if(isset($_REQUEST[$commentId."delete"])){
        deleteComment($commentId,$db);
        unset($_REQUEST[$commentId."delete"]);
    }
}
echo '</div>';
//////////////////////////////////////////////////////////////////////////////////////////////
/// connecting plus button with a php function newPost
if(isset($_POST['caption']) && isset($_FILES['imageupload']))
{
    createPost($db,$personId,'Location: home.php');
}
//////////////////////////////////////////////////////////////////////////////////////////////
///plus button for adding posts on own timeline
echo<<<LINE
<!-- plus button for adding posts on own timeline -->
    <div>
        <button onclick="document.getElementById('id02').style.display='block'" 
            class="w3-button w3-xlarge w3-circle w3-blue w3-card-4 w3-animate-zoom" 
            style="position: fixed; top: 80%; left: 65%">+</button>
    
        <!-- modal for create post -->
        <div id="id02" class="w3-modal">
            <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
    
                <!-- close button of modal-->
                <div class="w3-center"><br>
                    <span onclick="document.getElementById('id02').style.display='none'" 
                        class="w3-button w3-xlarge w3-hover-red w3-display-topright"
                        title="Close Modal">&times;</span>
    
        
                </div>
    
                <!-- form section in the modal-->
                <form method="post" class="w3-section w3-padding" enctype="multipart/form-data">
                    
                        <!-- image input for post -->
                        <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                        name="imageupload" required>
                        <!-- full name field in the modal-->
                        <label><b>Caption</b></label>
                        <input class="w3-input w3-border w3-margin-bottom" type="text" name="caption"
                            placeholder="Enter caption" required>
                        
                        <label><b>Tags</b></label>
                        <input class="w3-input w3-border w3-margin-bottom" type="text" name="tags"
                            placeholder="Enter tags (Enter multiple tags seperated by whitespace) ">
                        
                        <!-- submit post in the modal-->
                        <button class="w3-button w3-block w3-green w3-section w3-padding"
                        type="submit" >Post</button>    
                  
                </form>
    
                <!-- cancel button in the modal-->
                <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                    <button onclick="document.getElementById('id02').style.display='none'"
                        type="button" class="w3-button w3-red">Cancel</button>
                </div>
    
            </div>
        </div>
    </div>

LINE;
//////////////////////////////////////////////////////////////////////////////////////////////
echo '</div>';
//////////////////////////////////////////////////////////////////////////////////////////////
/// connecting setting form with a php function updatePersonInfo
if(isset($_POST['password']) && isset($_POST['fullName']) && isset($_FILES['imageupload']))
{
    updatePersonInfo($db,$personId);
}
/////////////////////////////////////////////////////////////////////////////////////////////
///  Content of tab 'Settings'
echo<<<EOF
<!-- Content of tab 'Settings' -->
<div id="Settings" class="w3-container w3-border tabPage" 
    style="display:none;background-color: white;height: 90%;padding-top: 10%" align="center">
    <div class="w3-card-4 w3-animate-opacity" style="max-width:600px">
        <b>Account Settings</b>
        <!-- close button-->
       
        <!-- form section in the modal-->
        <form class="w3-section w3-padding" method="post" enctype="multipart/form-data">
            <!-- form section in the modal-->
            <div >
                <br>
                <label><p>Choose a profile photo</p></label><br>
                <!-- image changing option -->
                <input class="w3-input w3-margin-bottom" type="file" accept="image/*" name="imageupload"
                    alt="Insert an image" required>
                <!-- full name field -->
                <label><p>Full Name</p></label>
                <input name="fullName" class="w3-input w3-border w3-margin-bottom" type="text"
                    placeholder="Enter Full Name" required>
                <!-- Password field -->
                <label><p>Password</p></label>
                <input id="p1" name="password" class="w3-input w3-border" type="password" 
                    placeholder="Enter Password" required>

                <!-- Confirm Password field -->
                <label><p>Confirm Password</p></label>
                <input id="p2" name="password2" class="w3-input w3-border" type="password"
                    placeholder="Confirm Password" required>
                
                <!-- change information button -->
                <button class="w3-button w3-block w3-green w3-section w3-padding"
                    type="submit" >Change Information</button>              
            </div>
        </form>


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
    
    
    function openTab(evt, tabName) {
        var i, x, tablinks;
        x = document.getElementsByClassName("tabPage");
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < x.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" w3-blue-gray", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " w3-blue-gray";
    }
</script>

</body>
</html>
EOF;
ob_end_flush();