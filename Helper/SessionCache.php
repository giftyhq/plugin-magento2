<?php

namespace Gifty\Magento\Helper;

use Magento\Framework\Session\SessionManagerInterface;
use Gifty\Client\Resources\GiftCard;

class SessionCache
{
    private const CACHE_KEY = 'gifty_giftcard_cache';
    private const CACHE_LIFETIME = 300;

    private SessionManagerInterface $session;

    public function __construct(SessionManagerInterface $session)
    {
        $this->session = $session;
    }

    public function getCachedGiftCard(string $code): ?GiftCard
    {
        $cache = $this->session->getData(self::CACHE_KEY) ?? [];

        if (!isset($cache[$code]) || $cache[$code]['expires'] <= time()) {
            return null;
        }

        return $cache[$code]['giftCard'];
    }

    public function cacheGiftCard(string $code, ?GiftCard $giftCard): void
    {
        $cache = $this->session->getData(self::CACHE_KEY) ?? [];

        $cache[$code] = [
            'giftCard' => $giftCard,
            'expires' => time() + self::CACHE_LIFETIME
        ];

        $this->session->setData(self::CACHE_KEY, $cache);
    }

    public function clearCache(): void
    {
        $this->session->unsetData(self::CACHE_KEY);
    }
}
