<?php


function getClient($user)
{
    $OAUTH2_CLIENT_ID = env("OAUTH2_CLIENT_ID");
    $OAUTH2_CLIENT_SECRET = env("OAUTH2_CLIENT_SECRET");


    $client = new \Google_Client();
    
    $client->setApplicationName('API Samples');
    
    $client->setScopes(['https://www.googleapis.com/auth/youtube.force-ssl', 'https://www.googleapis.com/auth/youtube']);
    $client->setClientId($OAUTH2_CLIENT_ID);
    $client->setClientSecret($OAUTH2_CLIENT_SECRET);
    $client->setAccessType('offline');
    $client->setAccessToken($user->access_token);

  // Refresh the token if it's expired.

    if ($client->isAccessTokenExpired()) {
        $client->refreshToken($user->refresh_token);
        $user->access_token = json_encode($client->getAccessToken());
        $user->save();
    }

    return $client;
}


function isSubscriber($service,  $channelId){
    
    $response = $service->subscriptions->listSubscriptions(
        'snippet,contentDetails',
        array_filter(array('forChannelId' => $channelId, 'mine' => true))
    );

    return count($response->items) > 0;
}


function videosRate($service, $id, $rating, $params)
{
    $params = array_filter($params);
    
    $response = $service->videos->rate(
        $id,
        $rating,
        $params
    );

    return $response;

}

function videosGetRating($service, $id)
{
    $params = array_filter(array('onBehalfOfContentOwner' => ''));
  
    $response = $service->videos->getRating(
        $id,
        $params
    );


    return $response;
}

function has_rate($service, $id){

   $response =  videosGetRating($service,$id);

   return strcmp($response->items[0]->rating, "none") == 0 ? false : true;
}


function videoComments($youtube, $video_id){
    
    
    $videoCommentThreads = $youtube->commentThreads->listCommentThreads('snippet', array(
        'videoId' => $video_id,
        'textFormat' => 'plainText',
    ));

    $parentId = $videoCommentThreads[0]['id'];

}

function addVideoComment($youtube, $comment_text){
    # Create a comment snippet with text.
    $commentSnippet = new \Google_Service_YouTube_CommentSnippet();
    $commentSnippet->setTextOriginal($comment_text);
    // $commentSnippet->setParentId($parentId);

    # Create a comment with snippet.
    $comment = new Google_Service_YouTube_Comment();
    $comment->setSnippet($commentSnippet);

    # Call the YouTube Data API's comments.insert method to reply to a comment.
    # (If the intention is to create a new top-level comment, commentThreads.insert
    # method should be used instead.)
    $commentInsertResponse = $youtube->comments->insert('snippet', $comment);

}


function commentThreadsInsert($service, $properties, $part, $params) {


    # Insert channel comment by omitting videoId.
    # Create a comment snippet with text.
    $commentSnippet = new \Google_Service_YouTube_CommentSnippet();
    $commentSnippet->setTextOriginal("cool");

    # Create a top-level comment with snippet.
    $topLevelComment = new \Google_Service_YouTube_Comment();
    $topLevelComment->setSnippet($commentSnippet);

    # Create a comment thread snippet with channelId and top-level comment.
    $commentThreadSnippet = new \Google_Service_YouTube_CommentThreadSnippet();
    $commentThreadSnippet->setChannelId("UCPDBP981aAzZj11jYRta8mw");
    $commentThreadSnippet->setTopLevelComment($topLevelComment);

    # Create a comment thread with snippet.
    $commentThread = new \Google_Service_YouTube_CommentThread();
    $commentThread->setSnippet($commentThreadSnippet);


    # Insert video comment
    $commentThreadSnippet->setVideoId("u8IlaoLlpQA");
    // Call the YouTube Data API's commentThreads.insert method to create a comment.
    $videoCommentInsertResponse = $service->commentThreads->insert('snippet', $commentThread);

    dd($videoCommentInsertResponse);

    $params = array_filter($params);
    $propertyObject = createResource($properties); // See full sample for function
    $resource = new Google_Service_YouTube_CommentThread($propertyObject);
    $response = $service->commentThreads->insert($part, $resource, $params);
    dd($response);
    print_r($response);
}

function createResource($properties)
{
    $resource = array();
    foreach ($properties as $prop => $value) {
        if ($value) {
            addPropertyToResource($resource, $prop, $value);
        }
    }
    return $resource;
}

function addPropertyToResource(&$ref, $property, $value)
{
    $keys = explode(".", $property);
    $is_array = false;
    foreach ($keys as $key) {
        // For properties that have array values, convert a name like
        // "snippet.tags[]" to snippet.tags, and set a flag to handle
        // the value as an array.
        if (substr($key, -2) == "[]") {
            $key = substr($key, 0, -2);
            $is_array = true;
        }
        $ref = &$ref[$key];
    }

    // Set the property value. Make sure array values are handled properly.
    if ($is_array && $value) {
        $ref = $value;
        $ref = explode(",", $value);
    } elseif ($is_array) {
        $ref = array();
    } else {
        $ref = $value;
    }
}
