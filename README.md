# Symfony-Album-Photo
Albums viewer written using Symfony v5 framework

# Key Features
* Album creation,
* upload photo,
* organize album (automatic|manual sorting, comments, delete, ...),
* bandwidth and speed optimization (reduce photo size according to the user screen),
* responsive (swipe gestures, css, ...),
* map to see album by country,
* all functions are API accessible (JWT authentication)
* Single Page application (only for the album photos part of the website)

# Installation
Install requirement
```
web server
mysql / mariaDB
php
composer
```

Modify config file
```
copy .env.default to .env
edit .env file and set variable DATABASE_URL and PHOTO_STORAGE (directory where you want to store your photo)
```

Create certificate for JavaWebToken (used for authentication on internal API)
```bash
mkdir "/var/www/config/jwt"
openssl genrsa  -out /var/www/config/jwt/private.pem  4096
openssl rsa -pubout -in /var/www/config/jwt/private.pem -out /var/www/config/jwt/public.pem
chown www-data /var/www/config/jwt/private.pem
chown www-data /var/www/config/jwt/public.pem
```

Install dependencies
```bash
composer install
```

Create tables
```bash
bin/console doctrine:schema:update --force
```

Configure your Web server to serve the directory

# ScreenShots

![Frontend](https://raw.githubusercontent.com/air01a/Symfony-Album-Photo/master/doc/images/Screenshot%202020-09-17%20at%2010.32.30.png)
# ----------------------
![Frontend](https://github.com/air01a/Symfony-Album-Photo/blob/master/doc/images/Screenshot%202020-09-17%20at%2010.34.38.png?raw=true)
# ----------------------
![Frontend](https://github.com/air01a/Symfony-Album-Photo/blob/master/doc/images/Screenshot%202020-09-17%20at%2010.36.14.png?raw=true)
# ----------------------
![Frontend](https://github.com/air01a/Symfony-Album-Photo/blob/master/doc/images/Screenshot%202020-09-17%20at%2010.36.35.png?raw=true)
# ----------------------
![Frontend](https://github.com/air01a/Symfony-Album-Photo/blob/master/doc/images/Screenshot%202020-09-17%20at%2010.36.55.png?raw=true)
# ----------------------
