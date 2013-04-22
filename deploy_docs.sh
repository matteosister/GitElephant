php vendor/bin/sami.php update docs.php
rsync --delete -arvuz build/* www-data@ovh:/var/www/ge_docs/