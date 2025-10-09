# 📸 backendphotograpy: API para el Sitio Web de Fierros's Photography
Este repositorio contiene el código fuente de la API (Backend) desarrollada en PHP puro para dar soporte al frontend (Angular) del sitio web de fotografía de Efren Fierro.

La API se encarga de las funcionalidades críticas, como la gestión de la galería, la búsqueda avanzada por reconocimiento facial (simulado) y el flujo de pago/descarga segura a través de Stripe.

# 🚀 Tecnologías Clave
Lenguaje: PHP (puro)

Gestor de Dependencias: Composer

Pagos: Stripe API (Checkout Sessions)

Correo: PHPMailer

# 🛠️ Configuración Inicial
Para que el proyecto funcione correctamente, es necesario realizar tres pasos de configuración cruciales.

# 1. Variables de Entorno y Claves Secretas
Debes reemplazar los placeholders en los siguientes archivos con tus claves secretas y configuraciones reales.

Archivo

Variables a Configurar

Descripción

create_checkout.php

\Stripe\Stripe::setApiKey('sk_test_...')

Clave Secreta de Stripe. Necesaria para crear sesiones de pago.

verify_payment.php

\Stripe\Stripe::setApiKey('sk_test_...')

Clave Secreta de Stripe. Necesaria para verificar la sesión de pago.

send_contact.php

$mail->Username, $mail->Password, $mail->setFrom, $mail->addAddress

Credenciales SMTP (ej. Gmail App Password) y correos de origen/destino.

download.php

$STORAGE_ROOT

Ruta ABSOLUTA y segura (fuera del directorio web público) donde se guardan las imágenes originales de alta resolución.

# 2. Instalación de Dependencias
Ejecuta Composer para instalar las librerías necesarias (Stripe y PHPMailer).

composer install

# 3. Estructura de Carpetas
Asegúrate de que la estructura de carpetas de la API sea similar a esta (asumiendo que los endpoints están dentro de una carpeta /api):

# backendphotograpy/
├── api/
│   ├── create_checkout.php
│   ├── download.php
│   ├── get_photos.php
│   ├── search_by_face.php
│   ├── send_contact.php
│   └── verify_payment.php
├── private_storage/
│   └── images/  <-- Tus imágenes de alta resolución (protegidas)
├── vendor/      <-- Creada por Composer
└── ... (otros archivos)

#⚙️ Endpoints de la API
El backend expone las siguientes funciones a través de sus archivos PHP:

Archivo

Método

Descripción

get_photos.php

# GET

Obtiene la lista completa de fotos disponibles para la galería principal. (Simulación de DB/Fuente).

search_by_face.php

# POST

Recibe una imagen (searchImage) vía multipart/form-data. Simula el proceso de reconocimiento facial y devuelve los IDs de las fotos que coinciden.

send_contact.php

# POST

Recibe datos de contacto (name, email, message) y utiliza PHPMailer para enviar un correo electrónico.

create_checkout.php

# POST

Recibe un photoId. Crea una Stripe Checkout Session y devuelve la URL de redirección al frontend.

verify_payment.php

# POST

Recibe sessionId y photoId de Stripe. Verifica que el pago haya sido exitoso y, si lo fue, genera y almacena un token de descarga temporal.

download.php

# GET

Recibe un token y un id de imagen. Verifica la validez y expiración del token y fuerza la descarga segura de la imagen de alta resolución desde el directorio privado.
