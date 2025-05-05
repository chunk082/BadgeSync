# Badge Sync Script

## Configuration

**Update the following lines before running the script:**

In `SyncHabboBadge.php`:
```php
$imagePath = '/var/www/assets/swf/c_images/album1584/';
```
Replace the path above with your own badge image directory path.

In `SyncHabboonBadge.php`:
```php
$savePath = '/var/www/assets/swf/c_images/album1584/';
```
Replace the path above with your own save location.

---

## Usage

Run the commands separately:

### For Habbo.com (English Only):
```bash
php artisan habbo:sync-badges --hotel=com --limit=1000
```

### For Habboon:
```bash
php artisan habboon:sync-badges
```

> 💡 You can adjust the `--limit` as needed. The max is `2000`.
