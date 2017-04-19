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
    //TODO CRIAR UMA VIEW?!? 
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM post post1
                                JOIN (SELECT post2_1.parentid, COUNT(*) AS COUNT 
                                FROM post post2_1 WHERE post2_1.posttype = 'answer' 
                                GROUP BY post2_1.parentid) post2 ON post1.id = post2.parentid
                            WHERE post1.posttype = 'question' 
                            ORDER BY post1.rating/post2.count DESC LIMIT 10;");
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

function createTopic($title, $content, $sectionId) { 
    global $conn;
    global $userId;
    $stmt = $conn->prepare("INSERT INTO post VALUES (DEFAULT,?, 'question', NULL,?,?,DEFAULT,NULL,0,?)");
    $stmt->execute(array($userId, $title, $content, $sectionId));
    return $stmt->errorCode();
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
    
    $stmt = $conn->prepare("SELECT * FROM post 
                            WHERE to_tsvector('portuguese', title) @@ to_tsquery('portuguese', ?);");
    $stmt->execute(array($title));
    return $stmt->fetchAll();     
}

function getTopicWithContent($content) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM post 
                            WHERE to_tsvector('portuguese', content) @@ to_tsquery('portuguese', ?);");
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


function calculateTimeDiff($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT creationDate FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    $creationDate=$stmt->fetch()["creationDate"];
    $interval = date_diff(date_create(),date_create($creationDate));

    return $interval->format('%a');
}

function getRatingTopic($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT rating FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    $rating=$stmt->fetch()["rating"];
    return $rating;
}

function getTitleTopic($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT title FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    $title=$stmt->fetch()["title"];
    return $title;
}

function getTopicAnswers($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT * FROM post WHERE parentid= ? AND postType= ?");
    $stmt->execute(array($topicId,'answer'));
    return $stmt->fetchAll();
}

function getUserTopic($topicId){
    global $conn;
    $stmt=$conn->prepare("SELECT userid FROM post WHERE id= ?");
    $stmt->execute(array($topicId));
    return $stmt->fetch()["userid"];
}

?>
