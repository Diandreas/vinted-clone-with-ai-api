<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class MessageCacheService
{
    const CACHE_TTL = 86400; // 24 heures
    const MAX_CACHED_MESSAGES = 50; // Derniers 50 messages en cache

    /**
     * Get messages from cache or database.
     */
    public function getMessages(int $conversationId, int $page = 1, int $perPage = 50)
    {
        $cacheKey = $this->getCacheKey($conversationId);

        try {
            // Essayer de récupérer depuis Redis
            $cachedMessages = Redis::get($cacheKey);

            if ($cachedMessages) {
                $messages = json_decode($cachedMessages, true);

                // Si page 1, retourner depuis cache
                if ($page === 1) {
                    Log::info("Messages loaded from Redis cache", [
                        'conversation_id' => $conversationId,
                        'count' => count($messages)
                    ]);

                    return [
                        'data' => array_slice($messages, 0, $perPage),
                        'from_cache' => true
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Redis cache read error", ['error' => $e->getMessage()]);
        }

        // Fallback vers MySQL
        return $this->loadFromDatabase($conversationId, $page, $perPage);
    }

    /**
     * Load messages from database.
     */
    protected function loadFromDatabase(int $conversationId, int $page = 1, int $perPage = 50)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar,username')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Cache la première page dans Redis
        if ($page === 1) {
            $this->cacheMessages($conversationId, $messages->items());
        }

        Log::info("Messages loaded from MySQL", [
            'conversation_id' => $conversationId,
            'page' => $page,
            'count' => $messages->count()
        ]);

        return [
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'total' => $messages->total(),
            ],
            'from_cache' => false
        ];
    }

    /**
     * Cache messages in Redis with TTL.
     */
    public function cacheMessages(int $conversationId, array $messages)
    {
        try {
            $cacheKey = $this->getCacheKey($conversationId);

            // Limiter à MAX_CACHED_MESSAGES
            $messagesToCache = array_slice($messages, 0, self::MAX_CACHED_MESSAGES);

            // Stocker avec TTL de 24h
            Redis::setex(
                $cacheKey,
                self::CACHE_TTL,
                json_encode($messagesToCache)
            );

            Log::info("Messages cached in Redis", [
                'conversation_id' => $conversationId,
                'count' => count($messagesToCache),
                'ttl' => self::CACHE_TTL
            ]);

        } catch (\Exception $e) {
            Log::error("Redis cache write error", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Add new message to cache.
     */
    public function addMessageToCache(Message $message)
    {
        try {
            $cacheKey = $this->getCacheKey($message->conversation_id);
            $cachedMessages = Redis::get($cacheKey);

            if ($cachedMessages) {
                $messages = json_decode($cachedMessages, true);

                // Ajouter le nouveau message au début
                array_unshift($messages, $this->formatMessage($message));

                // Limiter à MAX_CACHED_MESSAGES
                $messages = array_slice($messages, 0, self::MAX_CACHED_MESSAGES);

                // Re-cacher avec nouveau TTL
                Redis::setex($cacheKey, self::CACHE_TTL, json_encode($messages));

                Log::info("New message added to Redis cache", [
                    'conversation_id' => $message->conversation_id,
                    'message_id' => $message->id
                ]);
            } else {
                // Si pas de cache, créer un nouveau cache avec ce message
                $this->refreshCache($message->conversation_id);
            }

        } catch (\Exception $e) {
            Log::error("Error adding message to cache", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Invalidate cache for a conversation.
     */
    public function invalidateCache(int $conversationId)
    {
        try {
            $cacheKey = $this->getCacheKey($conversationId);
            Redis::del($cacheKey);

            Log::info("Cache invalidated", ['conversation_id' => $conversationId]);
        } catch (\Exception $e) {
            Log::error("Error invalidating cache", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Refresh cache from database.
     */
    public function refreshCache(int $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $messages = $conversation->messages()
            ->with('sender:id,name,avatar,username')
            ->orderBy('created_at', 'desc')
            ->limit(self::MAX_CACHED_MESSAGES)
            ->get()
            ->map(fn($msg) => $this->formatMessage($msg))
            ->toArray();

        $this->cacheMessages($conversationId, $messages);
    }

    /**
     * Get unread count from cache or database.
     */
    public function getUnreadCount(int $conversationId, int $userId): int
    {
        $cacheKey = "conversation:{$conversationId}:unread:{$userId}";

        try {
            $count = Redis::get($cacheKey);

            if ($count !== null) {
                return (int) $count;
            }
        } catch (\Exception $e) {
            Log::error("Redis unread count error", ['error' => $e->getMessage()]);
        }

        // Fallback vers MySQL
        $count = Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        // Cacher le résultat (TTL 5 min)
        try {
            Redis::setex($cacheKey, 300, $count);
        } catch (\Exception $e) {
            // Ignore cache errors
        }

        return $count;
    }

    /**
     * Update unread count in cache.
     */
    public function updateUnreadCount(int $conversationId, int $userId, int $count)
    {
        $cacheKey = "conversation:{$conversationId}:unread:{$userId}";

        try {
            Redis::setex($cacheKey, 300, $count);
        } catch (\Exception $e) {
            Log::error("Error updating unread count", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Set user online status.
     */
    public function setUserOnline(int $userId)
    {
        $cacheKey = "user:{$userId}:online";

        try {
            // Expire après 5 minutes d'inactivité
            Redis::setex($cacheKey, 300, time());

            Log::debug("User online status set", ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error("Error setting online status", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if user is online.
     */
    public function isUserOnline(int $userId): bool
    {
        $cacheKey = "user:{$userId}:online";

        try {
            return Redis::exists($cacheKey) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set typing indicator.
     */
    public function setTyping(int $conversationId, int $userId)
    {
        $cacheKey = "conversation:{$conversationId}:typing:{$userId}";

        try {
            // Expire après 10 secondes
            Redis::setex($cacheKey, 10, time());
        } catch (\Exception $e) {
            Log::error("Error setting typing indicator", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get users typing in conversation.
     */
    public function getTypingUsers(int $conversationId): array
    {
        try {
            $pattern = "conversation:{$conversationId}:typing:*";
            $keys = Redis::keys($pattern);

            $userIds = [];
            foreach ($keys as $key) {
                // Extraire user_id depuis la clé
                if (preg_match('/typing:(\d+)$/', $key, $matches)) {
                    $userIds[] = (int) $matches[1];
                }
            }

            return $userIds;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get cache key for conversation messages.
     */
    protected function getCacheKey(int $conversationId): string
    {
        return "conversation:{$conversationId}:messages";
    }

    /**
     * Format message for cache.
     */
    protected function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'type' => $message->type,
            'attachment_url' => $message->attachment_url,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at->toIso8601String(),
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'avatar' => $message->sender->avatar,
                'username' => $message->sender->username ?? null,
            ]
        ];
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        try {
            $info = Redis::info();

            return [
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'used_memory_human' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_keys' => Redis::dbSize(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
