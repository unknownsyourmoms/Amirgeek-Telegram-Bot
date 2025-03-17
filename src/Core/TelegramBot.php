<?php
class TelegramBot {
    private $token;
    private $apiUrl;

    public function __construct($token) {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$token}/";
    }

    public function sendMessage($params) {
        return $this->apiRequest('sendMessage', $params);
    }

    public function editMessageText($params) {
        return $this->apiRequest('editMessageText', $params);
    }

    public function deleteMessage($params) {
        return $this->apiRequest('deleteMessage', $params);
    }

    public function sendPhoto($params) {
        return $this->apiRequest('sendPhoto', $params);
    }

    public function sendDocument($params) {
        return $this->apiRequest('sendDocument', $params);
    }

    public function sendAudio($params) {
        return $this->apiRequest('sendAudio', $params);
    }

    public function sendVideo($params) {
        return $this->apiRequest('sendVideo', $params);
    }

    public function answerCallbackQuery($params) {
        return $this->apiRequest('answerCallbackQuery', $params);
    }

    public function setWebhook($url) {
        return $this->apiRequest('setWebhook', ['url' => $url]);
    }

    private function apiRequest($method, $params) {
        $url = $this->apiUrl . $method;
        $ch = curl_init();
        
        if (isset($params['photo']) && is_file($params['photo'])) {
            $params['photo'] = new CURLFile($params['photo']);
        }
        if (isset($params['document']) && is_file($params['document'])) {
            $params['document'] = new CURLFile($params['document']);
        }
        if (isset($params['audio']) && is_file($params['audio'])) {
            $params['audio'] = new CURLFile($params['audio']);
        }
        if (isset($params['video']) && is_file($params['video'])) {
            $params['video'] = new CURLFile($params['video']);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Telegram API Error: " . $error);
        }

        $result = json_decode($response, true);
        if (!$result['ok']) {
            throw new Exception("Telegram API Error: " . ($result['description'] ?? 'Unknown error'));
        }

        return $result['result'];
    }

    public function downloadFile($fileId) {
        $fileInfo = $this->apiRequest('getFile', ['file_id' => $fileId]);
        if (!isset($fileInfo['file_path'])) {
            return false;
        }

        $url = "https://api.telegram.org/file/bot{$this->token}/" . $fileInfo['file_path'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
