<?php

declare(strict_types=1);

namespace ZxArt\Screenshots\SpectrumComputing;

/**
 * Screens of a Spectrum Computing entry that are taken into the prod gallery,
 * by the caption the entry page gives them. The gallery takes them in the order
 * the cases are declared.
 */
enum SpectrumComputingScreenKind: string
{
    case Loading = 'Loading screen';
    case Opening = 'Opening screen';
    case Running = 'Running screen';
}
