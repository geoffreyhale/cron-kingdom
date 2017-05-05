<?php
namespace CronkdBundle\Twig;

class CronkdExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_Filter('filterNumber', [$this, 'filterNumber']),
        ];
    }

    public function filterNumber($number)
    {
        $modifiers = [
            'T' => 1E12,
            'B' => 1E9,
            'M' => 1E6,
            'K' => 1E3,
        ];

        foreach ($modifiers as $index => $value) {
            if ($number > $value) {
                return number_format($number/$value, 1) . $index;
            }
        }

        return number_format($number);
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('resourceIcon', [$this, 'getResourceIcon']),
        ];
    }

    public function getResourceIcon($resourceName)
    {
        switch ($resourceName) {
            case 'Civilian':
                return '<i class="fa fa-users fa-fw"></i> ';
            case 'Housing':
            case 'Wood House';
                return '<i class="fa fa-home fa-fw"></i> ';
            case 'Guard';
            case 'Wooden Wall';
                return '<i class="fa fa-shield fa-fw"></i> ';
            case 'Material':
            case 'Wood';
                return '<i class="fa fa-cubes fa-fw"></i> ';
            case 'Military':
            case 'Soldier';
                return '<i class="fa fa-fighter-jet fa-fw"></i> ';
            case 'Hacker';
                return '<i class="fa fa-user-secret fa-fw"></i> ';
            case 'Tree';
                return '<i class="fa fa-tree fa-fw"></i> ';
        }

        return '';
    }
}