<?php
session_start();
include_once("query.php");
include_once("functions.php");
include_once("cards.php");
$hour=$_SESSION["hour"];
$p="Statistics for last ".$hour." hours";
$db=connectToDatabase();
$likes=totalLikes($hour,$db);
$comments=totalComments($hour,$db);
$posts=totalPostsAdded($hour,$db);
echo<<<LINE
<!DOCTYPE html>
<html lang="en" style="height: 100%">
<head>
    <meta charset="UTF-8">
    <title>Statistics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/w3style.css">
</head>
<body style="
  height: 100%;
  margin: 0;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  width: 100%;
">
<div style="padding-left: 25%; padding-right: 25%">
    <div class="w3-panel w3-pale-green w3-bottombar w3-border-green w3-border w3-center w3-card-4" >
        <p align="left"><b>$p</b></p>
    </div>
    <div class="w3-panel w3-pale-green w3-bottombar w3-border-green w3-border w3-center w3-card-4" >
        <p align="left"><b>$likes</b></p>
    </div>
    <div class="w3-panel w3-pale-green w3-bottombar w3-border-green w3-border w3-center w3-card-4" >
        <p align="left"><b>$comments</b></p>
    </div>
    <div class="w3-panel w3-pale-green w3-bottombar w3-border-green w3-border w3-center w3-card-4" >
        <p align="left"><b>$posts</b></p>
    </div>
</div>
</div>

<div style="padding-left: 25%; padding-right: 25%">
    <div class="w3-panel w3-pale-yellow w3-border w3-border-yellow w3-card-4" >
        <p align="left"><b>Maximum liked posts</b></p>
    </div>
</div>
<div class="w3-container" style="padding-left: 25%; padding-right: 25%">
LINE;
$posts=getMaxLikedPost($hour,$db);
while($post=pg_fetch_row($posts)){
    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
        false,"ashraful","image",-1,null,"home.php",$post[6],false);
    setLikeComment($post[0],$db);
}
echo<<<LINE
</div>
<div style="padding-left: 25%; padding-right: 25%">
    <div class="w3-panel w3-pale-yellow w3-border w3-border-yellow w3-card-4" >
        <p align="left"><b>Maximum commented posts</b></p>
    </div>
</div>
<div class="w3-container" style="padding-left: 25%; padding-right: 25%">
LINE;
$posts=getMaxCommentedPost($hour,$db);
while($post=pg_fetch_row($posts)){
    echo postCard($post[0],$post[1],$post[2],$post[3],$post[4],$post[5],
        false,"ashraful","image",-1,null,"home.php",$post[6],false);
    setLikeComment($post[0],$db);
}
echo<<<LINE
</div>
LINE;
echo<<<LINE
<div style="padding-left: 25%; padding-right: 25%">
    <div class="w3-panel w3-pale-yellow w3-border w3-border-yellow w3-card-4">
        <p align="left"><b>Tags and their appearences</b></p>
    </div>
</div>
<div class="w3-container" style="padding-left: 25%; padding-right: 25%">
<br>
    <table class="w3-table-all w3-hoverable w3-xlarge w3-card-4">
        <tr class="w3-blue-grey">
            <th>Tag</th>
            <th>Count</th>
        </tr>
LINE;
$tags=getTagsCount($db);
while($tag=pg_fetch_row($tags)){
    echo<<<LINE
        <tr>
            <td>$tag[0]</td>
            <td>$tag[1]</td>
        </tr>
LINE;
}
echo<<<LINE
    </table>
    <br>
    <br>
    <br>
    <br>
</div>
</body>
</html>
LINE;
?>