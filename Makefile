tests: unit behat

release:
	./vendor/bin/RMT release

unit:
	php ./vendor/bin/phpunit

behat:
	php ./vendor/bin/behat --colors -vvv

database:
	./examples/scripts/create_core.sh
	./examples/scripts/load_fixtures.php

rusty:
	php ./vendor/bin/rusty check --no-execute README.md

solr_start:
	docker run -d -p 8983:8983 --name solr-rulerz solr:7.5.0-alpine

solr_stop:
	docker rm -f solr-rulerz