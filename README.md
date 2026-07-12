## Getting Started

Follow these steps to set up **Voyago** on your local machine.

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any stack with Apache, PHP 8.0+, and MySQL/MariaDB)
- A web browser
- Git (optional, for cloning the repository)

### Installation

1. **Clone or download this repository**
   
   Or download the ZIP file on google drive and extract it.

2. **Move the project into your server directory**
   Place the `voyago` folder inside your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\voyago
   ```

3. **Start Apache and MySQL**
   Open the XAMPP Control Panel and start both the **Apache** and **MySQL** modules.

4. **Create the database**
   - Open [phpMyAdmin](http://localhost/phpmyadmin).
   - Create a new database named `voyago`.
   - Import the provided `voyago.sql` file (found in the project root) into this database.

5. **Configure database credentials**
   Open `toyyibpay_config.php` and update the connection details if needed:
   ```php
   $host = 'localhost';
   $db   = 'voyago';
   $user = 'root';
   $pass = '';
   ```

6. **Set the base URL**
   In the same `toyyibpay_config.php` file, confirm the `BASE_URL` matches your local setup:
   ```php
   define('BASE_URL', 'http://localhost/voyago/');
   ```

7. **Payment gateway (ToyyibPay sandbox)**
   This project uses [ToyyibPay](https://toyyibpay.com/) in sandbox mode for payment processing. Sandbox credentials are pre-configured in `toyyibpay_config.php` for testing purposes. Replace these with your own credentials if deploying to production.

8. **Run the application**
   Open your browser and navigate to:
   ```
   http://localhost/voyago/
   ```

### Notes

- Uploaded files (homestay images, documents, receipts) are stored in the `/uploads` we seperate it into google drive because the file is too large .
- Email receipts are generated as `.txt` files in the project root for testing/demo purposes.
- Database schema updates are handled automatically on connection via migration checks in `toyyibpay_config.php`.
