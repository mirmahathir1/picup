<?php
/**
 * this function connect to database
 * @return resource
 */
function connectToDatabase(){
    $host        = "host = localhost";
    $port        = "port = 5432";
    $dbname      = "dbname = flickr";
    $credentials = "user = postgres password=123";
    /// postgresql connecting function
    $db = pg_connect( "$host $port $dbname $credentials"  );
    if(!$db) {
        echo "Error : Unable to open database\n";
    }
    return $db;
}

/**
 * this function is for setting user ip in the database along with his person id
 * @param $userId
 * @param $db
 * @param $ip
 */
function setUserId($userId,$db,$ip){
    $sql=<<<LINE
    DELETE FROM USERS
    WHERE IP_ADDRESS='$ip';
LINE;
    pg_query($db, $sql);
    $sql=<<<LINE
    INSERT INTO USERS (USER_ID,IP_ADDRESS) VALUES ($userId,'$ip');
LINE;
    pg_query($db, $sql);
}
/**
 * sign in function if password or email is wrong then give alert message
 * this function will save user information in the database
 * @param $db
 * @param $ip
 */

function signIn($db,$ip){
    /// getting information from form
    $email=$_POST['email1'];
    $password=$_POST['password1'];
    $sql=<<<LINE
    SELECT PERSON_ID
    FROM PERSONS
    WHERE EMAIL_ID='$email' AND PASSWORD=crypt('$password', PASSWORD);
LINE;
    $ret = pg_query($db, $sql);
    $queryResult = pg_fetch_row($ret);

    /// if login info is correct then go to homepage
    if($queryResult[0]){
        ///setting user id
        setUserId($queryResult[0],$db,$ip);

        $_SESSION["PERSON_ID"] = $queryResult[0];

        ///closing db
        pg_close($db);
        /// go to homepage
        header('Location: home.php');
    }
    else{
        echo <<<LINE
        <script>window.alert("Password or Email was wrong.");</script>
LINE;
    }
}
/**
 * sign up function
 * this function will save user information in the database
 * @param $db
 * @param $ip
 */
function signUp($db,$ip){
    /// saving signUp data
    $name=$_POST["fullName"];
    $email=$_POST["email"];
    $password=$_POST["password"];
    $imagename="img_avatar2.png";
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);

    list($imageLongName, $fileType)=explode(".",$imagename);

    $imagename="images/".$row[0].".".$fileType;

    ///  insert data in database
    $sql=<<<LINE
    INSERT INTO PERSONS(NAME,EMAIL_ID,PASSWORD,PROFILE_PHOTO) 
	    VALUES('$name','$email',crypt('$password', gen_salt('bf')),'$imagename');
LINE;
    $ret=pg_query($db, $sql);
    if(!$ret){
        echo <<<LINE
            <script>
                alert("please enter valid email");
            </script>
LINE;
        exit;
    }
    $sql=<<<LINE
    SELECT PERSON_ID
    FROM PERSONS
    WHERE EMAIL_ID='$email';
LINE;
    $ret = pg_query($db, $sql);
    $queryResult = pg_fetch_row($ret);
    ///setting user id in database
    setUserId($queryResult[0],$db,$ip);



    $_SESSION["PERSON_ID"]= $queryResult[0];



    /// uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    ///CREATING PRIVATE AND PUBLIC ALBUM FOR A PERSON
    $sql=<<<LINE
    INSERT INTO ALBUMS(PERSON_ID,ALBUM_NAME,IS_PRIVATE) VALUES ($queryResult[0],'$name',TRUE );
    INSERT INTO ALBUMS(PERSON_ID,ALBUM_NAME,IS_PRIVATE) VALUES ($queryResult[0],'$name',FALSE);
LINE;
    pg_query($db, $sql);
    /// closing database
    pg_close($db);
    /// go to home page.
    header('Location: home.php');
}
/**
 * this will save the searching text in the database by it's id
 * @param $searchingText
 * @param $db
 * @param $IP
 */
/*
function goToSearchPage($searchingText,$db,$IP){
    $sql=<<<LINE
    UPDATE USERS
    SET INFO='$searchingText'
    WHERE IP_ADDRESS='$IP';
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: search.php');
}
*/
function goToSearchPage2($searchingText,$db,$personId){
    $sql=<<<LINE
    UPDATE USERS
    SET INFO='$searchingText'
    WHERE USER_ID='$personId';
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: search.php');
}

/**
 * setting like comment count for a post
 * @param $postId
 * @param $db
 */
function setLikeComment($postId,$db){

    $like=getLikeAmount($postId,$db);
    $comments=getCommentsForAPost($postId,$db);
    $comment=pg_affected_rows($comments);
    $postLikeComment=$postId."likecomment";
    $text=$like." likes, ".$comment." comments";

    echo<<<LINE
    <script>
        document.getElementById('$postLikeComment').innerHTML='$text';
    </script>
LINE;




}
/**
 * deleting a post from database
 * @param $postId
 * @param $db
 */
function deletePost($postId,$db){
    $sql=<<<LINE
    SELECT post_photo
    FROM posts 
    WHERE POST_ID=$postId;
LINE;

    $ret = pg_query($db,$sql);

    $postPhoto = pg_fetch_row($ret);
    unlink($postPhoto[0]."");

    $sql=<<<LINE
    DELETE FROM POSTS
    WHERE POST_ID=$postId;
LINE;
    pg_query($db, $sql);

}
/**
 * modify caption for a post
 * @param $postId
 * @param $db
 */
function editCaption($postId,$db,$location){
    $id=$postId."captionnew";
    $newCaption=$_POST[$id];
    $sql=<<<LINE
    UPDATE POSTS
    SET CAPTION='$newCaption'
    WHERE POST_ID=$postId;
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header($location);
}
/**
 * adding like while one like on a post
 * @param $postId
 * @param $personId
 * @param $db
 */
function addLike($postId,$personId,$db){
    $sql=<<<LINE
    INSERT INTO LIKES (PERSON_ID,POST_ID,DATE_TIME)
    VALUES ($personId,$postId,CURRENT_TIMESTAMP(0) );
LINE;
    pg_query($db, $sql);

    setLikeComment($postId,$db);

}
/**
 * add a comment while one comment on post
 * after adding a comment page will be refreshed
 * @param $postId
 * @param $personId
 * @param $db
 */
function addComment($postId,$personId,$db,$location){
    $comment=$_POST[$postId."editcomment"];
    $sql=<<<LINE
    INSERT INTO COMMENTS (PERSON_ID,POST_ID,DATE_TIME,DESCRIPTION)
    VALUES ($personId,$postId,CURRENT_TIMESTAMP(0),'$comment' );
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header($location);
    /*
    echo<<<LINE
    <script>
        document.location.reload(true);
    </script>
LINE;
*/
}
/**
 * delete a comment for a post from database
 * @param $commentId
 * @param $db
 */
function deleteComment($commentId,$db){
    $sql=<<<LINE
    DELETE FROM COMMENTS
    WHERE COMMENT_ID=$commentId;
LINE;
    pg_query($db, $sql);


}
/**
 * function for adding new post in database
 * after this function page will be reloaded
 * @param $db
 * @param $personId
 * @param $location
 */
function createPost($db,$personId,$location){
    ///getting new post data from form
    $caption=$_POST["caption"];
    $tags=$_POST["tags"];
    $imagename="img_avatar2.png";
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);

    list($imageLongName, $fileType)=explode(".",$imagename);
    $imagename="images/".$row[0].".".$fileType;


    ///uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    /// insert data in database
    $sql=<<<LINE
    INSERT INTO POSTS(POST_PHOTO,PERSON_ID,DATE_TIME,CAPTION) 
	    VALUES('$imagename',$personId,CURRENT_TIMESTAMP(0),'$caption' );
    SELECT INSERT_TAGS('$tags');
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header($location);
}
/**
 * function for updating person's information
 * after this function page will be reloaded
 * @param $db
 * @param $personId
 */
function updatePersonInfo($db,$personId){
    ///getting changed data from form
    $sql=<<<LINE
    SELECT profile_photo
    FROM persons 
    WHERE person_id=$personId;
LINE;

    $ret = pg_query($db,$sql);

    $postPhoto = pg_fetch_row($ret);
    unlink($postPhoto[0]."");

    $name=$_POST["fullName"];
    $password=$_POST["password"];
    $imagename="img_avatar2.png";
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);

    list($imageLongName, $fileType)=explode(".",$imagename);

    $imagename="images/".$row[0].".".$fileType;


    ///uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    /// update information in database.
    $sql=<<<LINE
    UPDATE PERSONS
    SET NAME='$name',PASSWORD=crypt('$password', gen_salt('bf')),PROFILE_PHOTO='$imagename'
    WHERE PERSON_ID=$personId;
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: home.php');
}
/**
 * FUNCTION for joining in a group
 * @param $userId
 * @param $groupId
 * @param $db
 */
function joinGroup($userId,$groupId,$db){
    $sql=<<<LINE
    INSERT INTO GROUP_MEMBERS(PERSON_ID,GROUP_ID,IS_ADMIN) VALUES ($userId,$groupId,FALSE );
LINE;
    pg_query($db, $sql);
}
/**
 * function for leaving a group
 * @param $userId
 * @param $groupId
 * @param $db
 */
function leaveFromGroup($userId,$groupId,$db){
    $sql=<<<LINE
    DELETE FROM GROUP_MEMBERS
    WHERE GROUP_ID=$groupId AND PERSON_ID=$userId;
LINE;
    pg_query($db, $sql);
    $admins=getGroupMemebers($groupId,$db,true);
    $noAdmin=pg_affected_rows($admins);
    $members=getGroupMemebers($groupId,$db,false);
    $noMember=pg_affected_rows($members)+$noAdmin;
    if($noMember==0){
        $sql=<<<LINE
        DELETE FROM GROUPS
        WHERE GROUP_ID=$groupId;
LINE;
        pg_query($db, $sql);
    }

}
/**
 * this function is for unfriend a person.
 * @param $userId
 * @param $personId
 * @param $db
 */
function unFriendPerson($userId,$personId,$db){
    $sql=<<<LINE
    DELETE FROM FRIENDS
    WHERE (PERSON_ID=$userId AND FRIEND_ID=$personId) OR (PERSON_ID=$personId AND FRIEND_ID=$userId);
LINE;

    pg_query($db, $sql);


}
/**
 * this function is for rejecting friend request a person.
 * @param $userId
 * @param $personId
 * @param $db
 */
function rejectRequest($userId,$personId,$db){
    $sql=<<<LINE
    DELETE FROM FRIENDS_PENDING
    WHERE (PERSON_ID=$userId AND FRIEND_ID=$personId);
LINE;

    pg_query($db, $sql);

}
/**
 * this function is for accepting friend request a person.
 * @param $userId
 * @param $personId
 * @param $db
 */
function acceptRequest($userId,$personId,$db,$location){
    $sql=<<<LINE
    DELETE FROM FRIENDS_PENDING
    WHERE (PERSON_ID=$userId AND FRIEND_ID=$personId);
    INSERT INTO FRIENDS(PERSON_ID,FRIEND_ID) VALUES ($userId,$personId);
    INSERT INTO FRIENDS(PERSON_ID,FRIEND_ID) VALUES ($personId,$userId);
    
LINE;
    unset($_POST[$personId."Accept"]);
    pg_query($db, $sql);
    pg_close($db);
    header($location);
}
/**
 * this function is for adding a person.
 * @param $userId
 * @param $personId
 * @param $db
 */
function addFriend($userId,$personId,$db){
    $sql=<<<LINE
    INSERT INTO FRIENDS_PENDING(PERSON_ID,FRIEND_ID) VALUES ($personId,$userId);
LINE;
    unset($_POST[$personId."Add"]);
    pg_query($db, $sql);
    echo<<<LINE
    <script>
        document.getElementById('$personId').style.display='none';
    </script>
LINE;
}

/**
 * this function is for opening group page
 * @param $groupId
 * @param $db
 * @param $ip
 */
/*
function openGroupPage($groupId,$db,$ip){
    $sql=<<<LINE
    UPDATE USERS
    SET INFO='$groupId'
    WHERE IP_ADDRESS='$ip';
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: group.php');
}
*/
function openGroupPage2($groupId,$db,$personId){
    $sql=<<<LINE
    UPDATE USERS
    SET INFO='$groupId'
    WHERE USER_ID='$personId';
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: group.php');
}
/**
 * this function is for creating new group
 * @param $userId
 * @param $db
 */
function addGroup($userId,$db){
    ///getting new group data from form
    $groupName=$_POST["groupName"];
    $imagename="img_avatar2.png";
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);


    list($imageLongName, $fileType)=explode(".",$imagename);

    $imagename="images/".$row[0].".".$fileType;




    ///uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    /// insert data in database
    $sql=<<<LINE
    INSERT INTO GROUPS(GROUP_NAME,GROUP_PHOTO)VALUES('$groupName','$imagename');
LINE;
    pg_query($db, $sql);
    /// getting group id for adding user as group admin
    $sql=<<<LINE
    SELECT MAX(GROUP_ID)
    FROM GROUPS;
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);
    $groupId=$row[0];
    ///adding user as group admin
    $sql=<<<LINE
    INSERT INTO GROUP_MEMBERS(PERSON_ID,GROUP_ID,IS_ADMIN) VALUES ($userId,$groupId,true);
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: person.php');
}
/**
 * function for adding new post in database
 * after this function page will be reloaded
 * @param $db
 * @param $personId
 * @param $groupId
 */
function newPostForGroup($db,$personId,$groupId){
    ///getting new post data from form
    $caption=$_POST["caption"];
    $imagename="img_avatar2.png";
    $tags=$_POST["tags"];
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);


    list($imageLongName, $fileType)=explode(".",$imagename);

    $imagename="images/".$row[0].".".$fileType;
///////////////////////////////////////////////////////////////////////////////////////


    ///////////////////////////////////////uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    ///////////////////////////////////////// insert data in database
    $sql=<<<LINE
    INSERT INTO POSTS(POST_PHOTO,PERSON_ID,DATE_TIME,CAPTION) 
	    VALUES('$imagename',$personId,CURRENT_TIMESTAMP(0),'$caption' );
    SELECT INSERT_TAGS('$tags');
LINE;
    pg_query($db, $sql);
    $sql=<<<LINE
    SELECT MAX(POST_ID)
    FROM POSTS;
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);
    $postId=$row[0];
    $sql=<<<LINE
    INSERT INTO GROUP_POSTS(GROUP_ID,POST_ID) VALUES ($groupId,$postId);
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: group.php');
}
/**
 * promote a person to group admin
 * relaod the page
 * @param $groupId
 * @param $personId
 * @param $db
 */
function promotePerson($groupId,$personId,$db){
    $sql=<<<LINE
    UPDATE GROUP_MEMBERS
    SET IS_ADMIN=true 
    WHERE GROUP_ID=$groupId AND PERSON_ID=$personId;
LINE;
    unset($_POST[$personId."Promote"]);
    pg_query($db, $sql);
    pg_close($db);
    header('Location: group.php');
}
/**
 * kick out a person from group
 * reload the page
 * @param $groupId
 * @param $personId
 * @param $db
 */
function kickOutPerson($groupId,$personId,$db){
    $sql=<<<LINE
    DELETE FROM GROUP_MEMBERS
    WHERE GROUP_ID=$groupId AND PERSON_ID=$personId;
LINE;
    pg_query($db, $sql);
    pg_close($db);

}
/**
 * function for updating person's information
 * after this function page will be reloaded
 * @param $db
 * @param $groupId
 */
function updateGroupInfo($db,$groupId){
    $sql=<<<LINE
    SELECT GROUP_PHOTO
    FROM GROUPS 
    WHERE GROUP_ID=$groupId;
LINE;

    $ret = pg_query($db,$sql);

    $postPhoto = pg_fetch_row($ret);
    unlink($postPhoto[0]."");

    ///getting changed data from form
    $name=$_POST["groupName"];
    $imagename="img_avatar2.png";
    if (isset($_FILES['imageupload'])) {
        $imagename = $_FILES['imageupload']['name'];
        $imagetemp = $_FILES['imageupload']['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);


    ////////////////////////////////////////////
    list($imageLongName, $fileType)=explode(".",$imagename);

    $imagename="images/".$row[0].".".$fileType;
///////////////////////////////////////////////////////////////////////////////////////





    ///uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    /// update information in database.
    $sql=<<<LINE
    UPDATE GROUPS
    SET GROUP_NAME='$name',GROUP_PHOTO='$imagename'
    WHERE GROUP_ID=$groupId;
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: group.php');
}
function deleteAlbumPhoto($image,$db){
    unlink($image);
    $sql=<<<LINE
    DELETE FROM ALBUM_PHOTOS
    WHERE ALBUM_PHOTO='$image';
LINE;
    pg_query($db, $sql);
    pg_close($db);

}
function addAlbumPhoto($userId,$db,$image,$flag){
    ///getting new photo data from form
    $imagename="img.png";
    if (isset($_FILES[$image])) {
        $imagename = $_FILES[$image]['name'];
        $imagetemp = $_FILES[$image]['tmp_name'];
    }
    $sql=<<<LINE
    SELECT NEXTVAL('PHOTO_SEQ');
LINE;
    $ret=pg_query($db, $sql);
    $row=pg_fetch_row($ret);


    list($imageLongName, $fileType)=explode(".",$imagename);
    $imagename="albumImages/".$row[0].".".$fileType;


    ///uploading image to server.
    move_uploaded_file($imagetemp,$imagename );
    /// getting album id from database
    if($flag){
        $sql=<<<LINE
    SELECT ALBUM_ID
    FROM ALBUMS
    WHERE PERSON_ID=$userId AND IS_PRIVATE IS TRUE;
LINE;
    }
    else{
        $sql=<<<LINE
    SELECT ALBUM_ID
    FROM ALBUMS
    WHERE PERSON_ID=$userId AND IS_PRIVATE IS FALSE;
LINE;
    }
    $ret=pg_query($db, $sql);
    $albumId=pg_fetch_row($ret);
    $albumId=$albumId[0];
    ///inserting album image in database
    $sql=<<<LINE
    INSERT INTO ALBUM_PHOTOS(ALBUM_ID,ALBUM_PHOTO) VALUES ($albumId,'$imagename');
LINE;
    pg_query($db, $sql);
    pg_close($db);
    header('Location: person.php');
}
function getImageName($image){
    $returnString=substr($image,12);
    $t=strpos($returnString,".");
    return substr($returnString,0,strlen($returnString)-$t-1);
}

function message($string)
{
    echo<<<LINE
            <script>
                console.log('$string');
            </script>
LINE;
}