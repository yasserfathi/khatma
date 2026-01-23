<template>
    <q-page class="q-pa-md">
        <!-- Header -->
        <div class="row items-center q-mb-lg">
            <q-btn flat round color="primary" icon="arrow_forward" @click="$router.push('/khatma-tilawa')" />
            <div class="q-ml-md">
                <div class="text-h5 text-primary text-weight-bold">توزيع الأجزاء</div>
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
            <div class="col-12 col-md-4">
                <q-card class="glass-card q-pa-md">
                    <q-card-section>
                        <div class="text-h6 text-primary q-mb-md">تعيين جديد</div>
                        <q-form ref="assignForm" @submit="assignParts" class="q-gutter-md">

                            <!-- User Select -->
                            <q-select filled v-model="selectedUser" :options="userOptions" option-value="id"
                                option-label="name" label="اختر القارئ" color="primary" emit-value map-options use-input
                                @filter="filterUsers" :rules="[val => !!val || 'يرجى اختيار القارئ']">
                                <template v-slot:prepend>
                                    <q-icon name="person" color="primary" />
                                </template>
                                <template v-slot:option="scope">
                                    <q-item v-bind="scope.itemProps">
                                        <q-item-section>
                                            <q-item-label>{{ scope.opt.name }}</q-item-label>
                                            <q-item-label caption v-if="scope.opt.phone">{{ scope.opt.phone
                                            }}</q-item-label>
                                        </q-item-section>
                                    </q-item>
                                </template>
                            </q-select>

                            <!-- Parts Multiselect -->
                            <q-select filled v-model="selectedParts" :options="partOptions" label="اختر الأجزاء"
                                multiple use-chips stack-label color="primary" emit-value map-options
                                :rules="[val => val && val.length > 0 || 'يرجى اختيار الأجزاء']">
                                <template v-slot:prepend>
                                    <q-icon name="format_list_numbered" color="primary" />
                                </template>
                                <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                                    <q-item v-bind="itemProps">
                                        <q-item-section>
                                            <q-item-label>الجزء {{ getPartDisplay(opt) }}</q-item-label>
                                        </q-item-section>
                                        <q-item-section side>
                                            <q-toggle :model-value="selected" @update:model-value="toggleOption(opt)" />
                                        </q-item-section>
                                    </q-item>
                                </template>
                                <template v-slot:selected-item="{ opt }">
                                    <q-chip dense class="q-ma-xs" color="primary" text-color="white" removable
                                        @remove="selectedParts = selectedParts.filter(p => p !== opt)">
                                        الجزء {{ getPartDisplay(opt) }}
                                    </q-chip>
                                </template>
                            </q-select>

                            <q-btn :label="editingId ? 'تحديث التعيين' : 'حفظ التعيين'" type="submit" color="primary"
                                class="full-width q-mt-md" unelevated
                                :disable="selectedParts.length === 0 || !selectedUser" :loading="submitting" />

                            <q-btn v-if="editingId" label="إلغاء التعديل" flat color="grey" class="full-width q-mt-sm"
                                @click="resetForm" />
                        </q-form>
                    </q-card-section>
                </q-card>

                <!-- Current Assignments List (Mini) -->
                <q-card class="glass-card q-mt-md">
                    <q-card-section>
                        <div class="text-h6 text-primary q-mb-sm">القائمة الحالية</div>
                        <q-list separator>
                            <q-item v-for="assign in assignments" :key="assign.id">
                                <q-item-section>
                                    <q-item-label>{{ assign.user?.name }}</q-item-label>
                                    <q-item-label caption>
                                        الأجزاء: {{ assign.parts ? assign.parts.map(getPartDisplay).join(', ') : '' }}
                                    </q-item-label>
                                </q-item-section>
                                <q-item-section side>
                                    <div class="row items-center q-gutter-x-xs">
                                        <q-toggle size="sm" v-model="assign.read" color="teal"
                                            :label="assign.read ? 'تمت القراءة' : 'لم تتم القراءة'" left-label
                                            @update:model-value="(val) => toggleReadStatus(assign, val)" />
                                        <q-btn flat round color="primary" icon="edit" size="sm"
                                            @click="editAssignment(assign)" />
                                        <q-btn flat round color="red" icon="delete" size="sm"
                                            @click="deleteAssignment(assign)" />
                                    </div>
                                </q-item-section>
                            </q-item>
                            <q-item v-if="assignments.length === 0">
                                <q-item-section class="text-grey text-center">لا توجد تعيينات بعد</q-item-section>
                            </q-item>
                        </q-list>
                    </q-card-section>
                </q-card>
            </div>

            <!-- Right Column: Parts Grid -->
            <div class="col-12 col-md-8">
                <!-- Iterate over multiple grids if needed -->
                <div v-for="gridIndex in totalGrids" :key="gridIndex" class="q-mb-lg">
                    <q-card class="glass-card q-pa-md">
                        <q-card-section>
                            <div class="row items-center justify-between q-mb-md">
                                <div class="text-h6 text-primary">
                                    {{ totalGrids > 1 ? `الختمة ${gridIndex}` : 'شبكة الأجزاء' }}
                                    <span v-if="totalGrids > 1" class="text-caption text-grey">
                                        (من {{ getPartDisplay((gridIndex - 1) * 30 + 1) }} إلى {{
                                            getPartDisplay(Math.min(gridIndex * 30, totalParts)) }})
                                    </span>
                                </div>
                                <div class="row q-gutter-x-md text-caption">
                                    <div class="row items-center">
                                        <div class="legend-box bg-grey-2"></div> متاح
                                    </div>
                                    <div class="row items-center">
                                        <div class="legend-box bg-amber border-grey"></div> محدد
                                    </div>
                                    <div class="row items-center">
                                        <div class="legend-box bg-amber text-black">👤</div> محجوزء
                                    </div>
                                    <div class="row items-center">
                                        <div class="legend-box bg-teal text-white">✓</div> مقروء
                                    </div>
                                </div>
                            </div>

                            <div class="parts-grid">
                                <div v-for="i in getPartsForGrid(gridIndex)" :key="i" class="part-box"
                                    :class="getPartClass(i)" @click="togglePart(i)">
                                    <div class="part-number">{{ getPartDisplay(i) }}</div>
                                    <div class="part-status">
                                        <span v-if="isAssignedToOther(i)" class="text-caption ellipsis text-center">
                                            {{ getAssigneeName(i) }}
                                        </span>
                                        <q-icon v-else-if="selectedParts.includes(i)" name="check" size="sm" />
                                        <span v-else class="text-grey-5">متاح</span>
                                    </div>
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </div>
    </q-page>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'
import Swal from 'sweetalert2'

const route = useRoute()
const $q = useQuasar()
const khatmaId = route.params.id

const loading = ref(true)
const submitting = ref(false)
const khatma = ref(null)
const users = ref([])
const assignments = ref([])

const selectedUser = ref(null)
const selectedParts = ref([])
const userOptions = ref([]) // For filtering

// Computed: Total Parts based on Khatma or default 30
const totalParts = computed(() => khatma.value?.juz_count || 30)

// Computed: Number of 30-part grids needed
const totalGrids = computed(() => Math.ceil(totalParts.value / 30))

// Helper to get array of part numbers for a specific grid (1-based index)
const getPartsForGrid = (gridIndex) => {
    const start = (gridIndex - 1) * 30 + 1
    const end = Math.min(gridIndex * 30, totalParts.value)
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
}

const partOptions = computed(() => {
    // Generate options 1 to totalParts
    const allParts = Array.from({ length: totalParts.value }, (_, i) => i + 1);

    return allParts.filter(part => {
        // If we are editing, allow the parts that belong to the current assignment being edited
        // aka ignore them in "assigned check". isAssignedToOther does exactly this.
        return !isAssignedToOther(part)
    })
})

// Helper to check if a part is already assigned to someone else (excluding current edit)
const isAssignedToOther = (partNo) => {
    if (!Array.isArray(assignments.value)) return false
    return assignments.value.some(a => {
        // If we are editing this assignment, ignore it for "assigned to other" check
        if (editingId.value && a.id === editingId.value) return false
        return a.parts && Array.isArray(a.parts) && a.parts.includes(partNo)
    })
}

// Keep isAssigned for backward compatibility if needed, distinct from "ToOther"
const isAssigned = (partNo) => isAssignedToOther(partNo)

const getAssigneeName = (partNo) => {
    if (!Array.isArray(assignments.value)) return ''
    const assignment = assignments.value.find(a => {
        if (editingId.value && a.id === editingId.value) return false
        return a.parts && Array.isArray(a.parts) && a.parts.includes(partNo)
    })
    if (!assignment) return ''
    // Return first name or full name
    return assignment.user?.name || 'مستخدم'
}

const getAssigneeReadStatus = (partNo) => {
    if (!Array.isArray(assignments.value)) return false
    const assignment = assignments.value.find(a => {
        if (editingId.value && a.id === editingId.value) return false
        return a.parts && Array.isArray(a.parts) && a.parts.includes(partNo)
    })
    return !!assignment?.read
}

const getPartDisplay = (partNo) => {
    return (partNo - 1) % 30 + 1
}

const getPartClass = (partNo) => {
    // Check if assigned to someone else
    if (isAssignedToOther(partNo)) {
        const assignment = assignments.value.find(a => {
            if (editingId.value && a.id === editingId.value) return false
            return a.parts && Array.isArray(a.parts) && a.parts.includes(partNo)
        })
        if (assignment?.read) return 'bg-teal text-white cursor-not-allowed'
        return 'bg-amber text-black cursor-not-allowed'
    }

    // Check if selected (current editing/creating)
    if (selectedParts.value.includes(partNo)) {
        return 'bg-amber text-black shadow-3' // Yellow for selected unread
    }

    return 'bg-grey-2 text-grey-8 hover-effect'
}

const togglePart = (partNo) => {
    if (isAssignedToOther(partNo)) {
        // Optional: Show info about who has this part
        return
    }

    const index = selectedParts.value.indexOf(partNo)
    if (index === -1) {
        selectedParts.value.push(partNo)
    } else {
        selectedParts.value.splice(index, 1)
    }
}

const fetchData = async () => {
    loading.value = true
    try {
        // 1. Get Khatma Details
        const khatmaRes = await axios.get(`/api/khatmas/${khatmaId}`)
        khatma.value = khatmaRes.data

        // 2. Get Users (Scoped to Admin AND Group)
        const usersRes = await axios.get('/api/users')
        // Filter users who have this group in their assigned groups
        const groupIdStr = String(khatma.value.group_id)
        users.value = usersRes.data.filter(u => {
            if (!u.group_numbers || !Array.isArray(u.group_numbers)) return false
            // Check if group_numbers array contains the group_id (handle string/number difference)
            return u.group_numbers.some(g => String(g) === groupIdStr)
        })
        userOptions.value = users.value

        // 3. Get Existing Assignments
        const assignRes = await axios.get('/api/tilawa-khatma-assignments', {
            params: { khatma_id: khatmaId }
        })
        assignments.value = assignRes.data

    } catch (error) {
        console.error("Error loading data", error)
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل تحميل البيانات' })
    } finally {
        loading.value = false
        if (!Array.isArray(assignments.value)) assignments.value = []
    }
}

const filterUsers = (val, update) => {
    if (val === '') {
        update(() => { userOptions.value = users.value })
        return
    }
    update(() => {
        const needle = val.toLowerCase()
        userOptions.value = users.value.filter(v => v.name.toLowerCase().indexOf(needle) > -1)
    })
}

const isRead = ref(false)
const editingId = ref(null)

// ... existing refs ...

const assignParts = async () => {
    if (!selectedUser.value || selectedParts.value.length === 0) return

    submitting.value = true
    try {
        const payload = {
            khatma_id: khatmaId,
            user_id: selectedUser.value,
            parts: selectedParts.value,
            read: isRead.value
        }

        if (editingId.value) {
            await axios.put(`/api/tilawa-khatma-assignments/${editingId.value}`, payload)
            Swal.fire({ icon: 'success', title: 'تم التحديث', text: 'تم تحديث التعيين بنجاح', timer: 1500, showConfirmButton: false })
        } else {
            await axios.post('/api/tilawa-khatma-assignments', payload)
            Swal.fire({ icon: 'success', title: 'تم الحفظ', text: 'تم تعيين الأجزاء بنجاح', timer: 1500, showConfirmButton: false })
        }

        resetForm()

        // Reload assignments
        const assignRes = await axios.get('/api/tilawa-khatma-assignments', {
            params: { khatma_id: khatmaId }
        })
        assignments.value = assignRes.data

    } catch (error) {
        console.error("Error assigning", error)
        const msg = error.response?.data?.message || 'فشل حفظ التعيين'
        Swal.fire({ icon: 'error', title: 'خطأ', text: msg })
    } finally {
        submitting.value = false
    }
}

const editAssignment = (assign) => {
    editingId.value = assign.id
    selectedUser.value = assign.user_id
    selectedParts.value = assign.parts || []
    isRead.value = !!assign.read
}

const assignForm = ref(null)

const deleteAssignment = (assign) => {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من حذف هذا التعيين؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/tilawa-khatma-assignments/${assign.id}`)
                Swal.fire('تم الحذف', 'تم حذف التعيين', 'success')

                // If we were editing this assignment, reset the form
                if (editingId.value === assign.id) {
                    resetForm()
                }

                // Reload
                const assignRes = await axios.get('/api/tilawa-khatma-assignments', {
                    params: { khatma_id: khatmaId }
                })
                assignments.value = assignRes.data
            } catch (error) {
                console.error("Error deleting", error)
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الحذف' })
            }
        }
    })
}

const resetForm = () => {
    selectedParts.value = []
    selectedUser.value = null
    isRead.value = false
    editingId.value = null
    // Reset validation state if form ref exists, wait for DOM update
    nextTick(() => {
        if (assignForm.value) {
            assignForm.value.resetValidation()
        }
    })
}

onMounted(() => {
    if (khatmaId) {
        fetchData()
    }
})

const toggleReadStatus = async (assignment, newVal) => {
    try {
        await axios.put(`/api/tilawa-khatma-assignments/${assignment.id}`, {
            khatma_id: assignment.khatma_id,
            user_id: assignment.user_id,
            parts: assignment.parts,
            read: newVal
        })
        Swal.fire({
            icon: 'success',
            title: 'تم تحديث حالة القراءة',
            showConfirmButton: false,
            timer: 1500
        })
    } catch (error) {
        assignment.read = !newVal // Revert on error
        console.error('Error updating read status:', error)
        Swal.fire({
            icon: 'error',
            title: 'حدث خطأ',
            text: 'حدث خطأ أثناء تحديث الحالة'
        })
    }
}
</script>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.body--dark .glass-card {
    background: rgba(30, 30, 30, 0.85);
    /* Slightly darker for dark mode */
    border: 1px solid rgba(255, 255, 255, 0.1);
}


.parts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 0.2fr));
    gap: 12px;
}

.part-box {
    border-radius: 12px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    /* aspect-ratio: 1; Removed to reduce height */
}

.part-box.hover-effect:hover {
    transform: translateY(-2px);
    border-color: var(--q-primary);
}

.part-number {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 4px;
}

.legend-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-left: 8px;
    display: inline-block;
    border: 1px solid #ddd;
}
</style>
