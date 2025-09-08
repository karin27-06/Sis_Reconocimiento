<script setup>
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteMovimiento from './DeleteMovimiento.vue';
import UpdateMovimiento from './UpdateMovimiento.vue';

const dt = ref();
const movimientos = ref([]);
const selectedMovimientos = ref([]);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteMovimientoDialog = ref(false);
const movimiento = ref({});
const selectedMovimientoId = ref(null);
const selectedReconocido = ref(null);
const selectedAccess = ref(null);
const updateMovimientoDialog = ref(false);
const currentPage = ref(1);

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});

watch(() => props.refresh, () => {
    loadMovimientos();
});

// Opciones para filtros
const reconocidoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'Reconocido', value: 1 },
    { name: 'No Reconocido', value: 0 },
]);

const accessOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'Con Acceso', value: 1 },
    { name: 'Sin Acceso', value: 0 },
]);

const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

// Watchers para recargar cuando cambian filtros
watch([selectedReconocido, selectedAccess], () => {
    currentPage.value = 1;
    loadMovimientos();
});

// Funciones para abrir modales
function editMovimiento(mov) {
    selectedMovimientoId.value = mov.id;
    updateMovimientoDialog.value = true;
}

function confirmDeleteMovimiento(mov) {
    movimiento.value = mov;
    deleteMovimientoDialog.value = true;
}

function handleMovimientoDeleted() {
    loadMovimientos();
}
function handleMovimientoUpdated() {
    loadMovimientos();
}
// Cargar movimientos
const loadMovimientos = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            reconocido: selectedReconocido.value?.value ?? '',
            access: selectedAccess.value?.value ?? '',
        };

        const response = await axios.get('/movimiento', { params });
        movimientos.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar movimientos:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los movimientos', life: 3000 });
    } finally {
        loading.value = false;
    }
};

// Paginado
const onPage = (event) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadMovimientos();
};

// Global search
const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadMovimientos();
}, 500);

// Tags de severidad
const getSeverity = (value) => {
    if (value === true || value === '1') return 'success';
    if (value === false || value === '0') return 'danger';
    return null;
};

onMounted(() => {
    loadMovimientos();
});
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedMovimientos"
        :value="movimientos"
        dataKey="id"
        :paginator="true"
        :rows="pagination.perPage"
        :totalRecords="pagination.total"
        :loading="loading"
        :lazy="true"
        @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]"
        scrollable
        scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} movimientos"
        class="w-full"
    >
        <template #header>
            <div class="flex flex-col md:flex-row flex-wrap gap-2 items-start md:items-center justify-between">
                <h4 class="m-0">MOVIMIENTOS</h4>
                <div class="flex flex-col md:flex-row flex-wrap gap-2 w-full md:w-auto">
                    <IconField class="w-full md:w-auto">
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText 
                            v-model="globalFilterValue" 
                            @input="onGlobalSearch" 
                            placeholder="Buscar por espacio y tipo..." 
                            class="w-full md:w-80"
                        />
                    </IconField>
                    <Select 
                        v-model="selectedReconocido" 
                        :options="reconocidoOptions" 
                        optionLabel="name" 
                        placeholder="Reconocido" 
                        class="w-full md:w-auto" 
                    />
                    <Select 
                        v-model="selectedAccess" 
                        :options="accessOptions" 
                        optionLabel="name" 
                        placeholder="Acceso" 
                        class="w-full md:w-auto" 
                    />
                    <Button icon="pi pi-refresh" outlined rounded class="w-full md:w-auto" @click="loadMovimientos" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" />
        <Column field="Espacio" header="Espacio" sortable style="min-width: 12rem" />
        <Column field="idTipo" header="Tipo" sortable style="min-width: 10rem" />
        <Column field="reconocido" header="Reconocido" sortable style="min-width: 8rem">
            <template #body="{ data }">
                <Tag :value="data.reconocido ? 'Sí' : 'No'" :severity="getSeverity(data.reconocido)" />
            </template>
        </Column>
        <Column field="access" header="Acceso" sortable style="min-width: 8rem">
            <template #body="{ data }">
                <Tag :value="data.access ? 'Sí' : 'No'" :severity="getSeverity(data.access)" />
            </template>
        </Column>
        <Column field="error" header="Error" sortable style="min-width: 6rem" />
        <Column field="fechaEnvioESP32" header="Envío ESP32" sortable style="min-width: 13rem" />
        <Column field="fechaRecepcion" header="Recepción" sortable style="min-width: 13rem" />
        <Column field="fechaReconocimiento" header="Reconocimiento" sortable style="min-width: 13rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2 mb-2 md:mb-0" @click="editMovimiento(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" class="mb-2 md:mb-0" @click="confirmDeleteMovimiento(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteMovimiento 
        v-model:visible="deleteMovimientoDialog" 
        :movimiento="movimiento" 
        @deleted="handleMovimientoDeleted" 
    />
    <UpdateMovimiento 
        v-model:visible="updateMovimientoDialog" 
        :movimientoId="selectedMovimientoId"
        @updated="handleMovimientoUpdated" 
    />
</template>