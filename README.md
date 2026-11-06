# Nido

Sistema web para maternales y jardines de infantes: la comunicación entre el
jardín y las familias. Varios jardines comparten una misma instalación y los
datos de cada uno nunca se mezclan (`App\Security\JardinActualResolver`).

## Stack

Symfony 7.4, PHP 8.3, MySQL 8, Doctrine ORM 3, Webpack Encore con Bootstrap 5
y Sass. La base (seguridad, auditoría, log de errores, validadores) viene de
Aliar.

## Puesta en marcha local

```bash
composer install
npm install && npm run build
# .env.local: APP_ENV, APP_SECRET, DATABASE_URL (mysql://...@127.0.0.1:3306/nido) y MAILER_DSN
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console audit:schema:update --force   # tablas *_audit de la auditoría
php bin/console app:usuario:crear-super-admin <mail> <nombre> <apellido>
```

El comando muestra la contraseña inicial; en el primer ingreso se pide
cambiarla.

## Deploy

Igual que Aliar: `var/sessions/<entorno>` tiene que poder escribirlo el
servidor web, y después de cambiar controladores o `security.yaml` hay que
correr `php bin/console cache:clear`. Los logos de los jardines se guardan en
`public/uploads/jardines`, que también tiene que poder escribirse.

## Perfiles

| Perfil | Qué hace hoy |
| --- | --- |
| Super administrador | Da de alta los jardines y sus usuarios, ve la auditoría |
| Directora | Administra los usuarios de su jardín |
| Docente | Entra al sistema (módulos por venir) |
| Familia | Entra al sistema (módulos por venir) |
