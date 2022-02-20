## API
Endpoints for fetching data asynchronously using AJAX / Fetch / etc go in this folder. 
Generally speaking these files are responsible for their own auth with the standard format:

```php
# Begin standard auth
require "../classes.php";

$system = new System();

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth
```

Past this, whether to load further user data or not is up to the endpoint. Some
may want to update regen, some may want to trigger regen/training updates, some 
may want to do neither.