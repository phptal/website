all:
	php -q make.php

deploy:
	cleanup
	rsync -avz \
	--exclude=".svn" \
	--exclude="files/.svn" \
	--exclude="files/old/.svn" \
	--exclude="*~" \
	--exclude=".*.swp" \
	--rsh="ssh" www/ mtweb:phptal/www/
	ssh mtweb "\
	cd phptal; \
	find www -type d | xargs chmod 755; \
	find www -type f | xargs chmod 644; \
	./update-latest.sh; \
	cd ..; \
	./diffuse.sh phptal"
	echo -n "Latest link is : "
	@lynx -dump http://phptal.motion-twin.com/VERSION
