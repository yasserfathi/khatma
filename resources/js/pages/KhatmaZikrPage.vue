<template>
    <q-page class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
                <div>
                    <div class="text-h4 text-primary text-weight-bold text-islamic">ختمة ذكر</div>
                    <div class="text-subtitle2 text-grey-7">جداول الذكر والأوراد</div>
                </div>
            </div>
            <q-btn unelevated rounded color="primary" icon="add" label="ختمة ذكر جديدة" @click="openCreateDialog"
                class="shadow-3 q-px-md" />
        </div>

        <!-- Khatmas Table -->
        <q-card class="glass-card q-pa-sm">
            <q-table :rows="khatmas" :columns="columns" row-key="id" :loading="loading" flat :separator="'none'"
                class="bg-transparent text-grey-9 custom-table"
                table-header-class="text-primary text-weight-bold bg-primary-1 rounded-borders">
                <template v-slot:body-cell-id="props">
                    <q-td :props="props">
                        {{ props.rowIndex + 1 }}
                    </q-td>
                </template>
                <template v-slot:body-cell-description="props">
                    <q-td :props="props">
                        <div class="text-grey-8 ellipsis" style="max-width: 200px" v-html="props.row.description"></div>
                    </q-td>
                </template>

                <template v-slot:body-cell-progress="props">
                    <q-td :props="props">
                        <div class="column items-center q-gutter-y-xs" style="min-width: 140px">
                            <q-linear-progress :value="props.row.progress ? props.row.progress.percentage / 100 : 0"
                                color="teal" track-color="grey-3" rounded size="12px" class="shadow-1" />
                            <div class="row full-width justify-between text-caption text-grey-8">
                                <span class="text-weight-bold">{{ props.row.progress ? props.row.progress.percentage : 0
                                    }}% مكتمل</span>
                                <span>{{ props.row.progress ?
                                    `${props.row.progress.finished}/${props.row.progress.total}` : '0/0' }}</span>
                            </div>
                        </div>
                    </q-td>
                </template>

                <template v-slot:body-cell-actions="props">
                    <q-td :props="props" auto-width>
                        <div class="row justify-center q-gutter-x-sm no-wrap">
                            <q-btn flat round color="teal-7" icon="content_copy" size="sm" class="bg-teal-1 action-btn"
                                @click="copyDescription(props.row)">
                                <q-tooltip>نسخ الوصف</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="teal-7" icon="fa-brands fa-whatsapp" size="sm"
                                class="bg-teal-1 action-btn" @click="shareWhatsapp(props.row)">
                                <q-tooltip>مشاركة عبر واتساب</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="deep-purple-7" icon="assignment_ind" size="sm"
                                class="bg-deep-purple-1 action-btn" :to="`/khatma-zikr/${props.row.id}/assign`">
                                <q-tooltip>توزيع الأذكار</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="primary-7" icon="edit" size="sm" class="bg-primary-1 action-btn"
                                @click="editKhatma(props.row)">
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
                        لا توجد ختمات ذِكر حالياً
                    </div>
                </template>
            </q-table>
        </q-card>

        <!-- Create/Edit Dialog -->
        <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale" persistent maximized>
            <q-card class="shadow-24 overflow-hidden">
                <!-- Dialog Header -->
                <q-card-section class="bg-primary text-white row items-center q-py-md q-px-lg">
                    <q-avatar icon="menu_book" color="primary-8" text-color="white" size="md" class="q-mr-md" />
                    <div class="text-h6 text-weight-bold">{{ isEditing ? 'تعديل الختمة' : 'ختمة ذِكر جديدة' }}</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup class="text-white op-70 hover-op-100" />
                </q-card-section>

                <q-separator color="primary-10" />

                <!-- Form -->
                <q-card-section class="q-pa-lg scroll" style="max-height: calc(100vh - 150px)">
                    <q-form @submit="saveKhatma" class="q-gutter-md" style="max-width: 800px; margin: 0 auto;">

                        <div class="row q-col-gutter-md">
                            <div class="col-12">
                                <q-select filled dense v-model="form.group_id" :options="groupOptions" option-value="id"
                                    option-label="name" emit-value map-options label="المجموعة التابعة لها"
                                    color="primary" bg-color="grey-1" class="rounded-borders"
                                    :rules="[val => !!val || 'يرجى اختيار المجموعة']">
                                    <template v-slot:prepend>
                                        <q-icon name="group" color="primary" />
                                    </template>
                                </q-select>
                            </div>

                            <div class="col-12 col-md-6">
                                <q-input filled dense v-model="form.khatma_no" label="رقم الختمة (مثال: 2026/3)"
                                    color="primary" bg-color="grey-1" class="rounded-borders" />
                            </div>

                            <div class="col-12 col-md-6">
                                <q-input filled dense v-model="form.hijri_date" label="التاريخ الهجري" color="primary"
                                    bg-color="grey-1" class="rounded-borders" readonly>
                                    <template v-slot:append>
                                        <q-icon name="event" class="cursor-pointer">
                                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                <div class="q-pa-md bg-white row q-gutter-sm" style="min-width: 300px">
                                                    <q-select dense outlined v-model="hijriDay" :options="daysList"
                                                        label="اليوم" class="col"
                                                        @update:model-value="updateHijriDate" />
                                                    <q-select dense outlined v-model="hijriMonth"
                                                        :options="islamicLocale.months" label="الشهر" class="col-grow"
                                                        @update:model-value="updateHijriDate" />
                                                    <q-select dense outlined v-model="hijriYear" :options="yearsList"
                                                        label="السنة" class="col"
                                                        @update:model-value="updateHijriDate" />
                                                </div>
                                            </q-popup-proxy>
                                        </q-icon>
                                    </template>
                                </q-input>
                            </div>

                        </div>

                        <div class="q-mt-md">
                            <label class="text-weight-bold text-grey-8 q-mb-sm block">الوصف والتفاصيل</label>
                            <q-editor v-model="form.description" min-height="15rem" :definitions="{
                                insert_table: {
                                    tip: 'إدراج جدول',
                                    icon: 'table_chart',
                                    label: 'جدول',
                                    handler: insertTable
                                },
                                insert_emoji: {
                                    tip: 'إدراج رمز تعبيري',
                                    icon: 'sentiment_satisfied_alt',
                                    label: 'إيموجي',
                                    handler: insertEmoji
                                }
                            }" :toolbar="[
                                [
                                    'bold', 'italic', 'strike', 'underline', 'subscript', 'superscript'
                                ],
                                [
                                    'left', 'center', 'right', 'justify'
                                ],
                                ['insert_table', 'insert_emoji'],
                                ['print', 'fullscreen'],
                                [
                                    {
                                        label: $q.lang.editor.formatting,
                                        icon: $q.iconSet.editor.formatting,
                                        list: 'no-icons',
                                        options: [
                                            'p',
                                            'h1',
                                            'h2',
                                            'h3',
                                            'h4',
                                            'h5',
                                            'h6',
                                            'code'
                                        ]
                                    },
                                    {
                                        label: $q.lang.editor.fontSize,
                                        icon: $q.iconSet.editor.fontSize,
                                        fixedLabel: true,
                                        fixedIcon: true,
                                        list: 'no-icons',
                                        options: [
                                            'size-1',
                                            'size-2',
                                            'size-3',
                                            'size-4',
                                            'size-5',
                                            'size-6',
                                            'size-7'
                                        ]
                                    },
                                    {
                                        label: $q.lang.editor.defaultFont,
                                        icon: $q.iconSet.editor.font,
                                        fixedIcon: true,
                                        list: 'no-icons',
                                        options: [
                                            'default_font',
                                            'arial',
                                            'arial_black',
                                            'comic_sans',
                                            'courier_new',
                                            'impact',
                                            'lucida_grande',
                                            'times_new_roman',
                                            'verdana'
                                        ]
                                    },
                                    'removeFormat'
                                ],
                                ['quote', 'unordered', 'ordered', 'outdent', 'indent'],
                                ['undo', 'redo'],
                                ['viewsource']
                            ]" :fonts="{
                                arial: 'Arial',
                                arial_black: 'Arial Black',
                                comic_sans: 'Comic Sans MS',
                                courier_new: 'Courier New',
                                impact: 'Impact',
                                lucida_grande: 'Lucida Grande',
                                times_new_roman: 'Times New Roman',
                                verdana: 'Verdana'
                            }" />
                        </div>

                        <!-- Emoji Picker Dialog -->
                        <q-dialog v-model="emojiDialog">
                            <q-card style="width: 300px">
                                <q-card-section>
                                    <div class="text-h6">اختر رمزاً</div>
                                </q-card-section>
                                <q-card-section class="row q-gutter-sm justify-center">
                                    <q-btn v-for="emoji in emojis" :key="emoji" flat dense size="lg" :label="emoji"
                                        @click="selectEmoji(emoji)" class="emoji-btn" />
                                </q-card-section>
                                <q-card-actions align="right">
                                    <q-btn flat label="إغلاق" color="primary" v-close-popup />
                                </q-card-actions>
                            </q-card>
                        </q-dialog>

                        <div class="row justify-end q-mt-xl">
                            <q-btn label="إلغاء" color="grey-7" flat v-close-popup class="q-mr-sm" />
                            <q-btn :label="isEditing ? 'حفظ التغييرات' : 'إنشاء الختمة'" type="submit" color="primary"
                                icon="save" unelevated class="q-px-lg shadow-2" style="border-radius: 10px" />
                        </div>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- Hidden Capture Container -->
        <div id="capture-container"
            style="position: fixed; left: -9999px; top: 0; width: 600px; background: white; z-index: -1;"></div>

    </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useQuasar, Loading } from 'quasar'
import html2canvas from 'html2canvas'

const $q = useQuasar()
const khatmas = ref([])
const groupOptions = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, group_id: null, khatma_no: '', description: '', hijri_date: '' })

const columns = [
    { name: 'id', label: '#', field: 'id', sortable: true, align: 'left', style: 'width: 50px' },
    { name: 'group_id', label: 'المجموعة', field: row => row.group?.name || '', sortable: true, align: 'center' },
    { name: 'hijri_date', label: 'التاريخ الهجري', field: 'hijri_date', sortable: true, align: 'center' },
    { name: 'khatma_no', label: 'المسمى/الرقم', field: 'khatma_no', sortable: true, align: 'left' },
    { name: 'progress', label: 'الإنجاز', field: 'progress', align: 'center' },
    { name: 'actions', label: 'الإجراءات', field: 'actions' }
]

const fetchKhatmas = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/khatmas', { params: { type: 'zikr' } })
        khatmas.value = response.data
    } catch (error) {
        console.error('Error fetching khatmas:', error)
        Swal.fire({ icon: 'error', title: 'فشل التحميل', text: 'فشل تحميل الختمات' })
    } finally {
        loading.value = false
    }
}

const fetchGroups = async () => {
    try {
        const response = await axios.get('/api/groups', { params: { type: 'zikr' } })
        groupOptions.value = response.data
    } catch (error) {
        console.error('Error fetching groups')
    }
}

const openCreateDialog = () => {
    isEditing.value = false
    form.value = { id: null, group_id: null, khatma_no: '', description: '', hijri_date: '' }
    parseHijriDate('')
    fetchGroups()
    dialogVisible.value = true
}
const editKhatma = (khatma) => {
    isEditing.value = true
    form.value = { ...khatma }
    parseHijriDate(khatma.hijri_date)
    fetchGroups()
    dialogVisible.value = true
}

const saveKhatma = async () => {
    try {
        if (isEditing.value) {
            await axios.put(`/api/khatmas/${form.value.id}`, form.value)
            Swal.fire({ icon: 'success', title: 'تم التعديل', text: 'تم التعديل بنجاح', timer: 1500, showConfirmButton: false })
        } else {
            await axios.post('/api/khatmas', form.value)
            Swal.fire({ icon: 'success', title: 'تم الإنشاء', text: 'تم الإنشاء بنجاح', timer: 1500, showConfirmButton: false })
        }
        dialogVisible.value = false
        fetchKhatmas()
    } catch (error) {
        console.error('Error saving khatma:', error)
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'حدث خطأ أثناء الحفظ' })
    }
}

const insertTable = () => {
    form.value.description += `
        <table border="1" style="width:100%; border-collapse: collapse; border-color: #ddd;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 8px;">العنوان 1</th>
                    <th style="padding: 8px;">العنوان 2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px;">بيانات 1</td>
                    <td style="padding: 8px;">بيانات 2</td>
                </tr>
            </tbody>
        </table>
        <p>&nbsp;</p>
    `
}

const emojiDialog = ref(false)
const emojis = ['🤲', '🕌', '🌙', '⭐', '✅', '✨', '📖', '📿', '🕋', '🕯️', '🌹', '💐', '🟢', '🔴', '🔹', '🔸', '👋', '❤️', '👏', '🎉']

const insertEmoji = () => {
    emojiDialog.value = true
}

const selectEmoji = (emoji) => {
    form.value.description += emoji
    emojiDialog.value = false
}

const islamicLocale = {
    days: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
    months: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة']
}

const hijriDay = ref('')
const hijriMonth = ref('')
const hijriYear = ref('')
const daysList = Array.from({ length: 30 }, (_, i) => i + 1)
const yearsList = Array.from({ length: 10 }, (_, i) => 1445 + i)

const updateHijriDate = () => {
    if (hijriDay.value && hijriMonth.value && hijriYear.value) {
        form.value.hijri_date = `${hijriDay.value} ${hijriMonth.value} ${hijriYear.value}`
    }
}

const parseHijriDate = (dateStr) => {
    if (!dateStr) {
        hijriDay.value = ''
        hijriMonth.value = ''
        hijriYear.value = ''
        return
    }
    const parts = dateStr.split(' ')
    if (parts.length === 3) {
        hijriDay.value = parseInt(parts[0]) || parts[0]
        hijriMonth.value = parts[1]
        hijriYear.value = parseInt(parts[2]) || parts[2]
    }
}

const confirmDelete = (khatma) => {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من الحذف؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/khatmas/${khatma.id}`)
                Swal.fire('تم الحذف!', '', 'success')
                fetchKhatmas()
            } catch (error) {
                Swal.fire('خطأ', 'فشل الحذف', 'error')
            }
        }
    })
}

// Helper to strip HTML and format for plain text sharing
const formatDescriptionForSharing = (html) => {
    if (!html) return ''
    const tempDiv = document.createElement('div')
    tempDiv.innerHTML = html
    tempDiv.querySelectorAll('br').forEach(br => br.replaceWith('\n'))
    tempDiv.querySelectorAll('p, div, h1, h2, h3, h4, h5, h6, tr').forEach(block => {
        block.append('\n')
    })
    tempDiv.querySelectorAll('li').forEach(li => {
        li.prepend('• ')
        li.append('\n')
    })
    tempDiv.querySelectorAll('td, th').forEach(cell => {
        cell.append(' | ')
    })
    tempDiv.querySelectorAll('th').forEach(th => {
        th.prepend('*')
        th.append('*')
    })
    tempDiv.querySelectorAll('b, strong, h1, h2, h3, h4, h5, h6').forEach(el => {
        el.prepend('*')
        el.append('*')
    })
    tempDiv.querySelectorAll('i, em').forEach(el => {
        el.prepend('_')
        el.append('_')
    })
    let text = tempDiv.textContent || tempDiv.innerText || ''
    return text.replace(/\n\s+\n/g, '\n\n').replace(/\n{3,}/g, '\n\n').replace(/\|\s*\|/g, '|').trim()
}

const copyDescription = async (khatma) => {
    const htmlContent = khatma.description
    if (!htmlContent) {
        Swal.fire({ icon: 'info', title: 'تنبيه', text: 'لا يوجد وصف للنسخ', timer: 1500, showConfirmButton: false })
        return
    }

    const plainText = formatDescriptionForSharing(htmlContent)

    // 1. Try modern Clipboard API
    try {
        if (typeof ClipboardItem !== 'undefined' && navigator.clipboard && navigator.clipboard.write) {
            const clipboardItem = new ClipboardItem({
                'text/html': new Blob([htmlContent], { type: 'text/html' }),
                'text/plain': new Blob([plainText], { type: 'text/plain' })
            })
            await navigator.clipboard.write([clipboardItem])
            Swal.fire({ icon: 'success', title: 'تم النسخ', text: 'تم نسخ الوصف بالتنسيق', timer: 1500, showConfirmButton: false })
            return
        }
    } catch (err) {
        console.warn('Modern Clipboard API failed', err)
    }

    // 2. Reliable Fallback (Selection-based)
    const hiddenDiv = document.createElement("div")
    hiddenDiv.innerHTML = htmlContent
    hiddenDiv.style.position = "absolute"
    hiddenDiv.style.left = "-9999px"
    hiddenDiv.style.top = "0"
    document.body.appendChild(hiddenDiv)

    try {
        const range = document.createRange()
        range.selectNodeContents(hiddenDiv)
        const selection = window.getSelection()
        selection.removeAllRanges()
        selection.addRange(range)

        const successful = document.execCommand('copy')
        selection.removeAllRanges()

        if (successful) {
            Swal.fire({ icon: 'success', title: 'تم النسخ', text: 'تم نسخ الوصف بالتنسيق', timer: 1500, showConfirmButton: false })
        } else {
            throw new Error('Selection copy failed')
        }
    } catch (err) {
        console.error('Copy failed', err)
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل النسخ' })
    } finally {
        document.body.removeChild(hiddenDiv)
    }
}

const shareWhatsapp = async (selectedKhatma) => {
    try {
        Loading.show({ message: 'جاري تحضير الصورة...' })

        // 1. Fetch Assignments and Participants
        const [assignmentsRes, codesRes] = await Promise.all([
            axios.get('/api/zikr-khatma-assignments', { params: { khatma_id: selectedKhatma.id } }),
            axios.get('/api/zikr-group-participants', { params: { group_id: selectedKhatma.group_id } })
        ])

        const assignments = assignmentsRes.data
        const participantsMap = {} // userId -> { code, name }
        codesRes.data.forEach(p => {
            participantsMap[p.user_id] = { code: p.participant_no }
        })

        // 2. Prepare Data List
        // We only want assigned users or all participants? Usually those with assignments + codes.
        // Let's list everyone who has a code and fill their assignment if exists.

        let reportData = []

        // Iterate over assignments to get counts
        assignments.forEach(assign => {
            if (participantsMap[assign.user_id]) {
                reportData.push({
                    code: participantsMap[assign.user_id].code,
                    count: assign.zikr_count,
                    userId: assign.user_id
                })
            }
        })

        // Sort by Code (numeric)
        reportData.sort((a, b) => {
            return String(a.code).localeCompare(String(b.code), undefined, { numeric: true, sensitivity: 'base' })
        })

        // 3. Build HTML for 2-column layout (Right-to-Left)
        // We need pairs: [Item1, Item2], [Item3, Item4]
        // But table structure visually:
        // Col4(Count2) | Col3(Code2) | Col2(Count1) | Col1(Code1)

        const rows = []
        for (let i = 0; i < reportData.length; i += 2) {
            rows.push({
                right: reportData[i],
                left: reportData[i + 1] || null // distinct from undefined
            })
        }

        const captureDiv = document.getElementById('capture-container')
        if (!captureDiv) return

        let tableHtml = `
            <div dir="rtl" style="padding: 20px; background: #e0f2f1; font-family: 'Segoe UI', 'Tahoma', 'Arial', sans-serif; width: 600px;">
                <h3 style="text-align: center; margin: 0 0 10px 0; color: #000;">${selectedKhatma.hijri_date || 'التاريخ غير محدد'}</h3>
                
                <table style="width: 100%; border-collapse: collapse; text-align: center; background: transparent;">
                    <thead>
                        <tr style="background-color: #b2dfdb; border-bottom: 2px solid #000;">
                            <th style="padding: 8px; font-size: 18px; width: 25%;">العدد</th>
                            <th style="padding: 8px; font-size: 18px; width: 25%; border-left: 1px solid #999;">الكود</th>
                            <th style="padding: 8px; font-size: 18px; width: 25%;">العدد</th>
                            <th style="padding: 8px; font-size: 18px; width: 25%;">الكود</th>
                        </tr>
                    </thead>
                    <tbody>
        `

        rows.forEach(row => {
            const rightCode = row.right ? row.right.code : ''
            const rightCount = row.right ? row.right.count : ''

            const leftCode = row.left ? row.left.code : ''
            const leftCount = row.left ? row.left.count : ''

            tableHtml += `
                <tr style="border-bottom: 1px solid #ccc; background-color: #fff;">
                     <td style="padding: 8px; font-weight: bold; font-size: 18px; color: #d32f2f;">${leftCount}</td>
                     <td style="padding: 8px; font-size: 18px; border-left: 1px solid #999; color: #000;">${leftCode}</td>
                     <td style="padding: 8px; font-weight: bold; font-size: 18px; color: #d32f2f;">${rightCount}</td>
                     <td style="padding: 8px; font-size: 18px; color: #000;">${rightCode}</td>
                </tr>
            `
        })

        tableHtml += `
                    </tbody>
                </table>
            </div>
        `

        captureDiv.innerHTML = tableHtml

        // Wait for render
        await new Promise(resolve => setTimeout(resolve, 500))

        const canvas = await html2canvas(captureDiv, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#e0f2f1'
        })

        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'))
        Loading.hide()

        if (!blob) throw new Error('فشل إنشاء الصورة')

        const file = new File([blob], `khatma-${selectedKhatma.khatma_no}.png`, { type: 'image/png' })

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: 'توزيع ورد الذكر',
                text: `${selectedKhatma.hijri_date}`
            })
        } else {
            downloadImage(canvas, `khatma-${selectedKhatma.khatma_no}.png`)
        }

    } catch (err) {
        Loading.hide()
        console.error('Share failed', err)
        Swal.fire({ icon: 'error', title: 'خطأ', text: err.message || 'فشل المشاركة' })
    }
}

const downloadImage = (canvas, filename = 'khatma-zikr-share.png') => {
    const link = document.createElement('a')
    link.download = filename
    link.href = canvas.toDataURL()
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    Swal.fire({ icon: 'success', title: 'تم التحميل', text: 'تم تحميل الصورة للمشاركة', timer: 2000, showConfirmButton: false })
}

onMounted(() => {
    fetchKhatmas()
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

.action-btn {
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: scale(1.1);
}
</style>
