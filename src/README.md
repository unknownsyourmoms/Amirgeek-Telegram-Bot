# Amir Geek's Collaborative Bot | ربات همه‌کاره امیر گیک

[English](#english) | [فارسی](#persian)

<div id="persian" dir="rtl">

## 🎯 معرفی
ربات تلگرام چند منظوره با قابلیت‌های متنوع شامل هوش مصنوعی، دانلود، جستجو و ابزارهای کاربردی. این ربات با PHP نوشته شده و از API‌های مختلف برای ارائه خدمات استفاده می‌کند.

## ⚡️ امکانات
- 🤖 **خدمات هوش مصنوعی**
  - چت با ChatGPT
  - تولید تصویر با هوش مصنوعی
  - تبدیل متن به صدا
- ⬇️ **دانلود از شبکه‌های اجتماعی**
  - اینستاگرام
  - یوتیوب
  - پینترست
  - اسپاتیفای
  - ساندکلود
- 🔍 **جستجو**
  - آپارات
  - فری‌پیک
  - ویکی‌پدیا
  - دیجی‌کالا
  - یوتیوب
- ℹ️ **اطلاعات**
  - قیمت ارز و طلا
  - اطلاعات رمزارزها
  - پیگیری مرسولات تیپاکس
  - اطلاعات فوتبال
- 🛠️ **ابزارها**
  - اسکرین‌شات از سایت
  - اطلاعات آب و هوا
  - تولید کپچا
  - مترجم گوگل
  - بررسی شماره کارت

## 🔧 پیش‌نیازها
- PHP 7.4 یا بالاتر
- SSL فعال روی دامنه
- دسترسی به Cpanel
- دسترسی به MySQL
- توکن ربات تلگرام

## 💻 نصب و راه‌اندازی

### 1. آپلود فایل‌ها
1. یک پوشه `bot` در `public_html` بسازید
2. فایل‌های پروژه را آپلود کنید
3. مجوزهای فایل را تنظیم کنید:
   ```bash
   chmod 755 bot/
   chmod 644 bot/*.php
   chmod 644 bot/.htaccess
   ```

### 2. تنظیم دیتابیس
1. در Cpanel یک دیتابیس MySQL بسازید
2. یک کاربر MySQL ایجاد کنید
3. اطلاعات را در `config.php` وارد کنید

### 3. تنظیم ربات
1. توکن را از @BotFather دریافت کنید
2. اطلاعات را در `config.php` وارد کنید:
   ```php
   'bot_token' => 'YOUR_BOT_TOKEN',
   'bot_username' => 'YOUR_BOT_USERNAME',
   'webhook_url' => 'https://your-domain.com/bot/bot.php'
   ```

### 4. نصب Composer
```bash
cd public_html/bot
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### 5. تنظیم وب‌هوک
این URL را در مرورگر باز کنید:
```
https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook?url=https://your-domain.com/bot/bot.php
```

## 👥 مدیریت
- دستور `/start` برای شروع
- دستور `/admin` برای پنل مدیریت
- دستور `/help` برای راهنمایی

## 🔒 امنیت
- محافظت از فایل‌های حساس با `.htaccess`
- ثبت خطاها در `logs/error.log`
- بررسی دسترسی‌های ادمین
- محدودیت درخواست‌ها

## 🤝 مشارکت
1. پروژه را Fork کنید
2. یک Branch جدید بسازید
3. تغییرات را Commit کنید
4. به Branch اصلی Push کنید
5. یک Pull Request ایجاد کنید

## 📝 لایسنس
این پروژه تحت لایسنس MIT منتشر شده است.

## 👨‍💻 توسعه‌دهنده
- **نام**: امیر گیک
- **تلگرام**: [@Amirgeekbot](https://t.me/Amirgeekbot)

</div>

<div id="english">

## 🎯 Introduction
A multifunctional Telegram bot with various capabilities including AI services, downloads, search, and utility tools. This bot is written in PHP and uses various APIs to provide services.

## ⚡️ Features
- 🤖 **AI Services**
  - ChatGPT Integration
  - AI Image Generation
  - Text to Voice Conversion
- ⬇️ **Social Media Downloads**
  - Instagram
  - YouTube
  - Pinterest
  - Spotify
  - SoundCloud
- 🔍 **Search Services**
  - Aparat
  - FreePik
  - Wikipedia
  - Digikala
  - YouTube
- ℹ️ **Information**
  - Currency & Gold Rates
  - Cryptocurrency Info
  - Tipax Tracking
  - Football Information
- 🛠️ **Tools**
  - Website Screenshot
  - Weather Information
  - Captcha Generation
  - Google Translate
  - Card Number Verification

## 🔧 Prerequisites
- PHP 7.4 or higher
- Active SSL on domain
- Cpanel access
- MySQL access
- Telegram Bot Token

## 💻 Installation

### 1. Upload Files
1. Create a `bot` folder in `public_html`
2. Upload project files
3. Set file permissions:
   ```bash
   chmod 755 bot/
   chmod 644 bot/*.php
   chmod 644 bot/.htaccess
   ```

### 2. Database Setup
1. Create a MySQL database in Cpanel
2. Create a MySQL user
3. Enter details in `config.php`

### 3. Bot Configuration
1. Get token from @BotFather
2. Enter details in `config.php`:
   ```php
   'bot_token' => 'YOUR_BOT_TOKEN',
   'bot_username' => 'YOUR_BOT_USERNAME',
   'webhook_url' => 'https://your-domain.com/bot/bot.php'
   ```

### 4. Install Composer
```bash
cd public_html/bot
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### 5. Set Webhook
Open this URL in browser:
```
https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook?url=https://your-domain.com/bot/bot.php
```

## 👥 Management
- `/start` command to begin
- `/admin` for admin panel
- `/help` for guidance

## 🔒 Security
- Protection of sensitive files with `.htaccess`
- Error logging in `logs/error.log`
- Admin access verification
- Request rate limiting

## 🤝 Contributing
1. Fork the project
2. Create a new branch
3. Commit your changes
4. Push to the main branch
5. Create a Pull Request

## 📝 License
This project is licensed under the MIT License.

## 👨‍💻 Developer
- **Name**: Amir Geek
- **Telegram**: [@Amirgeekbot](https://t.me/Amirgeekbot)

</div>
