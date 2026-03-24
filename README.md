# 🏢 HubCorp - Portal de Noticias Corporativo

HubCorp es una aplicación web dinámica diseñada para la comunicación interna de empresas. Permite a los empleados mantenerse informados mediante un tablón de noticias, interactuar a través de comentarios y gestionar contenido según su nivel de privilegios.

<img width="1624" height="438" alt="image" src="https://github.com/user-attachments/assets/73a1cb6f-dcbf-47de-a569-1e2632ed6448" />

## 🚀 Características

- **Gestión de Usuarios:** Sistema de Login y perfiles con avatares dinámicos.
- **Sistema de Roles:**
  - `Admin`: Control total, edición y borrado de cualquier noticia.
  - `Redactor`: Puede publicar y gestionar sus propios artículos.
  - `Usuario`: Visualización y participación mediante comentarios.
- **Tablón Dinámico:** Noticias categorizadas con imágenes y formatos de fecha optimizados.
- **Interactividad:** Sistema de comentarios en tiempo real con vinculación de perfiles.
- **Diseño Responsive:** Interfaz moderna construida con Bootstrap 5.

## 🛠️ Tecnologías utilizadas

- **Backend:** PHP 8.x (Arquitectura modular).
- **Base de Datos:** MySQL / MariaDB.
- **Frontend:** HTML5, CSS3, JavaScript.
- **Framework UI:** Bootstrap 5.
- **Librerías:** [UI Avatars](https://ui-avatars.com/) para perfiles predeterminados.

## 📦 Instalación y Configuración

Sigue estos pasos para correr el proyecto localmente con XAMPP:

1. **Clonar el repositorio:**
   git clone [https://github.com/javiermole12/BlogEmpresa.git](https://github.com/javiermole12/BlogEmpresa.git)
   
   **IMPORTANTE**
   DENTRO de C:\xampp\htdocs

3. Configurar la Base de Datos:
- Abre PHPMyAdmin.
- Crea una base de datos llamada blog_empresa_db.
- Importa el archivo .sql que se encuentra en la carpeta del proyecto.

3. Conexión:
Edita el archivo includes/conexion.php con estas credenciales:

$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'blog_empresa_db';

4. Corre en el navegador: http://localhost/BlogEmpresa/ 

Tienes las credenciales en el login.


👤 Autores
Hugo Rodriguez y Javier Moleon
