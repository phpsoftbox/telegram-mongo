# Индексы

Для MongoDB рекомендуются два индекса:

- уникальный индекс по `chat_id`;
- TTL-индекс по `updated_at_dt` для автоматической очистки (опционально).

```php
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationIndexManager;

$indexes = new MongoConversationIndexManager($collection);
$indexes->ensureIndexes(ttlSeconds: 3600);
```

TTL индекс опирается на поле `updated_at_dt`, которое сохраняется как `UTCDateTime`.
