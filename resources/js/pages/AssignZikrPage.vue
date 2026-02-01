<template>
    <q-page class="q-pa-md">
        <!-- Header -->
        <div class="row items-center q-mb-lg">
            <q-btn flat round color="primary" icon="arrow_forward" @click="$router.push('/khatma-zikr')" />
            <div class="q-ml-md">
                <div class="text-h5 text-primary text-weight-bold">ختمة ذكر الرسول صلى الله عليه وسلم</div>
                <div class="text-subtitle2 text-grey-7" v-if="khatma">
                    {{ khatma.khatma_no || 'الختمة' }} - {{ khatma.group?.name }}
                </div>
            </div>
        </div>

        <div v-if="loading" class="row justify-center q-py-xl">
            <q-spinner color="primary" size="3em" />
        </div>

        <!-- Main Content -->
        <div v-else class="row q-col-gutter-lg">

            <!-- Left Column: Assignment Form -->
            <div class="col-12 col-md-5">
                <q-card class="glass-card q-pa-md">
                    <q-card-section>
                        <div class="text-h6 text-primary q-mb-md">ادخال جديد</div>
                        <q-form ref="assignForm" @submit="saveAssignment" class="q-gutter-md">
                            <!-- Selected User Detail -->
                            <div v-if="selectedUserDetail" class="row justify-center q-mb-sm">
                                <q-chip outline color="primary" text-color="primary" icon="tag" class="shadow-1">
                                    رقم المشارك: <span class="text-weight-bold q-ml-xs">{{
                                        selectedUserDetail.participant_no || 'غير محدد' }}</span>
                                </q-chip>
                            </div>

                            <!-- User Select -->
                            <q-select filled v-model="selectedUser" :options="userOptions" option-value="id"
                                option-label="label" label="اختر الشخص" color="primary" emit-value map-options use-input
                                @filter="filterUsers" :rules="[val => !!val || 'يرجى اختيار الشخص']">
                                <template v-slot:prepend>
                                    <q-icon name="person" color="primary" />
                                </template>
                            </q-select>

                            <!-- Zikr Count Input -->
                            <q-input filled v-model.number="zikrCount" label="عدد ذكر الرسول صلى الله عليه وسلم"
                                type="number" color="primary" :rules="[val => val >= 0 || 'يجب أن يكون 0 أو أكثر']">
                                <template v-slot:prepend>
                                    <q-icon name="pin" color="primary" />
                                </template>
                            </q-input>

                            <q-btn :label="editingId ? 'تحديث' : 'حفظ'" type="submit" color="primary"
                                class="full-width q-mt-md" unelevated :loading="submitting" />

                            <q-btn v-if="editingId" label="إلغاء التعديل" flat color="grey" class="full-width q-mt-sm"
                                @click="resetForm" />
                        </q-form>
                    </q-card-section>
                </q-card>
            </div>

            <!-- Right Column: Assignments List -->
            <div class="col-12 col-md-7">
                <q-card class="glass-card q-pa-sm">
                    <q-card-section>
                        <div class="text-h6 text-primary q-mb-md">تفاصيل الختمة</div>
                        <q-table :rows="assignments" :columns="columns" row-key="id" flat :separator="'none'"
                            class="bg-transparent custom-table">
                            <template v-slot:body-cell-actions="props">
                                <q-td :props="props" auto-width>
                                    <div class="row justify-center q-gutter-x-sm">
                                        <q-btn flat round color="primary" icon="edit" size="sm"
                                            @click="editAssignment(props.row)" />
                                        <q-btn flat round color="red" icon="delete" size="sm"
                                            @click="confirmDelete(props.row)" />
                                    </div>
                                </q-td>
                            </template>
                            <template v-slot:no-data>
                                <div class="full-width row flex-center text-grey-6 q-pa-lg">
                                    لا توجد توزيعات بعد
                                </div>
                            </template>
                        </q-table>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </q-page>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const route = useRoute()
const khatmaId = route.params.id

const loading = ref(true)
const submitting = ref(false)
const khatma = ref(null)
const users = ref([])
const assignments = ref([])
const userOptions = ref([])

const selectedUser = ref(null)
const zikrCount = ref(0)
const editingId = ref(null)
const participantNumbers = ref({})

const selectedUserDetail = computed(() => {
    if (!selectedUser.value) return null
    return users.value.find(u => u.id === selectedUser.value)
})

const columns = [
    {
        name: 'participant_no',
        label: 'الكود',
        field: row => participantNumbers.value[row.user_id] || '---',
        align: 'center',
        sortable: true
    },
    {
        name: 'user',
        label: 'الاسم',
        field: row => row.user?.name || '',
        align: 'left',
        sortable: true
    },
    { name: 'zikr_count', label: 'العدد', field: 'zikr_count', align: 'center', sortable: true },
    { name: 'created_by', label: 'تم الإنشاء بواسطة', field: row => row.creator?.name || '---', align: 'center', sortable: true },
    { name: 'actions', label: 'الإجراءات', align: 'center' }
]

const fetchData = async () => {
    loading.value = true
    try {
        const [khatmaRes, usersRes, assignRes] = await Promise.all([
            axios.get(`/api/khatmas/${khatmaId}`),
            axios.get('/api/users'),
            axios.get('/api/zikr-khatma-assignments', { params: { khatma_id: khatmaId } })
        ])
        khatma.value = khatmaRes.data

        // Fetch participant numbers for this group
        const numbersRes = await axios.get('/api/zikr-group-participants', {
            params: { group_id: khatma.value.group_id }
        })
        const numsMap = {}
        numbersRes.data.forEach(n => {
            numsMap[n.user_id] = n.participant_no
        })
        participantNumbers.value = numsMap

        users.value = usersRes.data.filter(u => {
            const gid = String(khatma.value.group_id)
            return u.group_numbers?.some(g => String(g) === gid)
        }).map(u => {
            const num = String(participantNumbers.value[u.id] || '')
            return {
                ...u,
                participant_no: num,
                label: num ? `${num} - ${u.name}` : u.name
            }
        })
        userOptions.value = users.value
        assignments.value = assignRes.data
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحميل البيانات' })
    } finally {
        loading.value = false
    }
}

const filterUsers = (val, update) => {
    if (val === '') {
        update(() => { userOptions.value = users.value })
        return
    }
    update(() => {
        const needle = val.toLowerCase()
        userOptions.value = users.value.filter(v =>
            v.name.toLowerCase().indexOf(needle) > -1 ||
            v.participant_no.toLowerCase().indexOf(needle) > -1
        )
    })
}

const saveAssignment = async () => {
    submitting.value = true
    try {
        const payload = {
            khatma_id: khatmaId,
            user_id: selectedUser.value,
            zikr_count: zikrCount.value
        }
        if (editingId.value) {
            await axios.put(`/api/zikr-khatma-assignments/${editingId.value}`, payload)
        } else {
            await axios.post('/api/zikr-khatma-assignments', payload)
        }
        resetForm()
        fetchData()
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الحفظ' })
    } finally {
        submitting.value = false
    }
}

const editAssignment = (assign) => {
    editingId.value = assign.id
    selectedUser.value = assign.user_id
    zikrCount.value = assign.zikr_count
}

const confirmDelete = (assign) => {
    Swal.fire({
        title: 'تأكيد الحذف',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/zikr-khatma-assignments/${assign.id}`)
                fetchData()
                if (editingId.value === assign.id) resetForm()
            } catch (error) {
                Swal.fire('خطأ', 'فشل الحذف', 'error')
            }
        }
    })
}

const assignForm = ref(null)
const resetForm = () => {
    selectedUser.value = null
    zikrCount.value = 0
    editingId.value = null
    nextTick(() => assignForm.value?.resetValidation())
}

onMounted(fetchData)
</script>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
</style>
