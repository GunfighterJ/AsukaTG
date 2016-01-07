AsukaTG
=======

A Telegram webhook bot written in PHP.

# Installation

Requirements:

* [Composer](https://getcomposer.org/)
* A webserver configured for [Lumen](https://lumen.laravel.com/docs/installation#configuration)

````bash
git clone https://github.com/TheReverend403/AsukaTG
cd AsukaTG
composer install
cp .env.example .env # And edit .env. Get a key from @BotFather if you need one.
## ^^ Make sure you set APP_DEBUG to false and set APP_KEY to a random 32 character string
````

Now navigate to https://where.you.installed.asuka/{bot_api_key}/webhook/set to set your webhook URL.