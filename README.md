# README #

### Quick Start For Initial Local Dev Setup ###

Terminal #1
1. composer install (composer version 2 required)
2. docker-compose up

Terminal #2 (Once docker-compose up is running in Terminal #1)
1. php bin/console doctrine:schema:drop --force
2. php bin/console doctrine:schema:create
3. php bin/console doctrine:fixtures:load ("yes" when prompted)
4. symfony serve:start

Terminal #3
1. npm install (confirmed working with node 12 -> 17)
2. npm run dev


### Ongoing Local Dev ###
Once the steps from the Initial Local Dev Setup have been complete, just run the last step from each terminal, i.e. in three separate terminals:

- npm run dev (theme compilation service)
- symfony serve:start (server/twig service)
- docker-compose up (database service)