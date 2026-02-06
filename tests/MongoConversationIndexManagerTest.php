<?php

declare(strict_types=1);

namespace PhpSoftBox\Telegram\Storage\Mongo\Tests;

use MongoDB\Client;
use MongoDB\Collection;
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationIndexManager;
use PHPUnit\Framework\TestCase;

use function getenv;

final class MongoConversationIndexManagerTest extends TestCase
{
    private Collection $collection;

    protected function setUp(): void
    {
        $this->collection = $this->makeCollection();
        $this->collection->drop();
    }

    /**
     * Проверяем создание индексов.
     */
    public function testEnsureIndexes(): void
    {
        $manager = new MongoConversationIndexManager($this->collection);
        $manager->ensureIndexes(ttlSeconds: 3600);

        $indexes = [];
        foreach ($this->collection->listIndexes() as $index) {
            $indexes[$index->getName()] = $index->getKey();
        }

        $this->assertArrayHasKey('chat_id_1', $indexes);
        $this->assertArrayHasKey('updated_at_dt_1', $indexes);
    }

    private function makeCollection(): Collection
    {
        $uri = getenv('MONGO_URI') ?: 'mongodb://mongo:27017';
        $client = new Client($uri);

        return $client->selectCollection('phpsoftbox_test', 'telegram_conversations_index_test');
    }
}
