#!/bin/bash
# Atlas Logistics restore from 20261011_175629
read -p "Restore OVERWRITES DB. Continue? (y/n): " -n 1 -r; echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then echo Cancelled; exit 1; fi
TMP_SQL=$(mktemp /tmp/atlas_logistics_restore_XXXXXX.sql)
gunzip -c "$1" > "$TMP_SQL"
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -f "$TMP_SQL"
rm "$TMP_SQL"
