#!/bin/sh

cat db.sql | sed -e 's/INTEGER UNSIGNED NOT NULL AUTO_INCREMENT/SERIAL NOT NULL/' \
	   | sed -e 's/UNSIGNED//' \
	   | sed -e 's/^ *INDEX.*$//' \
	   | sed -e 's/UNIQUE INDEX/UNIQUE/' \
	   > db_pg.sql

