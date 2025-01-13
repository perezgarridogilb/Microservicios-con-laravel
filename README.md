# Auth-Service Microservicio

Este proyecto es un microservicio desarrollado en Laravel para gestionar la autenticación y autorización utilizando JWT.

#### Pasos para la Configuración y Ejecución


#### Crear un Nuevo Proyecto Laravel
Si se crea desde una versión de PHP como 8.2, mantener los Dockerfile igual
```composer create-project --prefer-dist laravel/laravel:^10.0 auth-service```

#### Instalar y Configurar JWT-Auth
[jwt-auth documentación](https://jwt-auth.readthedocs.io/en/develop/quick-start/ "jwt-auth documentación")
```
composer require tymon/jwt-auth
php artisan jwt:secret
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"```

#### Instalar Firebase PHP-JWT
Usado dentro de la configuración del middleware
```composer require firebase/php-jwt```

#### Crear un Middleware para JWT
```php artisan make:middleware JwtMiddleware```

#### Construcción de la Imagen Docker
```docker build -t my-php-app .```

#### Levantar los servicios con Docker Compose
```docker-compose up --build```

#### Acceder al contenedor
```docker exec -it auth-service /bin/bash```

#### Comprobar el estado de las migraciones
```php artisan migrate:status```

#### Ejecutar migraciones si no se han realizado
```php artisan migrate```

### Instalar para trabajar con MongoDB (mover los dll primero).
En Macos es una serie de comandos
```
cd /Users/macbook/Downloads/mongodb-1.20.1/mongodb-1.20.1
brew install autoconf automake
phpize
./configure
// Que correspondan
phpize -v

php -v
// instala
make && make install
// dentro del microservicio que usa MongoDB
composer require mongodb/mongodb

```

#### php.ini

```
extension=mongodb.so
```
#### Reiniciar php

```
brew services restart php
```

#### Env

```
MONGO_URI=mongodb://root:root@localhost:27020/admin
```

Creando job y tablas del queue
```
php artisan make:job sendOrderShippedEmail
php artisan queue:table
macbook@MacBook-Pro-de-MacBook ~/D/M/email-service (master) [1]> php artisan migrate

   INFO  Preparing database.

  Creating migration table ........................................ 388ms DONE

   INFO  Running migrations.

  2014_10_12_000000_create_users_table ............................ 203ms DONE
  2014_10_12_100000_create_password_reset_tokens_table ............. 61ms DONE
  2019_08_19_000000_create_failed_jobs_table ...................... 123ms DONE
  2019_12_14_000001_create_personal_access_tokens_table ........... 127ms DONE
  2025_01_13_012109_create_jobs_table .............................. 92ms DONE
```