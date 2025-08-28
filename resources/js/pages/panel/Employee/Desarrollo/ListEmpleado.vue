<template>
    <DataTable ref="dt" v-model:selection="selectedEmpleados" :value="empleados" dataKey="id"
        :paginator="true" :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading"
        :lazy="true" @page="onPage" :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} empleados">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">EMPLEADOS</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar..." />
                    </IconField>
                    <Select v-model="selectedEstadoEmpleado" :options="estadoEmpleadoOptions" optionLabel="name"
                        placeholder="Estado" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadEmpleado" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />
        <Column field="name" header="Nombre" sortable style="min-width: 12rem" />
        <Column field="apellido" header="Apellido" sortable style="min-width: 12rem" />
        <Column field="codigo" header="Código" sortable style="min-width: 10rem" />
        <Column field="empleadoType" header="Tipo de Empleado" sortable style="min-width: 12rem" />
        <Column field="idHuella" header="ID Huella" sortable style="min-width: 8rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="state" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="foto" header="Foto" style="min-width: 8rem">
            <template #body="{ data }">
                <img v-if="data.foto" :src="`/storage/${data.foto}`" alt="Foto" class="w-12 h-12 rounded-full object-cover" />
            </template>
        </Column>
        <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editEmpleado(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger"
                    @click="confirmDeleteEmpleado(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteEmpleado v-model:visible="deleteEmpleadoDialog" :empleado="empleado" @deleted="handleEmpleadoDeleted" />
    <UpdateEmpleado v-model:visible="updateEmpleadoDialog" :empleadoId="selectedEmpleadoId"
        @updated="handleEmpleadoUpdated" />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteEmpleado from './DeleteEmpleado.vue';
import UpdateEmpleado from './UpdateEmpleado.vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const dt = ref();
const empleados = ref([]);
const selectedEmpleados = ref();
const loading = ref(false);
const globalFilterValue = ref('');
const deleteEmpleadoDialog = ref(false);
const empleado = ref({});
const selectedEmpleadoId = ref(null);
const selectedEstadoEmpleado = ref(null);
const updateEmpleadoDialog = ref(false);

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});

watch(() => props.refresh, () => loadEmpleado());
watch(() => selectedEstadoEmpleado.value, () => { loadEmpleado(); });

function editEmpleado(emp) {
    selectedEmpleadoId.value = emp.id;
    updateEmpleadoDialog.value = true;
}

function confirmDeleteEmpleado(selected) {
    empleado.value = selected;
    deleteEmpleadoDialog.value = true;
}

const estadoEmpleadoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });
//const filters = ref({ state: null });

function handleEmpleadoUpdated() { loadEmpleado(); }
function handleEmpleadoDeleted() { loadEmpleado(); }

const loadEmpleado = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: selectedEstadoEmpleado.value?.value ?? null
        };
        const response = await axios.get('/empleado', { params });
        empleados.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadEmpleado();
};

const getSeverity = (value) => (value ? 'success' : 'danger');

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadEmpleado();
}, 500);

onMounted(() => { loadEmpleado(); });
</script>
