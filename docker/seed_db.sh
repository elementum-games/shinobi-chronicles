#!/bin/sh

 while ! mysqladmin status -h"$DB_HOST" -p"$DB_PASSWORD" -u"$DB_USER" >> /dev/null 2>&1; do
    echo "Waiting on database"
    sleep 1
done

./vendor/bin/phinx seed:run
./vendor/bin/phinx migrate

exec $@