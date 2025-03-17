<?php
return [
    'bot_token' => '7730405713:AAHNWRfYCYa1X8BMo-hLcakHnyFRgffLciA',
    'bot_username' => 'Amirgeekbot',
    'admin_ids' => [
        7440711416
    ],
    'webhook_url' => 'https://your-domain.com/bot/bot.php', // این را با آدرس دامنه خود جایگزین کنید
    'database' => [
        'host' => 'localhost',
        'dbname' => 'YOUR_CPANEL_DB_NAME', // نام دیتابیس در Cpanel
        'username' => 'YOUR_CPANEL_DB_USER', // نام کاربری دیتابیس
        'password' => 'YOUR_CPANEL_DB_PASSWORD' // رمز عبور دیتابیس
    ],
    'languages' => ['en', 'fa'],
    'default_language' => 'fa',
    'coin_per_invite' => 1,
    'coin_per_api_call' => 1,
    'services' => [
        'ai' => [
            'ChatGPT' => 'https://open.wiki-api.ir/apis-1/ChatGPT?q=',
            'GPT4o' => 'https://open.wiki-api.ir/apis-1/ChatGPT-4o?q=',
            'PollinationsAi' => 'https://open.wiki-api.ir/apis-1/PollinationsAi?q=',
            'MakePhotoAi' => 'https://open.wiki-api.ir/apis-1/MakePhotoAi?q=',
            'TextToVoice' => 'https://open.wiki-api.ir/apis-1/TextToVoice?text='
        ],
        'download' => [
            'Instagram' => 'https://open.wiki-api.ir/apis-1/InstagramDownloader?url=',
            'YouTube' => 'https://open.wiki-api.ir/apis-2/YouTubeDownloade?url=',
            'Pinterest' => 'https://open.wiki-api.ir/apis-2/pintrestDownload?link=',
            'FreePik' => 'https://open.wiki-api.ir/apis-1/FreePikDownloader?img=',
            'Spotify' => 'https://open.wiki-api.ir/apis-2/DownloadSpotify?link=',
            'SoundCloud' => 'https://open.wiki-api.ir/apis-1/SoundcloudDownloader?url='
        ],
        'search' => [
            'Aparat' => 'https://open.wiki-api.ir/apis-1/AparatSearch?q=',
            'FreePik' => 'https://open.wiki-api.ir/apis-1/FreePikSearch?q=',
            'Wikipedia' => 'https://open.wiki-api.ir/apis-1/Wikipedia?q=',
            'Digikala' => 'https://open.wiki-api.ir/apis-1/SearchDigikala?q=',
            'YouTube' => 'https://open.wiki-api.ir/apis-1/SearchYouTube?q=',
            'SoundCloud' => 'https://open.wiki-api.ir/apis-1/SoundcloudeSearch?q='
        ],
        'info' => [
            'TipaxInfo' => 'https://open.wiki-api.ir/apis-1/TipaxInfo?code=',
            'Footballi' => 'https://open.wiki-api.ir/apis-1/Footballi',
            'GoldRate' => 'https://open.wiki-api.ir/apis-1/GoldRate',
            'WalletChecker' => 'https://open.wiki-api.ir/apis-1/WalletChecker?wallet_type=',
            'DigitalCurrencyRate' => 'https://open.wiki-api.ir/apis-1/DigitalCurrencyRate',
            'ExchangeRate' => 'https://open.wiki-api.ir/apis-1/ExchangeRate'
        ],
        'tools' => [
            'SiteShot' => 'https://open.wiki-api.ir/apis-1/SiteShot?url=',
            'Weather' => 'https://open.wiki-api.ir/apis-1/Weather?city=',
            'CreateCaptcha' => 'https://open.wiki-api.ir/apis-1/CreateCaptcha',
            'GoogleTranslate' => 'https://open.wiki-api.ir/apis-1/GoogleTranslate?text=',
            'CardNumberInquiry' => 'https://open.wiki-api.ir/apis-1/CardNumberInquiry?card='
        ]
    ],
    'admin_panel' => [
        'settings' => true,
        'statistics' => true,
        'manage_admins' => true,
        'broadcast_message' => true,
        'personal_message' => true,
        'coin_donation' => true
    ]
];
