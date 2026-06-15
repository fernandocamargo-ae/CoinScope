# CoinScope — Manual Técnico

**Plataforma Web para la Simulación de Compra, Venta e Intercambio de Criptomonedas**

Universidad Mariano Gálvez de Guatemala · Área de Análisis
Autor: Fernando Camargo · Carné 0908-19-2575

---

## Índice

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

---

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

*Documento generado como parte de los entregables del proyecto CoinScope.*
