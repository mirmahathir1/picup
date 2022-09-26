<?php
/// simple query

function getTagsCount($db){
    $sql=<<<LINE
    SELECT TAG,COUNT( DISTINCT POST_ID)
    FROM TAGS
    GROUP BY TAG
    ORDER BY COUNT(POST_ID) DESC;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}

function isSpecial($personId,$db){
    $sql=<<<LINE
        SELECT is_special(5,$personId);
LINE;
    $ret=pg_query($db,$sql);
    $row=pg_fetch_row($ret);
    return $row[0];
}

function isLiked($postId,$personId,$db)
{
    $sql=<<<LINE
        SELECT count(*)
        FROM LIKES
        WHERE PERSON_ID = $personId and post_id = $postId;
LINE;
    $ret=pg_query($db,$sql);

    $row=pg_fetch_row($ret);

    return $row[0]==1;
}

function totalLikes($hour,$db)
{
    $sql=<<<LINE
        SELECT count(*)
        FROM LIKES
        WHERE date_part('hour',CURRENT_TIMESTAMP(0))-
                        date_part('hour',DATE_TIME)<$hour;
LINE;
    $ret=pg_query($db,$sql);
    $row=pg_fetch_row($ret);
    return "Total likes in last ".$hour." hours is ".$row[0];
}
function totalComments($hour,$db)
{
    $sql=<<<LINE
        SELECT count(*)
        FROM COMMENTS
        WHERE date_part('hour',CURRENT_TIMESTAMP(0))-
                        date_part('hour',DATE_TIME)<$hour;
LINE;
    $ret=pg_query($db,$sql);
    $row=pg_fetch_row($ret);
    return "Total comments in last ".$hour." hours is ".$row[0];
}
function totalPostsAdded($hour,$db)
{
    $sql=<<<LINE
        SELECT count(*)
        FROM POSTS
        WHERE date_part('hour',CURRENT_TIMESTAMP(0))-
                        date_part('hour',DATE_TIME)<$hour;
LINE;
    $ret=pg_query($db,$sql);
    $row=pg_fetch_row($ret);
    return "Total posts in last ".$hour." hours is ".$row[0];
}
/**
 * getting searched result for persons
 * @param $serchingText
 * @param $db
 * @return resource
 */
function getSearchedPerson($serchingText,$db){
    $text="%".$serchingText."%";
    $sql=<<<LINE
    SELECT PERSON_ID,NAME,PROFILE_PHOTO
    FROM PERSONS 
    WHERE LOWER(NAME) LIKE LOWER('$text');
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}

function getGroupInfo($groupId,$db)
{
    $sql=<<<LINE
    SELECT GROUP_NAME,GROUP_PHOTO
    FROM GROUPS
    WHERE GROUP_ID = $groupId;
LINE;
    $ret = pg_query($db,$sql);
    return pg_fetch_row($ret);

}
/**
 * getting search result for group
 * @param $serchingText
 * @param $db
 * @return resource
 */
function getSearchedGroup($serchingText,$db){
    $text="%".$serchingText."%";
    $sql=<<<LINE
    SELECT GROUPS.GROUP_ID,GROUP_NAME,GROUP_PHOTO
    FROM GROUPS
    WHERE LOWER(GROUP_NAME) LIKE LOWER('$text');
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
/**
 * for showing user friends
 * @param $userId
 * @param $db
 * @return resource
 */
function getFriendsId($userId,$db){
    $sql=<<<LINE
    SELECT FRIEND_ID
    FROM FRIENDS
    WHERE PERSON_ID=$userId;
LINE;
    return pg_query($db, $sql);
}
/**
 * for showing user pending friends
 * @param $userId
 * @param $db
 * @return resource
 */
function getPendingFriendsId($userId,$db){
    $sql=<<<LINE
    SELECT FRIEND_ID
    FROM FRIENDS_PENDING
    WHERE PERSON_ID=$userId;
LINE;
    return pg_query($db, $sql);
}
/**
 * for getting group members
 * @param $groupId
 * @param $db
 * @param $adminFlag
 * @return resource
 */
function getGroupMemebers($groupId,$db,$adminFlag){
    if($adminFlag){
        $sql=<<<LINE
            SELECT PERSON_ID
            FROM GROUP_MEMBERS
            WHERE GROUP_ID=$groupId AND IS_ADMIN IS TRUE ;
LINE;
    }
    else{
        $sql=<<<LINE
            SELECT PERSON_ID
            FROM GROUP_MEMBERS
            WHERE GROUP_ID=$groupId AND IS_ADMIN IS FALSE ;
LINE;
    }

    $ret = pg_query($db, $sql);
    return $ret;
}

/**
 * getting $postId for a comment card
 * @param $commentId
 * @param $db
 * @return mixed
 */
function getCommentPostId($commentId,$db){
    $sql=<<<LINE
    SELECT POST_ID
    FROM COMMENTS
    WHERE COMMENT_ID=$commentId;
LINE;
    $ret=pg_query($db, $sql);
    $postId=pg_fetch_row($ret);
    return $postId[0];
}
/**
 * Getting user id and info
 * @param $db
 * @return array
 */
function getUserInfo2($personId,$db){
    $sql=<<<LINE
    SELECT USER_ID,INFO
    FROM USERS
    WHERE USER_ID='$personId';
LINE;
    $ret=pg_query($db, $sql);
    return pg_fetch_row($ret);
}
/**
 * getting total amount of like in a post
 * @param $postId
 * @param $db
 * @return mixed
 */
function getLikeAmount($postId,$db){
    $sql=<<<LINE
    SELECT COUNT(*)
    FROM LIKES
    WHERE POST_ID=$postId;
LINE;
    $ret=pg_query($db, $sql);
    $like=pg_fetch_row($ret);
    return $like[0];
}
/**
 * getting all comments for home page
 * @param $db
 * @return resource
 */
function getAllCommentId($db){
    $sql=<<<LINE
    SELECT COMMENT_ID
    FROM COMMENTS;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
/**
 * getting person information ( photo and name of a person)
 * @param $personId
 * @param $db
 * @return array
 */
function getPersonInfo($personId,$db){
    $sql=<<<LINE
    SELECT NAME,PROFILE_PHOTO
    FROM PERSONS 
    WHERE PERSON_ID=$personId;
LINE;
    $ret = pg_query($db, $sql);
    return pg_fetch_row($ret);
}
/**
 * getting notification for persons.
 * @param $personId
 * @param int $db
 * @return resource
 */
function getNotification($personId,$db=0){
    $sql=<<<LINE
    SELECT DESCRIPTION
    FROM NOTIFICATIONS
    WHERE PERSON_ID=$personId
    ORDER BY DATE_TIME DESC;
LINE;
    return pg_query($db, $sql);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///complex query
/**
 * getting all post for home page except group post
 * @param $db
 * @return resource
 */
function getAllPost($db){
    $sql=<<<LINE
    SELECT POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,POSTS.PERSON_ID
    FROM POSTS JOIN PERSONS
    ON(POSTS.PERSON_ID=PERSONS.PERSON_ID)
    EXCEPT
    (SELECT POSTS.POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,PERSONS.PERSON_ID
    FROM POSTS JOIN GROUP_POSTS
    ON(POSTS.POST_ID=GROUP_POSTS.POST_ID)
    JOIN PERSONS
    ON(PERSONS.PERSON_ID=POSTS.PERSON_ID))
    ORDER BY DATE_TIME DESC;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
/**
 * getting all group post for home page
 * @param $groupId
 * @param $db
 * @return resource
 */
function getAllGroupPost($groupId,$db){
    $sql=<<<LINE
    SELECT POSTS.POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,PERSONS.PERSON_ID
    FROM POSTS JOIN GROUP_POSTS
    ON(POSTS.POST_ID=GROUP_POSTS.POST_ID)
    JOIN PERSONS
    ON(PERSONS.PERSON_ID=POSTS.PERSON_ID)
    WHERE GROUP_POSTS.GROUP_ID=$groupId
    ORDER BY DATE_TIME DESC;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
/**
 * getting all comment for a specific post.
 * @param $postId
 * @param $db
 * @return resource
 */
function getCommentsForAPost($postId,$db){
    $sql=<<<LINE
    SELECT COMMENT_ID,NAME,PROFILE_PHOTO,DATE_TIME,DESCRIPTION,COMMENTS.PERSON_ID
    FROM COMMENTS JOIN PERSONS
    ON(COMMENTS.PERSON_ID=PERSONS.PERSON_ID)
    WHERE POST_ID=$postId
    ORDER BY DATE_TIME ASC;
LINE;
    return pg_query($db, $sql);
}

/**
 * getting all user post for person page
 * @param $userId
 * @param $db
 * @return resource
 */
function getUserPost($userId,$db){
    $sql=<<<LINE
    SELECT POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,POSTS.PERSON_ID
    FROM POSTS JOIN PERSONS
    ON(POSTS.PERSON_ID=PERSONS.PERSON_ID)
    WHERE POSTS.PERSON_ID=$userId
    ORDER BY DATE_TIME DESC;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}


/**
 * getting users group
 * @param $userId
 * @param $db
 * @return resource
 */
function getGroupsForUser($userId,$db){
    $sql=<<<LINE
    SELECT GROUPS.GROUP_ID,GROUP_NAME,GROUP_PHOTO,GROUP_MEMBERS.IS_ADMIN
    FROM GROUPS JOIN GROUP_MEMBERS
    ON(GROUPS.GROUP_ID=GROUP_MEMBERS.GROUP_ID)
    WHERE GROUP_MEMBERS.PERSON_ID=$userId;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}

function getSearchedPost($serchingText,$db)
{
    $text="%".$serchingText."%";
    $sql=<<<LINE
    SELECT POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,POSTS.PERSON_ID
    FROM POSTS JOIN PERSONS
    ON(POSTS.PERSON_ID=PERSONS.PERSON_ID)
    WHERE POSTS.POST_ID=ANY(
        SELECT DISTINCT TAGS.POST_ID
        FROM TAGS
        WHERE LOWER(TAGS.TAG) LIKE LOWER('$text')
    ) 
    EXCEPT
    (SELECT POSTS.POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,PERSONS.PERSON_ID
    FROM POSTS JOIN GROUP_POSTS
    ON(POSTS.POST_ID=GROUP_POSTS.POST_ID)
    JOIN PERSONS
    ON(PERSONS.PERSON_ID=POSTS.PERSON_ID)
    WHERE POSTS.POST_ID=ANY(
        SELECT DISTINCT TAGS.POST_ID
        FROM TAGS
        WHERE LOWER(TAGS.TAG) LIKE LOWER('$text')
    ) )
    ORDER BY DATE_TIME DESC;
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
function getAlbumPic($personId,$db,$isPrivate)
{
    if($isPrivate){
        $sql=<<<LINE
    SELECT ALBUM_PHOTO
    FROM ALBUM_PHOTOS
    WHERE ALBUM_ID = (
      SELECT ALBUM_ID
      FROM ALBUMS
      WHERE PERSON_ID=$personId AND IS_PRIVATE IS TRUE
    );
LINE;
    }
    else {
        $sql = <<<LINE
    SELECT ALBUM_PHOTO,PERSON_ID
    FROM ALBUM_PHOTOS JOIN ALBUMS ON(ALBUM_PHOTOS.ALBUM_ID=ALBUMS.ALBUM_ID)
    WHERE IS_PRIVATE IS FALSE ;
LINE;
    }
    $ret = pg_query($db,$sql);
    return $ret;
}
function getMaxLikedPost($hour,$db){
    $sql=<<<LINE
    SELECT POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,POSTS.PERSON_ID
    FROM POSTS JOIN PERSONS
    ON(POSTS.PERSON_ID=PERSONS.PERSON_ID)
    WHERE POST_ID = ANY (
        select post_id
        from (select post_id,count(like_id) as likeCount
            from likes
            where date_part('hour',current_timestamp(0))-date_part('hour',date_time)<$hour
            group by post_id) l
        where likeCount= (select max(likeCount)
                         from (select post_id,count(like_id) as likeCount
                                from likes
                                where date_part('hour',current_timestamp(0))-date_part('hour',date_time)<$hour
                                group by post_id) l
                         )
    );
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}

function getMaxCommentedPost($hour,$db){
    $sql=<<<LINE
    SELECT POST_ID,NAME,PROFILE_PHOTO,CAPTION,POST_PHOTO,DATE_TIME,POSTS.PERSON_ID
    FROM POSTS JOIN PERSONS
    ON(POSTS.PERSON_ID=PERSONS.PERSON_ID)
    WHERE POST_ID = ANY (
        select post_id
        from (select post_id,count(comment_id) as likeCount
            from comments
            where date_part('hour',current_timestamp(0))-date_part('hour',date_time)<$hour
            group by post_id) l
        where likeCount= (select max(likeCount)
                         from (select post_id,count(comment_id) as likeCount
                                from comments
                                where date_part('hour',current_timestamp(0))-date_part('hour',date_time)<$hour
                                group by post_id) l
                         )
    );
LINE;
    $ret = pg_query($db, $sql);
    return $ret;
}
