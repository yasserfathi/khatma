<template>
    <q-page class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
                <div>
                    <div class="text-h4 text-primary text-weight-bold text-islamic">مجموعات القراءة</div>
                    <div class="text-subtitle2 text-grey-7">إدارة أرقام المجموعات والأسماء المرتبطة بها</div>
                </div>
            </div>
            <q-btn unelevated rounded color="primary" icon="add" label="مجموعة قراءة جديدة" @click="openCreateDialog"
                class="shadow-3 q-px-md" />
        </div>

        <!-- Group Readings Table -->
        <q-card class="glass-card q-pa-sm">
            <q-table :rows="groupReadings" :columns="columns" row-key="id" :loading="loading" flat :separator="'none'"
                class="bg-transparent text-grey-9 custom-table"
                table-header-class="text-primary text-weight-bold bg-primary-1 rounded-borders">

                <template v-slot:body-cell-names="props">
                    <q-td :props="props">
                        <div class="ellipsis-2-lines" style="max-width: 400px;">
                            {{ props.row.names }}
                            <q-tooltip anchor="top middle" self="bottom middle" :offset="[10, 10]">
                                {{ props.row.names }}
                            </q-tooltip>
                        </div>
                    </q-td>
                </template>

                <template v-slot:body-cell-actions="props">
                    <q-td :props="props" auto-width>
                        <div class="row justify-center q-gutter-x-sm">
                            <q-btn flat round color="primary" icon="edit" size="sm" class="bg-primary-1 action-btn"
                                @click="editGroupReading(props.row)">
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
                        لا توجد بيانات حالياً
                    </div>
                </template>
            </q-table>
        </q-card>

        <!-- Create/Edit Dialog -->
        <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 500px; border-radius: 20px;" class="shadow-24 overflow-hidden">
                <!-- Dialog Header -->
                <q-card-section class="bg-primary text-white row items-center q-py-md q-px-lg">
                    <q-avatar icon="menu_book" color="primary-8" text-color="white" size="md" class="q-mr-md" />
                    <div class="text-h6 text-weight-bold">{{ isEditing ? 'تعديل مجموعة القراءة' : 'مجموعة قراءة جديدة'
                        }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup class="text-white op-70 hover-op-100" />
                </q-card-section>

                <q-separator color="primary-10" />

                <!-- Form -->
                <q-card-section class="q-pa-lg">
                    <q-form @submit="saveGroupReading" class="q-gutter-md">
                        <q-input filled dense v-model="form.group_no" label="رقم المجموعة" color="primary"
                            bg-color="grey-1" class="rounded-borders"
                            :rules="[val => !!val || 'يرجى إدخال رقم المجموعة']" placeholder="مثال: 101">
                            <template v-slot:prepend>
                                <q-icon name="number" color="primary" />
                            </template>
                        </q-input>

                        <q-input filled dense type="textarea" v-model="form.names" label="الأسماء" color="primary"
                            bg-color="grey-1" class="rounded-borders" :rules="[val => !!val || 'يرجى إدخال الأسماء']"
                            placeholder="أدخل الأسماء هنا...">
                            <template v-slot:prepend>
                                <q-icon name="people" color="primary" />
                            </template>
                        </q-input>

                        <div class="row justify-end q-mt-xl">
                            <q-btn label="إلغاء" color="grey-7" flat v-close-popup class="q-mr-sm" />
                            <q-btn :label="isEditing ? 'حفظ التغييرات' : 'إنشاء'" type="submit" color="primary"
                                icon="save" unelevated class="q-px-lg shadow-2" style="border-radius: 10px" />
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

const groupReadings = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, group_no: '', names: '' })

const columns = [
    { name: 'id', label: '#', field: 'id', sortable: true, align: 'left', style: 'width: 50px' },
    { name: 'group_no', label: 'رقم المجموعة', field: 'group_no', sortable: true, align: 'center' },
    { name: 'names', label: 'الأسماء', field: 'names', align: 'right' },
    { name: 'created_by', label: 'تم الإنشاء بواسطة', field: row => row.creator?.name || '---', align: 'center' },
    { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center', style: 'width: 150px' }
]

const fetchGroupReadings = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/group-readings')
        groupReadings.value = response.data
    } catch (error) {
        console.error('Error fetching group readings:', error)
        Swal.fire({
            icon: 'error',
            title: 'فشل التحميل',
            text: 'حدث خطأ أثناء تحميل بيانات مجموعات القراءة'
        })
    } finally {
        loading.value = false
    }
}

const openCreateDialog = () => {
    isEditing.value = false
    form.value = { id: null, group_no: '', names: '' }
    dialogVisible.value = true
}

const editGroupReading = (item) => {
    isEditing.value = true
    form.value = { ...item }
    dialogVisible.value = true
}

const saveGroupReading = async () => {
    try {
        if (isEditing.value) {
            await axios.put(`/api/group-readings/${form.value.id}`, form.value)
            Swal.fire({
                icon: 'success',
                title: 'تم التعديل',
                text: 'تم التعديل بنجاح',
                timer: 1500,
                showConfirmButton: false
            })
        } else {
            await axios.post('/api/group-readings', form.value)
            Swal.fire({
                icon: 'success',
                title: 'تم الإنشاء',
                text: 'تم الإنشاء بنجاح',
                timer: 1500,
                showConfirmButton: false
            })
        }
        dialogVisible.value = false
        fetchGroupReadings()
    } catch (error) {
        console.error('Error saving group reading:', error)
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: 'حدث خطأ أثناء الحفظ'
        })
    }
}

const confirmDelete = (item) => {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: `هل أنت متأكد من حذف هذه المجموعة؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/group-readings/${item.id}`)
                Swal.fire('تم الحذف!', 'تم الحذف بنجاح.', 'success')
                fetchGroupReadings()
            } catch (error) {
                console.error('Error deleting:', error)
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الحذف' })
            }
        }
    })
}

onMounted(() => {
    fetchGroupReadings()
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
}

.action-btn {
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: scale(1.1);
}

.op-70 {
    opacity: 0.7;
}

.hover-op-100:hover {
    opacity: 1;
}

.ellipsis-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;
}
</style>
