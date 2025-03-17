<?php
class StateHandler {
    private $db;
    private $states = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function setState($userId, $state, $data = null) {
        $this->states[$userId] = [
            'state' => $state,
            'data' => $data,
            'timestamp' => time()
        ];
    }

    public function getState($userId) {
        return $this->states[$userId] ?? null;
    }

    public function clearState($userId) {
        unset($this->states[$userId]);
    }

    public function isWaitingForInput($userId, $state) {
        $currentState = $this->getState($userId);
        return $currentState && $currentState['state'] === $state;
    }

    public function cleanOldStates() {
        $timeout = 3600; // 1 hour
        $now = time();
        foreach ($this->states as $userId => $state) {
            if ($now - $state['timestamp'] > $timeout) {
                $this->clearState($userId);
            }
        }
    }
}
