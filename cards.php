<?php
include_once("query.php");
include_once("functions.php");
function headerPersonCard()
{

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

    $user=getPersonInfo($userId,$db);
    $userName=$user[0];
    $userImage=$user[1];

    $returnString="";
    $returnString=$returnString.<<<EOF
    <span class="w3-button w3-bar-item" onclick="document.location.href='person.php'">
        <img id="homePageOwnerPhoto" src=$userImage class=" w3-circle"
            width="30" height="30">
        <b id="homePageOwnerName">$userName</b>
EOF;
    if(isSpecial($userId,$db)!="f") {
        $returnString = $returnString.<<<EOF
        <img src="coreImages/star.png" width="20" height="20" style="border-top: 10px">
EOF;
    }
    $returnString=$returnString.<<<EOF
    </span>
EOF;
    return $returnString;
}
/**
 * this function will add a comment card into post card
 * the id of this comment card is same as it's id in database
 * whether delete button is pressed or not can be recognized by checking $_POST[$commentDeleteId]
 * where commentId means it's id in database and $commentDeleteId=$commentId."delete";
 * @param $commentId
 * @param $personName
 * @param $personPhoto
 * @param $dateTime
 * @param $comment
 * @param $isEditable
 * @return string
 */
function addCommentCard($commentId,$personName,$personPhoto,$dateTime,$comment,$isEditable,$pageName){
    $commentDeleteId=$commentId."delete";

    $resultString=<<<LINE
    <div id=$commentId class="w3-display-container w3-card-4" style="transition:0.5s;width:100%">
        <div class="w3-bar w3-light-grey" style="width:100%" >
            <!--person photo with modal-->
            <div class="w3-dropdown-hover w3-light-grey">
                <img src=$personPhoto class="w3-bar-item w3-circle
                    w3-hide-small" style="width:100px">
                <div class="w3-dropdown-content w3-animate-zoom" style="width:200px"><br>
                    <img src=$personPhoto alt="Norway"
                         style="width:100%;height: 100%;">
                </div>
            </div>
            <!--person name and date time-->
            <div class="w3-bar-item w3-light-grey">
                <span class="w3-large">$personName</span><br>
                <span class="w3-small">$dateTime</span>
            </div>
            <!--comment statement-->
            <div class="w3-panel w3-light-grey">
                <p>$comment</p>
            </div>
        </div>
LINE;
    //display hovar delete button for a comment card
    if($isEditable){
        $resultString=$resultString.<<<LINE
        <div id=$commentDeleteId class="w3-display-topright w3-display-hover w3-small 
        w3-white w3-animate-opacity w3-btn w3-margin w3-round w3-white 
        w3-animate-opacity w3-btn w3-margin w3-round" title="delete"
        onclick="downButtonAction(this.id,'$commentId','$pageName')">
            <i class="material-icons w3-text-black">delete</i>
        </div>
        
    </div>
LINE;
    }
    else{
        $resultString=$resultString.<<<LINE
    </div>
LINE;
    }
    return $resultString;
}
/**
 * this function will add a post card
 * the id of this post card is same as it's id in database
 * delete a post via id $postId
 * set amount of like comment via id $postId."likecomment"
 * modify caption for a post via id $postId."captionnew";
 * @param $postId
 * @param $personName
 * @param $personPhoto
 * @param $postCaption
 * @param $postPhoto
 * @param $dateTime
 * @param $isEditAble
 * @param $userName
 * @param $userPhoto
 * @param $userId
 * @param $db
 * @return string
 */
function postCard($postId,$personName,$personPhoto,$postCaption,$postPhoto,$dateTime,
                  $isEditAble,$userName,$userPhoto,$userId,$db,$pageName,$personId,$isLiked=false){
    $postCard=$postId."";
    $postPhotoModal=$postId."modal";
    $postCommentModal=$postId."comment";
    $postLikeComment=$postId."likecomment";
    $postCaptionId=$postId."caption";
    $postCaptionNew=$postId."captionnew";
    $postCaptionModal=$postId."captionmodal";
    $postAddComment=$postId."addcomment";
    $postEditComment=$postId."editcomment";
    $postCardDelete=$postId."delete";
    $postLikeButton=$postId."like";

    $returnString ="";
    ///////////////////////////////////////////////////////////////////////////////////
    /// setting post owner bar and post photo and post caption
    $returnString=$returnString.<<<LINE
    <!-- post card -->
    <div id=$postCard class="w3-animate-zoom w3-card-4">
        <!-- person description -->
        <div class="w3-bar  w3-light-grey" style="width:100%" >
         <!-- person Image with modal on hovar-->
            <div class="w3-dropdown-hover w3-light-grey">
                <img src=$personPhoto alt="person profile pic" class="w3-bar-item w3-circle
                   w3-hide-small" style="width:100px">
                <div class="w3-dropdown-content w3-animate-zoom"><br>
                    <img src=$personPhoto alt="person profile pic" style="width: auto;height: 300px">
                </div>
            </div>
             <!-- person name and post date and time-->
            <div class="w3-bar-item w3-light-grey">
                <span class="w3-large">$personName</span>
                <span> 
LINE;
    if( $db && isSpecial($personId,$db)!="f") {
        $returnString = $returnString.<<<EOF
                <img src="coreImages/star.png" width="20" height="20" style="border-top: 10px">
EOF;
    }
    $returnString=$returnString.<<<LINE
                </span><br>
                <span class="w3-small">$dateTime</span>
            </div>
        </div>
        <!-- post image with display container-->
        <div class="w3-display-container" style="transition:0.5s;width:100%">
            <!-- post image with modal on click-->
            <img src=$postPhoto style="width: 100%" onclick=
                "document.getElementById('$postPhotoModal').style.display='block'">
            <!-- modal image -->
            
            <div id=$postPhotoModal class="w3-modal w3-animate-zoom w3-center" 
                onclick="this.style.display='none'">
                <img class="w3-modal-content" src=$postPhoto style="width:auto; height: 100%">
            </div>
LINE;

    if($isLiked) {

        $returnString = $returnString . <<<LINE
            <!-- like -->
            <div id=$postLikeButton class="w3-display-topleft w3-display-hover w3-small w3-white w3-animate-opacity w3-btn
                    w3-margin w3-round" title="like" onclick="Like(this.id,'$postCard','$pageName')"><img src="coreImages/likeButton2.png" height="25" width="35"></div>
LINE;
    }
    else
    {

        $returnString = $returnString . <<<LINE
            <!-- like -->
            <div id=$postLikeButton class="w3-display-topleft w3-display-hover w3-small w3-white w3-animate-opacity w3-btn
                    w3-margin w3-round" title="like" onclick="Like(this.id,'$postCard','$pageName')"><img src="coreImages/likeButton.png" height="25" width="35"></div>
LINE;
    }

    $returnString=$returnString.<<<LINE
            
            <!-- comment -->
            <div class="w3-display-topleft w3-display-hover w3-white w3-animate-opacity w3-btn w3-margin
                    w3-round" style="left:85px" title="comment"
                onclick="document.getElementById('$postCommentModal').style.display='block'">
                    <img src="coreImages/commentButton.png" height="25" width="35">
            </div>
            
LINE;
    ////////////////////////////////////////////////////////////////////////////////////
    /// display hovar delete button for postcard
    if($isEditAble){
        $returnString=$returnString.<<<LINE
            <div id=$postCardDelete class="w3-display-topright w3-display-hover 
            w3-small w3-white w3-animate-opacity w3-btn w3-margin w3-round" title="delete" onclick="downButtonAction(this.id,'$postCard','$pageName')">
                   <img src="coreImages/deleteButton.png" height="25" width="35">             
            </div>
LINE;
    }
    /////////////////////////////////////////////////////////////////////////////////////
    /// setting post modal (like,comment,share,like and comment count)
    $returnString=$returnString.<<<LINE
            <div class="w3-display-bottomleft w3-display-hover w3-small
                w3-text-black w3-light-grey w3-padding"><pre><b id=$postLikeComment></b></pre>
            </div>
        </div>
        <div class="w3-display-container w3-panel w3-pale-blue w3-leftbar w3-border-blue">
            <p id=$postCaptionId>$postCaption</p>
LINE;
    /////////////////////////////////////////////////////////////////////////////////////
    /// change caption portion
    if($isEditAble) {
        $returnString = $returnString . <<<LINE
            <div class="w3-display-topright w3-display-hover w3-small">
                <button onclick="document.getElementById('$postCaptionModal').style.display='block'"
                        class="w3-white w3-animate-opacity w3-btn w3-margin w3-round" title="edit">
                    <i class="material-icons w3-text-black">edit</i></button>
            </div>    
        </div>
        <!-- modal for change caption -->
        <div id=$postCaptionModal class="w3-modal">
            <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
                <!-- form section in the modal-->
                <form class="w3-container" method="post" enctype="multipart/form-data">
                    <div class="w3-section">
                        <!-- full name field in the modal-->
                        <label><b>Change Caption</b></label>
                        <input class="w3-input w3-border w3-margin-bottom" type="text" 
                            name=$postCaptionNew placeholder="New caption" required>

                        <!-- submit post in the modal-->
                        <button class="w3-button w3-block w3-green w3-section w3-padding" 
                            type="submit">Save changes</button>
                    </div>
                </form>

                <!-- cancel button in the modal-->
                <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
                    <button onclick="document.getElementById('$postCaptionModal').style.display='none'" type="button" class="w3-button w3-red">Cancel</button>
                </div>

            </div>
        </div>
    </div>
LINE;
    }
    else{
        $returnString = $returnString . <<<LINE
        </div>
        </div>
        
LINE;

    }

    //////////////////////////////////////////////////////////////////////////////////////
    /// comment section
    if($db){
        $returnString=$returnString.<<<LINE

    <div id=$postCommentModal class="w3-display-container w3-modal w3-animate-zoom" 
        style="padding-left: 25%; padding-right: 25%">
        <!-- setting minimize button-->
        <div class="w3-display-topright w3-display-hover w3-small" style="padding-right: 25%">
            <button onclick="document.getElementById('$postCommentModal').style.display='none'"
                type="button" class="w3-white w3-animate-opacity w3-btn w3-margin w3-round"
                title="minimize" >--</button>
        </div>
        <div id=$postAddComment>
LINE;

        $commentRows=getCommentsForAPost($postId,$db);
        while($comment=pg_fetch_row($commentRows)) {
            $flag2 = ($comment[5] == $userId);
            $returnString=$returnString.addCommentCard( $comment[0], $comment[1], $comment[2], $comment[3]
                    , $comment[4], $flag2,$pageName);
        }

        $returnString=$returnString.<<<LINE
        </div>
        <hr>
        <div class="w3-bar  w3-light-grey" style="width:100%" >
            <div class="w3-container">
                <h3>Give a comment</h3>
            </div>
            <div class="w3-dropdown-hover w3-light-grey">
                <img src=$userPhoto class="w3-bar-item w3-circle
               w3-hide-small" style="width:100px">
                <div class="w3-dropdown-content w3-animate-zoom" style="width:200px"><br>
                    <img src=$userPhoto alt="userPhoto" style="width:100%;height: 100%;">
                </div>
            </div>
            <div class="w3-bar-item   w3-light-grey">
                <span class="w3-large">$userName</span><br>
            </div>
            
            <form class="w3-container" method="post" enctype="multipart/form-data">
                <input name=$postEditComment class="w3-input w3-hover-light-blue" type="text">
                <button class="w3-white w3-btn w3-margin w3-round" type="submit">Confirm</button>
            </form>
        </div>
    </div>
LINE;
    }

    return $returnString;
}

/**
 * this function is for showing a group card
 * if user is already joined then put join flag false
 * which will show leave option in group card
 * if user is not joined in the group then put true in join flag
 * which will show join option in the group card
 * @param $groupId
 * @param $groupName
 * @param $groupImage
 * @param $joinFlag
 * @param $memberCount
 * @param $photoCount
 * @return string
 */
function groupCard($groupId,$groupName,$groupImage,$memberCount,$photoCount, $joinFlag,$pageName)
{
    $groupCardId=$groupId."";
    $groupJoin=$groupId."Join";
    $groupLeave=$groupId."Leave";
    $groupImageId="image".$groupId;

    //the string which is going to be returned
    $returnString = "";
    $returnString= $returnString.<<<EOF
    <!-- Example of a Group compact card-->
    <div id=$groupCardId class="w3-container w3-animate-right" align="center">
        <div class="w3-display-container" style="width:20%;height: 20%;">

            <!-- IMAGE OF THE GROUPCARD -->
            <form method="post" enctype="multipart/form-data">
                <button type="submit" name=$groupImageId >
                     <img src=$groupImage alt="Group image" class=" w3-hover-opacity" 
                        style="width:100%">
                </button>
            </form>

            <!-- NAME OF GROUPCARD-->
            <div class="w3-display-topleft w3-text-black" style="padding-left: 5%">
                <div class="w3-animate-opacity"><p>$groupName</p></div>
            </div>

EOF;
    if($joinFlag)
    {
        $returnString= $returnString.<<<EOF

        <!-- JOIN BUTTON OF THE GROUPCARD-->
                <div id=$groupJoin onclick="downButtonAction(this.id,'$groupCardId','$pageName')" class="w3-display-topright w3-display-hover w3-small w3-button w3-black">
                        Join
                </div>
           
EOF;
    }
    else
    {
        $returnString= $returnString.<<<EOF
        <!-- LEAVE BUTTON OF THE GROUPCARD-->
           
                <div id=$groupLeave onclick="downButtonAction(this.id,'$groupCardId','$pageName')" 
                class="w3-display-bottomright w3-display-hover w3-small w3-button w3-black">
                        Leave
                </div>
            
EOF;

    }

    $returnString= $returnString.<<<EOF
            <!-- INFO OF THE GROUPCARD-->
            <div class="w3-display-bottomleft w3-display-hover w3-text-black w3-small" style="padding-left: 5%">
                <div class="w3-animate-opacity "><pre><b>$memberCount members, $photoCount photos</b></pre></div>
            </div>
        </div>
    </div>
    <br>
EOF;
    return $returnString;
}

/**
 * this function make a person card
 * depend on requirement you can specify upright and downright button by using parameter.
 * person card id is same as the person's id
 * upButton click on action can be controled using $_POST[$personId."".$upButtonText]
 * similarly, downButton click on action can be controled using $_POST[$personId."".$downButtonText]
 * @param $personId
 * @param $personName
 * @param $personImage
 * @param $photoCount
 * @param $friendCount
 * @param $upButtonText
 * @param $upButtonFlag
 * @param $downButtonText
 * @param $downButtonFlag
 * @return string
 */
function personCard($personId,$personName,$personImage,$photoCount,
                    $friendCount,$upButtonText,$upButtonFlag,$downButtonText,$downButtonFlag,$pageName="")
{
    $personCardId=$personId."";
    $upButtonId=$personId."".$upButtonText;
    $downButtonId=$personId."".$downButtonText;

    //the string which is going to be returned
    $returnString="";

    $returnString=$returnString.<<<EOF
    
    <!-- Person compact card-->
    <div id=$personCardId class="w3-display-container w3-animate-right w3-card-4" style="width:50%;">
        <div class="w3-bar  w3-light-grey " style="width:100%">
            
            <!-- Image of person -->
            <div class="w3-dropdown-hover w3-light-grey">
                <img src=$personImage class="w3-bar-item w3-circle
               w3-hide-small" style="width:100px">
            </div>

            <!-- name and info of person-->
            <div class="w3-bar-item  w3-light-grey">
                <span class="w3-large">$personName</span>
                <span> 
EOF;
    $db=connectToDatabase();
    if(isSpecial($personId,$db)!="f") {
        $returnString = $returnString.<<<EOF
                <img src="coreImages/star.png" width="20" height="20" style="border-top: 10px">
EOF;
    }
    $returnString=$returnString.<<<EOF
                </span>
                <br>
                <span class="w3-small">$photoCount photos, $friendCount friends</span>
            </div>
EOF;
    if($upButtonFlag) {
        $returnString = $returnString . <<<EOF
            <!-- 'add' button of the card -->
            <form method="post" enctype="multipart/form-data" >
                <div class="w3-display-topright w3-display-hover w3-small ">
                    <button type="submit" name=$upButtonId class=
                        "w3-button w3-light-grey">$upButtonText</button>
                </div>
            </form>
EOF;
    }
    if($downButtonFlag) {
        $returnString = $returnString . <<<EOF
            <!-- 'reject' button of the card -->
                <div id=$downButtonId class="w3-display-bottomright w3-display-hover w3-small w3-button w3-light-grey"
                onclick="downButtonAction(this.id,'$personCardId','$pageName')">
                    $downButtonText
                </div>
                
            
EOF;
    }
    $returnString=$returnString.<<<EOF
        </div>
    </div>
    <br>
EOF;

    return $returnString;
}
/**
 * adding notification into notification bar
 * @param $note
 * @return string*
 */
function notificationCard($note)
{
    return<<<EOF
        <div class="w3-panel w3-light-gray w3-button w3-card-4" style="width: 100%">
             <p>$note</p>
        </div>
EOF;
}
function albumCard($image,$deleteFlag)
{
    $albumCardId=$image."id";
    $deleteButtonId=getImageName($image)."delete";
    $returnString="";

    $PhotoModal=$image."modal";

    $returnString=$returnString.<<<EOF
<div id="$albumCardId" class="w3-container w3-third w3-animate-zoom w3-card-4" >
    <div class="w3-display-container w3-card" onclick=
                "document.getElementById('$PhotoModal').style.display='block'">
        <img src="$image" alt="Avatar" style="width:100%">
EOF;
    if($deleteFlag){
        $returnString=$returnString.<<<EOF
        <!--form method="post">
            <div class="w3-display-bottomright w3-display-hover w3-xlarge">
                <button name="$deleteButtonId" type="submit" class="w3-button">
                     <i class="material-icons w3-text-black" style="width: 45px;height: 35px">delete</i>
                </button>
            </div>
        </form-->
        <div id=$deleteButtonId class="w3-display-bottomright w3-display-hover 
            w3-small w3-white w3-animate-opacity w3-btn w3-margin w3-round" title="delete" onclick="downButtonAction(this.id,'$albumCardId','person.php')">
                   <img src="coreImages/deleteButton.png" height="35" width="45">             
        </div>
EOF;
    }
    $returnString=$returnString.<<<EOF
    </div>
    <div id=$PhotoModal class="w3-modal w3-animate-zoom w3-center" onclick="this.style.display='none'">
          <img class="w3-modal-content" src=$image style="width:auto; height: 100%">
    </div>
</div>
EOF;
    return $returnString;
}