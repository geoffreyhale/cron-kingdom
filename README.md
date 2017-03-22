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

    INSERT INTO `resource` (`id`, `name`, `value`, `can_be_probed`)
    VALUES
    	(1, 'Civilian', 1, 1),
    	(2, 'Material', 1, 1),
    	(3, 'Housing', 2, 0),
    	(4, 'Military', 2, 1),
    	(5, 'Hacker', 3, 1);

    INSERT INTO `world` (`id`, `tick`, `name`, `active`)
    VALUES
    	(1, 1, 'Earth', 1);

#### Create your account
You can now create your account and kingdom and start playing!