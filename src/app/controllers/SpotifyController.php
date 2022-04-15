<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;

class SpotifyController extends Controller
{

    public function indexAction()
    {
        // echo "inside spotify!";
    }

    public function authorizationAction()
    {
        // echo "inside spotify!";
        $clientId = '0c57cbf1a96e47caa03a1dd451fd1d72';
        $clientSecret = 'e6d0d6fc53b44adcb9d6cd8ceeb65a7b';

        $redirectUri = 'http://localhost:8080/spotify/authentication';

        $bytes = random_bytes(20);
        

        $state = bin2hex($bytes);
        // print_r($bytes); die;
        $scope = "playlist-modify-public playlist-read-private playlist-modify-private";

        $data = array(
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => $scope,
            'redirect_uri' => $redirectUri,
            'state' => $state
        );
        
        $authUrl = 'https://accounts.spotify.com/authorize?'.http_build_query($data);



        $this->response->redirect($authUrl);
    }

    public function authenticationAction()
    {
        $code = $this->request->get('code');
        // $code1 = $_GET['code'];

        $clientId = '0c57cbf1a96e47caa03a1dd451fd1d72';
        $clientSecret = 'e6d0d6fc53b44adcb9d6cd8ceeb65a7b';


        // print_r($code1); die;
        $redirectUri = 'http://localhost:8080/spotify/authentication';

        $url = 'https://accounts.spotify.com/api/token';
        
        // $header = array(
        //     "headers" => array(
        //         'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
        //         'Content-Type' => 'application/x-www-form-urlencoded'
        //     ));

        $header = array(
                'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded'
            );

        $body = array(
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        );

        $client = new Client([
            'base_url' => $url,
            'headers' => $header
            
        ]);

        $response = $client->post($url, ['form_params' => ['code' => $code,
                                                'redirect_uri' => $redirectUri,
                                                'grant_type' => 'authorization_code']]);


        // $client = new Client();

        // $client->post($url, ['form_params' =>[
        //     'headers'         => ['Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
        //                           'Content-Type' => 'application/x-www-form-urlencoded'],
        //     'body'            => ['code' => $code,
        //                           'redirect_uri' => $redirectUri,
        //                           'grant_type' => 'authorization_code']
        //     ]
        // ]);

        // $authTokenUrl = 'https://accounts.spotify.com/api/token'.http_build_query($data);


        // ------------------ js format example in api
        // var authOptions = {url: 'https://accounts.spotify.com/api/token',
        //     form: {code: code, redirect_uri: redirect_uri, grant_type: 'authorization_code'},
        //     headers: {'Authorization': 'Basic ' + (new Buffer(client_id + ':' + client_secret).toString('base64'))},
        //     json: true};
        $response = json_decode($response->getBody(), true);

        $this->session->set('access_token', $response['access_token']);
        $this->session->set('token_type', $response['token_type']);
        $this->session->set('refresh_token', $response['refresh_token']);
        $this->session->set('scope', $response['scope']);

        // echo "<pre>";
        // print_r(json_decode($response->getBody(), true));
        $this->response->redirect('spotify/search');
    }

    public function searchAction()
    {
        
        /**
         * View of this search action is containing the form to search for the songs using Spotify's searach API.
         *
         * @return void
         */
    }
    
    public function searchResultAction()
    {
        $access_token = $this->session->get('access_token');
        $token_type = $this->session->get('token_type');
        $refresh_token = $this->session->get('refresh_token');
        $scope = $this->session->get('scope');

        $search = $this->request->getPost('song');
        $searchArrFmt = explode(' ', $search);
        $searchUrlFmt = implode('%20', $searchArrFmt);
        $this->view->search = $search;
        $this->view->searchUrlFmt = $searchUrlFmt;

        $album = $this->request->getPost('album');
        $artist = $this->request->getPost('artist');
        $playlist = $this->request->getPost('playlist');
        $track = $this->request->getPost('track');
        $show = $this->request->getPost('show');
        $episode = $this->request->getPost('episode');

        $tags = ['album' => $album, 'artist' => $artist, 'playlist' => $playlist, 'track' => $track, 'show' => $show, 'episode'=>$episode];
        $this->view->tags = $tags;


        $tagsUrl = [];

        if (isset($album)) {
            array_push($tagsUrl, 'album');
        }
        if (isset($artist)) {
            array_push($tagsUrl, 'artist');
        }
        if (isset($playlist)) {
            array_push($tagsUrl, 'playlist');
        }
        if (isset($track)) {
            array_push($tagsUrl, 'track');
        }
        if (isset($show)) {
            array_push($tagsUrl, 'show');
        }
        if (isset($episode)) {
            array_push($tagsUrl, 'episode');
        }

        // $this->view->tag = $tagsUrl;


        $tagUrl = implode(',', $tagsUrl);
        // echo $access_token;
        // print_r($access_token); die;

        $client = new Client();
        // $response = $client->get('https://api.spotify.com/v1/search?q=attention&type=track', ['form_params' => ['headers' => ['Authorization' => 'Bearer '.$access_token, 'Content-Type' => 'application/json']]]);

        
        /**
         * $response in here is the response get by the Search API from the spotify.
         */
        $response = $client->request('GET', 'https://api.spotify.com/v1/search?q='.$search.'&type='.$tagUrl, [
                                                                                                            'headers' => ['Authorization' => 'Bearer '.$access_token, 'Content-Type' => 'application/json']
                                                                                                        ]);

        $response = json_decode($response->getBody(), true);
        $this->view->response = $response;
        
        
    }

    public function playlistAction()
    {
        $songId =  $this->request->get('id');

    }

}



