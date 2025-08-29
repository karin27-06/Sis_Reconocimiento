<template>
<Dialog v-model:visible="dialogVisible" header="Editar Horario" modal :closable="true" :style="{ width: '700px' }">
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">
            <!-- Fecha -->
            <div class="col-span-4">
                <label class="block font-bold mb-2">Fecha <span class="text-red-500">*</span></label>
                <InputText v-model="schedule.fechaFormatted" type="date" required fluid :class="{ 'p-invalid': serverErrors.fecha }" />
                <small v-if="submitted && !schedule.fechaFormatted" class="text-red-500">La fecha es obligatoria.</small>
                <small v-if="serverErrors.fecha" class="text-red-500">{{ serverErrors.fecha[0] }}</small>
            </div>

            <!-- Fecha Inicio -->
            <div class="col-span-4">
                <label class="block font-bold mb-2">Fecha y Hora Inicio <span class="text-red-500">*</span></label>
                <InputText v-model="schedule.fechaInicioFormatted" type="datetime-local" required fluid :class="{ 'p-invalid': serverErrors.fechaInicio }" />
                <small v-if="submitted && !schedule.fechaInicioFormatted" class="text-red-500">La fecha de inicio es obligatoria.</small>
                <small v-if="serverErrors.fechaInicio" class="text-red-500">{{ serverErrors.fechaInicio[0] }}</small>
            </div>

            <!-- Fecha Fin -->
            <div class="col-span-4">
                <label class="block font-bold mb-2">Fecha y Hora Fin <span class="text-red-500">*</span></label>
                <InputText v-model="schedule.fechaFinFormatted" type="datetime-local" required fluid :class="{ 'p-invalid': serverErrors.fechaFin }" />
                <small v-if="submitted && !schedule.fechaFinFormatted" class="text-red-500">La fecha de fin es obligatoria.</small>
                <small v-if="serverErrors.fechaFin" class="text-red-500">{{ serverErrors.fechaFin[0] }}</small>
            </div>

            <!-- Espacio -->
            <div class="col-span-6">
                <label class="block font-bold mb-2">Espacio <span class="text-red-500">*</span></label>
                <Dropdown
                    v-model="schedule.idEspacio"
                    :options="espacios"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Seleccione un espacio"
                    fluid
                    filter
                    filterPlaceholder="Buscar espacio"
                    :class="{ 'p-invalid': serverErrors.idEspacio }"
                />
                <small v-if="submitted && !schedule.idEspacio" class="text-red-500">Debe seleccionar un espacio.</small>
                <small v-if="serverErrors.idEspacio" class="text-red-500">{{ serverErrors.idEspacio[0] }}</small>
            </div>

            <!-- Empleado -->
            <div class="col-span-6">
                <label class="block font-bold mb-2">Empleado <span class="text-red-500">*</span></label>
                <Dropdown
                    v-model="schedule.idEmpleado"
                    :options="empleados"
                    optionLabel="nameFull"
                    optionValue="id"
                    placeholder="Seleccione un empleado"
                    fluid
                    filter
                    filterPlaceholder="Buscar empleado"
                    :class="{ 'p-invalid': serverErrors.idEmpleado }"
                />
                <small v-if="submitted && !schedule.idEmpleado" class="text-red-500">Debe seleccionar un empleado.</small>
                <small v-if="serverErrors.idEmpleado" class="text-red-500">{{ serverErrors.idEmpleado[0] }}</small>
            </div>

            <!-- Estado -->
            <div class="col-span-12">
                <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-3">
                    <Checkbox v-model="schedule.state" :binary="true" />
                    <Tag :value="schedule.state ? 'Activo' : 'Inactivo'" :severity="schedule.state ? 'success' : 'danger'" />
                </div>
                <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
            </div>
        </div>
    </div>
    <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
        <Button label="Guardar" icon="pi pi-check" @click="updateSchedule" :loading="loading" />
    </template>
</Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: Boolean,
    scheduleId: Number
});
const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const dialogVisible = ref(props.visible);
const loading = ref(false);
const submitted = ref(false);
const serverErrors = ref({});

const schedule = ref({
    fechaFormatted: '',
    fechaInicioFormatted: '',
    fechaFinFormatted: '',
    idEspacio: null,
    idEmpleado: null,
    state: false,
});

const espacios = ref([]);
const empleados = ref([]);

watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.scheduleId) {
        fetchSchedule();
        fetchEspacios();
        fetchEmpleados();
    }
});
watch(dialogVisible, (val) => emit('update:visible', val));

// Función para convertir fecha del formato "dd/MM/yyyy" a "yyyy-MM-dd"
const convertDateToInputFormat = (apiDate) => {
    if (!apiDate) return '';
    console.log('Convirtiendo fecha simple:', apiDate);
    
    try {
        // Probar formato dd/MM/yyyy
        if (apiDate.includes('/')) {
            const [day, month, year] = apiDate.split('/');
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
        // Probar formato dd-MM-yyyy
        else if (apiDate.includes('-')) {
            const [day, month, year] = apiDate.split('-');
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
        return '';
    } catch (error) {
        console.error('Error converting date:', error, apiDate);
        return '';
    }
};

// Función para convertir fecha y hora del formato API a "yyyy-MM-ddTHH:mm"
const convertDateTimeToInputFormat = (apiDateTime) => {
    if (!apiDateTime) return '';
    console.log('Convirtiendo fecha y hora:', apiDateTime);
    
    try {
        // Probar formato "dd/MM/yyyy HH:mm"
        if (apiDateTime.includes('/') && apiDateTime.includes(':')) {
            const [datePart, timePart] = apiDateTime.split(' ');
            const [day, month, year] = datePart.split('/');
            const [hours, minutes] = timePart.split(':');
            
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hours.padStart(2, '0')}:${minutes}`;
        }
        // Probar formato "dd-MM-yyyy HH:mm:ss"
        else if (apiDateTime.includes('-') && apiDateTime.includes(':')) {
            const [datePart, timePart] = apiDateTime.split(' ');
            const [day, month, year] = datePart.split('-');
            const [hours, minutes] = timePart.split(':');
            
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hours.padStart(2, '0')}:${minutes}`;
        }
        // Probar formato con AM/PM
        else if ((apiDateTime.includes('AM') || apiDateTime.includes('PM')) && apiDateTime.includes(' ')) {
            const parts = apiDateTime.split(' ');
            const datePart = parts[0];
            const timePart = parts[1];
            const period = parts[2]; // AM o PM
            
            let day, month, year;
            
            // Detectar separador de fecha
            if (datePart.includes('/')) {
                [day, month, year] = datePart.split('/');
            } else if (datePart.includes('-')) {
                [day, month, year] = datePart.split('-');
            } else {
                return '';
            }
            
            const [hours, minutes] = timePart.split(':');
            
            let hours24 = parseInt(hours);
            if (period === 'PM' && hours24 !== 12) {
                hours24 += 12;
            } else if (period === 'AM' && hours24 === 12) {
                hours24 = 0;
            }
            
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}T${hours24.toString().padStart(2, '0')}:${minutes}`;
        }
        return '';
    } catch (error) {
        console.error('Error converting date time:', error, apiDateTime);
        return '';
    }
};

const fetchSchedule = async () => {
    try {
        loading.value = true;
        const res = await axios.get(`/horario/${props.scheduleId}`);
        const data = res.data.schedule;
        
        console.log('Datos completos recibidos del API:', data);

        schedule.value = {
            fechaFormatted: convertDateToInputFormat(data.fecha),
            fechaInicioFormatted: convertDateTimeToInputFormat(data.fechaInicio),
            fechaFinFormatted: convertDateTimeToInputFormat(data.fechaFin),
            idEspacio: data.idEspacio,
            idEmpleado: data.idEmpleado,
            state: data.state
        };
        
        console.log('Datos después de conversión:', schedule.value);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el horario', life: 3000 });
        console.error('Error fetching schedule:', error);
    } finally {
        loading.value = false;
    }
};

const fetchEspacios = async () => {
    try {
        const res = await axios.get('/espacio', { params: { state: 1 } });
        espacios.value = res.data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
    }
};

const fetchEmpleados = async () => {
    try {
        const res = await axios.get('/empleado', { params: { state: 1 } });
        // Crear nombre completo para mostrar en el dropdown
        empleados.value = res.data.data.map(emp => ({ 
            ...emp, 
            nameFull: emp.name + ' ' + emp.apellido 
        }));
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
    }
};

const updateSchedule = async () => {
    submitted.value = true;
    serverErrors.value = {};

    // Validación básica
    if (!schedule.value.fechaFormatted || !schedule.value.fechaInicioFormatted || 
        !schedule.value.fechaFinFormatted || !schedule.value.idEspacio || !schedule.value.idEmpleado) {
        return;
    }

    try {
        // Preparar datos para enviar al servidor
        const scheduleData = {
            fecha: schedule.value.fechaFormatted,
            fechaInicio: schedule.value.fechaInicioFormatted.replace('T', ' ') + ':00',
            fechaFin: schedule.value.fechaFinFormatted.replace('T', ' ') + ':00',
            idEspacio: schedule.value.idEspacio,
            idEmpleado: schedule.value.idEmpleado,
            state: schedule.value.state
        };
        
        console.log('Enviando datos al servidor:', scheduleData);
        
        await axios.put(`/horario/${props.scheduleId}`, scheduleData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Horario actualizado correctamente',
            life: 3000
        });
        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        if (error.response && error.response.data?.errors) {
            serverErrors.value = error.response.data.errors;
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el horario',
                life: 3000
            });
        }
        console.error('Error updating schedule:', error);
    }
};
</script>