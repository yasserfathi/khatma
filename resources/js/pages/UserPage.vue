<template>
    <q-page class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
                <div>
                    <div class="text-h4 text-primary text-weight-bold text-islamic">المشاركين</div>
                    <div class="text-subtitle2 text-grey-7">إدارة المشاركين</div>
                </div>
            </div>
            <q-btn unelevated rounded color="primary" icon="add" label="مشترك جديد" @click="openCreateDialog"
                class="shadow-3 q-px-md" />
        </div>

        <!-- Users Table -->
        <q-card class="glass-card q-pa-sm">
            <q-table :rows="users" :columns="columns" row-key="id" :loading="loading" flat :separator="'none'"
                class="bg-transparent text-grey-9 custom-table"
                table-header-class="text-primary text-weight-bold bg-primary-1 rounded-borders">
                <template v-slot:body-cell-active="props">
                    <q-td :props="props">
                        <q-badge rounded :color="props.row.active ? 'primary' : 'grey'"
                            class="q-px-sm q-py-xs shadow-1">
                            {{ props.row.active ? 'نشط' : 'غير نشط' }}
                            <q-icon :name="props.row.active ? 'check_circle' : 'cancel'" class="q-ml-xs" />
                        </q-badge>
                    </q-td>
                </template>

                <template v-slot:body-cell-group_numbers="props">
                    <q-td :props="props">
                        <div class="row q-gutter-xs">
                            <q-badge v-for="(num, index) in props.row.group_numbers" :key="index" rounded
                                color="primary" class="q-px-sm q-py-xs">
                                {{ getGroupName(num) }}
                            </q-badge>
                        </div>
                    </q-td>
                </template>

                <template v-slot:body-cell-actions="props">
                    <q-td :props="props" auto-width>
                        <div class="row justify-center q-gutter-x-sm">
                            <q-btn flat round color="primary-7" icon="edit" size="sm" class="bg-primary-1 action-btn"
                                @click="editUser(props.row)">
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
                        لا يوجد مشتركين حالياً
                    </div>
                </template>
            </q-table>
        </q-card>

        <!-- Create/Edit Dialog -->
        <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale">
            <q-card style="min-width: 450px; border-radius: 20px;" class="shadow-24 overflow-hidden">
                <!-- Dialog Header -->
                <q-card-section class="bg-primary text-white row items-center q-py-md q-px-lg">
                    <q-avatar icon="person" color="primary-8" text-color="white" size="md" class="q-mr-md" />
                    <div class="text-h6 text-weight-bold">{{ isEditing ? 'تعديل المشترك' : 'مشترك جديد' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup class="text-white op-70 hover-op-100" />
                </q-card-section>

                <q-separator color="primary-10" />

                <!-- Form -->
                <q-card-section class="q-pa-lg">
                    <q-form @submit="saveUser" class="q-gutter-md">
                        <q-input filled dense v-model="form.name" label="الاسم" color="primary" bg-color="grey-1"
                            class="rounded-borders" :rules="[val => !!val || 'يرجى إدخال الاسم']">
                            <template v-slot:prepend>
                                <q-icon name="person" color="primary" />
                            </template>
                        </q-input>

                        <q-input filled dense v-model="form.email" label="البريد الإلكتروني" color="primary"
                            bg-color="grey-1" class="rounded-borders">
                            <template v-slot:prepend>
                                <q-icon name="email" color="primary" />
                            </template>
                        </q-input>

                        <q-input filled dense v-model="form.phone" label="رقم الهاتف" color="primary" bg-color="grey-1"
                            class="rounded-borders" :rules="[val => !!val || 'يرجى إدخال رقم الهاتف']">
                            <template v-slot:prepend>
                                <q-icon name="phone" color="primary" />
                            </template>
                        </q-input>

                        <q-select filled dense v-model="form.group_numbers" label="أرقام المجموعات"
                            :options="groupOptions" option-value="id" option-label="name" multiple counter
                            max-values="10" emit-value map-options use-chips color="primary" bg-color="grey-1"
                            class="rounded-borders"
                            :rules="[val => val && val.length > 0 || 'يرجى اختيار مجموعة واحدة على الأقل']">
                            <template v-slot:prepend>
                                <q-icon name="list_alt" color="primary" />
                            </template>
                        </q-select>


                        <q-item tag="label" class="bg-grey-1 rounded-borders q-pa-md border-primary-1" v-ripple>
                            <q-item-section avatar>
                                <q-icon name="toggle_on" color="primary" size="md" v-if="form.active" />
                                <q-icon name="toggle_off" color="grey" size="md" v-else />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label class="text-weight-bold text-grey-8">حالة الحساب</q-item-label>
                                <q-item-label caption>{{ form.active ? 'حساب نشط' : 'حساب معطل' }}</q-item-label>
                            </q-item-section>
                            <q-item-section side>
                                <q-toggle v-model="form.active" color="primary" />
                            </q-item-section>
                        </q-item>

                        <div class="row justify-end q-mt-xl">
                            <q-btn label="إلغاء" color="grey-7" flat v-close-popup class="q-mr-sm" />
                            <q-btn :label="isEditing ? 'حفظ التغييرات' : 'إنشاء المشترك'" type="submit" color="primary"
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

const users = ref([])
const groupOptions = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, name: '', email: '', phone: '', group_numbers: [], password: '', active: true })

const columns = [
    { name: 'id', label: '#', field: 'id', sortable: true, align: 'left', style: 'width: 50px' },
    { name: 'name', label: 'الاسم', field: 'name', sortable: true, align: 'left' },
    { name: 'phone', label: 'رقم الهاتف', field: 'phone', sortable: true, align: 'left' },
    { name: 'group_numbers', label: 'أرقام المجموعات', field: 'group_numbers', align: 'left' },
    { name: 'active', label: 'الحالة', field: 'active', sortable: true, align: 'center', style: 'width: 150px' },
    { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center', style: 'width: 150px' }
]

const fetchGroups = async () => {
    try {
        const response = await axios.get('/api/groups')
        groupOptions.value = response.data
    } catch (error) {
        console.error('Error fetching groups')
    }
}

const fetchUsers = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/users')
        users.value = response.data
    } catch (error) {
        console.error('Error fetching users:', error)
        Swal.fire({
            icon: 'error',
            title: 'فشل التحميل',
            text: 'فشل تحميل المشتركين'
        })
    } finally {
        loading.value = false
    }
}

const openCreateDialog = () => {
    isEditing.value = false
    form.value = { id: null, name: '', email: '', phone: '', group_numbers: [], password: '', active: true }
    fetchGroups()
    dialogVisible.value = true
}

const editUser = (user) => {
    isEditing.value = true
    // Don't fill password on edit unless implemented
    form.value = { ...user, password: '', active: !!user.active }
    fetchGroups()
    dialogVisible.value = true
}

const saveUser = async () => {
    try {
        const data = { ...form.value }
        // Clean phone number: strip all whitespace
        if (data.phone) {
            data.phone = data.phone.replace(/\s+/g, '')
        }
        if (isEditing.value) {
            if (!data.password) delete data.password; // Don't send empty password on edit
            await axios.put(`/api/users/${form.value.id}`, data)
            Swal.fire({
                icon: 'success',
                title: 'تم التعديل',
                text: 'تم تعديل بيانات المشترك بنجاح',
                timer: 1500,
                showConfirmButton: false
            })
        } else {
            await axios.post('/api/users', data)
            Swal.fire({
                icon: 'success',
                title: 'تم الإنشاء',
                text: 'تم إنشاء المشترك بنجاح',
                timer: 1500,
                showConfirmButton: false
            })
        }
        dialogVisible.value = false
        fetchUsers()
    } catch (error) {
        console.error('Error saving user:', error)
        // Check for validation errors
        if (error.response && error.response.status === 422) {
            const message = error.response.data.message || 'يرجى التحقق من المدخلات'
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

const confirmDelete = (user) => {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: `هل أنت متأكد من حذف المشترك"${user.name}"؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذفه',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/users/${user.id}`)
                Swal.fire(
                    'تم الحذف!',
                    'تم حذف المشتركبنجاح.',
                    'success'
                )
                fetchUsers()
            } catch (error) {
                console.error('Error deleting user:', error)
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'فشل الحذف'
                })
            }
        }
    })
}

const getGroupName = (id) => {
    const group = groupOptions.value.find(g => g.id === id)
    return group ? group.name : id
}

onMounted(() => {
    fetchGroups()
    fetchUsers()
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

.border-primary-1 {
    border: 1px solid #e0f2f1;
}

.op-70 {
    opacity: 0.7;
}

.hover-op-100:hover {
    opacity: 1;
}
</style>
