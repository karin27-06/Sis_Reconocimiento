<template>
  <Dialog
    v-model:visible="dialogVisible"
    header="Movimientos de la Alerta"
    :modal="true"
    class="w-full max-w-full sm:max-w-3xl"
    :style="{ width: '95vw', maxWidth: '800px' }"
  >
    <div v-if="loading" class="text-center p-4">
      <i class="pi pi-spin pi-spinner text-2xl"></i>
      <p>Cargando movimientos...</p>
    </div>

    <div v-else>
      <DataTable
        :value="movimientos"
        class="p-datatable-sm"
        responsiveLayout="scroll"
        stripedRows
      >
        <Column field="id" header="ID" style="width: 5%" />
        <Column field="Espacio" header="Espacio" style="width: 20%" />
        <Column field="idTipo" header="Tipo" style="width: 10%" />
        <Column field="reconocido" header="Reconocido" style="width: 10%">
          <template #body="{ data }">
            <Tag :value="data.reconocido ? 'Sí' : 'No'" :severity="data.reconocido ? 'success' : 'danger'" />
          </template>
        </Column>
        <Column field="access" header="Acceso" style="width: 10%">
          <template #body="{ data }">
            <Tag :value="data.access ? 'Si' : 'No'" :severity="data.access ? 'success' : 'danger'" />
          </template>
        </Column>
    <Column field="error" header="Error" sortable style="width: 10%">
        <template #body="{ data }">
            {{ data.error && data.error != 0 ? data.error : 'Ninguno' }}
        </template>
    </Column>        <Column field="fechaEnvioESP32" header="Envío ESP32" style="width: 15%" />
        <Column field="fechaRecepcion" header="Recepción" style="width: 15%" />
        <Column field="fechaReconocimiento" header="Reconocimiento" style="width: 15%" />
      </DataTable>

      <div v-if="movimientos.length === 0" class="text-center text-gray-500 mt-4">
        No se encontraron movimientos relacionados con esta alerta.
      </div>
    </div>

    <template #footer>
      <Button label="Cerrar" icon="pi pi-times" text @click="dialogVisible = false" />
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

interface Movimiento {
  id: number;
  idEspacio: number;
  Espacio: string;
  idTipo: number;
  reconocido: boolean;
  access: boolean;
  error: string | null;
  fechaEnvioESP32: string;
  fechaRecepcion: string;
  fechaReconocimiento: string;
  creacion: string;
  actualizacion: string;
}

const props = defineProps<{
  visible: boolean;
  idMovimientos: number[];  // 👈 ahora recibimos directamente los IDs
}>();

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void;
}>();

const dialogVisible = ref(props.visible);
const loading = ref(false);
const movimientos = ref<Movimiento[]>([]);
const toast = useToast();

watch(() => props.visible, async (val) => {
  dialogVisible.value = val;
  if (val && props.idMovimientos?.length > 0) {
    await fetchMovimientos(props.idMovimientos);
  }
});

watch(dialogVisible, (val) => emit('update:visible', val));

async function fetchMovimientos(ids: number[]) {
  try {
    loading.value = true;
    movimientos.value = [];

    // 🚀 Traemos todos los movimientos
    const res = await axios.get(`/movimiento`, { params: { per_page: 100 } });

    if (!res.data || !res.data.data) {
      toast.add({ severity: 'warn', summary: 'Aviso', detail: 'No se encontraron movimientos', life: 3000 });
      return;
    }

    // 🎯 Filtramos solo los que coinciden con los IDs
    movimientos.value = res.data.data.filter((m: Movimiento) => ids.includes(m.id));
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los movimientos', life: 3000 });
    console.error(error);
  } finally {
    loading.value = false;
  }
}
</script>
