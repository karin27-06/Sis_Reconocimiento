# Sistema de Reconocimiento facial, dactilar y voz – Laravel 12 + Vue.js + PrimeVue + PostgreSQL + Python (2025)
**Sistema de reconocimiento para espacios de trabajo** es una aplicación full-stack desarrollada por  
[Karin27-06](https://github.com/karin27-06) y [PabloLupuX](https://github.com/PabloLupuX)
utilizando **Laravel 12**, **Vue.js** y la librería de componentes **PrimeVue** para la interfaz de usuario, con base de datos **PostgreSQL**.

🔗 Repositorio: (https://github.com/karin27-06/Sis_Reconocimiento.git)

---

## 🧰 Prerequisites

- ⚙️ **PHP** >= 8.1  
- 📦 **Composer**  
- 📟 **Node.js** y **npm**  
- 🐘 **PostgreSQL**
- **Python**  

---

## 🚀 Installation

1. 🔽 Clonar el repositorio:

    ```bash
    git clone https://github.com/karin27-06/Sis_Reconocimiento.git
    cd Sis_Reconocimiento
    ```

2. 📄 Copiar archivo de entorno:

    ```bash
    cp .env.example .env
    ```

3. 📥 Instalar dependencias PHP:

    ```bash
    composer install
    ```

4. 📥 Instalar dependencias frontend:

    ```bash
    npm install
    ```

5. 🔐 Generar llave de la aplicación:

    ```bash
    php artisan key:generate
    ```

6. ⚙️ Configurar la base de datos en `.env`:

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

7. 🗄️ Ejecutar migraciones y seeders (si aplican):

    ```bash
    php artisan migrate --seed
    ```

8. 🎨 Compilar assets frontend:

    ```bash
    npm run dev
    ```

9. 🚀 (Opcional) Levantar servidor local:

    ```bash
    php artisan serve
    ```

---

## 🎨 UI Framework

La interfaz está construida con **Vue.js** y utiliza **PrimeVue** para componentes modernos y responsivos. Ademas de utilizar python y otras librerias para reconocimiento facial y dactilar

---

## 👨‍💻 Developers

- [Karin27-06](https://github.com/karin27-06)  
- [PabloLupuX](https://github.com/PabloLupuX)  

---
### 📸 Team Pictures

<details>
  <summary>Landing page</summary>
  <p align="center">
    <img src="./public/imagenes/access/ini_ses.jpg" alt="Ini Ses" width="600">
  </p>
</details>

<details>
  <summary>Inicio de sesion</summary>
  <p align="center">
    <img src="./public/imagenes/access/landing.jpg" alt="Landing" width="600">
  </p>
</details>

<details>
  <summary>Sistema</summary>
  <p align="center">
    <img src="./public/imagenes/access/inicio.jpg" alt="Inicio" width="600">
  </p>
</details>

## 📄 License

> This project is protected under the  
> **Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)** license.

You are welcome to study, use, and adapt this code strictly for **non-commercial purposes**.  
Any commercial use, distribution, or reproduction without explicit, prior written consent from the authors is **strictly prohibited**.

Please review the full terms and conditions in the [LICENSE](./LICENSE) file to ensure compliance.

---

✨ _Thank you for checking out the project!_
