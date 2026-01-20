<template>
  <q-page class="q-pa-md">
    <!-- Page Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="row items-center q-gutter-x-md">
        <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
        <div>
          <div class="text-h4 text-primary text-weight-bold text-islamic">المجموعات</div>
          <div class="text-subtitle2 text-grey-7">إدارة مجموعات الختمة والمستخدمين</div>
        </div>
      </div>
      <q-btn unelevated rounded color="primary" icon="add" label="مجموعة جديدة" @click="openCreateDialog"
        class="shadow-3 q-px-md" />
    </div>

    <!-- Groups Table -->
    <q-card class="glass-card q-pa-sm">
      <q-table :rows="groups" :columns="columns" row-key="id" :loading="loading" flat :separator="'none'"
        class="bg-transparent text-grey-9 custom-table"
        table-header-class="text-primary text-weight-bold bg-primary-1 rounded-borders">
        <!-- Custom Header Slot if needed, but table-header-class handles bg -->

        <template v-slot:body-cell-active="props">
          <q-td :props="props">
            <q-badge rounded :color="props.row.active ? 'primary' : 'grey'" class="q-px-sm q-py-xs shadow-1">
              {{ props.row.active ? 'نشط' : 'غير نشط' }}
              <q-icon :name="props.row.active ? 'check_circle' : 'cancel'" class="q-ml-xs" />
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props" auto-width>
            <div class="row justify-center q-gutter-x-sm">
              <q-btn flat round color="primary-7" icon="edit" size="sm" class="bg-primary-1 action-btn"
                @click="editGroup(props.row)">
                <q-tooltip>تعديل</q-tooltip>
              </q-btn>
              <q-btn flat round color="red-7" icon="delete" size="sm" class="bg-red-1 action-btn"
                @click="confirmDelete(props.row)">
                <q-tooltip>حذف</q-tooltip>
              </q-btn>
            </div>
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width row flex-center text-grey-6 q-pa-lg">
            <q-icon name="warning" size="sm" class="q-mr-sm" />
            لا توجد مجموعات حالياً
          </div>
        </template>
      </q-table>
    </q-card>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale">
      <q-card style="min-width: 450px; border-radius: 20px;" class="shadow-24 overflow-hidden">
        <!-- Dialog Header -->
        <q-card-section class="bg-primary text-white row items-center q-py-md q-px-lg">
          <q-avatar icon="group" color="primary-8" text-color="white" size="md" class="q-mr-md" />
          <div class="text-h6 text-weight-bold">{{ isEditing ? 'تعديل المجموعة' : 'مجموعة جديدة' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup class="text-white op-70 hover-op-100" />
        </q-card-section>

        <q-separator color="primary-10" />

        <!-- Form -->
        <q-card-section class="q-pa-lg">
          <q-form @submit="saveGroup" class="q-gutter-md">
            <q-input filled dense v-model="form.name" label="اسم المجموعة" color="primary" bg-color="grey-1"
              class="rounded-borders" :rules="[val => !!val || 'يرجى إدخال الاسم']" placeholder="مثال: ختمة رمضان">
              <template v-slot:prepend>
                <q-icon name="badge" color="primary" />
              </template>
            </q-input>

            <q-item tag="label" class="bg-grey-1 rounded-borders q-pa-md border-primary-1" v-ripple>
              <q-item-section avatar>
                <q-icon name="toggle_on" color="primary" size="md" v-if="form.active" />
                <q-icon name="toggle_off" color="grey" size="md" v-else />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold text-grey-8">حالة المجموعة</q-item-label>
                <q-item-label caption>{{ form.active ? 'المجموعة نشطة ويمكن استخدامها' : 'المجموعة معطلة مؤقتاً'
                }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-toggle v-model="form.active" color="primary" />
              </q-item-section>
            </q-item>

            <div class="row justify-end q-mt-xl">
              <q-btn label="إلغاء" color="grey-7" flat v-close-popup class="q-mr-sm" />
              <q-btn :label="isEditing ? 'حفظ التغييرات' : 'إنشاء المجموعة'" type="submit" color="primary" icon="save"
                unelevated class="q-px-lg shadow-2" style="border-radius: 10px" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const groups = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, name: '', active: true })

const columns = [
  { name: 'name', label: 'اسم المجموعة', field: 'name', sortable: true, align: 'left' },
  { name: 'active', label: 'الحالة', field: 'active', sortable: true, align: 'center', style: 'width: 150px' },
  { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center', style: 'width: 150px' }
]

const fetchGroups = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/groups')
    groups.value = response.data
  } catch (error) {
    console.error('Error fetching groups:', error)
    Swal.fire({
      icon: 'error',
      title: 'فشل التحميل',
      text: 'فشل تحميل المجموعات'
    })
  } finally {
    loading.value = false
  }
}

const openCreateDialog = () => {
  isEditing.value = false
  form.value = { id: null, name: '', active: true }
  dialogVisible.value = true
}

const editGroup = (group) => {
  isEditing.value = true
  form.value = { ...group, active: !!group.active }
  dialogVisible.value = true
}

const saveGroup = async () => {
  try {
    if (isEditing.value) {
      await axios.put(`/api/groups/${form.value.id}`, form.value)
      Swal.fire({
        icon: 'success',
        title: 'تم التعديل',
        text: 'تم تعديل المجموعة بنجاح',
        timer: 1500,
        showConfirmButton: false
      })
    } else {
      await axios.post('/api/groups', form.value)
      Swal.fire({
        icon: 'success',
        title: 'تم الإنشاء',
        text: 'تم إنشاء المجموعة بنجاح',
        timer: 1500,
        showConfirmButton: false
      })
    }
    dialogVisible.value = false
    fetchGroups()
  } catch (error) {
    console.error('Error saving group:', error)
    if (error.response && error.response.status === 422) {
      const message = error.response.data.message || 'بيانات غير صالحة'
      Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: message
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: 'حدث خطأ أثناء الحفظ'
      })
    }
  }
}

const confirmDelete = (group) => {
  Swal.fire({
    title: 'تأكيد الحذف',
    text: `هل أنت متأكد من حذف المجموعة "${group.name}"؟`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'نعم، احذفها',
    cancelButtonText: 'إلغاء'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/groups/${group.id}`)
        Swal.fire(
          'تم الحذف!',
          'تم حذف المجموعة بنجاح.',
          'success'
        )
        fetchGroups()
      } catch (error) {
        console.error('Error deleting group:', error)
        Swal.fire({
          icon: 'error',
          title: 'خطأ',
          text: 'فشل الحذف'
        })
      }
    }
  })
}

onMounted(() => {
  fetchGroups()
})
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.custom-table :deep(th) {
  font-size: 0.95rem;
  opacity: 0.9;
}

.custom-table :deep(tbody tr:hover) {
  background: rgba(0, 137, 123, 0.05) !important;
  /* Teal tint on hover */
}

.action-btn {
  transition: all 0.2s ease;
}

.action-btn:hover {
  transform: scale(1.1);
}

.border-teal-1 {
  border: 1px solid #e0f2f1;
}

.op-70 {
  opacity: 0.7;
}

.hover-op-100:hover {
  opacity: 1;
}
</style>
