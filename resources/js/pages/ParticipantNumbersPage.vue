<template>
    <q-page class="q-pa-md">
        <!-- Header -->
        <div class="row items-center q-mb-lg">
            <q-btn flat round color="primary" icon="arrow_forward" @click="$router.push('/khatma-zikr')" />
            <div class="q-ml-md">
                <div class="text-h5 text-primary text-weight-bold">ترقيم المشاركين (مجموعات الذكر)</div>
                <div class="text-subtitle2 text-grey-7">تخصيص أرقام للمشاركين في كل مجموعة</div>
            </div>
        </div>

        <!-- Group Selector -->
        <q-card class="glass-card q-mb-lg q-pa-sm">
            <q-card-section>
                <div class="row items-center q-col-gutter-md">
                    <div class="col-12 col-sm-6">
                        <q-select filled v-model="selectedGroup" :options="zikrGroups" option-value="id"
                            option-label="name" label="اختر مجموعة الذكر" emit-value map-options
                            @update:model-value="fetchParticipants" />
                    </div>
                    <q-space />
                    <div class="col-auto">
                        <q-btn label="حفظ التغييرات" color="primary" icon="save" unelevated :loading="saving"
                            :disable="!selectedGroup || participants.length === 0" @click="saveNumbers" />
                    </div>
                </div>
            </q-card-section>
        </q-card>

        <!-- Participants Table -->
        <div v-if="loading" class="row justify-center q-py-xl">
            <q-spinner color="primary" size="3em" />
        </div>

        <q-card v-else-if="selectedGroup" class="glass-card">
            <q-table :rows="participants" :columns="columns" row-key="user_id" flat :pagination="{ rowsPerPage: 0 }"
                hide-pagination>
                <template v-slot:body-cell-participant_no="props">
                    <q-td :props="props">
                        <q-input v-model="props.row.participant_no" dense filled placeholder="مثلا: 101"
                            style="max-width: 150px" class="q-mx-auto" />
                    </q-td>
                </template>
                <template v-slot:no-data>
                    <div class="full-width row flex-center text-grey-6 q-pa-lg">
                        لا يوجد مشاركين في هذه المجموعة
                    </div>
                </template>
            </q-table>
        </q-card>

        <div v-else class="text-center q-pa-xl text-grey-6">
            يرجى اختيار مجموعة لعرض المشاركين
        </div>
    </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const loading = ref(false)
const saving = ref(false)
const selectedGroup = ref(null)
const zikrGroups = ref([])
const participants = ref([])

const columns = [
    { name: 'name', label: 'الاسم', field: 'name', align: 'left' },
    { name: 'phone', label: 'رقم الهاتف', field: 'phone', align: 'left' },
    { name: 'participant_no', label: 'رقم المشارك', field: 'participant_no', align: 'center' }
]

const fetchData = async () => {
    try {
        const res = await axios.get('/api/groups', { params: { type: 'zikr' } })
        zikrGroups.value = res.data
    } catch (error) {
        console.error('Error fetching groups:', error)
    }
}

const fetchParticipants = async (groupId) => {
    if (!groupId) return
    loading.value = true
    participants.value = []
    try {
        const [usersRes, numsRes] = await Promise.all([
            axios.get('/api/users'),
            axios.get('/api/zikr-group-participants', { params: { group_id: groupId } })
        ])

        const groupUsers = (usersRes.data || []).filter(u => {
            return u.group_numbers?.some(g => String(g) === String(groupId))
        })

        const numbersMap = {}
        if (Array.isArray(numsRes.data)) {
            numsRes.data.forEach(n => {
                numbersMap[n.user_id] = n.participant_no
            })
        }

        participants.value = groupUsers.map(u => ({
            user_id: u.id,
            name: u.name,
            phone: u.phone,
            participant_no: numbersMap[u.id] || ''
        }))
    } catch (error) {
        console.error('Error fetching participants:', error)
        Swal.fire('خطأ', 'فشل تحميل المشاركين', 'error')
    } finally {
        loading.value = false
    }
}

const saveNumbers = async () => {
    if (!selectedGroup.value) return
    saving.value = true
    try {
        const payload = {
            group_id: selectedGroup.value,
            participants: participants.value.map(p => ({
                user_id: p.user_id,
                participant_no: p.participant_no
            }))
        }
        await axios.post('/api/zikr-group-participants/bulk', payload)
        Swal.fire({
            icon: 'success',
            title: 'تم الحفظ',
            text: 'تم تحديث أرقام المشاركين بنجاح',
            timer: 1500,
            showConfirmButton: false
        })
    } catch (error) {
        console.error('Error saving numbers:', error)
        Swal.fire('خطأ', 'فشل حفظ التغييرات', 'error')
    } finally {
        saving.value = false
    }
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
