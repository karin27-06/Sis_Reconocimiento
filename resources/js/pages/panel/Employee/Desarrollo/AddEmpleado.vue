<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo empleado" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <ToolsEmployee/>       
        </template>
    </Toolbar>

    <Dialog v-model:visible="empleadoDialog" :style="{ width: '600px' }" header="Registro de empleado" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                    <InputText v-model.trim="empleado.name" placeholder="Ingrese su nombre" maxlength="150" fluid />
                    <small v-if="submitted && !empleado.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Apellido -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Apellido <span class="text-red-500">*</span></label>
                    <InputText v-model.trim="empleado.apellido" placeholder="Ingrese su apellido" maxlength="150" fluid />
                    <small v-if="submitted && !empleado.apellido" class="text-red-500">El apellido es obligatorio.</small>
                    <small v-if="serverErrors.apellido" class="text-red-500">{{ serverErrors.apellido[0] }}</small>
                </div>

                <!-- Código -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Código <span class="text-red-500">*</span></label>
                    <InputText v-model.trim="empleado.codigo" placeholder="Ingrese su código" maxlength="8" fluid />
                    <small v-if="submitted && !empleado.codigo" class="text-red-500">El código es obligatorio.</small>
                    <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
                </div>

                <!-- ID Huella -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">ID Huella <span class="text-red-500">*</span></label>
                    <InputText v-model.number="empleado.idHuella" placeholder="Ingrese ID de huella" fluid />
                    <small v-if="submitted && !empleado.idHuella" class="text-red-500">El ID de huella es obligatorio.</small>
                    <small v-if="serverErrors.idHuella" class="text-red-500">{{ serverErrors.idHuella[0] }}</small>
                </div>

                <!-- Tipo de Empleado -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Tipo de Empleado <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="empleado.employee_type_id"
                        :options="tiposEmpleado"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione tipo de empleado"
                        fluid
                        filter
                        filterPlaceholder="Buscar tipo de empleado"
                    />
                    <small v-if="submitted && !empleado.employee_type_id" class="text-red-500">Debe seleccionar un tipo.</small>
                    <small v-if="serverErrors.employee_type_id" class="text-red-500">{{ serverErrors.employee_type_id[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-12 flex items-center gap-3">
                    <Checkbox v-model="empleado.state" :binary="true" />
                    <Tag :value="empleado.state ? 'Activo' : 'Inactivo'" :severity="empleado.state ? 'success' : 'danger'" />
                    <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                </div>

                <!-- Foto -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Foto <span class="text-red-500">*</span></label>

                    <!-- Subir archivo -->
                    <input type="file" @change="onFileChange" accept="image/png, image/jpeg" />

                    <!-- Tomar foto -->
                    <div class="mt-2">
                        <Button label="Tomar Foto" icon="pi pi-camera" class="p-button-secondary" @click="startCamera" />
                    </div>

                    <!-- Video en vivo -->
                    <div v-if="cameraActive" class="mt-2">
                        <video ref="video" autoplay playsinline width="300"></video>
                        <div class="mt-2 flex gap-2">
                            <Button label="Capturar" icon="pi pi-check" class="p-button-success" @click="capturePhoto" />
                            <Button label="Cancelar" icon="pi pi-times" class="p-button-danger" @click="stopCamera" />
                        </div>
                    </div>

                    <!-- Preview y Reintentar -->
                    <div v-if="fotoPreview" class="mt-2">
                        <img :src="fotoPreview" alt="Preview" class="w-48 h-auto border rounded" />
                        <div class="mt-2">
                            <Button label="Reintentar" icon="pi pi-refresh" class="p-button-warning" @click="retryPhoto" />
                        </div>
                    </div>

                    <small v-if="submitted && !empleado.foto" class="text-red-500">La foto es obligatoria.</small>
                    <small v-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarEmpleado" />
        </template>
</Dialog>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import ToolsEmployee from './toolsEmployee.vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const empleadoDialog = ref(false);
const submitted = ref(false);
const serverErrors = ref({});
const tiposEmpleado = ref([]);

const empleado = ref({
    name: '',
    apellido: '',
    codigo: '',
    employee_type_id: null,
    idHuella: '',
    foto: null,
    state: true
});

const fotoPreview = ref(null);
const cameraActive = ref(false);
const video = ref(null);
let stream = null;

const emit = defineEmits(['empleado-agregado']);

// Reset formulario
function resetEmpleado() {
    empleado.value = {
        name: '',
        apellido: '',
        codigo: '',
        employee_type_id: null,
        idHuella: '',
        foto: null,
        state: true
    };
    fotoPreview.value = null;
    serverErrors.value = {};
    submitted.value = false;
}

// Abrir dialog
function openNew() {
    resetEmpleado();
    empleadoDialog.value = true;
    fetchTiposEmpleado();
}

function hideDialog() {
    empleadoDialog.value = false;
    resetEmpleado();
}

// Cargar tipos de empleado
function fetchTiposEmpleado() {
    axios.get('/tipo_empleado', { params: { state: 1 } })
        .then(res => tiposEmpleado.value = res.data.data)
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de empleado', life: 3000 });
        });
}

// Subir archivo
function onFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        empleado.value.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
}

// Iniciar cámara
function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(s => {
            stream = s;
            cameraActive.value = true;
            video.value.srcObject = stream;
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo acceder a la cámara', life: 3000 });
        });
}

// Detener cámara
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    cameraActive.value = false;
}

// Capturar foto de la cámara
function capturePhoto() {
    const canvas = document.createElement('canvas');
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video.value, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(blob => {
        const file = new File([blob], `empleado_${Date.now()}.png`, { type: 'image/png' });
        empleado.value.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
        stopCamera();
    }, 'image/png');
}

// Reintentar foto
function retryPhoto() {
    fotoPreview.value = null;
    empleado.value.foto = null;
    startCamera();
}

// Guardar empleado
function guardarEmpleado() {
    submitted.value = true;
    serverErrors.value = {};

    if (!empleado.value.name || !empleado.value.apellido || !empleado.value.codigo || !empleado.value.employee_type_id || !empleado.value.idHuella || !empleado.value.foto) {
        toast.add({ severity: 'warn', summary: 'Campos incompletos', detail: 'Debe completar todos los campos', life: 3000 });
        return;
    }
    if(!/^\d{8}$/.test(empleado.value.codigo)) {
    toast.add({ severity: 'warn', summary: 'Código inválido', detail: 'El código debe ser 8 números', life: 3000 });
    return;
    }
    if(!Number.isInteger(empleado.value.idHuella) || empleado.value.idHuella <= 0) {
        toast.add({ severity: 'warn', summary: 'Huella inválida', detail: 'La huella debe ser un número entero positivo', life: 3000 });
    return;
    }

    const formData = new FormData();
    for (const key in empleado.value) {
        formData.append(key, empleado.value[key]);
    }

    axios.post('/empleado', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Empleado registrado', life: 3000 });
            hideDialog();
            emit('empleado-agregado');
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar el empleado',
                    life: 3000
                });
            }
        });
}
</script>
