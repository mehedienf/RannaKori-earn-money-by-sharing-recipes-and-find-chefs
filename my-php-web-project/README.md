# My PHP Web Project

This is a dynamic website built using HTML, CSS, JavaScript, and PHP. The project follows a structured MVC (Model-View-Controller) architecture to separate concerns and enhance maintainability.

## Project Structure

```
my-php-web-project
├── public
│   ├── index.php          # Entry point of the application
│   ├── css
│   │   └── style.css      # Styles for the website
│   ├── js
│   │   └── main.js        # JavaScript functionality
│   └── assets             # Directory for static assets (images, fonts, etc.)
├── src
│   ├── config
│   │   └── config.php     # Configuration settings (e.g., database connection)
│   ├── controllers
│   │   └── HomeController.php # Controller for handling home page requests
│   ├── models
│   │   └── User.php       # User model for database interactions
│   └── views
│       ├── layouts
│       │   └── main.php   # Main layout for views
│       └── home.php       # Home page view
├── vendor                  # Third-party libraries and dependencies
├── .env                    # Environment variables (e.g., database credentials)
├── composer.json           # Composer configuration file
└── README.md               # Project documentation
```

## Setup Instructions

1. **Clone the Repository**: 
   Clone this repository to your local machine using:
   ```
   git clone <repository-url>
   ```

2. **Install Dependencies**: 
   Navigate to the project directory and run:
   ```
   composer install
   ```
   This will install all the required dependencies listed in `composer.json`.

3. **Configure Environment Variables**: 
   Create a `.env` file in the root directory and set your environment variables, such as database credentials.

4. **Run the Application**: 
   You can run the application using a local server. If you have PHP installed, you can use the built-in server:
   ```
   php -S localhost:8000 -t public
   ```
   Open your browser and navigate to `http://localhost:8000` to view the application.

## Usage

- The entry point of the application is `public/index.php`.
- The CSS styles are located in `public/css/style.css`.
- Client-side JavaScript functionality can be found in `public/js/main.js`.
- The application follows the MVC pattern, with controllers in `src/controllers`, models in `src/models`, and views in `src/views`.

## Contributing

Feel free to submit issues or pull requests if you would like to contribute to this project.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.