cronkd
======
## Installation Guide

### Spinning up your first world
#### Installing Cron
Edit your crontab:

    crontab -e

Modify the following line to accommodate your environment and add:

    0 * * * *   /your/path/to/php /your/path/to/symfony/root/bin/console cronkd:tick 2>&1

This will perform a tick every hour at the top of the hour.  For development or testing, change the 0 to an asterisk to perform a tick every minute.

#### Seed database
Go into the Symfony root directory and run the following command:
    
    php bin/console doctrine:schema:update --force
    
Run the following commands on the database:

    INSERT INTO `resource` (`id`, `name`, `value`, `can_be_probed`, `created_at`, `updated_at`)
    VALUES
        (1, 'Civilian', 1, 1, NOW(), NOW()),
        (2, 'Material', 1, 1, NOW(), NOW()),
        (3, 'Housing', 2, 1, NOW(), NOW()),
        (4, 'Military', 2, 1, NOW(), NOW()),
        (5, 'Hacker', 3, 0, NOW(), NOW());
    
    INSERT INTO `world` (`id`, `tick`, `name`, `active`, `created_at`, `updated_at`)
    VALUES
        (1, 1, 'Earth', 1, NOW(), NOW());
    
    INSERT INTO `policy` (`id`, `created_at`, `updated_at`, `name`, `description`)
    VALUES
    	    (1, NOW(), NOW(), 'Warmonger', 'Double the amount of housing received after a successful attack.'),
    	    (2, NOW(), NOW(), 'Defender', 'Military forces defend attacks with greater vigilance.  Defense increases by 10%.'),
    	    (3, NOW(), NOW(), 'Economist', 'Producing Materials and Building Housing takes 2 fewer ticks.  Training Military and Hackers takes 2 additional ticks.');


#### Create your account
You can now create your account and kingdom and start playing!