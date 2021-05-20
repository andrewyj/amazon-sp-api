<?php
namespace AmazonSellingPartnerAPI;

class RateLimiter
{
    protected $cache;

    protected $limiters = [];

    /**
     * RateLimiter constructor.
     * @param Object $cache a cache instance which implements of psr CacheInterface
     */
    public function __construct(Object $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Registering a new limiter.
     *
     * @param string $name
     * @param int $maxAttempts
     * @param float $ratePerSec
     * @return $this
     */
    public function for(string $name, int $maxAttempts, float $ratePerSec): self
    {
        $this->limiters[$name] = [
            'max_attempts' => $maxAttempts,
            'rate_per_sec' => $ratePerSec,
        ];

        return $this;
    }

    /**
     * determine whether a limiter can attempts or not.
     *
     * @param string $name
     * @return bool
     */
    public function attempt(string $name): bool
    {
        if (!isset($this->limiters[$name])) {
            return false;
        }
        $limiterInfo = $this->cacheGet($name);
        $attempts = 0;
        $startedAt = $this->curMicroTime();
        $ratePerSec = $this->limiters[$name]['rate_per_sec'];
        if (!empty($limiterInfo)) {
            $attempts = $limiterInfo['attempts'];
            $startedAt = $limiterInfo['micro_timestamp'];
        }
        $attempts = max(0, $attempts - $this->recoverAttempts($startedAt, $ratePerSec));

        return $this->incrementAttempt($name, $attempts);
    }

    /**
     * The next attempt duration of seconds.
     * @param string $name
     * @return int
     */
    public function nextAttemptDuration(string $name): int
    {
        $limiter = $this->limiters[$name];
        $limiterInfo = $this->cacheGet($name);
        if (empty($limiterInfo) || $limiterInfo['attempts'] < $limiter['max_attempts']
        || $this->recoverAttempts($limiterInfo['micro_timestamp'], $limiter['rate_per_sec']) > 0) return 0;

        return (int)(1/$limiter['rate_per_sec'] - $this->curMicroTime() + $limiterInfo['micro_timestamp']);
    }

    /**
     * Determine if name of limiters has registered.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->limiters[$name]);
    }

    protected function recoverAttempts(float $startedAt, float $ratePerSec): int
    {
        $duration = $this->curMicroTime()-$startedAt;
        return (int)$duration*$ratePerSec;
    }

    protected function incrementAttempt(string $name, $attempts): bool
    {
        ++$attempts;
        if ($attempts > $this->limiters[$name]['max_attempts']) {
            return false;
        }
        $this->cachePut($name, $attempts);

        return true;
    }

    protected function curMicroTime(): float
    {
        return array_sum(explode(' ', microtime()));
    }

    protected function cachePut(string $name, int $attempts)
    {
        $limiter = $this->limiters[$name];
        $expiredInSeconds = (int)($attempts / $limiter['rate_per_sec']);
        $this->cache->put($this->cacheKey($name), [
            'attempts' => $attempts,
            'micro_timestamp' => $this->curMicroTime()
        ], $expiredInSeconds);
    }

    protected function cacheGet(string $name): array
    {
        return $this->cache->get($this->cacheKey($name)) ? : [];
    }

    protected function cacheKey(string $name): string
    {
        return self::class. ':'. $name;
    }
}
