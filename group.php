<?php
session_start();
ob_start();
include_once("cards.php");
include_once("query.php");
include_once("functions.php");

////////////////////////////////////////////////////////////////////////////////////////////
/// connecting to db
$db=connectToDatabase();
/////////////////////////////////////////////////////////////////////////////////////////////
/// getting user id and group id from database





$personId= $_SESSION["PERSON_ID"];
$userId= $_SESSION["PERSON_ID"];


$personInfo=getUserInfo2($personId,$db);


/*
$ip=$_SERVER['REMOTE_ADDR'];
$personInfo=getUserInfo($ip,$db);
$personId=$personInfo[0];
$userId=$personInfo[0];
*/

$groupId=$personInfo[1];
$groupId=(int)$groupId;

/////////////////////////////////////////////////////////////////////////////////////////////
/// getting User image and name
$user=getPersonInfo($personId,$db);
$userName=$user[0];
$userPhoto=$user[1];

///////////////////////////////////////////////////////////
/// get group name and image
$groupInfo = getGroupInfo($groupId,$db);
$groupName = $groupInfo[0];
$groupImage = $groupInfo[1];



/////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['searchingText'])){
    goToSearchPage2($_POST['searchingText'],$db,$personId);
    //goToSearchPage($_POST['searchingText'],$db,$ip);

}
/////////////////////////////////////////////////////////////////////////////////////////////
/// head for html
echo<<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GROUP</title>
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
        <img src="coreImages/pickupLogo.png" class="w3-bar-item w3-button" width="120" 
            height="40" style="margin-left: 100px; margin-right: 25px"
            onclick="document.location.href='home.php'">
        <!-- owner information-->
        
EOF;

////////////////////////////////////////////////////////////////////////////////////////
/// PAGE OWNER INFO
echo headerPersonCard();

echo<<<EOF
        
        
        <!-- SIGN OUT BUTTON-->
        <span class="w3-button w3-bar-item w3-right" onclick= 
            "document.location.href='index.php'"/>Sign Out</span>
        
        <!--do later-->
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
            <div class="w3-dropdown-content w3-card-4 w3-animate-opacity" style="width:300px; 
                height: 400px;overflow: scroll">
                <p align="center">
                    <b>Notifications</b>
                </p>
                <hr>
EOF;
/////////////////////////////////////////////////////////////////////////////////////////////
/// getting notification and adding notification into notification bar
$notifications=getNotification($personId,$db);
if($notifications){
    while($row = pg_fetch_row($notifications)) {
        echo notificationCard($row[0]);
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Tab for home and setting-->
echo <<<EOF
            </div>
        </div>
        <!-- Tab for Posts,admin,members,pending,setting search-->
        <div class="w3-bar w3-gray">
        <!-- Group info information-->
        
        <span class="w3-bar-item w3-button w3-dropdown-hover">
            <img src=$groupImage class=" w3-circle" width="30" height="30">
            <b>$groupName</b>
            <div class="w3-dropdown-content w3-animate-zoom"><br>
                    <img src=$groupImage alt="group pic" style="width: auto%;height: 300px">
                </div>
        </span>
            <button class="w3-bar-item w3-button tablink w3-blue-gray" onclick="openTab(event,'Posts')">Posts</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Admins')">Admins</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Members')">Members</button>
            <button id="settingButton" class="w3-bar-item w3-button tablink" onclick="openTab(event,'Settings')">Settings</button>
        </div>

    </div>
</div>

<!-- Content of tab 'Posts' -->
<div id="Posts" class="w3-container w3-border tabPage" 
    style="background-color: white;height: 90%; padding-top: 10%">
EOF;
/////////////////////////////////////////////////////////////////////////////////////////////
/// first getting all posts from database then add post card for all of them
echo '<div class="w3-container" style="padding-left: 25%; padding-right: 25%">';
$allPosts=getAllGroupPost($groupId,$db);
$l=0;
while($post=pg_fetch_row($allPosts)){
    $flag=($post[6]==$personId);
    $postIds[$l]=$post[0];
    $l=$l+1;
    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
        $flag,$userName,$userPhoto,$personId,$db,"group.php",$post[6],isLiked($post[0],$personId,$db));
    setLikeComment($post[0],$db);
}
/////////////////////////////////////////////////////////////////////////////////////////////
/// connecting button click with a php function and setting
for($i=0;$i<$l;$i=$i+1){
    ///for delete post
    if(isset($_REQUEST[$postIds[$i]."delete"])){
        deletePost($postIds[$i],$db);
        unset($_REQUEST[$postIds[$i]."delete"]);
    }
    ///for editting caption
    if(isset($_POST[$postIds[$i]."captionnew"])){
        editCaption($postIds[$i],$db,'Location: group.php');
    }
    ///for adding a like
    if(isset($_REQUEST[$postIds[$i]."like"])){
        addLike($postIds[$i],$personId,$db);
        unset($_REQUEST[$postIds[$i]."like"]);
    }

    ///for adding a comment
    if(isset($_POST[$postIds[$i]."editcomment"])){
        addComment($postIds[$i],$personId,$db,'Location: group.php');
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////
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
/////////////////////////////////////////////////////////////////////////////////////////////
/// connecting plus button with a php function newPost
if(isset($_POST['caption']) && isset($_FILES['imageupload']))
{
    newPostForGroup($db,$personId,$groupId);
}
/////////////////////////////////////////////////////////////////////////////////////////////
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
                        
                        <!-- full caption field in the modal-->
                        <label><b>Caption</b></label>
                        <input class="w3-input w3-border w3-margin-bottom" type="text" name="caption"
                            placeholder="Enter caption" required>
                            
                        <!-- tag field in the modal-->
                        <label><b>Tag</b></label>
                        <input class="w3-input w3-border w3-margin-bottom" type="text" name="tags"
                            placeholder="Enter tag (Enter multiple tags seperated by whitespace) ">
    
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
echo<<<EOF
</div>
<!-- Content of tab 'Admins' -->
<div id="Admins" class="w3-container w3-border tabPage" 
    style="display:none;background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// getting friend from database and setting friend card
$ret=getGroupMemebers($groupId,$db,true);
$settingFlag=false;
while($row=pg_fetch_row($ret)){
    $adminInfo=getPersonInfo($row[0],$db);
    $friends=getFriendsId($row[0],$db);
    $friendCount=pg_affected_rows($friends);
    $posts=getUserPost($row[0],$db);
    $postCount=pg_affected_rows($posts);
    echo personCard($row[0],$adminInfo[0],$adminInfo[1],$postCount,
        $friendCount,"hk",false,"HJJ",false,"group.php");
    /// $setting flag is for determining whether a person is admin
    /// and only admin can modify a group.
    if($row[0]==$userId){
        $settingFlag=true;
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////
echo<<<EOF
</div>
<!-- Content of tab 'Members' -->
<div id="Members" class="w3-container w3-border tabPage" 
    style="display:none;background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
/////////////////////////////////////////////////////////////////////////////////////////////
/// getting friend from database and setting friend card
$ret=getGroupMemebers($groupId,$db,false);
while($row=pg_fetch_row($ret)){
    $adminInfo=getPersonInfo($row[0],$db);
    $friends=getFriendsId($row[0],$db);
    $friendCount=pg_affected_rows($friends);
    $posts=getUserPost($row[0],$db);
    $postCount=pg_affected_rows($posts);
    echo personCard($row[0],$adminInfo[0],$adminInfo[1],$postCount,
        $friendCount,"Promote",$settingFlag,"Kickout",$settingFlag,"group.php");
    ///for kick out a friend from group
    if( $settingFlag && isset($_REQUEST[$row[0]."Kickout"])){
        kickOutPerson($groupId,$row[0],$db);
        unset($_REQUEST[$row[0]."Kickout"]);
    }
    /// for promoting a friend to admin
    if( $settingFlag && isset($_POST[$row[0]."Promote"])){
        promotePerson($groupId,$row[0],$db);
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// connecting setting form with a php function updatePersonInfo
if(isset($_POST['groupName']) && isset($_FILES['imageupload']))
{
    updateGroupInfo($db,$groupId);
}
////////////////////////////////////////////////////////////////////////////////////////////
echo<<<EOF
</div>
<div id="Settings" class="w3-container w3-border tabPage" style="display:none;
    background-color: white;height: 90%;padding-top: 10%" align="center">
    <div class="w3-card-4 w3-animate-opacity" style="max-width:600px">
        <b>Group Settings</b>
        
        <!-- form section in the modal-->
        <form class="w3-container" method="post" enctype="multipart/form-data">
            <div class="w3-section">
                <label><b>Choose another group photo</b><br>
                <!-- image changing option -->
                <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                    name="imageupload" alt="Insert an image" required>
                <!-- full name field in the modal-->
                <label><b>Group name</b></label>
                <input name="groupName" class="w3-input w3-border w3-margin-bottom" type="text" 
                    placeholder="Enter Group Name" required>

                <!-- confirm button in the modal-->
                <button class="w3-button w3-block w3-green w3-section w3-padding" type="submit">
                    Confirm Changes</button>
            </div>
        </form>

        <!-- cancel button in the modal-->
        <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
            <button type="button" class="w3-button w3-red">Cancel</button>
        </div>

    </div>
</div>
<script>
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
EOF;
if($settingFlag){
    echo<<<EOF
    </body>
    </html>
EOF;
}
else{
    echo<<<EOF
    <script>
        document.getElementById('settingButton').style.display='none';
    </script>
    </body>
    </html>
EOF;
}
ob_end_flush();