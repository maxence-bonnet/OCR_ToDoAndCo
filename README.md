# <p align="center">ToDo&Co</p>
<p align="center">Project 8 of the PHP / Symfony application developer course at OpenClassrooms</p>

## Requirements

- [MariaDB 10.4.0+](https://go.mariadb.com/) or [MySQL 5.7.0+](https://www.mysql.com/)

- [PHP 8.0.0+](https://www.php.net/) 

- [Composer 2.1+](https://getcomposer.org/) 

- [Symfony 6.0+](https://symfony.com/)

- [Yarn 1.20+](https://yarnpkg.com/) or [npm](https://www.npmjs.com/package/npm)

---

#### For practical reasons, I chose to use :

- [XAMPP 8.0.3](https://www.apachefriends.org/fr/index.html) -> manage the database more easily (on/off + phpMyAdmin)

- [Symfony cli 4.28.1](https://symfony.com/download) -> more command lines + improved local web server

---


## Install (local developpement purpose)

### 1. Clone the repository


```
git clone https://github.com/maxence-bonnet/OCR_ToDoAndCo.git
```

or [`download .zip`](https://github.com/maxence-bonnet/OCR_ToDoAndCo/archive/refs/heads/master.zip) in case you don't have git installed

---

### 2. Install depencies via composer

In project folder :

Retrieve PHP/Symfony dependencies
```
composer install
```

Retrieve front-end dependencies and build assets
```
yarn install && yarn dev
```
---

### 3. Configure the environment

Update `.env` file or create a new `.env.local` file and override / write + fill in these lines : 

```env
###> doctrine/doctrine-bundle ###
# DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
DATABASE_URL="postgresql://symfony:ChangeMe@127.0.0.1:5432/app?serverVersion=13&charset=utf8"
###< doctrine/doctrine-bundle ###
```
Do not forget to encode special characters

#### here is a schema + example for the `.env.local` :

```env.local
###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://my_user_identifier:my_user_pass@127.0.0.1:3306/my_db_name?serverVersion=my_db_version"
# exemple : DATABASE_URL="mysql://root:@127.0.0.1:3306/bilemo_api?serverVersion=mariadb-10.4.18"
###< doctrine/doctrine-bundle ###
```

#### NB: I recommend creating the `.env.local` file (ignored in commits) rather than using the `.env` to avoid committing any sensitive data

---

### 4. Generating database

you can use `symfony console` instead of `php bin/console` if you have [Symfony cli](https://symfony.com/download) installed

#### a. Create database

```
php bin/console doctrine:database:create
```

#### b. Create tables structures from migrations

```
php bin/console doctrine:migrations:migrate
```
or

```
php bin/console doctrine:schema:update -f
```

#### c. Get demonstration data with doctrine data fixtures (optional)

```
php bin/console doctrine:fixtures:load
```

---

### 5. Run your local server

either with :

```
php -S 127.0.0.1:8000 -t public
```

or with symfony-cli :

```
symfony server:start -d
```
-d for --daemon flag (optional) disables verbose mode and runs server in the background so you can keep using your terminal

notice that you can also [simulate TLS](https://symfony.com/doc/current/setup/symfony_server.html#enabling-tls) thanks to symfony web server:

```
symfony server:ca:install
```

---
## Global features overview
anonymous User :
  - Register
  - Authenticate

regular User :
  - Everything an anonymous User can do
  - Get Tasks index page
  - Get one Task page
  - Edit a Task
  - Post a new Task
  - Delete its own Tasks

Admin :
  - Everything a regular User can do
  - Get Users index page
  - Get one User page
  - Edit a User
  - Post (register) a new User
  - Delete Users
  - Delete anonymous Tasks (in fact, it is allowed to delete any task for consistency reasons)

## Website preview 

You can check the online demonstration [here](https://todoandco.maxence-bonnet.fr/)

Feel free to connect (with fixtures) or register, and try it. Data will be reset 3 times a day.

## Testing the application

Create the dedicated test database 

```
php bin/console doctrine:schema:update -f --env=test
```

Load fixtures
```
php bin/console doctrine:fixtures:load
```

Run 
```
php bin/phpunit
```

add the flag `--coverage-html coverage` to retrieve a full test coverage report, wich which will be created in the folder `/coverage` at project root

add the flag `--filter testWhatIWant` to run only the test named `testWhatIWant`

Thanks to the DAMADoctrineTestBundle, all changes to the database are rolled back when a test completes. This means that all the application tests begin with the same database contents.

## Code Analysis

the project is monitored by these two analysis tools : Symfony Insight & Codacy


[![SymfonyInsight](https://insight.symfony.com/projects/8eb64bf7-10ea-4567-b210-5c19163da852/small.svg)](https://insight.symfony.com/projects/8eb64bf7-10ea-4567-b210-5c19163da852)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7a4f8c16a20142718284351695912537)](https://www.codacy.com/gh/maxence-bonnet/OCR_ToDoAndCo/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=maxence-bonnet/OCR_ToDoAndCo&amp;utm_campaign=Badge_Grade)

## Contribute

See [Contribute to the project](https://github.com/maxence-bonnet/OCR_ToDoAndCo/#readme)
