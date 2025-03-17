<?php
class ApiHandler {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function makeRequest($serviceKey, $input) {
        foreach ($this->config['services'] as $category => $services) {
            if (isset($services[$serviceKey])) {
                $url = $services[$serviceKey];
                if ($category === 'ai' && $serviceKey === 'TextToVoice') {
                    $url .= $input['text'] . '&voice=' . $input['voice'];
                } else {
                    $url .= urlencode($input);
                }
                return $this->callApi($url);
            }
        }
        return null;
    }

    private function callApi($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    public function downloadMedia($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return [
            'data' => $data,
            'type' => $contentType
        ];
    }
}
