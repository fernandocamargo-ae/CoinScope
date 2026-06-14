<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-night text-slate-200">
        <nav class="sticky top-0 z-40 border-b border-edge bg-surface/80 backdrop-blur">
            <!-- Primary Navigation Menu -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')" class="flex items-center gap-2">
                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-neon/15 text-neon ring-1 ring-neon/30">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l9 5v10l-9 5-9-5V7z" opacity=".9"/></svg>
                                </span>
                                <span class="text-lg font-bold tracking-tight text-white">CoinScope</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</NavLink>
                            <NavLink :href="route('simulations.buy.form')" :active="route().current('simulations.buy.form')">Comprar</NavLink>
                            <NavLink :href="route('simulations.sell.form')" :active="route().current('simulations.sell.form')">Vender</NavLink>
                            <NavLink :href="route('simulations.exchange.form')" :active="route().current('simulations.exchange.form')">Intercambiar</NavLink>
                            <NavLink href="/prices" :active="$page.url.startsWith('/prices')">Precios</NavLink>
                            <NavLink :href="route('simulations.history')" :active="route().current('simulations.history')">Historial</NavLink>
                            <NavLink href="/audit" :active="$page.url.startsWith('/audit')">Auditoría</NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <!-- Settings Dropdown -->
                        <div class="relative ms-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-lg">
                                        <button type="button"
                                                class="inline-flex items-center gap-2 rounded-lg border border-edge bg-surface-2 px-3 py-2 text-sm font-medium leading-4 text-slate-300 transition duration-150 ease-in-out hover:text-white focus:outline-none">
                                            <span class="grid h-6 w-6 place-items-center rounded-full bg-neon/15 text-xs font-bold text-neon">
                                                {{ $page.props.auth.user.name.charAt(0) }}
                                            </span>
                                            {{ $page.props.auth.user.name }}
                                            <svg class="-me-0.5 ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink href="/settings">Ajustes</DropdownLink>
                                    <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Cerrar sesión</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-surface-2 hover:text-white focus:bg-surface-2 focus:text-white focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('simulations.buy.form')" :active="route().current('simulations.buy.form')">Comprar</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('simulations.sell.form')" :active="route().current('simulations.sell.form')">Vender</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('simulations.exchange.form')" :active="route().current('simulations.exchange.form')">Intercambiar</ResponsiveNavLink>
                    <ResponsiveNavLink href="/prices" :active="$page.url.startsWith('/prices')">Precios</ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('simulations.history')" :active="route().current('simulations.history')">Historial</ResponsiveNavLink>
                    <ResponsiveNavLink href="/audit" :active="$page.url.startsWith('/audit')">Auditoría</ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="border-t border-edge pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-slate-400">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink href="/settings">Ajustes</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('profile.edit')">Perfil</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Cerrar sesión</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="border-b border-edge bg-surface/40" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
