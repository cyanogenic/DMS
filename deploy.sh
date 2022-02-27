#!/bin/bash

# 依赖(nginx php mariadb composer *redis)
composer install
php artisan admin:install
echo "默认用户名/密码: admin/admin"
