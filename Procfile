web: $(composer config bin-dir)/heroku-php-nginx -C nginx.conf ./
release: if [ -n "${DB_HOST}${KNOWN_DATABASE_URL}" ]; then php release.php; fi