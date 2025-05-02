# Python Login System

## Requirements
- Python 3.x
- MySQL Server
- `mysql-connector-python` (install with `pip install -r requirements.txt`)

## Database Setup
1. Log in to your MySQL server and run the following commands:

```sql
CREATE DATABASE IF NOT EXISTS login_db;
USE login_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO users (email, password) VALUES ('test@example.com', 'password123');
```

## Running the App
1. Make sure your MySQL server is running and the database is set up.
2. Install dependencies:
   ```
   pip install -r requirements.txt
   ```
3. Run the login GUI:
   ```
   python login.py
   ```

Login with:
- Email: `test@example.com`
- Password: `password123` 