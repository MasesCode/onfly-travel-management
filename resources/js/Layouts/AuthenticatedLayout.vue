<script setup lang="ts">
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50">
            <nav class="shadow-lg bg-gradient-to-r from-blue-600 to-blue-700">
                <!-- Primary Navigation Menu -->
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex items-center shrink-0">
                                <Link :href="route('dashboard')">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-8 h-8 mr-3 bg-white rounded-lg">
                                            <span class="text-xl font-bold text-blue-600">O</span>
                                        </div>
                                        <span class="text-xl font-bold text-white">Onfly</span>
                                    </div>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out border-b-2 focus:outline-none"
                                    :class="route().current('dashboard')
                                        ? 'border-white text-white font-semibold'
                                        : 'border-transparent text-white/90 hover:text-white hover:border-white/50 hover:font-semibold'"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('reports')"
                                    :active="route().current('reports')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out border-b-2 focus:outline-none"
                                    :class="route().current('reports')
                                        ? 'border-white text-white font-semibold'
                                        : 'border-transparent text-white/90 hover:text-white hover:border-white/50 hover:font-semibold'"
                                >
                                    Relatórios
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.is_admin"
                                    :href="route('admin')"
                                    :active="route().current('admin')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out border-b-2 focus:outline-none"
                                    :class="route().current('admin')
                                        ? 'border-white text-white font-semibold'
                                        : 'border-transparent text-white/90 hover:text-white hover:border-white/50 hover:font-semibold'"
                                >
                                    Admin
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.is_admin"
                                    :href="route('order-status')"
                                    :active="route().current('order-status')"
                                    class="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out border-b-2 focus:outline-none"
                                    :class="route().current('order-status')
                                        ? 'border-white text-white font-semibold'
                                        : 'border-transparent text-white/90 hover:text-white hover:border-white/50 hover:font-semibold'"
                                >
                                    Status
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-blue-500 border border-transparent rounded-md shadow-md hover:bg-blue-400 focus:outline-none focus:bg-blue-400"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Sair
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="flex items-center -me-2 sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 text-blue-100 transition duration-150 ease-in-out rounded-md hover:bg-blue-500 hover:text-white focus:bg-blue-500 focus:text-white focus:outline-none"
                            >
                                <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="bg-blue-700 sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('dashboard')
                                ? 'bg-blue-800 text-white border-l-4 border-white font-semibold'
                                : 'text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold'"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('reports')"
                            :active="route().current('reports')"
                            class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('reports')
                                ? 'bg-blue-800 text-white border-l-4 border-white font-semibold'
                                : 'text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold'"
                        >
                            Relatórios
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.is_admin"
                            :href="route('admin')"
                            :active="route().current('admin')"
                            class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('admin')
                                ? 'bg-blue-800 text-white border-l-4 border-white font-semibold'
                                : 'text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold'"
                        >
                            Admin
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.is_admin"
                            :href="route('order-status')"
                            :active="route().current('order-status')"
                            class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('order-status')
                                ? 'bg-blue-800 text-white border-l-4 border-white font-semibold'
                                : 'text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold'"
                        >
                            Status
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-blue-600">
                        <div class="px-4">
                            <div class="text-base font-medium text-white">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-blue-200">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold"
                            >
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block py-2 pl-3 pr-4 text-base font-medium transition duration-150 ease-in-out text-white/90 hover:text-white hover:bg-blue-600 hover:font-semibold"
                            >
                                Sair
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="shadow-lg bg-gradient-to-r from-blue-600 to-blue-700" v-if="$slots.header">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Componente de Notificações -->
        <NotificationToast />
    </div>
</template>
