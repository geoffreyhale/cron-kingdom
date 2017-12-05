<?php
namespace CronkdBundle\Twig;

use CronkdBundle\Entity\Resource\Resource;

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
            'S' => 1E24,
            's' => 1E21,
            'Q' => 1E18,
            'q' => 1E15,
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

    public function getResourceIcon($resource)
    {
        if ($resource instanceof Resource) {
            if (!empty($resource->getIcon())) {
                return '<i class="fa fa-fw fa-' . $resource->getIcon() . '"></i> ';
            }
        }

        return '<i class="fa fa-fw fa-users"></i> ';
    }
}