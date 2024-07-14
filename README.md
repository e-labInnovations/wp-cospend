# WP Cospend

WP Cospend is a WordPress plugin for managing expenses and transactions, similar to NextCloud Cospend. It allows users to create and manage expenses, transactions, and attachments, and offers REST API endpoints for easy integration with other applications.

## Features

- Manage users, groups, expenses, and transactions.
- REST API endpoints for CRUD operations.
- Database management for plugin data.
- Custom post types and taxonomies.

## Installation

1. Download the plugin files and upload them to your WordPress installation's `wp-content/plugins` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. The necessary database tables will be created automatically on activation.

## Usage

1. Navigate to the WP Cospend settings page to configure the plugin.
2. Use the REST API endpoints to manage users, groups, expenses, and transactions programmatically.

## REST API Endpoints

- `GET /wp-json/wp-cospend/v1/users`: Get all users.
- `POST /wp-json/wp-cospend/v1/users`: Create a new user.
- `PUT /wp-json/wp-cospend/v1/users/{id}`: Update a user.
- `DELETE /wp-json/wp-cospend/v1/users/{id}`: Delete a user.

## Development

To contribute to the development of this plugin, follow these steps:

1. Fork the repository on GitHub.
2. Clone your forked repository locally.
3. Create a new branch for your feature or bug fix.
4. Make your changes and commit them.
5. Push your changes to your forked repository.
6. Submit a pull request to the main repository.

## License

This plugin is licensed under the GPL-2.0+ license. See the `LICENSE` file for more details.

## Credits

Developed by Mohammed Ashad. Inspired by NextCloud Cospend.

[GitHub Profile](https://github.com/e-labInnovations)
