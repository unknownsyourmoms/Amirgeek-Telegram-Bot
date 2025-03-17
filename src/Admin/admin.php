<?php
require_once 'vendor/autoload.php';

class AdminPanel {
    private $config;
    private $db;
    private $telegram;
    private $stateHandler;

    public function __construct() {
        $this->config = require 'config.php';
        $this->db = new Database($this->config['database']);
        $this->telegram = new TelegramBot($this->config['bot_token']);
        $this->stateHandler = new StateHandler($this->db);
    }

    public function handleAdminCommands($message) {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if (!$this->db->isAdmin($chatId)) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'You are not authorized to use admin commands.'
            ]);
            return;
        }

        if ($this->stateHandler->isWaitingForInput($chatId, 'waiting_for_broadcast')) {
            $this->handleBroadcastMessage($chatId, $text);
            return;
        }

        if ($this->stateHandler->isWaitingForInput($chatId, 'waiting_for_user_id')) {
            $this->handleUserMessage($chatId, $text);
            return;
        }

        switch ($text) {
            case '/admin':
                $this->sendAdminMenu($chatId);
                break;
            case '/admin_settings':
                $this->sendSettingsMenu($chatId);
                break;
            case '/admin_statistics':
                $this->sendStatistics($chatId);
                break;
            case '/admin_broadcast':
                $this->startBroadcast($chatId);
                break;
            case '/admin_support':
                $this->showSupportTickets($chatId);
                break;
            case '/admin_users':
                $this->showUsersList($chatId);
                break;
            default:
                if (strpos($text, '/coin_add') === 0) {
                    $this->handleCoinAddition($chatId, $text);
                } elseif (strpos($text, '/respond') === 0) {
                    $this->handleTicketResponse($chatId, $text);
                } else {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Unknown admin command.'
                    ]);
                }
                break;
        }
    }

    private function sendAdminMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '⚙️ Settings', 'callback_data' => 'admin_settings'],
                    ['text' => '📊 Statistics', 'callback_data' => 'admin_statistics']
                ],
                [
                    ['text' => '📢 Broadcast', 'callback_data' => 'admin_broadcast'],
                    ['text' => '👥 Users', 'callback_data' => 'admin_users']
                ],
                [
                    ['text' => '🎫 Support Tickets', 'callback_data' => 'admin_support'],
                    ['text' => '💰 Add Coins', 'callback_data' => 'admin_coins']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Welcome to the Admin Panel\n\nSelect an option:",
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendSettingsMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Change Start Text', 'callback_data' => 'admin_change_start_text'],
                    ['text' => 'Manage Admins', 'callback_data' => 'admin_manage_admins']
                ],
                [
                    ['text' => 'Service Settings', 'callback_data' => 'admin_service_settings'],
                    ['text' => 'Coin Settings', 'callback_data' => 'admin_coin_settings']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'admin_menu']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Bot Settings:\n\nSelect a setting to modify:",
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendStatistics($chatId) {
        $stmt = $this->db->pdo->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(coins) as total_coins,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as new_users_24h
            FROM users
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->pdo->query("
            SELECT COUNT(*) as open_tickets
            FROM support_tickets
            WHERE status = 'open'
        ");
        $tickets = $stmt->fetch(PDO::FETCH_ASSOC);

        $text = "📊 Bot Statistics\n\n";
        $text .= "👥 Total Users: {$stats['total_users']}\n";
        $text .= "💰 Total Coins: {$stats['total_coins']}\n";
        $text .= "🆕 New Users (24h): {$stats['new_users_24h']}\n";
        $text .= "🎫 Open Support Tickets: {$tickets['open_tickets']}\n";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    private function startBroadcast($chatId) {
        $this->stateHandler->setState($chatId, 'waiting_for_broadcast');
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Please enter the message you want to broadcast to all users:'
        ]);
    }

    private function handleBroadcastMessage($chatId, $text) {
        $this->stateHandler->clearState($chatId);
        
        $stmt = $this->db->pdo->query("SELECT telegram_id FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $sent = 0;
        foreach ($users as $userId) {
            try {
                $this->telegram->sendMessage([
                    'chat_id' => $userId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ]);
                $sent++;
            } catch (Exception $e) {
                // Log failed message
            }
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Broadcast complete!\nMessage sent to {$sent} users."
        ]);
    }

    private function showSupportTickets($chatId) {
        $tickets = $this->db->getOpenTickets();
        if (empty($tickets)) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'No open support tickets.'
            ]);
            return;
        }

        $text = "Open Support Tickets:\n\n";
        foreach ($tickets as $ticket) {
            $text .= "🎫 Ticket #{$ticket['id']}\n";
            $text .= "👤 User: {$ticket['username']}\n";
            $text .= "📝 Message: {$ticket['message']}\n";
            $text .= "⏰ Created: {$ticket['created_at']}\n\n";
        }
        $text .= "To respond to a ticket, use:\n/respond <ticket_id> <message>";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }

    private function handleTicketResponse($chatId, $text) {
        $parts = explode(' ', $text, 3);
        if (count($parts) < 3) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Usage: /respond <ticket_id> <message>'
            ]);
            return;
        }

        $ticketId = $parts[1];
        $response = $parts[2];

        try {
            $this->db->respondToTicket($ticketId, $response);
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Response sent to ticket #{$ticketId}"
            ]);
        } catch (Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Error: Could not respond to ticket.'
            ]);
        }
    }

    private function handleCoinAddition($chatId, $text) {
        $parts = explode(' ', $text);
        if (count($parts) !== 3) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Usage: /coin_add <user_id> <amount>'
            ]);
            return;
        }

        $userId = $parts[1];
        $amount = intval($parts[2]);

        try {
            $this->db->updateUserCoins($userId, $amount, 'admin_add', 'Added by admin');
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Added {$amount} coins to user {$userId}"
            ]);
        } catch (Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Error: Could not add coins.'
            ]);
        }
    }

    private function showUsersList($chatId) {
        $stmt = $this->db->pdo->query("
            SELECT telegram_id, username, coins, created_at
            FROM users
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $text = "Recent Users:\n\n";
        foreach ($users as $user) {
            $text .= "👤 User: {$user['username']}\n";
            $text .= "🆔 ID: {$user['telegram_id']}\n";
            $text .= "💰 Coins: {$user['coins']}\n";
            $text .= "📅 Joined: {$user['created_at']}\n\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}

$adminPanel = new AdminPanel();
$adminPanel->handleAdminCommands($message);
