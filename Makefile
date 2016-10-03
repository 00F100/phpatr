.PHONY: all update-repo clean-dist download-composer composer-run download-php2phar php2phar-run commit-push-changes-git test phpunit-run push

all: update-repo clean-dist mkdir-bin download-composer composer-run download-php2phar php2phar-run commit-push-changes-git
test: update-repo clean-dist mkdir-bin download-composer composer-dev-run download-php2phar php2phar-run phpunit-run

update-repo:
	git reset --hard;
	git checkout master;
	git pull origin master;

clean-dist:
	if [ -f "dist/phpatr.phar" ] ; then \
		rm dist/phpatr.phar; \
	fi
	if [ -f "dist/phpatr.phar.gz" ] ; then \
		rm dist/phpatr.phar.gz; \
	fi

mkdir-bin:
	if [ ! -d "bin" ] ; then \
		mkdir bin; \
	fi

download-composer:
	if [ ! -f "bin/composer.phar" ] ; then \
		cd bin; \
		php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
		php composer-setup.php; \
		php -r "unlink('composer-setup.php');"; \
	fi;

composer-run:
	if [ -f "composer.lock" ] ; then \
		php bin/composer.phar update --no-dev; \
	else \
		php bin/composer.phar install --no-dev; \
	fi

composer-dev-run:
	if [ -f "composer.lock" ] ; then \
		php bin/composer.phar update; \
	else \
		php bin/composer.phar install; \
	fi

download-php2phar:
	if [ ! -f "bin/php2phar.phar" ] ; then \
		cd bin; \
		wget https://github.com/00F100/php2phar/raw/master/dist/php2phar.phar;
	fi

php2phar-run:
	php bin/php2phar.phar -d ./ -i src/index.php -o dist/phpatr.phar;

commit-push-changes-git:
	git add "dist/phpatr.phar";
	git commit -m "Jenkins update the phpatr.phar";
	git push origin master;

phpunit-run:
	./vendor/bin/phpunit tests/ --coverage-clover=clover.xml
