<?php
class config
{
    const TOKEN_URL = 'https://api.instagram.com/oauth/access_token'; //API_OAUTH_TOKEN_URL
    const API_URL = 'https://api.instagram.com/v1/'; //API_URL
    const OAUTH_URL = 'https://api.instagram.com/oauth/authorize'; //API_OAUTH_URL
    
    private $apikey = "6dd74ef468144815ae3b91ca7432cd67";
    private $apisecret = "55b0ec76b31f4f8282c0d5d1ad8abfab";
    private $callbackurl = "http://dev2.spaceo.in/project/socialapp/success.php";
    private $accesstoken;
    private $signedheader = false;

    public function setAccessToken($data)
    {
        $token = is_object($data) ? $data->access_token : $data;

        $this->accesstoken = $token;
    }
    public function getAccessToken()
    {
        return $this->accesstoken;
    }

    
    public function setApiKey($apiKey)
    {
        $this->apikey = $apiKey;
    }
    public function getApiKey()
    {
        return $this->apikey;
    }

    
    public function setApiSecret($apiSecret)
    {
        $this->apisecret = $apiSecret;
    }
    public function getApiSecret()
    {
        return $this->apisecret;
    }

    
    public function setApiCallback($apiReturnback)
    {
        $this->callbackurl = $apiReturnback;
    }
    public function getApiCallback()
    {
        return $this->callbackurl;
    }
    
    public function getAuthToken($code, $token = false)
    {
        $Data = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->getApiKey(),
            'client_secret' => $this->getApiSecret(),
            'redirect_uri' => $this->getApiCallback(),
            'code' => $code
        );
        
        $result = $this->AuthCall($Data);
        
        return !$token ? $result : $result->access_token;
    }
    
    private function AuthCall($Data)
    {
        
        $Host = self::TOKEN_URL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Host);
        curl_setopt($ch, CURLOPT_POST, count($Data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $jsonData = curl_exec($ch);

        if (!$jsonData) {
            throw new InstagramException('Error: AuthCall() - cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($jsonData);
    }
    
    public function getUserMedia($id = 'self', $limit = 0)
    {
        $params = array();

        if ($limit > 0) {
            $params['count'] = $limit;
        }

        return $this->makeRequest('users/' . $id . '/media/recent', strlen($this->getAccessToken()), $params);
    }
    
    protected function makeRequest($function, $auth = false, $params = null, $method = 'GET')
    {
        if (!$auth) {
            // if the call doesn't requires authentication
            $authMethod = '?client_id=' . $this->getApiKey();
        } else {
            // if the call needs an authenticated user
            if (!isset($this->accesstoken)) {
                throw new InstagramException("Error: _makeCall() | $function - This method requires an authenticated users access token.");
            }

            $authMethod = '?access_token=' . $this->getAccessToken();
        }
        
        $paramString = null;

        if (isset($params) && is_array($params)) {
            $paramString = '&' . http_build_query($params);
        }

        $apiCall = self::API_URL . $function . $authMethod . (('GET' === $method) ? $paramString : null);

        // we want JSON
        $headerData = array('Accept: application/json');
        
        if ($this->signedheader) {
            $apiCall .= (strstr($apiCall, '?') ? '&' : '?') . 'sig=' . $this->headerSign($function, $authMethod, $params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiCall);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, count($params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, ltrim($paramString, '&'));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $jsonData = curl_exec($ch);
        
        // split header from JSON data
        // and assign each to a variable
        list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);

        // convert header content into an array
        $headers = $this->runHeaders($headerContent);
        
        // get the 'X-Ratelimit-Remaining' header value
        $this->_xRateLimitRemaining = $headers['X-Ratelimit-Remaining'];
        
        if (!$jsonData) {
            throw new InstagramException('Error: _makeCall() - cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        return json_decode($jsonData);
    }
    
    private function headerSign($endpoint, $authMethod, $params)
    {
        if (!is_array($params)) {
            $params = array();
        }
        if ($authMethod) {
            list($key, $value) = explode('=', substr($authMethod, 1), 2);
            $params[$key] = $value;
        }
        $baseString = '/' . $endpoint;
        ksort($params);
        foreach ($params as $key => $value) {
            $baseString .= '|' . $key . '=' . $value;
        }
        $signature = hash_hmac('sha256', $baseString, $this->apisecret, false);

        return $signature;
    }
    private function runHeaders($headerContent)
    {
        $headers = array();

        foreach (explode("\r\n", $headerContent) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
                continue;
            }

            list($key, $value) = explode(':', $line);
            $headers[$key] = $value;
        }

        return $headers;
    }
}
