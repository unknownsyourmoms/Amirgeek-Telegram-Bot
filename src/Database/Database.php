<?php
class Database {
    private $pdo;

    public function __construct($config) {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['username'], $config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initTables();
    }

    private function initTables() {
        // Users table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                telegram_id BIGINT UNIQUE,
                username VARCHAR(255),
                first_name VARCHAR(255),
                coins INT DEFAULT 0,
                language VARCHAR(2) DEFAULT 'en',
                referral_code VARCHAR(32) UNIQUE,
                referred_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Admins table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT PRIMARY KEY AUTO_INCREMENT,
                telegram_id BIGINT UNIQUE,
                role VARCHAR(20) DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Transactions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT,
                type VARCHAR(20),
                amount INT,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Support tickets table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS support_tickets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id BIGINT,
                message TEXT,
                status VARCHAR(20) DEFAULT 'open',
                admin_response TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Settings table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) UNIQUE,
                value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    public function createUser($telegramId, $username, $firstName) {
        $referralCode = $this->generateReferralCode();
        $stmt = $this->pdo->prepare("
            INSERT INTO users (telegram_id, username, first_name, referral_code)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            username = VALUES(username),
            first_name = VALUES(first_name)
        ");
        $stmt->execute([$telegramId, $username, $firstName, $referralCode]);
    }

    private function generateReferralCode() {
        return substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }

    public function getUser($telegramId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$telegramId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserLanguage($telegramId, $language) {
        $stmt = $this->pdo->prepare("UPDATE users SET language = ? WHERE telegram_id = ?");
        $stmt->execute([$language, $telegramId]);
    }

    public function updateUserCoins($telegramId, $coins, $type, $description) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET coins = coins + ? WHERE telegram_id = ?");
            $stmt->execute([$coins, $telegramId]);

            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, description)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$telegramId, $type, $coins, $description]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getUserCoins($telegramId) {
        $stmt = $this->pdo->prepare("SELECT coins FROM users WHERE telegram_id = ?");
        $stmt->execute([$telegramId]);
        return $stmt->fetchColumn();
    }

    public function createSupportTicket($userId, $message) {
        $stmt = $this->pdo->prepare("
            INSERT INTO support_tickets (user_id, message)
            VALUES (?, ?)
        ");
        $stmt->execute([$userId, $message]);
        return $this->pdo->lastInsertId();
    }

    public function respondToTicket($ticketId, $response) {
        $stmt = $this->pdo->prepare("
            UPDATE support_tickets
            SET admin_response = ?, status = 'closed'
            WHERE id = ?
        ");
        $stmt->execute([$response, $ticketId]);
    }

    public function getOpenTickets() {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.username, u.first_name
            FROM support_tickets t
            JOIN users u ON t.user_id = u.telegram_id
            WHERE t.status = 'open'
            ORDER BY t.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAdmin($telegramId, $role = 'admin') {
        $stmt = $this->pdo->prepare("
            INSERT INTO admins (telegram_id, role)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE role = VALUES(role)
        ");
        $stmt->execute([$telegramId, $role]);
    }

    public function removeAdmin($telegramId) {
        $stmt = $this->pdo->prepare("DELETE FROM admins WHERE telegram_id = ?");
        $stmt->execute([$telegramId]);
    }

    public function isAdmin($telegramId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE telegram_id = ?");
        $stmt->execute([$telegramId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getSetting($name) {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    }

    public function setSetting($name, $value) {
        $stmt = $this->pdo->prepare("
            INSERT INTO settings (name, value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value)
        ");
        $stmt->execute([$name, $value]);
    }
}
