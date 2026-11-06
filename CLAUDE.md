# Nido

Sistema web para maternales y jardines de infantes de la ciudad de Santa Fe:
la comunicación entre el jardín y las familias. No tiene relación con Aliar
(`/var/www/html/proyecto-aliar`), pero se armó sobre su base y sigue su línea.

## Material de referencia

- Brochure: `/home/pablo/Descargas/Brochure Nido.pdf` (3 páginas).
- Recorrido de la maqueta: `/home/pablo/Descargas/Nido_recorrido.mp4` (1:56,
  vertical). Primero el equipo del jardín (la directora ve el día y envía un
  comunicado a una sala, también por WhatsApp), después las familias
  (comunicados marcados como Nuevo, cuaderno del día, cumpleaños y eventos,
  avisar una falta y subir el certificado, fotos de la sala, cuotas con
  comprobante, docentes).

## Decisiones

- Varios jardines en una misma instalación; los datos de cada uno nunca se
  mezclan. Todo pasa por `App\Security\JardinActualResolver`. El super
  administrador no tiene jardín y ve todos.
- Todos entran con mail y contraseña. "Soy familia / Soy del equipo" en el
  login es sólo visual (cambia la ayuda).
- Perfiles (`App\Security\RoleHelper`): Directora, Docente, Familia y Super
  administrador. Las pantallas piden permisos finos de `role_hierarchy`
  (`security.yaml`), nunca el perfil.
- La contraseña inicial la genera el sistema y se muestra una sola vez; en el
  primer ingreso se obliga a cambiarla.
- Textos en voseo, como el brochure ("Ingresá tu mail", "Entrá para ver...").
- Diseño del brochure: petróleo `#1f4d5a`, crema `#fff8ee`, amarillo
  `#f2c04d`, terracota `#b2472e` (acción principal), suaves `#fbe5b6` y
  `#fce0d4`. Tipografías Baloo 2 (títulos) y Nunito (texto), identificadas a
  ojo. Pensado primero para el celular: navegación en píldoras, tarjetas
  blancas redondeadas, tablas que pasan a tarjetas (`.table-responsive-stack`).
- Herramientas del frontend con las mismas versiones que Aliar (Encore 5,
  Babel 7); las más nuevas no compilaban. Turbo Drive desactivado.

## Estado

Hecho (sin commitear): base reutilizada de Aliar (zona horaria, login con
límite de intentos, restablecer y cambiar contraseña, usuarios
deshabilitados, cierre por inactividad, auditoría, log de errores por mail,
validadores de CUIL y contraseña), Jardines (logo y color) y Usuarios.

Módulos que siguen, en este orden:

1. Salas, docentes y chicos: ficha del chico con alergias destacadas,
   certificados médicos y personas autorizadas a retirar; familias vinculadas
   a cada chico; ficha de la docente con foto, formación y sala a cargo.
2. Comunicados a una sala o a todo el jardín, con confirmación de lectura y
   envío por WhatsApp.
3. Cuaderno diario de maternal (comida, siesta, pañales, ánimo, qué traer).
4. Asistencia y faltas avisadas por la familia, con certificado.
5. Fotos por sala, cumpleaños y calendario de eventos.
6. Cuotas: comprobante subido por la familia, estados Pendiente, En revisión
   y Pagada, recordatorio antes del vencimiento.
7. Panel de dirección: presentes del día, faltas avisadas, cuotas
   pendientes, quién no leyó un comunicado, resumen por sala.

## Local

- `php -S 127.0.0.1:8000 -t public` (o `symfony serve`); `npm run watch` al
  tocar `assets/`; `php bin/console cache:clear` al tocar `config/`.
- Base MySQL `nido`. Las tablas `*_audit` se crean con
  `php bin/console audit:schema:update --force`, no con las migraciones.
- Usuarios de prueba: `admin@nido.local` / `Admin2026!` (super
  administrador) y `andrea@girasoles.local` / `Andrea2026!` (directora del
  "Jardín Los Girasoles").

## Forma de trabajo

- Responder en español rioplatense.
- No commitear sin que se pida; los commits sin línea de coautor.
- Escribir el código como el de Aliar: comentarios en español que explican el
  porqué, nombres en español.
