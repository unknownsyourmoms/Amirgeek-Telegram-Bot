<?php
require_once 'vendor/autoload.php';
require_once 'Database.php';
require_once 'TelegramBot.php';
require_once 'StateHandler.php';
require_once 'ApiHandler.php';
require_once 'admin.php';
require_once 'error_log.php';

class AmirGeekBot {
    private $config;
    private $db;
    private $telegram;
    private $stateHandler;
    private $apiHandler;
    private $adminPanel;
    private $languages;

    public function __construct() {
        try {
            $this->config = require 'config.php';
            $this->languages = require 'languages.php';
            $this->db = new Database($this->config['database']);
            $this->telegram = new TelegramBot($this->config['bot_token']);
            $this->stateHandler = new StateHandler($this->db);
            $this->apiHandler = new ApiHandler($this->config);
            $this->adminPanel = new AdminPanel($this->telegram, $this->db, $this->config);
        } catch (Exception $e) {
            logError($e->getMessage());
            die('Bot initialization failed');
        }
    }

    public function handleWebhook() {
        $update = json_decode(file_get_contents('php://input'), true);
        
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    private function handleMessage($message) {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $username = $message['from']['username'] ?? '';
        $firstName = $message['from']['first_name'] ?? '';

        // Create or update user
        $this->db->createUser($chatId, $username, $firstName);
        $user = $this->db->getUser($chatId);

        // Handle admin commands
        if ($this->db->isAdmin($chatId) && strpos($text, '/admin') === 0) {
            $this->adminPanel->handleAdminCommands($message);
            return;
        }

        // Handle user state
        if ($state = $this->stateHandler->getState($chatId)) {
            $this->handleUserState($chatId, $text, $state);
            return;
        }

        // Handle regular commands
        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chatId, $user);
                break;
            case '/language':
                $this->sendLanguageSelection($chatId);
                break;
            case '/balance':
                $this->sendBalanceInfo($chatId);
                break;
            case '/help':
                $this->sendHelp($chatId, $user['language']);
                break;
            case '/support':
                $this->startSupportChat($chatId);
                break;
            case '/invite':
                $this->sendInviteLink($chatId);
                break;
            default:
                $this->handleServiceInput($chatId, $text);
                break;
        }
    }

    private function handleCallback($callbackQuery) {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'];

        $this->telegram->answerCallbackQuery([
            'callback_query_id' => $callbackQuery['id']
        ]);

        if (strpos($data, 'lang_') === 0) {
            $language = substr($data, 5);
            $this->changeLanguage($chatId, $language);
            return;
        }

        if (strpos($data, 'admin_') === 0) {
            $this->adminPanel->handleCallback($callbackQuery);
            return;
        }

        switch ($data) {
            case 'menu_ai':
                $this->sendAiMenu($chatId);
                break;
            case 'menu_download':
                $this->sendDownloadMenu($chatId);
                break;
            case 'menu_search':
                $this->sendSearchMenu($chatId);
                break;
            case 'menu_info':
                $this->sendInfoMenu($chatId);
                break;
            case 'menu_entertainment':
                $this->sendEntertainmentMenu($chatId);
                break;
            case 'menu_tools':
                $this->sendToolsMenu($chatId);
                break;
            case 'menu_account':
                $this->sendAccountInfo($chatId);
                break;
            case 'menu_balance':
                $this->sendBalanceMenu($chatId);
                break;
            case 'menu_support':
                $this->startSupportChat($chatId);
                break;
            case 'menu_guide':
                $this->sendGuide($chatId);
                break;
            default:
                $this->handleServiceCallback($chatId, $data);
                break;
        }
    }

    private function sendWelcomeMessage($chatId, $user) {
        $coins = $this->db->getUserCoins($chatId);
        $lang = $user['language'] ?? 'en';
        $welcomeText = sprintf($this->languages[$lang]['welcome'], $coins);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🤖 AI Services', 'callback_data' => 'menu_ai'],
                    ['text' => '⬇️ Download', 'callback_data' => 'menu_download']
                ],
                [
                    ['text' => '🔍 Search', 'callback_data' => 'menu_search'],
                    ['text' => 'ℹ️ Information', 'callback_data' => 'menu_info']
                ],
                [
                    ['text' => '🎮 Entertainment', 'callback_data' => 'menu_entertainment'],
                    ['text' => '🛠️ Tools', 'callback_data' => 'menu_tools']
                ],
                [
                    ['text' => '👤 Account', 'callback_data' => 'menu_account'],
                    ['text' => '💰 Balance', 'callback_data' => 'menu_balance']
                ],
                [
                    ['text' => '🎫 Support', 'callback_data' => 'menu_support'],
                    ['text' => '📖 Guide', 'callback_data' => 'menu_guide']
                ],
                [
                    ['text' => '🌐 Language', 'callback_data' => 'menu_language']
                ]
            ]
        ];
        
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $welcomeText,
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ]);
    }

    private function sendLanguageSelection($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🇺🇸 English', 'callback_data' => 'lang_en'],
                    ['text' => '🇮🇷 فارسی', 'callback_data' => 'lang_fa']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Please select your language:\nلطفا زبان خود را انتخاب کنید:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function changeLanguage($chatId, $language) {
        $this->db->updateUserLanguage($chatId, $language);
        $text = $this->languages[$language]['language_changed'];
        
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);

        $user = $this->db->getUser($chatId);
        $this->sendWelcomeMessage($chatId, $user);
    }

    private function handleServiceInput($chatId, $text) {
        $state = $this->stateHandler->getState($chatId);
        if (!$state) return;

        $service = $state['data']['service'];
        $coins = $this->db->getUserCoins($chatId);
        
        if ($coins < $this->config['coin_per_api_call']) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $this->languages[$user['language']]['insufficient_coins']
            ]);
            return;
        }

        try {
            $response = $this->apiHandler->makeRequest($service, $text);
            if ($response && isset($response['results'])) {
                $this->db->updateUserCoins($chatId, -1, 'service_use', "Used {$service} service");
                $this->sendServiceResponse($chatId, $service, $response);
            } else {
                throw new Exception('Invalid response from service');
            }
        } catch (Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $this->languages[$user['language']]['service_error']
            ]);
        }

        $this->stateHandler->clearState($chatId);
    }

    private function sendServiceResponse($chatId, $service, $response) {
        $results = $response['results'];

        switch ($service) {
            case 'ChatGPT':
            case 'GPT4o':
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $results['text'] ?? 'No response'
                ]);
                break;

            case 'PollinationsAi':
            case 'MakePhotoAi':
                if (isset($results['image'])) {
                    $this->telegram->sendPhoto([
                        'chat_id' => $chatId,
                        'photo' => $results['image'],
                        'caption' => 'Generated image'
                    ]);
                }
                break;

            case 'TextToVoice':
                if (isset($results['audio'])) {
                    $this->telegram->sendAudio([
                        'chat_id' => $chatId,
                        'audio' => $results['audio'],
                        'caption' => 'Generated audio'
                    ]);
                }
                break;

            default:
                if (isset($results['text'])) {
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $results['text']
                    ]);
                }
                if (isset($results['file'])) {
                    $this->telegram->sendDocument([
                        'chat_id' => $chatId,
                        'document' => $results['file'],
                        'caption' => 'Downloaded file'
                    ]);
                }
                break;
        }
    }

    private function sendAiMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🤖 ChatGPT', 'callback_data' => 'service_ChatGPT'],
                    ['text' => '🔥 GPT4o', 'callback_data' => 'service_GPT4o']
                ],
                [
                    ['text' => '🎨 Image Generator', 'callback_data' => 'service_PollinationsAi'],
                    ['text' => '🖼️ 3D Generator', 'callback_data' => 'service_MakePhotoAi']
                ],
                [
                    ['text' => '🗣️ Text to Voice', 'callback_data' => 'service_TextToVoice']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select an AI service:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendDownloadMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📸 Instagram', 'callback_data' => 'service_Instagram'],
                    ['text' => '🎥 YouTube', 'callback_data' => 'service_YouTube']
                ],
                [
                    ['text' => '📌 Pinterest', 'callback_data' => 'service_Pinterest'],
                    ['text' => '🎨 FreePik', 'callback_data' => 'service_FreePik']
                ],
                [
                    ['text' => '🎵 Spotify', 'callback_data' => 'service_Spotify'],
                    ['text' => '🎧 SoundCloud', 'callback_data' => 'service_SoundCloud']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select a download service:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendSearchMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🎥 Aparat', 'callback_data' => 'service_Aparat'],
                    ['text' => '🎨 FreePik', 'callback_data' => 'service_FreePikSearch']
                ],
                [
                    ['text' => '📺 Uptvs', 'callback_data' => 'service_Uptvs'],
                    ['text' => '📚 Wikipedia', 'callback_data' => 'service_Wikipedia']
                ],
                [
                    ['text' => '🛒 Digikala', 'callback_data' => 'service_Digikala'],
                    ['text' => '🎵 Ahangify', 'callback_data' => 'service_Ahangify']
                ],
                [
                    ['text' => '🎧 Shazam', 'callback_data' => 'service_Shazam'],
                    ['text' => '▶️ YouTube', 'callback_data' => 'service_YouTubeSearch']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select a search service:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendInfoMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📦 Tipax Tracking', 'callback_data' => 'service_TipaxInfo'],
                    ['text' => '⚽ Football Info', 'callback_data' => 'service_Footballi']
                ],
                [
                    ['text' => '💰 Gold Price', 'callback_data' => 'service_GoldRate'],
                    ['text' => '💳 Wallet Check', 'callback_data' => 'service_WalletChecker']
                ],
                [
                    ['text' => '🪙 Crypto Price', 'callback_data' => 'service_DigitalCurrencyRate'],
                    ['text' => '💱 Currency Rate', 'callback_data' => 'service_ExchangeRate']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select an information service:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendEntertainmentMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❓ Ready Answer', 'callback_data' => 'service_ReadyAnswer'],
                    ['text' => '😂 Joke', 'callback_data' => 'service_4Jok']
                ],
                [
                    ['text' => '📝 Poems', 'callback_data' => 'service_Horoscope']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select an entertainment service:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendToolsMenu($chatId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🔗 URL Shortener', 'callback_data' => 'service_SiteShot'],
                    ['text' => '☁️ Weather', 'callback_data' => 'service_Weather']
                ],
                [
                    ['text' => '🔒 Captcha Gen', 'callback_data' => 'service_CreateCaptcha'],
                    ['text' => '🌐 Translator', 'callback_data' => 'service_GoogleTranslate']
                ],
                [
                    ['text' => '💳 Card Check', 'callback_data' => 'service_CardNumberInquiry']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu_main']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Select a tool:',
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function sendAccountInfo($chatId) {
        $user = $this->db->getUser($chatId);
        $text = "👤 Your Account Information\n\n";
        $text .= "📃 Name: {$user['first_name']}\n";
        $text .= "📝 Username: @{$user['username']}\n";
        $text .= "🆔 User ID: {$user['telegram_id']}\n";
        $text .= "┈┅┈┅┈┅┈┅┈┅┈┅┈┈┅┈┅┈┅┈┅┈┅┈┅┈\n";
        $text .= "💎 Balance: {$user['coins']} coins\n";
        $text .= "👥 Referrals: " . $this->getReferralCount($chatId) . "\n";
        $text .= "┈┅┈┅┈┅┈┅┈┅┈┅┈┈┅┈┅┈┅┈┅┈┅┈┅┈\n";
        $text .= "📆 Date: " . date('Y/m/d') . "\n";
        $text .= "⏱️ Time: " . date('H:i:s');

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    private function getReferralCount($userId) {
        $stmt = $this->db->pdo->prepare("
            SELECT COUNT(*) FROM users WHERE referred_by = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    private function sendBalanceMenu($chatId) {
        $coins = $this->db->getUserCoins($chatId);
        $text = "💰 Your Balance: {$coins} coins\n\n";
        $text .= "Select a method to increase your balance:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💳 Purchase Coins', 'callback_data' => 'balance_purchase'],
                    ['text' => '👥 Invite Friends', 'callback_data' => 'balance_invite']
                ]
            ]
        ];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ]);
    }

    private function startSupportChat($chatId) {
        $this->stateHandler->setState($chatId, 'waiting_for_support');
        
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => 'Please enter your message for support:'
        ]);
    }

    private function handleSupportMessage($chatId, $text) {
        $ticketId = $this->db->createSupportTicket($chatId, $text);
        
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Your support ticket #{$ticketId} has been created. We will respond shortly."
        ]);

        $this->stateHandler->clearState($chatId);
    }

    private function sendInviteLink($chatId) {
        $user = $this->db->getUser($chatId);
        $inviteLink = "https://t.me/{$this->config['bot_username']}?start={$user['referral_code']}";
        
        $text = "🎉 Share this link with your friends:\n{$inviteLink}\n\n";
        $text .= "You will receive {$this->config['coin_per_invite']} coins for each friend who joins!";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }

    private function handleServiceCallback($chatId, $data) {
        if (strpos($data, 'service_') === 0) {
            $service = substr($data, 8);
            $this->stateHandler->setState($chatId, 'waiting_for_input', ['service' => $service]);
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "You are now using the {$service} service. Please provide your input:"
            ]);
        }
    }
}

$bot = new AmirGeekBot();
$bot->handleWebhook();
