<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class TitleCaseExtension
 * Twig extension to provide a 'title_case' filter for templates
 *
 * @package App\Twig
 */
class TitleCaseExtension extends AbstractExtension
{
    /**
     * Registers the 'title_case' filter with Twig
     *
     * @return array The registered Twig filters
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('title_case', [$this, 'titleCase']),
        ];
    }

    /**
     * Converts a string to title case.
     *
     * @param string $value The string to convert.
     * @return string The string as title case
     */
    public function titleCase(string $value): string
    {
        return ucwords(strtolower($value));
    }
}