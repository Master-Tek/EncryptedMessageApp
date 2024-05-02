# Encrypted Messaging App

The Encrypted Messaging App is a simple messaging platform built with Laravel. It allows users to securely send and receive messages, manage conversations, and schedule message cleanup tasks.

## Features

- User authentication (login/sign up)
- View sent and received messages with unread count
- Mark messages as read
- Delete messages
- Compose and send new messages
- Automatic message cleanup and deletion

## Message Encryption

Messages in the Encrypted Messaging App are stored in the database in an encrypted format using PHP's built-in Crypt AES algorithm. The messages are encrypted before saving to the database and decrypted during retrieval using PHP's Crypt facade.

### Why AES Encryption?

For most web applications, AES (Advanced Encryption Standard) is recommended due to its balance of speed and security. Laravel simplifies the use of AES encryption through its built-in encryption facilities, which by default use AES-256-CBC.


## Requirements

- Laravel: 10.48.9
- PHP: 8.3.4
- Node: 16.13.2
- MySQL: 8.2.0

## Setup Instructions

1. Clone the repository:

   ```bash
   git clone git@github.com:Master-Tek/EncryptedMessageApp.git
   ```

2. Install Composer dependencies:

   ```bash
   composer install
   ```

3. Install NPM dependencies:

   ```bash
   npm install
   ```

4. Run database migrations:

   ```bash
   php artisan migrate
   ```

5. Seed the database with initial data:

   ```bash
   php artisan db:seed --class=UserSeeder
   ```

6. Start the Laravel server:

   ```bash
   php artisan serve
   ```

7. Compile assets for development:

   ```bash
   npm run dev
   ```

## Running Tests

To run tests, use the following command:

```bash
php artisan test
```

## Scheduler Commands

The app includes scheduler commands for managing message cleanup and deletion:

- `php artisan messages:cleanup`: Marks read messages as deleted after 5 minutes.
- `php artisan messages:delete-trashed`: Deletes soft-deleted messages older than 7 days.
