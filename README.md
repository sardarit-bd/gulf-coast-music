# Gulf Music

A Laravel-based API for a music application focused on the Gulf region.

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/gulfmusic.git
    cd gulfmusic
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Set up your environment:**
    ```bash
    cp .env.example .env
    ```
    *Update your `.env` file with your database credentials and other settings.*

4.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

6.  **(Optional) Seed the database:**
    ```bash
    php artisan db:seed
    ```

7.  **Start the development servers:**
    ```bash
    # In one terminal, for the Laravel backend
    php artisan serve

    # In another terminal, for the frontend assets
    npm run dev
    ```

## API Endpoints

The following API endpoints are available:

*   `POST /api/register`: Register a new user.
*   `POST /api/login`: Log in a user and receive a JWT token.
*   `POST /api/logout`: Log out the currently authenticated user.
*   `GET /api/user`: Get the currently authenticated user's information.

## Testing

To run the test suite, use the following command:

```bash
php artisan test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.