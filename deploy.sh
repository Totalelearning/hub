#!/bin/bash
set -e

# =============================================================================
# TotaleLearning Hub — Production Deployment Script
# =============================================================================
#
# USAGE:
#   1. Copy the entire project to the server:
#      scp -r . nishanta@100.79.55.39:/home/nishanta/totale-learning/
#
#   2. SSH into the server:
#      ssh nishanta@100.79.55.39
#
#   3. Run this script:
#      cd /home/nishanta/totale-learning
#      chmod +x deploy.sh
#      sudo bash deploy.sh
#
# =============================================================================

echo "======================================="
echo "  TotaleLearning Hub — Deploying..."
echo "======================================="

# --- Check Docker is installed ---
if ! command -v docker &> /dev/null; then
    echo "[1/8] Installing Docker..."
    apt-get update
    apt-get install -y ca-certificates curl gnupg
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    systemctl enable docker
    systemctl start docker
    echo "  Docker installed."
else
    echo "[1/8] Docker already installed."
fi

# --- Check Docker Compose plugin ---
if ! docker compose version &> /dev/null; then
    echo "ERROR: docker compose plugin not found. Install docker-compose-plugin."
    exit 1
fi
echo "[2/8] Docker Compose available."

# --- Generate app key if missing ---
if grep -q "^APP_KEY=$" .env.production; then
    echo "[3/8] Generating application key..."
    APP_KEY=$(docker run --rm -v "$(pwd)":/app -w /app php:8.2-cli php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    sed -i "s|^APP_KEY=$|APP_KEY=${APP_KEY}|" .env.production
    echo "  Key generated."
else
    echo "[3/8] App key already set."
fi

# --- Set secure DB password if still default ---
if grep -q "DB_PASSWORD=changeme" .env.production; then
    echo "[4/8] Generating secure database password..."
    DB_PASS=$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 32)
    sed -i "s|DB_PASSWORD=changeme|DB_PASSWORD=${DB_PASS}|" .env.production
    echo "  DB password set."
else
    echo "[4/8] DB password already customised."
fi

# --- Export DB creds for compose ---
export DB_DATABASE=$(grep ^DB_DATABASE .env.production | cut -d= -f2)
export DB_USERNAME=$(grep ^DB_USERNAME .env.production | cut -d= -f2)
export DB_PASSWORD=$(grep ^DB_PASSWORD .env.production | cut -d= -f2)

# --- Build and start containers ---
echo "[5/8] Building containers (this may take a few minutes on first run)..."
docker compose -f docker-compose.prod.yml build --no-cache

echo "[6/8] Starting services..."
docker compose -f docker-compose.prod.yml up -d

# --- Wait for database ---
echo "[7/8] Waiting for PostgreSQL..."
sleep 5
until docker compose -f docker-compose.prod.yml exec -T pgsql pg_isready -U "$DB_USERNAME" > /dev/null 2>&1; do
    echo "  Waiting for database..."
    sleep 2
done
echo "  PostgreSQL is ready."

# --- Enable pgvector extension ---
docker compose -f docker-compose.prod.yml exec -T pgsql psql -U "$DB_USERNAME" -d "$DB_DATABASE" -c "CREATE EXTENSION IF NOT EXISTS vector;" 2>/dev/null || true

# --- Run migrations and seed ---
echo "[8/8] Running migrations and seeders..."
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --class=DemoComplianceDataSeeder --force
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link --force 2>/dev/null || true
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize

echo ""
echo "======================================="
echo "  DEPLOYMENT COMPLETE"
echo "======================================="
echo ""
echo "  URL:   http://100.79.55.39"
echo "  Admin: admin@totalelearning.local / password"
echo ""
echo "  Useful commands:"
echo "    docker compose -f docker-compose.prod.yml logs -f        # View logs"
echo "    docker compose -f docker-compose.prod.yml exec app bash  # Shell into app"
echo "    docker compose -f docker-compose.prod.yml down           # Stop services"
echo "    docker compose -f docker-compose.prod.yml up -d          # Start services"
echo ""
