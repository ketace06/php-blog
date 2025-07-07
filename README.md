# Daily Loot - Full Installation Guide

**Daily Loot** is a dynamic content platform built with PHP and Composer. It allows you to create, manage, and customize blogs, posts, articles... easily.

---

## Features

- Simple blog and post creation
- Composer-based PHP project structure
- Easy setup and configuration

---

## Requirements

Ensure the following are installed on your system:

- PHP (higher recommended)
- Composer (PHP dependency manager)
- Git (for cloning the repository)

---

## Installation Steps

### 1. Clone the Repository

#### Linux/macOS:
```bash
git clone https://github.com/ketace06/php-blog
cd php-blog
```

### 2. Setup the localhost:

Once all the dependencies have been installed, you can configure the project on your local server (make sure you're at the root of the project). Go to the terminal then type : 
```bash
php -S localhost:8000 (or other port that are not used like "localhost:1234...") -t public
```

### 3. Launch the application:

Finally, after setting up the project you can now go to the localhost:8000 by typing this in your favorite browser!
```
localhost:8000
``` 
### 4. Using the application: 

Then voilà! you can now use my application, create user, post blogs and more. 

### 5. Create an Admin User

To access the admin pages, you can manually create an admin user directly in the SQLite3 database.

Open the database and navigate to the user table.

Insert a new user entry, filling in all required columns.

Ensure the role column is set to either "user" or "admin" depending on the desired access level.

Hash the password you intend to use and store the hashed value in the password column.

Once this user is created with the "admin" role, you will be able to log in and access the admin interface.

The given application does not allow you to create admin, only users can be created.