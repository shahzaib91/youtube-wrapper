<?php

// Namespace
namespace Mmg\Youtube;

// Packages
use Exception;

/**
 * This is just a wrapper class which will help you
 * to consume Youtube API.
 * 
 * @author Syed Shahzaib <shahzaib.2377@gmail.com>
 */
class Wrapper {
    
    private $googleClient = null;
    private $isClientPrepared = false;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->googleClient = new \Google\Client();
    }
    
    /**
     * Function to config client via wrapper class
     *
     * @param String $name Set application name
     * @param Array $scopes Set scope(s) for api to consume.
     * @param String $configJsonPath Set path of json downloaded from google console
     * @param String $apiKey Set api key obtained from console
     * @throws Exception If $scopes is not an array
     * @return void
     */
    public function prepare($name, $scopes, $configJsonPath, $apiKey) {
        
        // If not in desired format 
        if(!is_array($scopes)) {
            throw new Exception("setScopesException: Expecting (Array) provided (".gettype($scopes).")");
        }
        
        // Set
        $this->googleClient->setApplicationName($name);
        $this->googleClient->setScopes($scopes);
        $this->googleClient->setAuthConfig($configJsonPath);
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setDeveloperKey($apiKey);
        $this->isClientPrepared = true;
    }
    
    /**
     * Function to get auth url for authentication
     *
     * @throws Exception If wrapper is not prepared
     * @return String
     */
    public function getAuthUrl() {
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getAuthUrl: Wrapper is not configured properly");
        }
        
        // Return url
        return $this->googleClient->createAuthUrl();
    }
    
    /**
     * Function to fetch access token and set it to client
     *
     * @param String $code Pass code or refresh token
     * @throws Exception When code is empty
     * @return Array Returned by google client
     */
    public function getAccessToken($code) {
        
        // When empty code
        if(empty($code)) {
            throw new Exception("getAccessToken: Can not exchange empty code with the access token");
        }
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getAccessToken: Wrapper is not configured properly");
        }
        
        // Get/Set
        $accessTokenArray = $this->googleClient->fetchAccessTokenWithAuthCode($code);
        $this->googleClient->setAccessToken($accessTokenArray);
        
        // Return access token
        return $accessTokenArray;
    }
    
    /**
     * Function sets access token to google client object
     *
     * @param String $accessTokenJson Pass received access token response
     * @throws Exception When access token json is empty or not a valid object
     * @return Object Boolean false or StdClass object
     */
    public function setAccessToken($accessTokenJson) {
        
        // When empty json
        if(empty($accessTokenJson)) {
            throw new Exception("refreshAccessToken: Can not exchange empty refresh token with the access token");
        }
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("refreshAccessToken: Wrapper is not configured properly");
        }
        
        // Parse current access token json
        $accessTokenJson = $this->parseAccessTokenJson($accessTokenJson);
        
        // If not a valid token
        if(empty($accessTokenJson->access_token) || empty($accessTokenJson->created) || empty($accessTokenJson->expires_in)){
            throw new Exception("refreshAccessToken: Access token can not be empty");
        }
        
        // Calculate token life
        $tokenLife = $accessTokenJson->created + $accessTokenJson->expires_in;
        
        ### DEBUGGING
        // echo time() . '  |||  '. $tokenLife.'<br/><br/>';
        // echo $tokenLife - time().'<br/><br/>';
        
        // If expired obtain and set new token
        if(($tokenLife - time()) <= 0){
            $accessTokenJson = (object) $this->googleClient->fetchAccessTokenWithRefreshToken($accessTokenJson->refresh_token);
        }
        
        // Set the client token
        $this->googleClient->setAccessToken((Array) $accessTokenJson);
        
        // Return object
        return $accessTokenJson;
    }
    
    /**
     * Helper function to abstractedly perform json parsing
     *
     * @param String $accessTokenJson Pass access token json response
     * @return Bool|Object Boolean false or StdClass object
     */
    private function parseAccessTokenJson($accessTokenJson) {
        
        // When empty json
        if(empty($accessTokenJson)) {
            throw new Exception("parseAccessTokenJson: Can not pass empty access token json");
        }
        
        // Return object
        return json_decode($accessTokenJson);
    }
    
    /**
     * Function to retrieve list of available channels owned by authenticated user
     *
     * @throws Exception Can throw sdk exception
     * @return Array Dataset
     */
    public function getChannelsList() {
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getChannelsList: Wrapper is not configured properly");
        }
        
        // Fetch channels list
        $youtubeService = new \Google\Service\YouTube($this->googleClient);
        $data = $youtubeService->channels->listChannels('snippet,contentDetails,statistics', [ 
            'mine' => true
        ]);
        
        // Convert in desired format
        $finalData = [];
        $index = 0;
        if(isset($data->items) && count($data->items) > 0){
            foreach($data as $channel) {
                $finalData[$index]['name'] = $channel->snippet->localized->title;
                $finalData[$index]['username'] = $channel->snippet->customUrl;
                $finalData[$index]['created_at'] = $channel->snippet->publishedAt;
                $finalData[$index]['total_plays'] = $channel->statistics->viewCount;
                $finalData[$index]['subscribers'] = $channel->statistics->subscriberCount;
                $finalData[$index]['id'] = $channel->id;
                $index++;
            }
        }
        
        // Return data
        return $finalData;
    }
    
    /**
     * Function to retrieve list of videos
     *
     * @param String $channelId Channel id from which you want to retrieve videos
     * @param integer $maxItems (optional) Number of items to fetch default to 12 items
     * @throws Exception Can throw sdk exception
     * @return Array Dataset
     */
    public function getVideosList($channelId, $maxItems = 12) {
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getVideosList: Wrapper is not configured properly");
        }
        
        // Fetch videos list
        $youtubeService = new \Google\Service\YouTube($this->googleClient);
        $data = $youtubeService->search->listSearch('snippet,id', [ 
            'channelId' => $channelId,
            'maxResults' => $maxItems,
            'order' => 'date',
            'type' => 'video'
        ])->toSimpleObject();
        
        // Convert in desired format
        $finalData = [];
        $index = 0;
        if(isset($data->items) && count($data->items) > 0){
            foreach($data->items as $item) {
                $finalData[$index]['id'] = $item->id->videoId;
                $finalData[$index]['title'] = $item->snippet->title;
                $finalData[$index]['description'] = $item->snippet->description;
                $finalData[$index]['created_at'] = $item->snippet->publishTime;
                $finalData[$index]['thumbnail'] = $item->snippet->thumbnails->default->url;
                $finalData[$index]['channel_name'] = $item->snippet->channelTitle;
                $index++;
            }
        }
        
        // Return data
        return $finalData;
    }
    
    /**
     * Function to retrieve video details
     *
     * @param String $videoId Pass youtube video id
     * @throws Exception Can throw sdk exception
     * @return Array Dataset
     */
    public function getVideoDetail($videoId) {
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getVideoDetail: Wrapper is not configured properly");
        }
        
        // Fetch video detail
        $youtubeService = new \Google\Service\YouTube($this->googleClient);
        $data = $youtubeService->videos->listVideos('snippet,statistics', [ 
            'id' => $videoId
        ])->toSimpleObject();
        
        // Convert in desired format
        $finalData = [];
        if(isset($data->items) && count($data->items) > 0){
            // Since it can return multiple items as per response structure we will pick 1 only
            $item = $data->items[0];
            $finalData['id'] = $item->id;
            $finalData['title'] = $item->snippet->title;
            $finalData['description'] = $item->snippet->description;
            $finalData['created_at'] = $item->snippet->publishedAt;
            $finalData['channel_name'] = $item->snippet->channelTitle;
            $finalData['stats']['views'] = $item->statistics->viewCount;
            $finalData['stats']['likes'] = $item->statistics->likeCount;
            $finalData['stats']['dislikes'] = $item->statistics->dislikeCount;
            $finalData['stats']['favorites'] = $item->statistics->favoriteCount;
            $finalData['stats']['comments'] = $item->statistics->commentCount;
            $finalData['comments'] = [];
            
            // Fetch comments if present
            if(!empty($item->statistics->commentCount) && $item->statistics->commentCount>0) {
                $finalData['comments'] = $this->getVideoComments($videoId);
            }
        }
        
        // Return data
        return $finalData;
        
    }
    
    /**
     * Function to retrieve video comments and replies
     *
     * @param String $videoId Pass youtube video id
     * @param integer $maxItems (optional) Number of items to fetch default to 12 items
     * @throws Exception Can throw sdk exception
     * @return Array Dataset
     */
    public function getVideoComments($videoId, $maxItems = 12) {
        
        // If not ready
        if(!$this->isClientPrepared) {
            throw new Exception("getVideoComments: Wrapper is not configured properly");
        }
        
        // Fetch video detail
        $youtubeService = new \Google\Service\YouTube($this->googleClient);
        $data = $youtubeService->commentThreads->listCommentThreads('snippet,replies', [ 
            'videoId' => $videoId,
            'maxResults' => $maxItems
        ])->toSimpleObject();
        
        // Convert in desired format
        $finalData = [];
        $index = 0;
        if(isset($data->items) && count($data->items) > 0){
            foreach($data->items as $item) {
                
                // Only process those comments which are public
                if(empty($item->snippet->isPublic) || !$item->snippet->isPublic) {
                    continue;
                }
                
                $finalData[$index]['id'] = $item->id;
                $finalData[$index]['author'] = $item->snippet->topLevelComment->snippet->authorDisplayName;
                $finalData[$index]['author_image'] = $item->snippet->topLevelComment->snippet->authorProfileImageUrl;
                $finalData[$index]['text'] = $item->snippet->topLevelComment->snippet->textDisplay;
                $finalData[$index]['updated_at'] = $item->snippet->topLevelComment->snippet->updatedAt;
                $finalData[$index]['replies'] = [];
                $finalData[$index]['replies_count'] = $item->snippet->totalReplyCount;
                
                // Only process replies when it is on and replies are present
                if(!empty($item->snippet->canReply) && $item->snippet->canReply && $finalData[$index]['replies_count']>0) {
                    $finalData[$index]['replies'] = $this->sortReplies($item->id, $item->replies->comments);
                }
                
                // Increment
                $index++;
            }
        }
        
        // Return data
        return $finalData;
    }
    
    /**
     * Function is helping in tagging right reply to right comment by id
     *
     * @param String $commentID
     * @param Object $repliesDataset
     * @return Array Dataset
     */
    private function sortReplies($commentID, $repliesDataset) {
        
        // Convert in desired format
        $finalData = [];
        $index = 0;
        if(count($repliesDataset) > 0) {
            foreach($repliesDataset as $item) {
                
                if(empty($item->snippet->parentId) || (!empty($item->snippet->parentId) && $item->snippet->parentId != $commentID)) {
                    continue;
                }
                
                $finalData[$index]['id'] = $item->id;
                $finalData[$index]['author'] = $item->snippet->authorDisplayName;
                $finalData[$index]['author_image'] = $item->snippet->authorProfileImageUrl;
                $finalData[$index]['text'] = $item->snippet->textDisplay;
                $finalData[$index]['updated_at'] = $item->snippet->updatedAt;
                
                // Increment
                $index++;
            }
        }
        
        // Return sorted data
        return $finalData;
    }
}
?>