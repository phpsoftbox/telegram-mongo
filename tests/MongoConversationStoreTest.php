<?php

declare(strict_types=1);

namespace PhpSoftBox\Telegram\Storage\Mongo\Tests;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;
use PhpSoftBox\Telegram\Conversation\ConversationState;
use PhpSoftBox\Telegram\Storage\Mongo\MongoConversationStore;
use PHPUnit\Framework\TestCase;

use function getenv;

final class MongoConversationStoreTest extends TestCase
{
    private Collection $collection;

    protected function setUp(): void
    {
        $this->collection = $this->makeCollection();
        $this->collection->drop();
    }

    /**
     * Проверяем сохранение и получение диалога.
     */
    public function testSaveAndGet(): void
    {
        $store = new MongoConversationStore($this->collection);
        $state = new ConversationState('demo', 'chat-1', ['name' => 'A'], [], 1, 100, 200);

        $store->save($state);
        $loaded = $store->get('chat-1');

        $this->assertNotNull($loaded);
        $this->assertSame('demo', $loaded->name());
        $this->assertSame(['name' => 'A'], $loaded->data());
        $this->assertSame(1, $loaded->stepIndex());
    }

    /**
     * Проверяем удаление диалога.
     */
    public function testDelete(): void
    {
        $store = new MongoConversationStore($this->collection);
        $state = new ConversationState('demo', 'chat-2');

        $store->save($state);
        $store->delete('chat-2');

        $this->assertNull($store->get('chat-2'));
    }

    /**
     * Проверяем сохранение updated_at_dt для TTL.
     */
    public function testUpdatedAtDateTime(): void
    {
        $store = new MongoConversationStore($this->collection);
        $state = new ConversationState('demo', 'chat-3', [], [], 0, 100, 200);

        $store->save($state);

        $raw = $this->collection->findOne(['chat_id' => 'chat-3']);
        $this->assertNotNull($raw);

        $payload = $raw instanceof \MongoDB\Model\BSONDocument ? $raw->getArrayCopy() : $raw;
        $this->assertInstanceOf(UTCDateTime::class, $payload['updated_at_dt'] ?? null);
        $this->assertSame(200, ($payload['updated_at_dt'])->toDateTime()->getTimestamp());
    }

    private function makeCollection(): Collection
    {
        $uri = getenv('MONGO_URI') ?: 'mongodb://mongo:27017';
        $client = new Client($uri);

        return $client->selectCollection('phpsoftbox_test', 'telegram_conversations_test');
    }
}
