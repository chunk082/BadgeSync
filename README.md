In SyncHabboBadge.php
Look for this line..
"$imagePath = '/var/www/assets/swf/c_images/album1584';" replace if your own directory path.

In SyncHabboonBadge.php
Look for this line
$savePath = '/var/www/assets/swf/c_images/album1584/'; replace if your own directory path.

You need to run the command seperately..

For SynHabboBadge.php
php artisan habbo:sync-badges --hotel=com --limit=1000  (You change the limit max 2000) This script is only for Habbo.com (English Only)

For SyncHabboonBadge.php
php artisan habboon:sync-badges

