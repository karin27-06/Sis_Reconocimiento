<template>
    <Dialog 
        v-model:visible="dialogVisible" 
        header="Editar Alerta" 
        :modal="true"
        class="w-full max-w-full sm:max-w-lg"
        :style="{ width: '95vw', maxWidth: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">

                <!-- Movimiento relacionado -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Movimiento relacionado</label>
                    <MultiSelect
                        v-model="alerta.idMovimientos"
                        :options="movimientos"
                        optionLabel="id"
                        optionValue="id"
                        placeholder="Seleccione movimientos"
                        class="w-full"
                        display="chip"
                        :class="{ 'p-invalid': serverErrors.idMovimientos }"
                    />
                    <small v-if="serverErrors.idMovimientos" class="text-red-500">{{ serverErrors.idMovimientos[0] }}</small>
                </div>

                <!-- Descripción -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Descripción <span class="text-red-500">*</span></label>
                    <InputText 
                        v-model.trim="alerta.descripcion" 
                        placeholder="Ingrese la descripción de la alerta" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.descripcion }"
                    />
                    <small v-if="submitted && !alerta.descripcion" class="text-red-500">La descripción es obligatoria.</small>
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
                        placeholder="Seleccione el tipo de alerta"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.tipo }"
                    />
                    <small v-if="submitted && !alerta.tipo" class="text-red-500">Debe seleccionar un tipo.</small>
                    <small v-if="serverErrors.tipo" class="text-red-500">{{ serverErrors.tipo[0] }}</small>
                </div>

                <!-- Fecha -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha</label>
                    <InputText 
                        type="date" 
                        v-model="alerta.fecha" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.fecha }"
                    />
                    <small v-if="serverErrors.fecha" class="text-red-500">{{ serverErrors.fecha[0] }}</small>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text class="mr-2" @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateAlerta" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import MultiSelect from 'primevue/multiselect';

// Tipos
interface Alerta {
    idMovimientos: number[];   // ✅ array de IDs
    descripcion: string;
    tipo: number | null;
    fecha: string;
}

interface Movimiento {
    id: number;
}

interface ServerErrors {
    [key: string]: string[];
}

// Props
const props = defineProps<{
    visible: boolean;
    alertaId: number | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

// Refs
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const movimientos = ref<Movimiento[]>([]);
const alerta = ref<Alerta>({
    idMovimientos: [],
    descripcion: '',
    tipo: null,
    fecha: ''
});

// Opciones de tipo (1 = Huella, 2 = Cara)
const tipos = [
    { label: 'Huella', value: 1 },
    { label: 'Cara', value: 2 }
];

// Toast
const toast = useToast();

// Watchers
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.alertaId) {
        fetchAlerta();
        fetchMovimientos();
    }
});
watch(dialogVisible, (val) => emit('update:visible', val));

// Cargar alerta
const fetchAlerta = async (): Promise<void> => {
    try {
        loading.value = true;
        const res = await axios.get(`/alerta/${props.alertaId}`);
        if (!res.data || !res.data.alert) {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Alerta no encontrada', life: 3000 });
            return;
        }
        const data = res.data.alert;

        // Convertir la fecha de "DD-MM-YYYY" a "YYYY-MM-DD"
        let fechaISO = '';
        if (data.fecha) {
            const partes = data.fecha.split('-'); // ["14", "09", "2025"]
            if (partes.length === 3) {
                fechaISO = `${partes[2]}-${partes[1]}-${partes[0]}`; // "2025-09-14"
            }
        }

        alerta.value = {
            idMovimientos: Array.isArray(data.idMovimientos) ? data.idMovimientos : [],
            descripcion: data.descripcion ?? '',
            tipo: data.tipo ?? null,
            fecha: fechaISO
        };
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la alerta', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Cargar movimientos
const fetchMovimientos = async (): Promise<void> => {
    try {
        const res = await axios.get('/movimiento', { params: { per_page: 100 } });
        movimientos.value = res.data.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los movimientos', life: 3000 });
        console.error(error);
    }
};

// Actualizar alerta
const updateAlerta = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};
    if (!alerta.value.descripcion || !alerta.value.tipo) return;

    try {
        loading.value = true;
        await axios.put(`/alerta/${props.alertaId}`, alerta.value);
        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Alerta actualizada correctamente', life: 3000 });
        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        const err = error as AxiosError<{ errors?: ServerErrors }>;
        if (err.response?.data?.errors) {
            serverErrors.value = err.response.data.errors;
            toast.add({ severity: 'error', summary: 'Error de validación', detail: 'Revisa los campos e intenta nuevamente', life: 5000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar la alerta', life: 3000 });
        }
        console.error(error);
    } finally {
        loading.value = false;
    }
};
</script>
