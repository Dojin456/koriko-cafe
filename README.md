# Koriko Cafe

A responsive Korean-cafe menu and pickup-order demo for XAMPP.

## Run locally

1. Start Apache in the XAMPP Control Panel.
2. Open `http://localhost/ARYA-WDP/Pratical-21/koriko%20cafe/`.
3. Add menu items to the bag, enter pickup details, and place an order.
4. Open `http://localhost/ARYA-WDP/Pratical-21/koriko%20cafe/order.php` to create the cafe admin password on first use. Use at least 12 characters and keep it private.
5. Sign in there to view orders and update their status. Customer pages do not expose the order list.

Orders and the admin password hash are stored under `data/`; the included Apache rule blocks direct web access to that folder. This demo uses JSON files, not a database. Product photos and web fonts are loaded from external services, so an internet connection is needed for those assets.
