<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppMenuItem from './AppMenuItem.vue';

const page = usePage();
const permissions = computed(() => page.props.auth.user?.permissions ?? []);
const hasPermission = (perm) => permissions.value.includes(perm);

const model = computed(() => [
    {
        label: 'Home',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }
        ]
    },
    {
  label: 'Usuarios y Seguridad',
  items: [
    hasPermission('ver usuarios') && { label: 'Gestión de Usuarios', icon: 'pi pi-fw pi-user-edit', to: '/usuario' },
    hasPermission('ver roles') && { label: 'Roles', icon: 'pi pi-fw pi-shield', to: '/roles' },
    (hasPermission('ver empleados') || hasPermission('ver tipos_empleados')) && {
      label: 'Empleado',
      icon: 'pi pi-fw pi-id-card',
      items: [
        hasPermission('ver empleados') && { label: 'Empleados', icon: 'pi pi-fw pi-id-card', to: '/empleados' },
        hasPermission('ver tipos_empleados') && { label: 'Tipo de empleados', icon: 'pi pi-fw pi-sitemap', to: '/tipo_empleados' },
      ].filter(Boolean),
    },
    hasPermission('ver presentaciones') && { label: 'Presentaciones', icon: 'pi pi-fw pi-check-square', to: '/presentaciones' },
  ].filter(Boolean),
},
].filter(section => section.items.length > 0));
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item :item="item" :index="i" />
        </template>
    </ul>
</template>

<style scoped lang="scss">
/* Estilos personalizados opcionales */
</style>
