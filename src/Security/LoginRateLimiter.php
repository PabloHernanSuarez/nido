<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RateLimiter\AbstractRequestRateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginRateLimiter extends AbstractRequestRateLimiter
{
    public function __construct(
        private RateLimiterFactory $byIpFactory,
        private RateLimiterFactory $byUsernameFactory,
        #[\SensitiveParameter] private string $secret,
    ) {
    }

    protected function getLimiters(Request $request): array
    {
        $username = $request->attributes->get(SecurityRequestAttributes::LAST_USERNAME, '');
        $username = preg_match('//u', $username) ? mb_strtolower($username, 'UTF-8') : strtolower($username);

        return [
            $this->byIpFactory->create($this->hash($request->getClientIp())),
            $this->byUsernameFactory->create($this->hash($username)),
        ];
    }

    private function hash(string $data): string
    {
        return strtr(substr(base64_encode(hash_hmac('sha256', $data, $this->secret, true)), 0, 8), '/+', '._');
    }
}
