## Important Notice
This project is currently undergoing a complete rewrite and modernization in development branch. 

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
