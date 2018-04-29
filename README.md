# PHPOSTKit
A rather uninteresting PHP implementation of OST Kit Alpha.

Currently supports the full OSTKit API:

* Create user
* Edit user
* List users
* Airdrop users
* Airdrop status
* Create transaction type
* Edit a transaction type
* List transaction types
* Execute a transaction type
* Get status of an executed transaction (or multiple transactions at once)


Pretty straightforward usage:

```php
require_once "path/to/OST.class.php"; // or autoload it. whatever. i'm not your mum.
$name = 'Foo Bar';
$user_uuid = OST::create_user($name);


// to check a single tx status, ensure you pass the single uuid as a single element array like:
// $txs = ['a62fb7fe-ded8-4ecb-8489-4cb4c7d981ac'];
$txs = ['a62fb7fe-ded8-4ecb-8489-4cb4c7d981ac','1131d672-8859-42c6-99f0-2002dcaa2f6b'];
$tx_status = OST::tx_status($txs);

```

Don't forget to add in your API key, API secret, and base URL into your project. I defined them as a constant in a config file in my project.
