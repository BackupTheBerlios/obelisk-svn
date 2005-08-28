#!/bin/sh

cat db.sql | sed -e 's/INTEGER UNSIGNED NOT NULL AUTO_INCREMENT/SERIAL NOT NULL/' \
	   | sed -e 's/UNSIGNED//' \
	   | sed -e 's/UNIQUE INDEX/UNIQUE/' \
	   | sed -e 's/^ *INDEX.*$//' \
	   > db_pg.sql

