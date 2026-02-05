# Event-Management

A wedding photos event management system built with PHP, MySQL, and Bootstrap 5. It supports super admin and user roles, QR-based guest access, secure cookie usage, and encrypted tokens for improved security and performance.

## Features
- Super admin access to all users and events.
- Users can create events and upload/manage wedding photos.
- QR code generation for every event, with scanning support for guests.
- Encrypted cookies to remember last access securely.
- PIN-protected galleries with localStorage helpers for faster repeat access.

## Setup
1. Create a MySQL database and import the schema:
   ```sql
   source database.sql
   ```
2. Update database credentials in `config.php`.
3. Serve the project with PHP (example):
   ```bash
   php -S localhost:8000
   ```
4. Log in with the seeded super admin account:
   - Email: `admin@example.com`
   - Password: `Admin@123`

## Notes
- Uploaded images are stored in `/uploads`.
- Ensure the `uploads` directory is writable by your web server.
- Event PINs are required for guest access after scanning a QR code.
