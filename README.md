AsukaTG
=======

A Telegram webhook bot written in PHP.

# Installation

Install [Composer](https://getcomposer.org/)

````bash
git clone https://github.com/TheReverend403/AsukaTG
cd AsukaTG
composer install
cp config.ini.dist config.ini # And edit config.ini. Get a key from @BotFather if you need one.
````

Set up a webserver in accordance with https://core.telegram.org/bots/api#setwebhook so that the URL you set in config.php is a publicly accessible (but hide it in a folder only you know about.)

eg. https://example.com/2447b56339fa202b9e8df1c3d73e6129fd7364aca92cb21fcd44db70da6045c5/webhook.php

Run the webhook script in the root of this repo like so:

    php webhook.php --set