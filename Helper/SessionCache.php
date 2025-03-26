<?php

namespace Gifty\Magento\Helper;

use Gifty\Magento\Logger\GiftyLogger;
use Magento\Framework\Session\SessionManagerInterface;
use Gifty\Client\Resources\GiftCard;

/**
 * Class SessionCache
 *
 * Handles caching of gift card data in the session to reduce API calls.
 * Gift cards are cached for a limited time to ensure data freshness while
 * maintaining performance.
 *
 */
class SessionCache
{
    /**
     * Session storage key for the gift card cache
     *
     * @var string
     */
    private const CACHE_KEY = 'gifty_gift_card_cache';

    /**
     * Cache lifetime in seconds (5 minutes)
     *
     * @var int
     */
    private const CACHE_LIFETIME = 300;

    /**
     * Magento session manager instance
     *
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $session;

    /**
     * Logger instance for Gifty operations
     * @var GiftyLogger
     */
    private GiftyLogger $logger;

    /**
     * Constructor
     *
     * @param SessionManagerInterface $session The Magento session manager
     * @param GiftyLogger $logger The Gifty logger instance
     */
    public function __construct(
        SessionManagerInterface $session,
        GiftyLogger $logger
    ) {
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * Retrieves a cached gift card by its code
     *
     * Returns null if the gift card is not cached or if the cache has expired.
     *
     * @param string $code The gift card code to retrieve
     * @return GiftCard|null The cached gift card or null if not found/expired
     */
    public function getCachedGiftCard(string $code): ?GiftCard
    {
        $cache = $this->session->getData(self::CACHE_KEY) ?? [];

        if (!isset($cache[$code]) || $cache[$code]['expires'] <= time()) {
            $this->logger->debug('Gift card not found in cache or expired', ['code' => $code]);

            return null;
        }

        $this->logger->debug('Gift card found in cache', ['code' => $code]);

        return $cache[$code]['giftCard'];
    }

    /**
     * Caches a gift card with its code
     *
     * Stores the gift card in the session cache with an expiration timestamp.
     * If the gift card is null, it will still be cached to prevent repeated
     * API calls for invalid codes.
     *
     * @param string $code The gift card code to cache
     * @param GiftCard|null $giftCard The gift card instance to cache
     * @return void
     */
    public function cacheGiftCard(string $code, ?GiftCard $giftCard): void
    {
        $cache = $this->session->getData(self::CACHE_KEY) ?? [];

        $cache[$code] = [
            'giftCard' => $giftCard,
            'expires'  => time() + self::CACHE_LIFETIME
        ];

        $this->session->setData(self::CACHE_KEY, $cache);
    }

    /**
     * Clears the entire gift card cache
     *
     * Removes all cached gift cards from the session. This should be called
     * when the cart is modified to ensure fresh gift card data is fetched.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->session->unsetData(self::CACHE_KEY);
    }
}
