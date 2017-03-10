cronkd
======
### Installation Guide

### Installing Cron
Edit your crontab:

    crontab -e

Modify the following line to accommodate your environment and add:

    0 * * * *   /your/path/to/php /your/path/to/symfony/root/bin/console cronkd:tick 2>&1

This will perform a tick every hour at the top of the hour.  For development or testing, change the 0 to an asterisk to perform a tick every minute.