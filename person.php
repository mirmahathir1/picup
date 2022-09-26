<?php
session_start();
ob_start();
include_once("cards.php");
include_once("query.php");
include_once("functions.php");

///////////////////////////////////////////////////////////////////////////////////////////
/// connecting to db
$db=connectToDatabase();

/// getting User id,image and name



/*
$ip=$_SERVER['REMOTE_ADDR'];
$personId=getUserInfo($ip,$db);
$personId=$personId[0];
$userId=$personId;
*/


$personId= $_SESSION["PERSON_ID"];
$userId= $_SESSION["PERSON_ID"];
$notice = $_SESSION["PERSON_ID"];

$user=getPersonInfo($personId,$db);
$userName=$user[0];
$userPhoto=$user[1];

///////////////////////////////////////////////////////////////////////////////////////////
if(isset($_POST['searchingText'])){
    goToSearchPage2($_POST['searchingText'],$db,$personId);
    //goToSearchPage($_POST['searchingText'],$db,$ip);

}
///////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////
/// head of html
echo <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>$userName</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/w3style.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/icon.css">
    <script src="functions.js"></script>
</head>
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
            <img src="coreImages/notification-circle-blue-512.png" class="w3-bar-item w3-button w3-animate-fading" width="50" height="50">

            <!-- NOTIFICATION DROPDOWN CONTENTS-->
            <div class="w3-dropdown-content w3-card-4 w3-animate-opacity" style="width:300px; height: 400px;overflow: scroll">
                <p align="center">
                    <b>Notifications</b>
                </p>
                <hr>
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// getting notification and adding notification into notification bar
$notifications=getNotification($userId,$db);
if($notifications){
    while($row = pg_fetch_row($notifications)) {
        echo notificationCard($row[0]);
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Tab for post, group,friend and pending friend of user-->
echo <<<EOF
            </div>
        </div>
        <!-- Tab for post, group,friend and pending friend of user-->
        <div class="w3-bar w3-gray">
            <button class="w3-bar-item w3-button tablink w3-blue-gray" onclick="openTab(event,'Posts')">Posts</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Groups')">Groups</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Friends')">Friends</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'Pending')">Pending</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'PersonalAlbum')">Personal Album</button>
            <button class="w3-bar-item w3-button tablink" onclick="openTab(event,'PublicAlbum')">Public Album</button>
            
           </div>
    </div>
</div>
<br><br>
<!-- PostCards for user post only------------------------------------------------------------------------ -->
<div id="Posts" class="w3-container w3-border tabPage" style=
    "background-color: white;height: 90%; padding-top: 10%">

EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// first getting all posts of user from database then add post card for all of them
echo '<div class="w3-container" style="padding-left: 25%; padding-right: 25%">';
$allPosts=getUserPost($userId,$db);
$l=0;
while($post=pg_fetch_row($allPosts)){
    $flag=($post[6]==$userId);
    $postIds[$l]=$post[0];
    $l=$l+1;
    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
        $flag,$userName,$userPhoto,$userId,$db,"person.php",$post[6],isLiked($post[0],$personId,$db));
    setLikeComment($post[0],$db);

;
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
        editCaption($postIds[$i],$db,'Location: person.php');
    }
    ///for adding a like
    if(isset($_REQUEST[$postIds[$i]."like"])){
        addLike($postIds[$i],$personId,$db);

        unset($_REQUEST[$postIds[$i]."like"]);
    }
    ///for adding a comment
    if(isset($_POST[$postIds[$i]."editcomment"])){
        addComment($postIds[$i],$userId,$db,'Location: person.php');
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
    createPost($db,$personId,'Location: person.php');
}
///////////////////////////////////////////////////////////////////////////////////////////////
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
                        
                        <!-- tags -->    
                        <label><b>Tags</b></label>
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
    </div>
LINE;
/////////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Content of tab 'Group' -->
echo<<<EOF
<!-- Content of tab 'Group' -->
<div id="Groups" class="w3-container w3-border tabPage" style=
    "display:none;background-color: white;height: 90%; padding-top: 10%">
EOF;
/////////////////////////////////////////////////////////////////////////////////////////////
/// setting groups for users
$ret=getGroupsForUser($userId,$db);
while($row=pg_fetch_row($ret)){
    $posts=getAllGroupPost($row[0],$db);
    $noPost=pg_affected_rows($posts);
    $admins=getGroupMemebers($row[0],$db,true);
    $noAdmin=pg_affected_rows($admins);
    $members=getGroupMemebers($row[0],$db,false);
    $noMember=pg_affected_rows($members)+$noAdmin;

    echo groupCard($row[0],$row[1],$row[2],$noMember,$noPost, false,"person.php");
    ///leave button to php
    ///
    if(isset($_REQUEST[$row[0]."Leave"])){
        leaveFromGroup($userId,$row[0],$db);
        unset($_REQUEST[$row[0]."Leave"]);
    }

    /// open group to php
    if(isset($_POST["image".$row[0]])){
        //openGroupPage($row[0],$db,$ip);
        openGroupPage2($row[0],$db,$personId);
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////
///connecting html to php
if(isset($_POST['groupName']) && $_FILES['imageupload']){
    addGroup($userId,$db);
}
////////////////////////////////////////////////////////////////////////////////////////////////
/// plus button for adding group.
echo<<<EOF
    <button onclick="document.getElementById('id100').style.display='block'" 
        class="w3-button w3-xlarge w3-circle w3-blue w3-card-4 w3-animate-zoom" 
        style="position: fixed; top: 80%; left: 65%">+</button>
    <div id="id100" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
            
            <!-- close button of modal-->
            <div class="w3-center"><br>
                <span onclick="document.getElementById('id100').style.display='none'" 
                    class="w3-button w3-xlarge w3-hover-red w3-display-topright" 
                    title="Close Modal">&times;</span>

            </div>

            <!-- form section in the modal-->
            <form method="post" class="w3-section w3-padding" enctype="multipart/form-data">
                <div class="w3-section">
                    <!-- image input for group cover photo -->
                    <label><b>Group Photo</b></label>
                    <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                        name="imageupload" required>
                    <!-- Group name field in the modal-->
                    <label><b>Group name</b></label>
                    <input name="groupName" class="w3-input w3-border w3-margin-bottom" type="text" 
                        placeholder="Enter name of group" required>

                    <!-- create group button in the modal-->
                    <button class="w3-button w3-block w3-green w3-section w3-padding" 
                        type="submit">Create group</button>
                </div>
            </form>

            <!-- cancel button in the modal-->
            <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                <button onclick="document.getElementById('id100').style.display='none'" 
                    type="button" class="w3-button w3-red">Cancel</button>
            </div>
        </div>
    </div>
</div>
EOF;
/////////////////////////////////////////////////////////////////////////////////////////////
///<!-- Content of tab 'Friends' -->
echo<<<EOF
<!-- Content of tab 'Friends' -->
<div id="Friends" class="w3-container w3-border tabPage" style=
    "display:none;background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
//////////////////////////////////////////////////////////////////////////////////////////
/// getting friend from database and setting friend card
$ret=getFriendsId($userId,$db);
while($row=pg_fetch_row($ret)){
    $friendInfo=getPersonInfo($row[0],$db);
    $friends=getFriendsId($row[0],$db);
    $friendCount=pg_affected_rows($friends);
    $posts=getUserPost($row[0],$db);
    $postCount=pg_affected_rows($posts);
    echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
        $friendCount,"hk",false,"Unfriend",true,"person.php");
    ///for unfriend a friend.
    if(isset($_REQUEST[$row[0]."Unfriend"])){
        unFriendPerson($userId,$row[0],$db);
        unset($_REQUEST[$row[0]."Unfriend"]);
    }

}
///////////////////////////////////////////////////////////////////////////////////////////
/// <!-- Content of tab 'Pending' -->
echo<<<EOF
</div>
<!-- Content of tab 'Pending' -->
<div id="Pending" class="w3-container w3-border tabPage" style="display:none;background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
//////////////////////////////////////////////////////////////////////////////////////////////
/// getting pending friend from database and setting friend card
$ret=getPendingFriendsId($userId,$db);
while($row=pg_fetch_row($ret)){
    $friendInfo=getPersonInfo($row[0],$db);
    $friends=getFriendsId($row[0],$db);
    $friendCount=pg_affected_rows($friends);
    $posts=getUserPost($row[0],$db);
    $postCount=pg_affected_rows($posts);
    echo personCard($row[0],$friendInfo[0],$friendInfo[1],$postCount,
        $friendCount,"Accept",true,"Reject",true,"person.php");
    ///for unfriend a friend.
    if(isset($_REQUEST[$row[0]."Reject"])){
        rejectRequest($userId,$row[0],$db);
        unset($_REQUEST[$row[0]."Reject"]);
    }
    if(isset($_POST[$row[0]."Accept"])){
        acceptRequest($userId,$row[0],$db,'Location: person.php');
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// add personal album tab
echo<<<EOF
</div>
<div id="PersonalAlbum" class="w3-container w3-border tabPage" style="display:none;
    background-color: white;height: 90%;padding-top: 10%" align="center">
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// set personal album photo
$ret=getAlbumPic($personId,$db,true);
while($row=pg_fetch_row($ret)){
    echo albumCard($row[0],true);

    //if(isset($_POST[getImageName($row[0])."delete"])){
    if(isset($_REQUEST[getImageName($row[0])."delete"])){
        deleteAlbumPhoto($row[0],$db);
        unset($_REQUEST[getImageName($row[0])."delete"]);
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// setting plus button for adding personal album photo
echo<<<EOF
<button onclick="document.getElementById('id1000').style.display='block'"
        class="w3-button w3-xlarge w3-circle w3-blue w3-card-4 w3-animate-zoom" 
        style="position: fixed; top: 80%; left: 65%">+</button>
</div>
    <div id="id1000" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
            
            <!-- close button of modal-->
            <div class="w3-center"><br>
                <span onclick="document.getElementById('id1000').style.display='none'" 
                    class="w3-button w3-xlarge w3-hover-red w3-display-topright" 
                    title="Close Modal">&times;</span>

            </div>

            <!-- form section in the modal-->
            <form method="post" class="w3-section w3-padding" enctype="multipart/form-data">
                <div class="w3-section">
                    <!-- image input for album photo -->
                    <label><b>Add a photo</b></label>
                    <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                        name="personalimage" required>
                </div>
                <!-- create group button in the modal-->
                <button class="w3-button w3-block w3-green w3-section w3-padding" 
                    type="submit">Add</button>
            </form>

            <!-- cancel button in the modal-->
            <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                <button onclick="document.getElementById('id1000').style.display='none'" 
                    type="button" class="w3-button w3-red">Cancel</button>
            </div>
        </div>
    </div>
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// adding photo to personal album
if(isset($_FILES['personalimage'])){
    addAlbumPhoto($userId,$db,'personalimage',true);
}
////////////////////////////////////////////////////////////////////////////////////////////
/// adding public album tab
echo<<<EOF
<div id="PublicAlbum" class="w3-container w3-border tabPage" style="display:none;background-color:
        white;height: 90%;padding-top: 10%" align="center">
EOF;
/// set public album photo
$ret=getAlbumPic($personId,$db,false);
while($row=pg_fetch_row($ret)){
    echo albumCard($row[0],$row[1]==$personId);
    if($row[1]==$personId){
        if(isset($_REQUEST[getImageName($row[0])."delete"]))
        {
            deleteAlbumPhoto($row[0],$db);
            unset($_REQUEST[getImageName($row[0])."delete"]);
        }
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
/// setting plus button for adding personal album photo
echo<<<EOF
<button onclick="document.getElementById('id10000').style.display='block'"
        class="w3-button w3-xlarge w3-circle w3-blue w3-card-4 w3-animate-zoom" 
        style="position: fixed; top: 80%; left: 65%">+</button>
</div>
    <div id="id10000" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
            
            <!-- close button of modal-->
            <div class="w3-center"><br>
                <span onclick="document.getElementById('id10000').style.display='none'" 
                    class="w3-button w3-xlarge w3-hover-red w3-display-topright" 
                    title="Close Modal">&times;</span>

            </div>

            <!-- form section in the modal-->
            <form method="post" class="w3-section w3-padding" enctype="multipart/form-data">
                <div class="w3-section">
                    <!-- image input for album photo -->
                    <label><b>Add a photo</b></label>
                    <input class="w3-input w3-margin-bottom" type="file" accept="image/*" 
                        name="publicimage" required>
                </div>
                <!-- create group button in the modal-->
                <button class="w3-button w3-block w3-green w3-section w3-padding" 
                    type="submit">Add</button>
            </form>

            <!-- cancel button in the modal-->
            <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                <button onclick="document.getElementById('id1000').style.display='none'" 
                    type="button" class="w3-button w3-red">Cancel</button>
            </div>
        </div>
    </div>
EOF;
////////////////////////////////////////////////////////////////////////////////////////////
/// adding photo to personal album
if(isset($_FILES['publicimage'])){
    addAlbumPhoto($userId,$db,'publicimage',false);
}
////////////////////////////////////////////////////////////////////////////////////////////

echo<<<EOF
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