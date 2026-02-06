<?php

declare(strict_types=1);

namespace PhpSoftBox\Telegram\Storage\Mongo;

use MongoDB\Collection;

final class MongoConversationIndexManager
{
    public function __construct(
        private readonly Collection $collection,
        private readonly string $chatIdField = 'chat_id',
        private readonly string $updatedAtDateField = 'updated_at_dt',
    ) {
    }

    public function ensureIndexes(?int $ttlSeconds = null): void
    {
        $this->collection->createIndex([$this->chatIdField => 1], ['unique' => true]);

        if ($ttlSeconds !== null) {
            $this->collection->createIndex(
                [$this->updatedAtDateField => 1],
                ['expireAfterSeconds' => $ttlSeconds],
            );
        }
    }
}
