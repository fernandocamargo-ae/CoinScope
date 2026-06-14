<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

// Ticker decorativo del hero (datos ilustrativos)
const ticker = [
    { symbol: 'BTC', price: '$63,499', change: '+1.2%', up: true },
    { symbol: 'ETH', price: '$1,665', change: '+0.8%', up: true },
    { symbol: 'USDT', price: '$1.00', change: '0.0%', up: true },
    { symbol: 'SOL', price: '$172', change: '-2.1%', up: false },
];

const features = [
    {
        title: 'Compra, vende e intercambia',
        desc: 'Simula las tres operaciones con saldo virtual y mira el resultado al instante.',
        icon: 'M3 12l4-4 4 4 6-6M21 6h-4M21 6v4',
    },
    {
        title: 'Precios reales de CoinGecko',
        desc: 'Cotizaciones actuales e históricas con gráficos, sin arriesgar un centavo.',
        icon: 'M4 19V5m0 14h16M8 15l3-3 2 2 4-5',
    },
    {
        title: 'Portafolio virtual',
        desc: 'Administra tu saldo en USD y tus criptomonedas con valorización en vivo.',
        icon: 'M3 7h18v12H3zM3 7l2-3h14l2 3M9 13h6',
    },
    {
        title: 'Historial y reportes',
        desc: 'Cada simulación queda registrada. Exporta a CSV o PDF cuando quieras.',
        icon: 'M9 12h6M9 16h6M9 8h2M5 3h11l4 4v14H5z',
    },
];
</script>

<template>
    <Head title="CoinScope — Simula el mercado cripto" />

    <div class="relative min-h-screen overflow-hidden bg-night text-slate-200">
        <!-- Fondos decorativos -->
        <div class="pointer-events-none absolute inset-0 bg-grid-faint [background-size:42px_42px] opacity-60"></div>
        <div class="pointer-events-none absolute -top-40 left-1/2 h-[520px] w-[820px] -translate-x-1/2 rounded-full bg-neon/20 blur-[160px]"></div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-[380px] w-[480px] rounded-full bg-ice/10 blur-[150px]"></div>

        <div class="relative mx-auto max-w-7xl px-6">
            <!-- Navbar -->
            <header class="flex items-center justify-between py-6">
                <div class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon/15 text-neon ring-1 ring-neon/30">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l9 5v10l-9 5-9-5V7z" opacity=".9"/></svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-white">CoinScope</span>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-2">
                    <Link :href="route('login')"
                          class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white">
                        Iniciar sesión
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                          class="rounded-lg bg-neon px-4 py-2 text-sm font-bold text-night shadow-neon transition hover:bg-neon-dark">
                        Crear cuenta
                    </Link>
                </nav>
            </header>

            <!-- Hero -->
            <section class="grid items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
                <div>
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-edge bg-surface/60 px-3 py-1 text-xs text-slate-400">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-neon"></span>
                        Datos en tiempo real · 100% simulado
                    </div>

                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">
                        Aprende a invertir en
                        <span class="bg-gradient-to-r from-neon to-ice bg-clip-text text-transparent">cripto</span>
                        sin arriesgar tu dinero
                    </h1>

                    <p class="mt-5 max-w-xl text-lg text-slate-400">
                        CoinScope es una plataforma de simulación: compra, vende e intercambia
                        Bitcoin, Ethereum y más con precios reales del mercado y un portafolio
                        virtual. Ideal para practicar y entender el mercado.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <Link v-if="canRegister" :href="route('register')"
                              class="rounded-xl bg-neon px-6 py-3 text-base font-bold text-night shadow-neon transition hover:bg-neon-dark">
                            Crear cuenta gratis →
                        </Link>
                        <Link :href="route('login')"
                              class="rounded-xl border border-edge bg-surface/60 px-6 py-3 text-base font-semibold text-slate-200 transition hover:border-slate-600 hover:bg-surface-2">
                            Ya tengo cuenta
                        </Link>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        Empiezas con <span class="font-semibold text-slate-300">$100,000 USD virtuales</span> para practicar.
                    </p>
                </div>

                <!-- Mock de tarjeta de portafolio -->
                <div class="relative">
                    <div class="rounded-2xl border border-edge bg-surface/80 p-6 shadow-card backdrop-blur">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-400">Valor total del portafolio</span>
                            <span class="rounded-full bg-neon/15 px-2 py-0.5 text-xs font-semibold text-neon">▲ 2.1%</span>
                        </div>
                        <div class="mt-1 text-3xl font-bold text-white">$99,996.33</div>

                        <!-- Barra de composición -->
                        <div class="mt-5 flex h-3 w-full overflow-hidden rounded-full bg-surface-2">
                            <div class="h-full bg-amber-400" style="width: 36%"></div>
                            <div class="h-full bg-neon" style="width: 51%"></div>
                            <div class="h-full bg-ice" style="width: 13%"></div>
                        </div>

                        <div class="mt-5 space-y-2">
                            <div v-for="t in ticker" :key="t.symbol"
                                 class="flex items-center justify-between rounded-lg border border-edge/60 bg-surface-2/50 px-3 py-2">
                                <span class="font-semibold text-white">{{ t.symbol }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-slate-300">{{ t.price }}</span>
                                    <span :class="t.up ? 'text-neon' : 'text-loss'" class="w-12 text-right text-xs font-semibold">
                                        {{ t.change }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute -inset-1 -z-10 rounded-2xl bg-gradient-to-tr from-neon/20 to-ice/10 blur-2xl"></div>
                </div>
            </section>

            <!-- Funciones -->
            <section class="grid gap-4 pb-20 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="f in features" :key="f.title"
                     class="group rounded-2xl border border-edge bg-surface/60 p-5 transition hover:border-neon/40 hover:bg-surface-2">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-neon/10 text-neon ring-1 ring-neon/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="f.icon" />
                        </svg>
                    </span>
                    <h3 class="mt-4 font-semibold text-white">{{ f.title }}</h3>
                    <p class="mt-1 text-sm text-slate-400">{{ f.desc }}</p>
                </div>
            </section>

            <!-- Footer -->
            <footer class="flex flex-col items-center justify-between gap-2 border-t border-edge py-6 text-xs text-slate-500 sm:flex-row">
                <span>© 2026 CoinScope · Plataforma académica de simulación.</span>
                <span>Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}</span>
            </footer>
        </div>
    </div>
</template>
