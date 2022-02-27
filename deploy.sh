#!/bin/bash

# TODO: 依赖(Nginx PHP MariaDB Composer *Redis)

# Composer 依赖
composer install

# 检查.env
if [ ! -f .env ]; then
    cp .env.example .env
    echo "请填写.env中的数据库字段后重新执行脚本"
    exit 0
fi
# 加载.env
source .env

# 校验数据库连接信息
SQLTEST=`mysql -u$DB_USERNAME -p$DB_PASSWORD -e "quit" 2>&1`
if [ -n "$SQLTEST" ]; then
    echo "数据库信息不正确"
    exit 1
fi
# 运行迁移
php artisan admin:install
# 生成APP_KEY
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

echo "默认用户名/密码: admin/admin"
