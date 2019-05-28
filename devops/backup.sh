#!/usr/bin/env bash
mkdir -p /backup/mysql/latest
USER=robot_user
PASSWORD=ByYX8NPe6kbu
HOST=192.168.10.11
DATABASE=alarmdb
DB_FILE=/backup/mysql/latest/alarmdb.sql
EXCLUDED_TABLES=(
)

IGNORED_TABLES_STRING=''
for TABLE in "${EXCLUDED_TABLES[@]}"
do :
   IGNORED_TABLES_STRING+=" --ignore-table=${DATABASE}.${TABLE}"
done

echo "Dump structure"
mysqldump --host=${HOST} --user=${USER} --password=${PASSWORD} --single-transaction --no-data --routines ${DATABASE} | pv --progress --size 1m > ${DB_FILE}

echo "Dump content"
mysqldump --host=${HOST} --user=${USER} --password=${PASSWORD} ${DATABASE} --no-create-info --skip-triggers ${IGNORED_TABLES_STRING} | pv --progress --size 1m >> ${DB_FILE}

echo "Compress"
cd /backup/mysql/latest/ && tar -czf alarmdb.sql.gz alarmdb.sql