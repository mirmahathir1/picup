CREATE OR REPLACE FUNCTION is_special (criteria integer,personid integer) 
RETURNS boolean AS $$
declare
	postCount integer;
	likeCount integer;
	commentCount integer;
begin
	select count(post_id) into postCount
	from posts
	where person_id=personid;
	
	select count(like_id) into likeCount
	from likes l join posts p
	on(l.post_id=p.post_id)
	where p.person_id=personid;
	
	select count(comment_id) into commentCount
	from comments l join posts p
	on(l.post_id=p.post_id)
	where p.person_id=personid;

	IF ((likeCount + commentCount*2)/postCount)>criteria THEN
	  	return true;
	else 
		return false;
	END IF;
exception 
    when others then
	return false;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION INSERT_TAGS(tag_text text) 
RETURNS text AS $$
declare
	str text;
	postid integer;
	counter integer;
begin
	select max(post_id) into postid from posts;
	tag_text=ltrim(tag_text);
	tag_text=rtrim(tag_text);
	str=split_part(tag_text, ' ', 1);
	counter:=array_length(regexp_split_to_array(tag_text, ' '), 1);
	for i in 1..counter
	loop
	str=split_part(tag_text, ' ' , i);
	if str !='' then
		insert into tags(POST_ID,TAG) values(postid,str);
	end if;
	end loop ; 
	return 'success';
exception 
    when others then
	return tag_text;
END;
$$ language plpgsql;
