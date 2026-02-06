# Использование

`MongoConversationStore` реализует `ConversationStoreInterface` и хранит состояние диалогов в MongoDB. Один документ соответствует одному `chat_id`.

```php
use MongoDB\Client;
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationStore;

$client = new Client('mongodb://localhost:27017');
$collection = $client->selectCollection('app', 'telegram_conversations');

$store = new MongoConversationStore($collection);
```
