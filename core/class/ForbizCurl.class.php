<?php

/**
 * Description of ForbizCurl
 *
 * @author hoksi
 */
class ForbizCurl extends \Curl\Curl
{
    protected $baseUrl = false;

    protected function getApiUrl($url)
    {
        return ($this->baseUrl !== false) ? $this->baseUrl . $url : $url;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function get($url, $data = array())
    {
        $url = $this->getApiUrl($url);

        if (count($data) > 0) {
            $this->setOpt(CURLOPT_URL, $url . '?' . http_build_query($data));
        } else {
            $this->setOpt(CURLOPT_URL, $url);
        }
        $this->setOpt(CURLOPT_HTTPGET, true);
        $this->exec();

        return $this->response;
    }

    public function post($url, $data = array())
    {
        $url = $this->getApiUrl($url);

        if (!empty($data)) {
            if (defined('API_TEST_CLIENT') && API_TEST_CLIENT === true) {
                $json = json_encode($data);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->preparePayload($json);
                }
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_POST, true);
        $this->exec();
        return $this;
    }

    public function put($url, $data = array(), $payload = false)
    {
        $url = $this->getApiUrl($url);

        if (!empty($data)) {
            $json = json_encode($data);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->preparePayload($json);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->exec();
        return $this;
    }

    public function delete($url, $data = array(), $payload = false)
    {
        $url = $this->getApiUrl($url);

        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->exec();
        return $this;
    }

    public function send()
    {
        $this->exec();

        return $this;
    }
}