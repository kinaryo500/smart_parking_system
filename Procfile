web: php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
mqtt_worker: php artisan mqtt:subscribe
queue_worker: php artisan queue:work --tries=3 --timeout=90