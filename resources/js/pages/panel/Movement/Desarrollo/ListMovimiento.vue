<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
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
import { useToast } from 'primevue/usetoast';

// Tipos
interface Movimiento {
    id: number;
    Espacio: string;
    idTipo: string;
    reconocido: boolean | number | string;
    access: boolean | number | string;
    error: string | number;
    fechaEnvioESP32: string;
    fechaRecepcion: string;
    fechaReconocimiento: string;
    creacion: string;
    actualizacion: string;
}

interface Option {
    name: string;
    value: string | number;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

// Props
const props = defineProps<{ refresh: number }>();

// Toast
const toast = useToast();

// Refs
const dt = ref<any>(null);
const movimientos = ref<Movimiento[]>([]);
const selectedMovimientos = ref<Movimiento[]>([]);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteMovimientoDialog = ref(false);
const updateMovimientoDialog = ref(false);
const movimiento = ref<Movimiento | null>(null);
const selectedMovimientoId = ref<number | null>(null);
const selectedReconocido = ref<Option | null>(null);
const selectedAccess = ref<Option | null>(null);
const currentPage = ref(1);
const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

// Filtros
const reconocidoOptions = ref<Option[]>([
    { name: 'TODOS', value: '' },
    { name: 'Reconocido', value: 1 },
    { name: 'No Reconocido', value: 0 },
]);

const accessOptions = ref<Option[]>([
    { name: 'TODOS', value: '' },
    { name: 'Con Acceso', value: 1 },
    { name: 'Sin Acceso', value: 0 },
]);

// Watchers
watch(() => props.refresh, () => {
    loadMovimientos();
});

watch([selectedReconocido, selectedAccess], () => {
    currentPage.value = 1;
    loadMovimientos();
});

// Funciones
function editMovimiento(mov: Movimiento) {
    selectedMovimientoId.value = mov.id;
    updateMovimientoDialog.value = true;
}

function confirmDeleteMovimiento(mov: Movimiento) {
    movimiento.value = mov;
    deleteMovimientoDialog.value = true;
}

function handleMovimientoDeleted() {
    loadMovimientos();
}

function handleMovimientoUpdated() {
    loadMovimientos();
}

const loadMovimientos = async (): Promise<void> => {
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
const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadMovimientos();
};

// Búsqueda global
const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadMovimientos();
}, 500);

// Severidad para tags
const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

onMounted(() => {
    loadMovimientos();
});
const tipoTemplate = (rowData) => {
  return rowData.idTipo === 1 ? 'Cara' : rowData.idTipo === 2 ? 'Huella' : 'Desconocido';
};

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
<Column 
  field="idTipo" 
  header="Tipo" 
  sortable 
  style="min-width: 10rem"
  :body="tipoTemplate" 
/>
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
    <Column field="error" header="Error" sortable style="min-width: 6rem">
        <template #body="{ data }">
            {{ data.error && data.error != 0 ? data.error : 'Ninguno' }}
        </template>
    </Column>
    <Column field="fechaEnvioESP32" header="Envío ESP32" sortable style="min-width: 13rem" />
    <Column field="fechaRecepcion" header="Recepción" sortable style="min-width: 13rem" />
    <Column field="fechaReconocimiento" header="Reconocimiento" sortable style="min-width: 13rem" />
    <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
    <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
    <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
        <template #body="{ data }">
            <Button icon="pi pi-pencil" outlined rounded class="mr-2 mb-2 md:mb-0" @click="editMovimiento(data)" />
            <Button icon="pi pi-trash" outlined rounded severity="danger" class="mb-2 md:mb-0" @click="confirmDeleteMovimiento(data)" />
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