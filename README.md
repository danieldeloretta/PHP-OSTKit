# PHPOSTKit
A rather uninteresting PHP implementation of OST Kit Alpha

Pretty straightforward usage:

```php
require_once "path/to/OST.class.php"; // or autoload it. whatever. i'm not your mum.
$name = 'Foo Bar';
$user_uuid = OST::create_user($name);
```

Don't forget to add in your API key, API secret, and base URL into your project. I defined them as a constant in a config file.
