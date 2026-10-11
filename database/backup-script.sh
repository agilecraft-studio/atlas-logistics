#!/bin/bash
# Atlas Logistics — PostgreSQL Database Backup Script
# Full logical backup (schema + data) via pg_dump.
# Modelled on /var/www/atlas-agro/database/backup-scritp.sh (same flow, logistics tables).
# Usage: chmod +x database/backup-script.sh && ./database/backup-script.sh
# Cron (daily 02:00):
#   0 2 * * * /var/www/atlas-logistic/database/backup-script.sh >> /var/log/atlas-logistics-backup.log 2>&1
set -u
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
if [ -f "$PROJECT_ROOT/.env" ]; then
    set -a; . "$PROJECT_ROOT/.env"; set +a
fi
DB_NAME="${DB_DATABASE:-atlas_logistics}"
DB_USER="${DB_USERNAME:-atlas_logistics}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
export PGPASSWORD="$DB_PASSWORD"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/atlas_logistics_backup_$TIMESTAMP.sql"
LOG_FILE="$BACKUP_DIR/backup_log_$TIMESTAMP.log"
mkdir -p "$BACKUP_DIR"
log() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1" | tee -a "$LOG_FILE"
}
if [ -z "$DB_PASSWORD" ]; then
    log "ERROR: DB password is empty. Set DB_PASSWORD in the environment or project .env."
    exit 1
fi
log "Starting comprehensive database backup..."
log "Database: $DB_NAME"
log "Backup file: $BACKUP_FILE"
if ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" > /dev/null 2>&1; then
    log "ERROR: PostgreSQL is not running or not accessible."
    exit 1
fi
TABLE_LIST_FILE="$BACKUP_DIR/table_list_$TIMESTAMP.txt"
psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -A -t -c "SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name;" > "$TABLE_LIST_FILE"
ESSENTIAL_TABLES=(
    "users" "users_meta" "password_resets"
    "countries" "currencies" "exchange_rates" "border_posts"
    "trip_expense_categories" "document_types"
    "vehicles" "vehicle_types" "vehicle_group" "trailers"
    "vehicle_logistics_profiles" "driver_compliance_profiles"
    "compliance_documents" "vehicle_components" "driver_vehicle"
    "trips" "trip_checkpoints" "trip_expenses" "driver_advances" "trip_incidents"
    "bookings" "booking_alerts" "booking_income" "booking_payments" "booking_quotation"
    "fuel" "service_items" "service_reminder" "work_orders" "work_order_logs"
    "vehicle_breakdown" "vehicle_review" "driver_logs" "driver_alert" "driver_payments"
    "expense" "expense_cat" "income" "income_cat"
    "parts" "parts_category" "parts_used" "vendors" "mechanics"
    "addresses" "cities" "reasons" "notes" "message" "messages"
    "reviews" "team" "testimonials" "company_services"
    "settings" "api_settings" "chat_settings" "email_content"
    "fare_settings" "frontend" "payment_settings" "twilio_settings"
)
log "Checking for essential tables..."
for table in "${ESSENTIAL_TABLES[@]}"; do
    if ! grep -qx "$table" "$TABLE_LIST_FILE"; then
        log "WARNING: Essential table '$table' not found in database!"
    else
        log "Essential table '$table' found."
    fi
done
TOTAL_TABLES=$(wc -l < "$TABLE_LIST_FILE")
log "Total tables to backup: $TOTAL_TABLES"
log "Creating full database backup (schema + data)..."
if ! pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -F p -f "$BACKUP_FILE" --verbose --clean --if-exists --no-owner --no-privileges; then
    log "ERROR: Backup failed!"
    exit 1
fi
if [ ! -s "$BACKUP_FILE" ]; then
    log "ERROR: Backup file is missing or empty after pg_dump."
    exit 1
fi
BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
log "Backup completed successfully. Backup size: $BACKUP_SIZE"
log "Compressing backup file..."
gzip -f "$BACKUP_FILE"
COMPRESSED_FILE="$BACKUP_FILE.gz"
COMPRESSED_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
log "Compression completed. Compressed size: $COMPRESSED_SIZE"
MISSING_IN_BACKUP=0
log "Verifying backup contains essential tables..."
for table in "${ESSENTIAL_TABLES[@]}"; do
    if zgrep -Fq "CREATE TABLE public.${table} " "$COMPRESSED_FILE"; then
        log "Verified: Table '$table' schema found in backup."
    else
        log "WARNING: Table '$table' schema not found in backup!"
        MISSING_IN_BACKUP=$((MISSING_IN_BACKUP + 1))
    fi
done
SUMMARY_FILE="$BACKUP_DIR/backup_summary_$TIMESTAMP.txt"
{
    echo "Backup Summary"
    echo "=============="
    echo "Date: $(date)"
    echo "Database: $DB_NAME"
    echo "Backup File: $COMPRESSED_FILE"
    echo "Original Size: $BACKUP_SIZE"
    echo "Compressed Size: $COMPRESSED_SIZE"
    echo "Total Tables: $TOTAL_TABLES"
    echo "Missing From Backup: $MISSING_IN_BACKUP"
    echo ""
    echo "Essential Tables:"
    for table in "${ESSENTIAL_TABLES[@]}"; do
        if zgrep -Fq "CREATE TABLE public.${table} " "$COMPRESSED_FILE"; then
            echo "OK $table"
        else
            echo "MISSING $table (NOT FOUND)"
        fi
    done
} > "$SUMMARY_FILE"
log "Backup summary created: $SUMMARY_FILE"
if [ "$MISSING_IN_BACKUP" -gt 0 ]; then
    log "WARNING: $MISSING_IN_BACKUP essential table(s) missing from backup."
else
    log "Backup process completed successfully."
fi
RESTORE_SCRIPT="$BACKUP_DIR/restore_${TIMESTAMP}.sh"
printf '%s\n' '#!/bin/bash' "# Atlas Logistics restore from $TIMESTAMP" 'read -p "Restore OVERWRITES DB. Continue? (y/n): " -n 1 -r; echo' 'if [[ ! $REPLY =~ ^[Yy]$ ]]; then echo Cancelled; exit 1; fi' 'TMP_SQL=$(mktemp /tmp/atlas_logistics_restore_XXXXXX.sql)' 'gunzip -c "$1" > "$TMP_SQL"' 'psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -v ON_ERROR_STOP=1 -f "$TMP_SQL"' 'rm "$TMP_SQL"' > "$RESTORE_SCRIPT"
chmod +x "$RESTORE_SCRIPT"
log "Restore script created: $RESTORE_SCRIPT"

log "Cleaning up old backups..."
ls -t "$BACKUP_DIR"/atlas_logistics_backup_*.sql.gz 2>/dev/null | tail -n +8 | xargs -r rm --
ls -t "$BACKUP_DIR"/backup_log_*.log 2>/dev/null | tail -n +8 | xargs -r rm --
ls -t "$BACKUP_DIR"/table_list_*.txt 2>/dev/null | tail -n +8 | xargs -r rm --
ls -t "$BACKUP_DIR"/backup_summary_*.txt 2>/dev/null | tail -n +8 | xargs -r rm --
ls -t "$BACKUP_DIR"/restore_*.sh 2>/dev/null | tail -n +8 | xargs -r rm --
log "Backup process completed."
echo "Backup completed. Backup: $COMPRESSED_FILE — see log: $LOG_FILE"