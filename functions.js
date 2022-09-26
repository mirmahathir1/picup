function downButtonAction(id,objectId,pageName) {

    var ajax=new XMLHttpRequest();
    var method="GET";
    var url=pageName+"?"+id+"="+"jnj";

    var asynchronous=true;

    ajax.open(method,url,asynchronous);
    ajax.send();

    document.getElementById(objectId+"").style.display='none';
}

function Like(id,postId,pageName)
{


    if(document.getElementById(id).innerHTML=="<img src=\"coreImages/likeButton.png\" height=\"25\" width=\"35\">")
    {
        document.getElementById(id).innerHTML= "<img src=\"coreImages/likeButton2.png\" height=\"25\" width=\"35\">";
        var text = document.getElementById(postId+"likecomment").innerHTML;
        text = text.split(" ");
        document.getElementById(postId+"likecomment").innerHTML= (parseInt(text[0])+1)+" "+text[1]+" "+text[2]+" "+text[3];
    }
    else
    {
        document.getElementById(id).innerHTML= "<img src=\"coreImages/likeButton.png\" height=\"25\" width=\"35\">";
        var text = document.getElementById(postId+"likecomment").innerHTML;
        text = text.split(" ");
        document.getElementById(postId+"likecomment").innerHTML= (parseInt(text[0])-1)+" "+text[1]+" "+text[2]+" "+text[3];
    }


    var ajax=new XMLHttpRequest();
    var method="GET";
    var url=pageName+"?"+id+"="+"jnj";

    var asynchronous=true;

    ajax.open(method,url,asynchronous);
    ajax.send();

}