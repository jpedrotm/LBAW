<?php
function getHotTopics() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE post.posttype = 'question'
                            ORDER BY post.rating
                            DESC LIMIT 10;");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMostRecentTopics() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE post.posttype = 'question'
                            ORDER BY post.creationdate
                            DESC LIMIT 10;");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getFeaturedTopics() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post post1
                            LEFT JOIN (SELECT post2_1.parentid, COUNT(*) AS COUNT
                            FROM post post2_1 WHERE post2_1.posttype = 'answer'
                            GROUP BY post2_1.parentid) post2 ON post1.id = post2.parentid
                            WHERE post1.posttype = 'question'
                            ORDER BY  post1.visualizations DESC LIMIT 10;");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTopicContent($topicId) {
    global $conn;
    $stmt = $conn->prepare("SELECT content FROM post
                            WHERE post.id = ? OR post.parentid = ?;");
    $stmt->execute(array($topicId, $topicId));
    return $stmt->fetch()["content"];
}

function createTopic($userId, $title, $content, $sectionId, $tags) {
    global $conn;

    $conn->beginTransaction();

    $stmt = $conn->prepare("BEGIN;
							SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
							INSERT INTO post VALUES (DEFAULT, ?, 'question', NULL, ?, ?, DEFAULT, NULL, 0, ?) RETURNING id;

							INSERT INTO tag(name)
								SELECT newtag.name FROM tag
								RIGHT JOIN (SELECT name FROM unnest(?::text[]) name) newtag
								ON (tag.name = newtag.name)
								WHERE id IS NULL;

							INSERT INTO feature(postid, tagid)
								SELECT (SELECT currval(pg_get_serial_sequence('post', 'id'))), id FROM tag
								WHERE name = ANY (?::text[]);
								COMMIT;");
    $stmt->execute(array($userId, $title, $content, $sectionId, to_pg_array($tags),to_pg_array($tags)));
    $topicId = $conn->lastInsertId('post_id_seq');

    $conn->commit();

    return $topicId;
}

//TODO VER SE TA na DOC
function getFeaturedTagsTopic($topicId) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM feature
                            WHERE postid = ?");
    $stmt->execute(array($topicId));
    return $stmt->fetchAll();
}
function getTopicWithTitle($title) {
    global $conn;

    $pieces = explode(" ", $title);
    $query = $pieces[0];
    for ($i = 1; $i < count($pieces); $i++) {
          $query = $query . " & ". $pieces[$i];
    }
    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE to_tsvector('portuguese', title) @@ to_tsquery('portuguese', ?);");
    $stmt->execute(array($query));
    return $stmt->fetchAll();
}

function getTopicWithContent($content) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE to_tsvector('portuguese', content) @@ plainto_tsquery('portuguese', ?);");
    $stmt->execute(array($content));
    return $stmt->fetchAll();
}


// ADD TO DOC
function getTopicWithTag($tagId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post
                            WHERE post.id IN
                                    (SELECT postid FROM feature
                                     WHERE tagid = ?)
                                  AND parentid IS NULL;");
    $stmt->execute(array($tagId));
    return $stmt->fetchAll();
}

function getTopicInfo($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT * FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    return $stmt->fetchAll();
}

function calculateTimeDiff($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT creationDate FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    $creationDate=$stmt->fetch()["creationDate"];
    $interval = date_diff(date_create(),date_create($creationDate));

    return $interval->format('%a');
}

function getTopicAnswers($topicId) {
    global $conn;
    $stmt=$conn->prepare("SELECT * FROM post WHERE parentid = ? AND postType= ? ORDER BY creationdate");
    $stmt->execute(array($topicId,'answer'));
    return $stmt->fetchAll();
}

function hasAlreadyMade($userId,$topicId,$type) {
    global $conn;
    $stmt=$conn->prepare("SELECT * FROM vote WHERE postid= ? AND userid=? AND voteType=?");
    $stmt->execute(array($topicId,$userId,$type));
    return $stmt->fetch() > 0;
}

function getTopicVoteType($userId,$topicId){
    global $conn;

    $stmt=$conn->prepare("SELECT votetype FROM vote WHERE postid= ? AND userid=?");
    $stmt->execute(array($topicId,$userId));
    return $stmt->fetchAll();
}

function getAcceptVoteByTopic($topicId){
    global $conn;

    $stmt=$conn->prepare("SELECT votetype,userid FROM vote WHERE postid= ? AND votetype=?");
    $stmt->execute(array($topicId,"accept"));
    return $stmt->fetchAll();
}

function getUserAcceptTopic($topicId){
    $vote=getAcceptVoteByTopic($topicId);
    if(sizeof($vote)>0){
        return $vote[0]["userid"];
    }else{
        return "";
    }
}

function getUserIdByTopic($topicId){
    global $conn;

    $stmt=$conn->prepare("SELECT userid FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    return $stmt->fetchAll()[0]["userid"];
}

function getCreationDateTopic($topicId){
    global $conn;

    $stmt=$conn->prepare("SELECT creationdate FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    return $stmt->fetchAll()[0]["creationdate"];
}

function hasAlreadyAnAccept($topicId){
    return sizeof(getAcceptVoteByTopic($topicId))>0;
}

function insertNewVote($userId, $type, $topicId){
    global $conn;

    $stmt = $conn->prepare("INSERT INTO vote (userid, postid, voteType) VALUES (?,?,?)");
    $stmt->execute(array($userId,$topicId,$type));
    return $stmt->errorCode();
}

function deleteVote($userId,$topicId){
    global $conn;

    $stmt=$conn->prepare("DELETE FROM vote WHERE userid=? AND postid=?");
    $stmt->execute(array($userId,$topicId));
    return $stmt->errorCode();
}

function deleteAcceptVoteByTopic($topicId){
    global $conn;

    $stmt=$conn->prepare("DELETE FROM vote WHERE postid=? AND votetype=?");
    $stmt->execute(array($topicId,"accept"));
    return $stmt->errorCode();
}


function getTopicsByUser($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post WHERE userid = ? AND postType = ?;");
    $stmt->execute(array($userId, "question"));
    return $stmt->fetchAll();
}


function countTopicAnswers($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT COUNT(*) FROM post WHERE parentid= ? AND postType= ?");
    $stmt->execute(array($topicId,'answer'));
    return $stmt->fetch();
}


function countTopics() {
    global $conn;
    $stmt=$conn->prepare("SELECT COUNT(*) FROM post WHERE postType= ?");
    $stmt->execute(array('question'));
    return $stmt->fetch()['count'];
}

function getAllTopicComments($topicId) {
    global $conn;

    $stmt=$conn->prepare("SELECT comment.postid, comment.id, comment.content, comment.creationdate, comment.userid, account.name AS publisher FROM comment
                            JOIN post ON (comment.postid = post.id)
                            JOIN account ON (comment.userid = account.id)
                            WHERE (parentid = ? OR post.id = ?)
                            ORDER BY comment.creationdate DESC;");

    $stmt->execute(array($topicId, $topicId));
    return $stmt->fetchAll(PDO::FETCH_GROUP);
}


function timelineGetTopics($userId) {
    global $conn;
    $stmt=$conn->prepare("SELECT 'question' AS tablename, id AS postid, title, creationdate, rating FROM post WHERE (userid = ? AND posttype = 'question')");
    $stmt->execute(array($userId));
    return $stmt->fetchAll();
}

function timelineGetAnswers($userId) {
    global $conn;
    $stmt=$conn->prepare("SELECT 'answer' AS tablename, post1.id AS postid, post1.title AS posttitle, post2.creationdate, post1.rating FROM post post1
                            JOIN post post2 ON (post1.id = post2.parentid)
                            WHERE (post2.userid = ? AND post2.posttype = 'answer')");
    $stmt->execute(array($userId));
    return $stmt->fetchAll();
}

function timelineGetComments($userId) {
    global $conn;
    $stmt=$conn->prepare("SELECT 'comment' AS tablename, comment.postid, post.id AS postid, comment.creationdate, post.title, post.posttype FROM comment
                            JOIN post ON (comment.postid = post.id)
                            WHERE comment.userid = ? AND post.posttype = 'question'");
    $stmt->execute(array($userId));
    return $stmt->fetchAll();
}

function timelineGetCommentsAnswers($userId) {
    global $conn;
    $stmt=$conn->prepare("SELECT 'comment' AS tablename, comment.postid, post2.id AS postid, comment.creationdate, post2.title, post2.posttype FROM comment
                            JOIN post AS post1 ON (comment.postid = post1.id)
                            JOIN post AS post2 ON (post1.parentid = post2.id)
                            WHERE comment.userid = ?");
    $stmt->execute(array($userId));
    return $stmt->fetchAll();
}


function updateVisualizations($topicId) {
    global $conn;
    $stmt=$conn->prepare("UPDATE post
                            SET visualizations = visualizations + 1
                            WHERE id = ?;");

    $stmt->execute(array($topicId));
}


function createReply($postid, $userid, $content) {
    global $conn;
    $stmt=$conn->prepare("INSERT INTO comment(postid, userid, content) VALUES (?, ?, ?);");
    $stmt->execute(array($postid, $userid, $content));
}


function createComment($parentid, $userid, $content) {
    global $conn;
    $stmt=$conn->prepare("INSERT INTO post(parentid, userid, posttype, content) VALUES (?, ?, 'answer', ?) RETURNING *;");
    $stmt->execute(array($parentid, $userid, $content));
    return $stmt->fetch();
}

function deleteReply($replyId) {
    global $conn;
    $stmt=$conn->prepare("DELETE FROM comment WHERE id = ?;");
    $stmt->execute(array($replyId));
    return $stmt->errorCode();
}

function deletePost($postId) {
    global $conn;
    $stmt=$conn->prepare("DELETE FROM post WHERE id = ?;");
    $stmt->execute(array($postId));
    return $stmt->errorCode();
}

function updatePostContent($postid, $content) {
    global $conn;
    $stmt=$conn->prepare("UPDATE post SET content = ? WHERE id = ?;");
    $stmt->execute(array($content, $postid));
}
?>
