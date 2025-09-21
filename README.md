# Gulf Music

A Laravel-based API for a music application focused on the Gulf region. This application allows users to manage artists, events, news, and venues. It uses JWT for authentication and includes features for admin management and Printify integration.

## Features

*   **User Management:** User registration and authentication with JWT.
*   **Role-Based Access Control:** Different roles for Admin, Artist, Journalist, and Venue owners.
*   **Artist Management:** CRUD operations for artists, including managing their photos and songs.
*   **Journalist & News Management:** CRUD operations for journalists and news articles, including news photos.
*   **Venue & Event Management:** CRUD operations for venues and events.
*   **Admin Panel:**
    *   Activate pending user profiles.
*   **Printify Integration:**
    *   View shop details.
    *   Manage products (list and add).
    *   View orders.

## Tech Stack

*   **Backend:** PHP 8.2, Laravel 12
*   **Database:** MySQL
*   **Authentication:** `php-open-source-saver/jwt-auth`
*   **E-commerce:** `garissman/printify`
*   **Frontend:** Vite

## Prerequisites

*   PHP >= 8.2
*   Composer
*   Node.js & npm
*   MySQL

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/ahsan-alam-500/gulf_official.git
    cd gulfmusic
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install frontend dependencies:**
    ```bash
    npm install
    ```

4.  **Set up your environment:**
    ```bash
    cp .env.example .env
    ```
    *Update your `.env` file with your database credentials, JWT secret, and Printify API token.*

5.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

6.  **Generate JWT secret:**
    ```bash
    php artisan jwt:secret
    ```

7.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

8.  **(Optional) Seed the database:**
    ```bash
    php artisan db:seed
    ```

9.  **Start the development servers:**
    ```bash
    # For the Laravel backend
    php artisan serve

    # For the frontend assets
    npm run dev
    ```

## API Endpoints

All endpoints are prefixed with `/api`.

### Authentication

| Method | Endpoint      | Description                  |
| :----- | :------------ | :--------------------------- |
| `POST` | `/register`   | Register a new user.         |
| `POST` | `/login`      | Log in and get a JWT token.  |
| `POST` | `/logout`     | Log out the user.            |
| `POST` | `/refresh`    | Refresh a JWT token.         |
| `GET`  | `/me`         | Get authenticated user's info. |

### Admin

| Method | Endpoint            | Description                  |
| :----- | :------------------ | :--------------------------- |
| `GET`  | `/user/pending`     | Get pending user profiles.   |
| `GET`  | `/user/activate/{id}` | Activate a user profile.     |

### Artists

| Method | Endpoint            | Description                  |
| :----- | :------------------ | :--------------------------- |
| `GET`  | `/artists`          | Get all artists.             |
| `GET`  | `/artists/{artist}` | Get a specific artist.       |
| `POST` | `/artists`          | Create a new artist.         |
| `PUT`  | `/artists/{artist}` | Update an artist.            |
| `DELETE`| `/artists/{artist}` | Delete an artist.            |

### Artist Photos

| Method | Endpoint        | Description              |
| :----- | :-------------- | :----------------------- |
| `GET`  | `/photos`       | Get all artist photos.   |
| `GET`  | `/photos/{photo}` | Get a specific photo.    |
| `POST` | `/photos`       | Upload a new photo.      |
| `PUT`  | `/photos/{photo}` | Update a photo.          |
| `DELETE`| `/photos/{photo}` | Delete a photo.          |

### Artist Songs

| Method | Endpoint      | Description            |
| :----- | :------------ | :--------------------- |
| `GET`  | `/songs`      | Get all artist songs.  |
| `GET`  | `/songs/{song}` | Get a specific song.   |
| `POST` | `/songs`      | Upload a new song.     |
| `PUT`  | `/songs/{song}` | Update a song.         |
| `DELETE`| `/songs/{song}` | Delete a song.         |

### Journalists

| Method | Endpoint                | Description                      |
| :----- | :---------------------- | :------------------------------- |
| `GET`  | `/journalists`          | Get all journalists.             |
| `GET`  | `/journalists/{journalist}` | Get a specific journalist.       |
| `POST` | `/journalists`          | Create a new journalist.         |
| `PUT`  | `/journalists/{journalist}` | Update a journalist.             |
| `DELETE`| `/journalists/{journalist}` | Delete a journalist.             |

### News

| Method | Endpoint      | Description                |
| :----- | :------------ | :------------------------- |
| `GET`  | `/news`       | Get all news articles.     |
| `GET`  | `/news/{news}`  | Get a specific article.    |
| `POST` | `/news`       | Create a new article.      |
| `PUT`  | `/news/{news}`  | Update an article.         |
| `DELETE`| `/news/{news}`  | Delete an article.         |

### News Photos

| Method | Endpoint                  | Description                    |
| :----- | :------------------------ | :----------------------------- |
| `GET`  | `/news/{news}/photos`     | Get photos for a news article. |
| `POST` | `/news/{news}/photos`     | Add a photo to an article.     |
| `PATCH`| `/news/{news}/photos/{photo}` | Update a photo.                |
| `DELETE`| `/news/{news}/photos/{photo}` | Delete a photo.                |

### Venues

| Method | Endpoint          | Description                |
| :----- | :---------------- | :------------------------- |
| `GET`  | `/venues`         | Get all venues.            |
| `GET`  | `/venues/{venue}` | Get a specific venue.      |
| `POST` | `/venues`         | Create a new venue.        |
| `PUT`  | `/venues/{venue}` | Update a venue.            |
| `DELETE`| `/venues/{venue}` | Delete a venue.            |

### Events

| Method | Endpoint          | Description                |
| :----- | :---------------- | :------------------------- |
| `GET`  | `/events`         | Get all events.            |
| `GET`  | `/events/{event}` | Get a specific event.      |
| `POST` | `/events`         | Create a new event.        |
| `PUT`  | `/events/{event}` | Update an event.           |
| `DELETE`| `/events/{event}` | Delete an event.           |

### Printify

| Method | Endpoint              | Description          |
| :----- | :-------------------- | :------------------- |
| `GET`  | `/printify/shop`      | Get shop details.    |
| `GET`  | `/printify/products`  | Get all products.    |
| `POST` | `/printify/products`  | Add a new product.   |
| `GET`  | `/printify/orders`    | Get all orders.      |

## Testing

To run the test suite, use the following command:

```bash
php artisan test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.