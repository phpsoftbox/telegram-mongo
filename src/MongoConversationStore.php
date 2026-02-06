<?php

declare(strict_types=1);

namespace PhpSoftBox\Telegram\Storage\Mongo;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use PhpSoftBox\Telegram\Conversation\ConversationState;
use PhpSoftBox\Telegram\Conversation\ConversationStoreInterface;

use function is_array;
use function is_numeric;

final class MongoConversationStore implements ConversationStoreInterface
{
    public function __construct(
        private readonly Collection $collection,
        private readonly string $chatIdField = 'chat_id',
        private readonly string $updatedAtField = 'updated_at',
        private readonly string $updatedAtDateField = 'updated_at_dt',
    ) {
    }

    public function get(string $chatId): ?ConversationState
    {
        $document = $this->collection->findOne([$this->chatIdField => $chatId]);
        if ($document === null) {
            return null;
        }

        if ($document instanceof BSONDocument) {
            $payload = $document->getArrayCopy();
        } elseif (is_array($document)) {
            $payload = $document;
        } else {
            return null;
        }

        unset($payload['_id']);

        if (!isset($payload[$this->updatedAtField]) && isset($payload[$this->updatedAtDateField])) {
            $payload[$this->updatedAtField] = $this->normalizeUpdatedAt($payload[$this->updatedAtDateField]);
        }

        return ConversationState::fromArray($payload);
    }

    public function save(ConversationState $state): void
    {
        $payload = $state->toArray();

        if (isset($payload[$this->updatedAtField]) && is_numeric($payload[$this->updatedAtField])) {
            $payload[$this->updatedAtDateField] = new UTCDateTime(((int) $payload[$this->updatedAtField]) * 1000);
        }

        $this->collection->replaceOne(
            [$this->chatIdField => $state->chatId()],
            $payload,
            ['upsert' => true],
        );
    }

    public function delete(string $chatId): void
    {
        $this->collection->deleteOne([$this->chatIdField => $chatId]);
    }

    private function normalizeUpdatedAt(mixed $value): ?int
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->getTimestamp();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
