<?php

namespace App\Twig;

use App\Security\RoleHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('saludo', $this->saludo(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('perfil', RoleHelper::etiqueta(...)),
            new TwigFilter('fecha_larga', $this->fechaLarga(...)),
        ];
    }

    // "Buen día", "Buenas tardes" o "Buenas noches", según la hora de acá.
    public function saludo(?\DateTimeInterface $ahora = null): string
    {
        $hora = (int) ($ahora ?? new \DateTimeImmutable())->format('G');

        return match (true) {
            $hora >= 5 && $hora < 13 => 'Buen día',
            $hora >= 13 && $hora < 20 => 'Buenas tardes',
            default => 'Buenas noches',
        };
    }

    // "Viernes 6 de noviembre".
    public function fechaLarga(\DateTimeInterface $fecha): string
    {
        $formatter = new \IntlDateFormatter('es_AR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, $fecha->getTimezone(), null, "EEEE d 'de' MMMM");

        return mb_convert_case(mb_substr($formatter->format($fecha), 0, 1), MB_CASE_UPPER) . mb_substr($formatter->format($fecha), 1);
    }
}
