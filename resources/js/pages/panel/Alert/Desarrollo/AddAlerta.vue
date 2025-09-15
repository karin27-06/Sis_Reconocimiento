<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nueva Alerta" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
    </Toolbar>

    <Dialog 
        v-model:visible="alertaDialog" 
        :modal="true" 
        :style="{ width: '95vw', maxWidth: '600px' }"
        header="Registro de Alerta" 
        class="w-full max-w-full sm:max-w-lg"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">

                <!-- Movimiento relacionado -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Movimiento <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="alerta.idMovimiento"
                        :options="movimientos"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione un movimiento"
                        class="w-full"
                        filter
                        filterPlaceholder="Buscar movimiento"
                    />
                    <small v-if="submitted && !alerta.idMovimiento" class="text-red-500">Debe seleccionar un movimiento.</small>
                    <small v-if="serverErrors.idMovimiento" class="text-red-500">{{ serverErrors.idMovimiento[0] }}</small>
                </div>

                <!-- Descripción -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Descripción</label>
                    <InputText v-model.trim="alerta.descripcion" placeholder="Ingrese descripción" class="w-full"/>
                    <small v-if="serverErrors.descripcion" class="text-red-500">{{ serverErrors.descripcion[0] }}</small>
                </div>

                <!-- Tipo -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Tipo <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="alerta.tipo"
                        :options="tipos"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione tipo"
                        class="w-full"
                    />
                    <small v-if="submitted && !alerta.tipo" class="text-red-500">El tipo es obligatorio.</small>
                    <small v-if="serverErrors.tipo" class="text-red-500">{{ serverErrors.tipo[0] }}</small>
                </div>

                <!-- Fecha -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha</label>
                    <InputText type="date" v-model="alerta.fecha" class="w-full"/>
                    <small v-if="serverErrors.fecha" class="text-red-500">{{ serverErrors.fecha[0] }}</small>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text class="mr-2" @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarAlerta" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';

// Tipos
interface Alerta {
    idMovimiento: number | null;
    descripcion: string;
    tipo: number | null;
    fecha: string;
}

interface Movimiento {
    id: number;
    name: string;
}

// Refs
const toast = useToast();
const submitted = ref(false);
const alertaDialog = ref(false);
const serverErrors = ref<Record<string, string[]>>({});
const movimientos = ref<Movimiento[]>([]);
const tipos = [
    { label: 'Huella', value: 1 },
    { label: 'Cara', value: 2 }
];
const emit = defineEmits<{
    (e: 'alerta-agregada'): void
}>();

const alerta = ref<Alerta>({
    idMovimiento: null,
    descripcion: '',
    tipo: null,
    fecha: ''
});

// Abrir modal
function openNew() {
    resetAlerta();
    alertaDialog.value = true;
    fetchMovimientos();
}

// Cerrar modal
function hideDialog() {
    alertaDialog.value = false;
    resetAlerta();
}

// Reset
function resetAlerta() {
    alerta.value = {
        idMovimiento: null,
        descripcion: '',
        tipo: null,
        fecha: ''
    };
    serverErrors.value = {};
    submitted.value = false;
}

// Cargar movimientos
function fetchMovimientos() {
    axios.get<{ data: Movimiento[] }>('/movimiento', { params: { state: 1 } })
        .then(res => {
            // Mapeamos los datos para crear un "name" legible
            movimientos.value = res.data.data.map(mov => ({
                id: mov.id,
                name: `ID: ${mov.id}`
            }));
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los movimientos', life: 3000 });
        });
}

// Guardar alerta
function guardarAlerta() {
    submitted.value = true;
    serverErrors.value = {};

    if (!alerta.value.idMovimiento || !alerta.value.tipo) return;

    axios.post('/alerta', alerta.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Alerta registrada', life: 3000 });
            hideDialog();
            emit('alerta-agregada');
        })
        .catch((error: AxiosError<{ errors?: Record<string, string[]> }>) => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar la alerta',
                    life: 3000
                });
            }
        });
}
</script>
