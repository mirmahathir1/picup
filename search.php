<?php
session_start();
ob_start();
include_once("cards.php");
include_once("query.php");
include_once("functions.php");

/////////////////////////////////////////////////////////////////////////////////////////////
/// connecting to db
$db=connectToDatabase();
//////////////////////////////////////////////////////////////////////////////////////////////
/// getting user id from database

/*
$ip=$_SERVER['REMOTE_ADDR'];
$personInfo=getUserInfo($ip,$db);
$personId=$personInfo[0];
$userId=$personInfo[0];
*/


$personId= $_SESSION["PERSON_ID"];
$userId= $_SESSION["PERSON_ID"];

$personInfo=getUserInfo2($personId,$db);
$serchingText=$personInfo[1];


//////////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['searchingText'])){
    //goToSearchPage($_POST['searchingText'],$db,$ip);
    goToSearchPage2($_POST['searchingText'],$db,$personId);
}
//////////////////////////////////////////////////////////////////////////////////////////////
/// getting User image and name
$user=getPersonInfo($personId,$db);
$userName=$user[0];
$userImage=$user[1];
///////////////////////////////////////////////////////////////////////////////////////////////
/// head for html
echo<<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
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
        <img src="coreImages/pickupLogo.png" onclick="document.location.href='home.php'"
            class="w3-bar-item w3-button" width="120" height="40" 
            style="margin-left: 100px; margin-right: 25px">
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
            <img src="coreImages/notification-circle-blue-512.png" class="w3-bar-item w3-button w3-animate-fading" width="50" height="50">

            <!-- NOTIFICATION DROPDOWN CONTENTS-->
            <div class="w3-dropdown-content w3-card-4 w3-animate-opacity" style="width:300px; height: 400px;overflow: scroll">
                <p align="center">
                    <b>Notifications</b>
                </p>
                <hr>
EOF;
///////////////////////////////////////////////////////////////////////////////////////////////
/// getting notification and adding notification into notification bar
$notifications=getNotification($personId,$db);
if($notifications){
    while($row = pg_fetch_row($notifications)) {
        echo notificationCard($row[0]);
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Tab for group and people search-->
echo <<<EOF
            </div>
        </div>
        <!-- Tab for group and people search-->
        <div class="w3-bar w3-gray">
            <button class="w3-bar-item w3-button tablink w3-blue-gray" onclick="openTab(event,'People')">People</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Posts')">Posts</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Groups')">Groups</button>
        </div>

    </div>
</div>
EOF;
//////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Content of tab 'Group' -->
echo<<<EOF
<!-- Content of tab 'Group' -->
<div id="Groups" class="w3-container w3-border tabPage" style="display:none;background-color: white;height: 90%; padding-top: 10%">
EOF;
///////////////////////////////////////////////////////////////////////////////////////////////
$ret=getSearchedGroup($serchingText,$db);
while($row=pg_fetch_row($ret)){
    $posts=getAllGroupPost($row[0],$db);
    $noPost=pg_affected_rows($posts);
    /// flag is for detecting whether user is already in the group or not
    /// flag=true user is not a member of this group
    /// flag=false user is a member of this group
    $flag=true;
    $admins=getGroupMemebers($row[0],$db,true);
    $noAdmin=pg_affected_rows($admins);
    while($row1=pg_fetch_row($admins)){
        if($row1[0]==$userId){
            $flag=false;
        }
    }
    $members=getGroupMemebers($row[0],$db,false);
    $noMember=pg_affected_rows($members)+$noAdmin;
    while($row1=pg_fetch_row($members)){
        if($row1[0]==$userId){
            $flag=false;
        }
    }
    if($flag){
        echo groupCard($row[0],$row[1],$row[2],$noMember,$noPost, true,"search.php");

        if(isset($_REQUEST[$row[0]."Join"])){
            joinGroup($userId,$row[0],$db);
            unset($_REQUEST[$row[0]."Join"]);
        }
    }
    else{
        echo groupCard($row[0],$row[1],$row[2],$noMember,$noPost, false,"search.php");

        if(isset($_POST[$row[0]."Leave"])){
            leaveFromGroup($userId,$row[0],$db);
        }
    }
    echo '<hr>';
}
//////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Content of tab 'People' -->
echo<<<EOF
</div>
<!-- Content of tab 'People' -->
<div id="People" class="w3-container w3-border tabPage" style=
    "background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
//////////////////////////////////////////////////////////////////////////////////////////////
$ret=getSearchedPerson($serchingText,$db);
while($row=pg_fetch_row($ret)){
    if($row[0]==$userId){
        continue;
    }
    $flag=true;
    $friendInfo=getPersonInfo($row[0],$db);
    $posts=getUserPost($row[0],$db);
    $postCount=pg_affected_rows($posts);
    $friends=getFriendsId($row[0],$db);
    $friendCount=pg_affected_rows($friends);
    /// if searched person and user are friend
    /// so user can only unfriend searched person
    while($row1=pg_fetch_row($friends)){
        if($row1[0]==$userId){
            echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
                $friendCount,"hk",false,"Unfriend",true,"search.php");
            /// for unfriend a friend.
            if(isset($_POST[$row[0]."Unfriend"])){
                unFriendPerson($userId,$row[0],$db);
            }
            $flag=false;
            break;
        }
    }
    ///if searched person sent a friend request to user
    /// so user can only accept or reject searched person's friend request
    $pendingFriends=getPendingFriendsId($userId,$db);
    while($row1=pg_fetch_row($pendingFriends)){
        if($row1[0]==$row[0]){
            echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
                $friendCount,"Accept",true,"Reject",true);
            ///for reject a friend request.

            /*if(isset($_POST[$row[0]."Reject"])){
                rejectRequest($userId,$row[0],$db);
            }
*/
            if(isset($_REQUEST[$row[0]."Reject"])){
                rejectRequest($userId,$row[0],$db);
                unset($_REQUEST[$row[0]."Reject"]);
            }

            ///for accept a friend request.
            if(isset($_POST[$row[0]."Accept"])){
                acceptRequest($userId,$row[0],$db,'Location: search.php');
            }
            $flag=false;
            break;
        }
    }
    ///if user sent a friend request of the searched people
    /// so user can nothing have to do
    $pendingFriends=getPendingFriendsId($row[0],$db);
    while($row1=pg_fetch_row($pendingFriends)){
        if($row1[0]==$userId){
            echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
                $friendCount,"Accept",false,"Reject",false);
            $flag=false;
            break;
        }
    }
    /// if user and searched person are not connected to each other
    /// so user can sent a friend request only
    if($flag){
        echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
            $friendCount,"Add",true,"Reject",false);
        if(isset($_POST[$row[0]."Add"])){
            addFriend($userId,$row[0],$db);
        }
    }

}
////////////////////////////////////////////////////////////////////////////////////////////////////
/// post tab
echo<<<EOF
</div>
<div id="Posts" class="w3-container w3-border tabPage" style=
    "display:none;background-color: white;height: 90%; padding-top: 10%">
<div class="w3-container" style="padding-left: 25%; padding-right: 25%">
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// first getting all searched posts from database then add post card for all of them

$allPosts=getSearchedPost($serchingText,$db);
$l=0;
while($post=pg_fetch_row($allPosts)){
    $flag=($post[6]==$personId);
    $postIds[$l]=$post[0];
    $l=$l+1;
    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
        $flag,$userName,$userImage,$personId,$db,"search.php",$post[6],isLiked($post[0],$personId,$db));
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
        editCaption($postIds[$i],$db,'Location: search.php');
    }
    ///for adding a like
    if(isset($_REQUEST[$postIds[$i]."like"])){
        addLike($postIds[$i],$personId,$db);
        unset($_REQUEST[$postIds[$i]."like"]);
    }

    ///for adding a comment
    if(isset($_POST[$postIds[$i]."editcomment"])){
        addComment($postIds[$i],$personId,$db,'Location: search.php');
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
//////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
/// end of html
echo<<<EOF
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
</body>
</html>
EOF;
ob_end_flush();