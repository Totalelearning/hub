param(
    [string]$AppUser = "sail",
    [string]$AppGroup = "sail"
)

$command = "APP_USER='$AppUser' APP_GROUP='$AppGroup' sh /var/www/html/scripts/repair-laravel-permissions.sh"
docker compose exec -T laravel.test sh -lc $command
