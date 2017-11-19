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
        $resourceType = '';
        if ($resource instanceof Resource) {
            $resourceType = $resource->getType()->getName();
            $resource = $resource->getName();
        }

        switch (strtolower($resource)) {
            case 'civilian':
                return '<i class="fa fa-users fa-fw"></i> ';
            case 'housing':
                return '<i class="fa fa-home fa-fw"></i> ';
            case 'defender';
            case 'wall';
                return '<i class="fa fa-shield fa-fw"></i> ';
            case 'material':
                return '<i class="fa fa-cubes fa-fw"></i> ';
            case 'military':
            case 'soldier':
            case 'attacker':
                return '<i class="fa fa-fighter-jet fa-fw"></i> ';
            case 'hacker';
            case 'spy';
                return '<i class="fa fa-user-secret fa-fw"></i> ';
            case 'trainer':
                return '<i class="fa fa-male fa-fw"></i> ';
        }

        switch (strtolower($resourceType)) {
            case 'material':
                return '<i class="fa fa-cubes fa-fw"></i> ';
            case 'building':
                return '<i class="fa fa-home fa-fw"></i> ';
            case 'population':
                return '<i class="fa fa-users fa-fw"></i> ';
        }

        return '';
    }
}