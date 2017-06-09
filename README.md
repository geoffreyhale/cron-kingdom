cronkd
======
Updated 2017-06-08.

## Installation Guide

#### Set up Cron job
CronKD operates by periodically running a cron job to update all of the active worlds on the server.  We must first set this job to run.  To edit your crontab:

    crontab -e

Modify the following line to accommodate your environment and add:

    * * * * *   /your/path/to/php /your/path/to/symfony/root/bin/console cronkd:tick --env=prod 2>&1

#### Seed database
Go into the Symfony root directory and run the following command:
    
    php bin/console doctrine:schema:update --force
    
Run the following command to load initial settings and populate the database:

    php bin/console cronkd:init --env=prod

#### Create your account

    php bin/console fos:user:create
    
Follow the interactive menu to create your user account.  Be sure to make your account an administrative account:

    php bin/conosle fos:user:promote $username ROLE_ADMIN