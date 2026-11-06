<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        // "today" must be the Argentine day, not the server's (UTC) one: at 21hs it would already be tomorrow
        date_default_timezone_set('America/Argentina/Buenos_Aires');

        parent::boot();
    }
}
