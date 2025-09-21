# Gulf Music

A Laravel-based API for a music application focused on the Gulf region. This application allows users to manage artists, events, news, and venues. It uses JWT for authentication.

## Features

*   User registration and authentication (JWT).
*   CRUD operations for artists, including photos and songs.
*   CRUD operations for journalists and news, including news photos.
*   CRUD operations for venues.
*   CRUD operations for events.

## Technologies Used

*   PHP 8.2
*   Laravel 12
*   MySQL
*   JWT-Auth

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/ahsan-alam-500/gulf_official.git

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

### Authentication

*   `POST /api/register`: Register a new user.
*   `POST /api/login`: Log in a user and receive a JWT token.
*   `POST /api/logout`: Log out the currently authenticated user.
*   `GET /api/me`: Get the currently authenticated user's information.
*   `POST /api/refresh`: Refresh a JWT token.

### Artists

*   `GET /api/artists`: Get a list of artists.
*   `GET /api/artists/{artist}`: Get a specific artist.
*   `POST /api/artists`: Create a new artist.
*   `PUT/PATCH /api/artists/{artist}`: Update an artist.
*   `DELETE /api/artists/{artist}`: Delete an artist.

### Artist Photos

*   `GET /api/photos`: Get a list of artist photos.
*   `GET /api/photos/{photo}`: Get a specific artist photo.
*   `POST /api/photos`: Create a new artist photo.
*   `PUT/PATCH /api/photos/{photo}`: Update an artist photo.
*   `DELETE /api/photos/{photo}`: Delete an artist photo.

### Artist Songs

*   `GET /api/songs`: Get a list of artist songs.
*   `GET /api/songs/{song}`: Get a specific artist song.
*   `POST /api/songs`: Create a new artist song.
*   `PUT/PATCH /api/songs/{song}`: Update an artist song.
*   `DELETE /api/songs/{song}`: Delete an artist song.

### Journalists

*   `GET /api/journalists`: Get a list of journalists.
*   `GET /api/journalists/{journalist}`: Get a specific journalist.
*   `POST /api/journalists`: Create a new journalist.
*   `PUT/PATCH /api/journalists/{journalist}`: Update a journalist.
*   `DELETE /api/journalists/{journalist}`: Delete a journalist.

### News

*   `GET /api/news`: Get a list of news articles.
*   `GET /api/news/{news}`: Get a specific news article.
*   `POST /api/news`: Create a new news article.
*   `PUT/PATCH /api/news/{news}`: Update a news article.
*   `DELETE /api/news/{news}`: Delete a news article.

### News Photos

*   `GET /api/news/{news}/photos`: Get a list of photos for a news article.
*   `POST /api/news/{news}/photos`: Add a photo to a news article.
*   `PATCH /api/news/{news}/photos/{photo}`: Update a photo for a news article.
*   `DELETE /api/news/{news}/photos/{photo}`: Delete a photo from a news article.

### Venues

*   `GET /api/venues`: Get a list of venues.
*   `GET /api/venues/{venue}`: Get a specific venue.
*   `POST /api/venues`: Create a new venue.
*   `PUT/PATCH /api/venues/{venue}`: Update a venue.
*   `DELETE /api/venues/{venue}`: Delete a venue.

### Events

*   `GET /api/events`: Get a list of events.
*   `GET /api/events/{event}`: Get a specific event.
*   `POST /api/events`: Create a new event.
*   `PUT/PATCH /api/events/{event}`: Update an event.
*   `DELETE /api/events/{event}`: Delete an event.

## Testing

To run the test suite, use the following command:

```bash
php artisan test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
