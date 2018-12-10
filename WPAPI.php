<?php namespace WPAPI;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use Config, Cache, App;

class WPAPI {

    private $_endpoint;
    private $_url;
    private $_cache = true;
    private $_auth_cookie;


    public function __construct(array $config)
    {
        $locale = App::getLocale();

        $this->_wpapi_config = Config::get('wpapi');
        $this->_url = $this->_wpapi_config[$locale];

        if ( isset($config['url']) ) {
            $this->_url = $config['url'];
        }

        $this->_api_url = $this->_wpapi_config['api_url'];
        $this->_username = $this->_wpapi_config['username'];
        $this->_password = $this->_wpapi_config['password'];
        $this->_endpoint = $config['endpoint'];

        if ( isset($config['cache']) ) {
            $this->_cache = $config['cache'];
        }

        if ( isset($_GET['nocache']) || isset($_GET['preview']) ) {
            $this->_cache = false;
        }
    }

    /**
     * Prepares endpoint URL, checks if request is
     * cached and either returns cached data or requests data using
     * executeRequest method
     * @param  string $post
     * @return array
     */

    public function requestData($post = [])
    {
        //if the endpoint does not have a / then it wont pull the data,
        // wp 301 redirects it.
        if ( substr($this->_endpoint, -1) != '/' ) {
            $this->_endpoint = $this->_endpoint . '/';
        }

        if (defined('PREVIEW_POST') && $post != null) {
            $this->_auth_cookie = $this->getAuthCookie();
            $post['post_id'] = PREVIEW_POST;
            $post['cookie'] = $this->_auth_cookie;
        }

        $url = $this->_url;
        $endpoint = $this->_endpoint;

        $cacheString = call_user_func(function ($params) {
            $cacheString = '';

            if ( is_array($params) ) {
                foreach ($params as $paramKey => $param) {
                    
                    if (is_array($param)) {
                        $param = serialize($param);
                    }

                    $cacheString .= $paramKey . $param;
                }
            }

            return $cacheString;
        }, $post);

        $cacheKey = $this->createCacheKey($this->_endpoint . $cacheString);
        
        if ( $this->_cache ) {
            $data = $this->getCache($cacheKey);
        } else {
            Cache::forget($cacheKey);
        }

        if ( empty($data) ) {
            $data = $this->executeRequest($url, $endpoint, $cacheKey, $post);
        }

        return (object) $data;

    }

    /**
     * Makes GET request to specified URL and sets Cache of response using combination of url and post slug
     * @param  string $url
     * @param  string $endpoint
     * @param  string $cacheKey
     * @return string
     */
    private function executeRequest($url, $endpoint, $cacheKey, $query)
    {
        $client = new Client();

        $response = $client->request('GET', $url . $endpoint, [
            'query' => $query
        ]);

        $data = $response->getBody();

        if ( $this->_cache ) {
            $this->setCache($cacheKey, $data);
        }

        return json_decode($data, JSON_FORCE_OBJECT);
    }

    private function getAuthCookie()
    {
        if ( !defined('PREVIEW_POST') ) {
            return null;
        }

        if (Cache::has('wpapi_auth_cookie')) {
            $auth_cookie = Cache::get('wpapi_auth_cookie');
            $is_valid = $this->validAuthCookie($auth_cookie);

            if ( $is_valid['valid'] != '1' ) {
                Cache::forget('wpapi_auth_cookie');
                $this->getAuthCookie();
            }
        } else {
            $nonce_res = $this->generateNonce();
            $auth_res = $this->generateAuthCookie($nonce_res['nonce']);
            $auth_cookie = $auth_res['cookie'];
            Cache::put('wpapi_auth_cookie', $auth_cookie, 18720); //13 days
        }

        return $auth_cookie;
    }

    private function generateNonce()
    {
        $client = new Client();
        $endpoint = 'get_nonce/?controller=auth&method=generate_auth_cookie';
        $response = $client->request('GET', $this->_api_url . $endpoint);
        return json_decode($response->getBody(), JSON_FORCE_OBJECT);
    }

    private function generateAuthCookie($nonce)
    {
        $client = new Client();
        $endpoint = "auth/generate_auth_cookie/?nonce=$nonce&username=$this->_username&password=$this->_password";
        $response = $client->request('GET', $this->_api_url . $endpoint);
        return json_decode($response->getBody(), JSON_FORCE_OBJECT);
    }

    private function validAuthCookie($auth_cookie)
    {
        $client = new Client();
        $endpoint = 'auth/validate_auth_cookie/?cookie=' . $auth_cookie;
        $response = $client->request('GET', $this->_api_url . $endpoint);
        return json_decode($response->getBody(), JSON_FORCE_OBJECT);
    }

    private function createCacheKey($key)
    {
        return md5($this->_endpoint . Config::get('app.locale') . '-' . $key);
    }

    private function getCache($key)
    {
        return Cache::get($key);
    }

    private function setCache($cacheKey, GuzzleStream $stream)
    {
        Cache::put($cacheKey, $this->json($stream), 60); //1 hours
    }

    public static function status()
    {
        return 'WPAPI is enabled.';
    }

    public static function json($json)
    {
        return json_decode($json, JSON_FORCE_OBJECT);
    }
}