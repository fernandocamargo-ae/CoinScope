# CoinScope — Manual Técnico y de Usuario

**Plataforma Web para la Simulación de Compra, Venta e Intercambio de Criptomonedas**

Universidad Mariano Gálvez de Guatemala · Área de Análisis
Autor: Fernando Camargo · Carné 0908-19-2575

---

## Índice

**Parte I — Manual Técnico**
1. Descripción general
2. Stack tecnológico
3. Requisitos del sistema
4. Arquitectura del software
5. Estructura de carpetas
6. Modelo de datos
7. Integración con la API externa y estrategia de caché
8. Instalación en entorno local
9. Despliegue en producción (hosting compartido)
10. Mapa de rutas (endpoints)

**Parte II — Manual de Usuario**
11. Registro y estado inicial
12. Inicio de sesión
13. Dashboard (mi portafolio)
14. Comprar
15. Vender
16. Intercambiar
17. Precios históricos
18. Historial y reportes (CSV/PDF)
19. Auditoría
20. Ajustes del portafolio
21. Cerrar sesión

---

# PARTE I — MANUAL TÉCNICO

## 1. Descripción general

CoinScope es una plataforma web que permite a los usuarios **simular** operaciones de compra, venta e intercambio de criptomonedas utilizando **precios reales** obtenidos de una API externa, sin arriesgar dinero real. Cada usuario administra un **portafolio virtual** (saldo en USD + criptomonedas) cuyo estado solo se modifica cuando el usuario **guarda** una simulación.

## 2. Stack tecnológico

| Componente | Tecnología |
|------------|------------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js |
| Estilos | Tailwind CSS 3 |
| Base de datos | MySQL 8/9 |
| Autenticación | Laravel Breeze |
| API de criptomonedas | CoinGecko |
| API de tipo de cambio | open.er-api.com (USD→GTQ) |
| Generación de PDF | barryvdh/laravel-dompdf |
| Rutas en JS | Ziggy |
| Empaquetado de assets | Vite |
| Arquitectura | DDD Lite pragmático (Services, Repositories, DTOs) |

## 3. Requisitos del sistema

- **PHP** 8.2 o superior, con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`, `gd`, `dom`.
- **Composer** 2.x
- **Node.js** 18+ y **npm** (solo para compilar los assets)
- **MySQL** 8 o superior
- Conexión a Internet (para consultar CoinGecko y el tipo de cambio)

## 4. Arquitectura del software

La aplicación sigue un enfoque **DDD Lite pragmático** organizado en capas lógicas:

```
PRESENTACIÓN      Vue 3 + Inertia · Controllers
       │
APLICACIÓN/NEGOCIO  Services (lógica de negocio) · DTOs
       │
ACCESO A DATOS    Repositories · CryptoPriceService
       │
PERSISTENCIA      Modelos Eloquent · MySQL
```

**Flujo de una operación:** `Vue → Controller → Service → Repository → Eloquent/MySQL`, con un `CryptoPriceService` dedicado a la integración con la API.

**Componentes clave:**
- **Services**
  - `CryptoPriceService` — consulta precios actuales/históricos a CoinGecko, maneja el caché, convierte criptos y guarda snapshots de precio.
  - `SimulationService` — lógica de compra/venta/intercambio. Separa `preview*` (calcula sin guardar) de `execute*` (persiste y afecta el portafolio dentro de una transacción).
  - `AuditService` — registra eventos importantes en `audit_logs`.
- **Repository** — `SimulationRepository` (con su interfaz `SimulationRepositoryInterface`) aísla el acceso a datos de las simulaciones.
- **DTO** — `SimulationResultData` transporta el resultado calculado entre capas.

## 5. Estructura de carpetas

```
app/
├── DTOs/                    SimulationResultData.php
├── Http/Controllers/        Dashboard, Simulation, Price, Audit, Settings, Auth/...
├── Models/                  User, Portfolio, PortfolioAsset, Cryptocurrency,
│                            PriceSnapshot, Simulation, AuditLog
├── Repositories/
│   ├── Contracts/           SimulationRepositoryInterface.php
│   └── Eloquent/            SimulationRepository.php
└── Services/                CryptoPriceService, SimulationService, AuditService

resources/js/
├── Pages/                   Dashboard, Welcome, Auth/, Simulations/, Prices/, Audit/, Settings/
├── Layouts/                 AuthenticatedLayout, GuestLayout
├── Components/              componentes reutilizables (Breeze)
└── composables/             useMoney.js (formato USD/GTQ)

database/migrations/         migraciones de todas las tablas
lang/es/                     traducciones (auth, validation, passwords, pagination)
docs/                        documentación del proyecto
```

## 6. Modelo de datos

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios (incluye `display_currency` para preferencia USD/GTQ) |
| `portfolios` | Portafolio del usuario (saldo base `usd_balance`) |
| `portfolio_assets` | Saldo por criptomoneda (modelo dinámico y extensible) |
| `cryptocurrencies` | Catálogo de criptos (`symbol`, `api_id`, `is_active`) |
| `price_snapshots` | Histórico de precios consultados (RN-05/RN-06) |
| `simulations` | Simulaciones guardadas (`type`: BUY/SELL/EXCHANGE) |
| `audit_logs` | Auditoría de eventos del usuario |
| `cryptocurrency_user` | Criptomonedas favoritas por usuario (pivote) |

> La escalabilidad (RNF-004) se logra con `portfolio_assets` dinámico + el campo `is_active`: se pueden agregar criptos sin alterar el esquema.

## 7. Integración con la API externa y estrategia de caché

Para evitar saturar la API, se usa la capa de caché de Laravel (`Cache::remember`). El código es independiente del driver (hoy `database`, conmutable a Redis solo cambiando el `.env`).

| Información | Vigencia del caché |
|------------|--------------------|
| Precio actual | 5 minutos |
| Histórico (gráfico) | 24 horas |
| Tipo de cambio USD/GTQ | 1 hora |

Cada vez que se consulta CoinGecko (caché vencido), se guarda automáticamente un `price_snapshot`. Si la API de divisas falla, se usa un tipo de cambio de respaldo configurable.

## 8. Instalación en entorno local

```bash
# 1. Dependencias de PHP
composer install

# 2. Archivo de entorno + clave
cp .env.example .env     # (en Windows: copy .env.example .env)
php artisan key:generate

# 3. Configurar en el .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Crear la base de datos en MySQL
#    CREATE DATABASE coinscope;

# 5. Migraciones
php artisan migrate

# 6. Datos iniciales (criptomonedas)
php artisan db:seed --class=CryptocurrencySeeder

# 7. Dependencias de Node y compilación de assets
npm install
npm run build

# 8. Levantar el servidor
php artisan serve
```

Acceso: `http://127.0.0.1:8000`

## 9. Despliegue en producción (hosting compartido)

1. Compilar los assets **localmente**: `npm run build` (genera `public/build/`).
2. Subir el proyecto al servidor **excepto** `node_modules/` y `.env`.
3. Crear el `.env` de producción con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` del dominio y las credenciales de la base de datos.
4. En el servidor (SSH):
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan db:seed --class=CryptocurrencySeeder --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
5. La carpeta pública (`public/`) debe ser la **raíz del dominio o subdominio** (document root). En hosting compartido se recomienda un **subdominio** apuntando a `.../public`.

> Tras cualquier cambio de código PHP en producción: `php artisan optimize:clear`.

## 10. Mapa de rutas (endpoints)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/` | Landing público |
| GET/POST | `/register` | Registro (con estado inicial) |
| GET/POST | `/login`, `/logout` | Autenticación |
| GET | `/dashboard` | Portafolio del usuario |
| GET/POST | `/simulations/buy` | Formulario y guardado de compra |
| GET/POST | `/simulations/sell` | Formulario y guardado de venta |
| GET/POST | `/simulations/exchange` | Formulario y guardado de intercambio |
| GET | `/simulations/history` | Historial con filtros |
| GET | `/simulations/export` | Exportar historial a CSV |
| GET | `/simulations/export-pdf` | Exportar historial a PDF |
| GET | `/prices` | Precios históricos (gráfico) |
| GET | `/prices/history` | Serie histórica (JSON) |
| GET | `/prices/current` | Precio actual + tipo de cambio (JSON) |
| GET | `/audit` | Auditoría de actividades |
| GET/PATCH/POST | `/settings/*` | Moneda, fondear/reiniciar, favoritas |

---

# PARTE II — MANUAL DE USUARIO

## 11. Registro y estado inicial

1. En la página de inicio, clic en **"Crear cuenta"**.
2. Ingresa tu **nombre, correo y contraseña**.
3. En la sección **"Estado inicial"**:
   - Define tu **saldo inicial en USD** (por defecto $100,000).
   - Opcionalmente, indica la **cantidad** de cada criptomoneda que ya posees (BTC, ETH, USDT, SOL). Si no tienes, déjalas en blanco.
4. Clic en **"Crear cuenta"**. Entrarás automáticamente al sistema con ese estado inicial.

## 12. Inicio de sesión

1. Clic en **"Iniciar sesión"**.
2. Ingresa tu correo y contraseña.
3. (Opcional) marca **"Recordarme"** para mantener la sesión.

## 13. Dashboard (mi portafolio)

Al entrar verás:
- **Valor total del portafolio** (efectivo + criptomonedas, valorizado a precio actual).
- Desglose de **efectivo** y **valor en cripto**.
- **Composición** de tus criptomonedas (barra de porcentajes).
- Tabla de **tus criptomonedas** con cantidad, precio actual y valor. Las **favoritas** se marcan con una estrella ★.
- Accesos rápidos a Comprar / Vender / Intercambiar.

## 14. Comprar

1. Menú **Comprar**.
2. Elige la criptomoneda. Verás su **precio en vivo**.
3. Escribe el **monto en USD** (o usa los botones rápidos $100/$500/$1,000/$5,000/Máx). El sistema calcula **al instante** cuánta cripto recibirás.
4. Clic en **"Comprar [símbolo]"**. La simulación se guarda y tu saldo USD baja, tu cripto sube.

## 15. Vender

1. Menú **Vender**.
2. Elige una criptomoneda que poseas.
3. Indica la **cantidad** (o usa 25/50/75/100%). Verás el resultado en **USD y en Quetzales (GTQ)**.
4. Clic en **"Vender [símbolo]"**. Tu cripto baja, tu saldo USD sube.

## 16. Intercambiar

1. Menú **Intercambiar**.
2. Selecciona la cripto de **origen** y la de **destino** (deben ser distintas).
3. Indica la **cantidad** a entregar. El sistema muestra cuánto recibirás de la cripto destino.
4. Clic en **"Intercambiar"**. Cambia la composición de tu portafolio **sin tocar tu saldo en efectivo**.

## 17. Precios históricos

1. Menú **Precios**.
2. Elige una criptomoneda y un rango (**24h / 7d / 30d / 90d**).
3. Verás el **precio actual**, la **variación %** y un **gráfico** de la evolución, con mínimo y máximo.

## 18. Historial y reportes (CSV/PDF)

1. Menú **Historial**.
2. Verás todas tus simulaciones (fecha `dd/mm/aaaa`, tipo COMPRA/VENTA/INTERCAMBIO, detalle y monto).
3. Usa el **filtro por tipo** para acotar.
4. Botones **CSV** y **PDF** para **exportar** el historial (respetan el filtro activo).

## 19. Auditoría

1. Menú **Auditoría**.
2. Muestra la **trazabilidad** de tu cuenta: registro, inicios y cierres de sesión, y cada simulación, con fecha, hora e IP.

## 20. Ajustes del portafolio

Desde el menú de usuario (arriba a la derecha) → **Ajustes**:
- **Moneda de visualización:** cambia entre **USD** y **GTQ**; todos los montos del panel se muestran en la moneda elegida.
- **Saldo virtual:** **agrega** USD de práctica (atajos +$10,000 / +$50,000) o **reinicia** el portafolio al estado inicial (se conserva el historial).
- **Criptomonedas favoritas:** márcalas con la estrella para destacarlas en el dashboard.

## 21. Cerrar sesión

Menú de usuario (arriba a la derecha) → **Cerrar sesión**.

---

*Documento generado como parte de los entregables del proyecto CoinScope.*
