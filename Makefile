.PHONY: all update-repo clean-dist download-composer composer-run download-php2phar php2phar-run commit-push-changes-git

all: update-repo clean-dist download-composer composer-run download-php2phar php2phar-run commit-push-changes-git

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

download-composer:
	mkdir bin; cd bin; php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; php composer-setup.php; php -r "unlink('composer-setup.php');"

composer-run:
	if [ -f "composer.lock" ] ; then \
		php bin/composer.phar update --no-dev; \
	else \
		php bin/composer.phar install --no-dev; \
	fi

download-php2phar:
	cd "bin"; \
	wget https://github.com/00F100/php2phar/raw/master/dist/php2phar.phar;

php2phar-run:
	php bin/php2phar.phar -d ./ -i src/index.php -o dist/phpatr.phar;

commit-push-changes-git:
	git add "dist/phpatr.phar";
	git commit -m "Jenkins update the phpatr.phar";
	git push origin master;
