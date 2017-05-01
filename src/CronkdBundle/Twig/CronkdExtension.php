<?php
namespace CronkdBundle\Twig;

class CronkdExtension extends \Twig_Extension
{
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
            case 'Wood House';
                return '<i class="fa fa-home fa-fw"></i> ';
            case 'Guard';
            case 'Wooden Wall';
                return '<i class="fa fa-shield fa-fw"></i> ';
            case 'Wood';
                return '<i class="fa fa-cubes fa-fw"></i> ';
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