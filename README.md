# Telegram Mongo

## About

`phpsoftbox/telegram-mongo` — MongoDB-хранилище для диалогов Telegram.

## Quick Start

```php
use MongoDB\Client;
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationStore;

$client = new Client('mongodb://localhost:27017');
$collection = $client->selectCollection('app', 'telegram_conversations');

$store = new MongoConversationStore($collection);
```

## Индексы

```php
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationIndexManager;

$indexes = new MongoConversationIndexManager($collection);
$indexes->ensureIndexes(ttlSeconds: 3600);
```

## Документация

- [Использование](docs/01-usage.md)
- [Индексы](docs/02-indexes.md)
