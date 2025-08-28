<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Empleado" modal :closable="true" :style="{ width: '650px' }">
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

                    <small v-if="submitted && !empleado.foto && !fotoPreview" class="text-red-500">La foto es obligatoria.</small>
                    <small v-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateEmpleado" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({ visible: Boolean, empleadoId: Number });
const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const dialogVisible = ref(props.visible);
const loading = ref(false);
const submitted = ref(false);
const serverErrors = ref({});

const empleado = ref({
    name: '',
    apellido: '',
    codigo: '',
    employee_type_id: null,
    idHuella: '',
    foto: null,
    state: true
});

const tiposEmpleado = ref([]);
const fotoPreview = ref(null);
const cameraActive = ref(false);
const video = ref(null);
let stream = null;
let fotoOriginal = null;

// Watches
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.empleadoId) {
        fetchEmpleado();
        fetchTiposEmpleado();
    }
});
watch(dialogVisible, (val) => {
    if (!val) resetEmpleado();
    emit('update:visible', val);
});

// Reset
function resetEmpleado() {
    empleado.value.foto = null;
    fotoPreview.value = fotoOriginal;
    serverErrors.value = {};
    submitted.value = false;
    stopCamera();
}

// Fetch empleado
const fetchEmpleado = async () => {
    try {
        loading.value = true;
        const res = await axios.get(`/empleado/${props.empleadoId}`);
        const data = res.data.employee;
        empleado.value = {
            name: data.name,
            apellido: data.apellido,
            codigo: data.codigo,
            employee_type_id: data.employee_type_id,
            idHuella: data.idHuella || '',
            foto: null,
            state: data.state
        };
        fotoOriginal = data.foto ? `/storage/${data.foto}` : null;
        fotoPreview.value = fotoOriginal;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el empleado', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Fetch tipos
const fetchTiposEmpleado = async () => {
    try {
        const res = await axios.get('/tipo_empleado', { params: { state: 1 } });
        tiposEmpleado.value = res.data.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de empleado', life: 3000 });
        console.error(error);
    }
};

// Foto
function onFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        empleado.value.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
}

// Cámara
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
function stopCamera() {
    if (stream) stream.getTracks().forEach(track => track.stop());
    cameraActive.value = false;
}
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
function retryPhoto() {
    fotoPreview.value = null;
    empleado.value.foto = null;
    startCamera();
}

// Update
const updateEmpleado = async () => {
    submitted.value = true;
    serverErrors.value = {};

    if (!empleado.value.name || !empleado.value.codigo || !empleado.value.employee_type_id) {
        toast.add({
            severity: 'warn',
            summary: 'Campos incompletos',
            detail: 'Complete todos los campos obligatorios',
            life: 3000
        });
        return;
    }

    // FormData para enviar archivo y datos
    const formData = new FormData();
    formData.append('_method', 'PUT'); // Indica a Laravel que es PUT
    formData.append('name', empleado.value.name);
    formData.append('apellido', empleado.value.apellido);
    formData.append('idHuella', empleado.value.idHuella);
    formData.append('codigo', empleado.value.codigo);
    formData.append('employee_type_id', Number(empleado.value.employee_type_id));
    formData.append('state', empleado.value.state ? 1 : 0);

    if (empleado.value.foto) {
        formData.append('foto', empleado.value.foto);
    }

    try {
        await axios.post(`/empleado/${props.empleadoId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Empleado actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');

    } catch (error) {
        if (error.response && error.response.status === 422) {
            serverErrors.value = error.response.data.errors || {};
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el empleado',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>
