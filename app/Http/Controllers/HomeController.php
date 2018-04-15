<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\History;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function manageAccount(){
        $users = User::paginate(40);
        return view('manage_accounts', compact('users'));
    }
    public function getAccessToken(Request $request, User $user){
        

        $OAUTH2_CLIENT_ID = env("OAUTH2_CLIENT_ID");
        $OAUTH2_CLIENT_SECRET = env("OAUTH2_CLIENT_SECRET");

        $client = new \Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $client->setAccessType('offline');
        $redirect = filter_var(route('oauth2callback'), FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);

        
        // Define an object that will be used to make all API requests.
        //$youtube = new Google_Service_YouTube($client);

        // Check if an auth token exists for the required scopes
        $tokenSessionKey = 'token-' . $client->prepareScopes();

        

        $state = mt_rand();
        
        $client->setState($state);
        session(['state' => $state]);
        session(['user_id' => $user->id]);
        $authUrl = $client->createAuthUrl();

        return "Authorization Required: <a href='" . $authUrl . "'>Autorize</a>";
    }

    public function oauth2callback(Request $request){


        $user = User::findOrFail($request->session()->get('user_id'));

        $code = $request->code;
        $state = $request->state;

        $OAUTH2_CLIENT_ID = env("OAUTH2_CLIENT_ID");
        $OAUTH2_CLIENT_SECRET = env("OAUTH2_CLIENT_SECRET");

        $client = new \Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $client->setAccessType('offline');
        $redirect = filter_var(route('oauth2callback'), FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);
        // Define an object that will be used to make all API requests.
        //$youtube = new Google_Service_YouTube($client);

        // Check if an auth token exists for the required scopes
        $tokenSessionKey = 'token-' . $client->prepareScopes();


        if (isset($code)) {
            if (strval($request->session()->get('state')) !== strval($state)) {
                die('The session state did not match.');
            }


            $client->authenticate($code);

            $user->access_token = json_encode($client->getAccessToken());
            $user->refresh_token = $client->getRefreshToken();

            if ($user->access_token !== "null") {
                $user->save();
            }


            return redirect('manage_account');

        }
    }


    public function update(Request $request){

        
        $users = User::whereNotNull('access_token')->whereNotNull('refresh_token')->get();

        $history = History::create([
            'channel_id' => $request->channel_id
        ]);

        //subscribe
        $subscribes_quantity = $request->subscribes_quantity;
        $subscribe_errors = [];
        $subscription_counts = 0;
        $old_subscribers = 0;


        //likes variables
        $likes_quantity = $request->likes_quantity;
        $old_likes_count = 0;
        $new_likes_count = 0;

        $unlikes_quantity = $request->unlikes_quantity;
        $old_unlikes_count = 0;
        $new_unlikes_count = 0;
        
        $like_errors = [];
        $unlike_errors = [];


        //comments
        $comments_quantity = $request->comments_quantity;
        $comments_count = 0;
        $comment_errors = [];


        if($request->subscribe == 'on'){
            //channel subscribe
            $history->subscribes_quantity = $subscribes_quantity;
                
            foreach ($users->shuffle() as $user) {

                $client = getClient($user);

                $youtube = new \Google_Service_YouTube($client);

                $has_subscription = isSubscriber($youtube, $request->channel_id);

                if ($subscription_counts >= $subscribes_quantity) {
                    break;
                }
                if (!$has_subscription) {

                    try {
                        $resourceId = new \Google_Service_YouTube_ResourceId();
                        $resourceId->setChannelId($request->channel_id);
                        $resourceId->setKind('youtube#channel');

                    // Create a snippet object and set its resource ID.
                        $subscriptionSnippet = new \Google_Service_YouTube_SubscriptionSnippet();
                        $subscriptionSnippet->setResourceId($resourceId);

                    // Create a subscription request that contains the snippet object.
                        $subscription = new \Google_Service_YouTube_Subscription();
                        $subscription->setSnippet($subscriptionSnippet);

                    // Execute the request and return an object containing information
                    // about the new subscription.
                        $subscriptionResponse = $youtube->subscriptions->insert(
                            'id,snippet',
                            $subscription,
                            array()
                        );

                        $subscription_counts++;

                    } catch (\Google_Service_Exception $e) {
                        $subscribe_errors[] = ['email' => $user->email, 'messsage' => $e->getMessage()];
                    } catch (\Google_Exception $e) {
                        $subscribe_errors[] = ['email' => $user->email, 'messsage' => $e->getMessage()];
                    }

                } else {
                    $old_subscribers++;
                }

            }

            $history->old_subscribers_count = $old_subscribers;
            $history->new_subscribers_count = $subscription_counts;
            $history->subscribe_errors = json_encode($subscribe_errors);
                     
        }

         // likes
        if ($request->likes == 'on') { 
            foreach ($users->shuffle() as $user) {

                if ($new_likes_count >= $likes_quantity) {
                    break;
                }

                $client = getClient($user);

                $youtube = new \Google_Service_YouTube($client);

                $has_rate = has_rate($youtube, $request->video_id);

                if (!$has_rate) {
                    try {
                        $response = videosRate($youtube, $request->video_id, 'like', array());
                        $new_likes_count++;
                    } catch (\Google_Service_Exception $e) {
                        $like_errors[] = [$user->email, $e->getMessage()];
                    } catch (\Google_Exception $e) {
                        $like_errors[] = [$user->email, $e->getMessage()];
                    }
                } else {
                    $old_likes_count++;
                }

            }

            $history->likes_quantity = $likes_quantity;
            $history->old_likes_count = $old_likes_count;
            $history->new_likes_count = $new_likes_count;
            $history->like_errors = json_encode($like_errors);

    }
        
        //unlikes
        if ($request->unlikes == 'on') { 
        foreach ($users->shuffle() as $user) {

            if ($new_unlikes_count >= $unlikes_quantity) {
                break;
            }
            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $has_rate = has_rate($youtube, $request->video_id);

            if (!$has_rate) {
                try {
                    $response = videosRate($youtube, $request->video_id, 'dislike', array());
                    $new_unlikes_count++;
                } catch (\Google_Service_Exception $e) {
                    $unlike_errors[] = [$user->email, $e->getMessage()];
                } catch (\Google_Exception $e) {
                    $unlike_errors[] = [$user->email, $e->getMessage()];
                }
            } else {
                $old_unlikes_count++;
            }
        }
            $history->unlikes_quantity = $unlikes_quantity;
            $history->old_unlikes_count = $old_unlikes_count;
            $history->new_unlikes_count = $new_unlikes_count;
            $history->unlike_errors = json_encode($unlike_errors);

    }
        
        // comments
        if ($request->comments == 'on') { 
        foreach ($users->shuffle() as $user) {

                if ($comments_count >= $comments_quantity) {
                    break;
                }

            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            try{

                $comment = commentThreadsInsert(
                    $youtube,
                    array(
                        'snippet.channelId' => $request->channel_id,
                        'snippet.videoId' => $request->video_id,
                        'snippet.topLevelComment.snippet.textOriginal' => 'Lovely video',
                    ),
                    'snippet',
                    $params = array_filter(array('onBehalfOfContentOwner' => 'true', 'mine' => true))
                );

                $comments_count++;

            }catch(\Google_Exception $e){
                $comment_errors[] = [$user->email, $e->getMessage()];                
            }
        }

                $history->comments_quantity = $comments_quantity;
                $history->comments_count = $comments_count;
                $history->comment_errors = json_encode($comment_errors);
    }

        $history->save();
    

        return back()->with('history', $history);
    }


    public function subscribe(Request $request){

        $quantity = $request->quantity;
        $users = User::whereNotNull('access_token')->whereNotNull('refresh_token')->get()->shuffle();

        $subscribe_errors = [];
       
        $subscription_counts = 0;
        $old_subscribers = 0;
        $limit_reached = false;

        
        foreach ($users as  $user) {
            
            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $has_subscription = isSubscriber($youtube, $request->channel_id);
            
            if($subscription_counts >= $quantity){
                break;
            }
            if (!$has_subscription) {

                try {
                    $resourceId = new \Google_Service_YouTube_ResourceId();
                    $resourceId->setChannelId($request->channel_id);
                    $resourceId->setKind('youtube#channel');

                // Create a snippet object and set its resource ID.
                    $subscriptionSnippet = new \Google_Service_YouTube_SubscriptionSnippet();
                    $subscriptionSnippet->setResourceId($resourceId);

                // Create a subscription request that contains the snippet object.
                    $subscription = new \Google_Service_YouTube_Subscription();
                    $subscription->setSnippet($subscriptionSnippet);

                // Execute the request and return an object containing information
                // about the new subscription.
                    $subscriptionResponse = $youtube->subscriptions->insert(
                        'id,snippet',
                        $subscription,
                        array()
                    );

                    $subscription_counts++;

                } catch (Google_Service_Exception $e) {
                    $subscribe_errors[] = ['email' => $user->email, 'messsage' => $e->getMessage()];
                } catch (Google_Exception $e) {
                    $subscribe_errors[] = ['email' => $user->email, 'messsage' => $e->getMessage()];
                }

            }else{
                $old_subscribers++;
            }
       
        }

        $subscription_info = [
            'old_subscribers' => $old_subscribers, 
            'subscription_counts' => $subscription_counts, 
            'error_count' => count($subscribe_errors),
            'subscribe_errors' => $subscribe_errors,
            'limit_reached' => $limit_reached,
        ];

        return back()->with('subscription_info', $subscription_info);

    }

    public function videoRate(Request $request){
        
        // dd($request->all());

        $users = User::whereNotNull('access_token')->whereNotNull('refresh_token')->get();
        $old_likes_count = 0;
        $new_likes_count = 0;
        $old_dislikes_count = 0;



        //comments
        foreach ($users->shuffle() as $user) {

            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $comment = commentThreadsInsert(
                $youtube,
                array(
                    'snippet.channelId' => $request->channel_id,
                    'snippet.videoId' => $request->video_id,
                    'snippet.topLevelComment.snippet.textOriginal' => 'Lovely video',
                ),
                'snippet',
                array()
            );

            dd($comment);


        }


       // likes
        foreach ($users->shuffle() as  $user) {

            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $has_rate = has_rate($youtube, $request->video_id);
            
            if(!$has_rate){
                try{
                    $response = videosRate($youtube, $request->video_id, 'like', array());
                    $new_likes_count++;
                }catch(\Google_Exception $e){
                    $rate_errors[] = [$user->email, $e->getMessage()];
                }
            }else{
                $old_likes_count++;
            }

        }
        
        //unlikes
        foreach ($users->shuffle() as  $user) {

            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $has_rate = has_rate($youtube, $request->video_id);

            if (!$has_rate) {
                try {
                    $response = videosRate($youtube, $request->video_id, 'dislike', array());
                    $new_likes_count++;
                } catch (\Google_Exception $e) {
                    $rate_errors[] = [$user->email, $e->getMessage()];
                }
            } else {
                $old_dislikes_count++;
            }
        }  
        
        
        //comments
        foreach($users->shuffle() as $user){

            $client = getClient($user);

            $youtube = new \Google_Service_YouTube($client);

            $comment = commentThreadsInsert(
                    $youtube,
                    array(
                        'snippet.channelId' => $request->channel_id,
                        'snippet.videoId' => $request->video_id,
                        'snippet.topLevelComment.snippet.textOriginal' => 'Lovely video',
                    ),
                    'snippet',
                    array()
                );

                dd($comment);


        }

          
    }

    public function addAccounts(Request $request){
        $accounts = \Excel::load($request->accounts)->all();
        $account_counts = 0;

        foreach ($accounts as $account) {
            $email = $account->email;
            $name = $account->name;
            $password = explode(' ', $name)[0];

            $user = User::whereEmail($email)->first();

            if(!$user){
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt($name),
                ]);

                $account_counts++;
            }
        }


            
        History::create([
            'channel_id' => $request->channel_id,
            'video_id'   => $request->video_id,
            'subscribe_quantity' => $subscribe_quantity,
            'subscribe_errors' => $subscribe_errors,
            'subscribes' => $subscription_counts,
            'old_subscribers' => $old_subscribers,
            'old_likes_count' => $old_likes_count,
            'likes' => $new_likes_count,
            'old_unlikes_count' => $old_unlikes_count,
            'unlikes' => $new_unlikes_count,
            'likes_rate_errors' => $likes_rate_errors,
            'unlikes_rate_errors' => $unlikes_rate_errors,
            'comments' => $comment_counts,
            'comment_errors' => $comment_errors,
        ]);
         
        
        return back()->withMessage($account_counts . ' accounts added');
    }
}
