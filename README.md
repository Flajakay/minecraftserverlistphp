## Important Notice
This project is currently undergoing a complete rewrite and modernization. The current PHP-based version will be replaced with a new, more efficient implementation.

### What's Coming
The new version will feature:
- Node.js backend with Express
- Bootstrap 5 frontend
- PostgreSQL database for better data integrity and performance
- Modern API architecture
- Better scalability for handling thousands of servers

## OLD README:

LOGIN: admin
PASSWORD: admin

Custom pages

create a file in /pages NAME_OF_FILE.php, for example test.php

insert this into a file:

<?php
initiate_html_columns();
?>

<Here goes your code>

Then open .htaccess and add new rule.

RewriteRule ^test$ index.php?page=test

Now this page will be available at LINK_TO_YOUR_WEBSITE/test
