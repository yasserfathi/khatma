<template>
    <q-page class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
                <div>
                    <div class="text-h4 text-primary text-weight-bold text-islamic">ختمة تلاوة</div>
                    <div class="text-subtitle2 text-grey-7">جداول الختمات والمجموعات</div>
                </div>
            </div>
            <q-btn unelevated rounded color="primary" icon="add" label="ختمة جديدة" @click="openCreateDialog"
                class="shadow-3 q-px-md" />
        </div>

        <!-- Khatmas Table -->
        <q-card class="glass-card q-pa-sm">
            <q-table :rows="khatmas" :columns="columns" row-key="id" :loading="loading" flat :separator="'none'"
                class="bg-transparent text-grey-9 custom-table"
                table-header-class="text-primary text-weight-bold bg-primary-1 rounded-borders">
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
                                class="bg-deep-purple-1 action-btn" :to="`/khatma-tilawa/${props.row.id}/assign`">
                                <q-tooltip>توزيع الأجزاء</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="primary-7" icon="edit" size="sm" class="bg-primary-1 action-btn"
                                @click="editKhatma(props.row)">
                                <q-tooltip>تعديل</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="red-7" icon="delete" size="sm" class="bg-red-1 action-btn"
                                @click="confirmDelete(props.row)">
                                <q-tooltip>حذف</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="pink-7" icon="local_florist" size="sm" class="bg-pink-1 action-btn"
                                @click="generateRoseImage(props.row)">
                                <q-tooltip>توليد صورة الوردة</q-tooltip>
                            </q-btn>
                        </div>
                    </q-td>
                </template>

                <template v-slot:no-data>
                    <div class="full-width row flex-center text-grey-6 q-pa-lg">
                        <q-icon name="warning" size="sm" class="q-mr-sm" />
                        لا توجد ختمات حالياً
                    </div>
                </template>
            </q-table>
        </q-card>

        <!-- Hidden Capture Container -->
        <div id="capture-container"
            style="position: fixed; left: -9999px; top: 0; width: 600px; background: white; z-index: -1;"></div>

        <!-- Hidden Rose Capture Container -->
        <div id="rose-capture-container" dir="rtl"
            style="position: fixed; left: 0; top: 0; width: 600px; background: #FFCCBC; z-index: -100; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; direction: rtl; padding: 20px; border: 1px solid #E64A19;">

            <!-- Header -->
            <div
                style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #E64A19; padding-bottom: 10px;">
                <table style="width: 100%; margin: 5px 0 10px 0;">
                    <tr>
                        <td style="text-align: left; width: 50%; padding-right: 10px;">
                            <span style="font-size: 22px; font-weight: bold; color: #3E2723;">الختمة رقم</span>
                        </td>
                        <td style="text-align: right; width: 50%; padding-left: 10px;">
                            <span dir="ltr"
                                style="font-size: 22px; font-weight: bold; color: #3E2723; font-family: sans-serif;">({{
                                    roseData.khatma_no }})</span>
                        </td>
                    </tr>
                </table>
                <h3 v-if="roseData.group_name"
                    style="margin: 5px 0; color: #5D4037; font-size: 18px; font-weight: bold; text-decoration: underline;">
                    المجموعة رقم ({{ roseData.people_group_no || '---' }})
                </h3>
                <div v-if="roseData.description_text"
                    style="font-size: 16px; color: #3E2723; font-weight: bold; margin-top: 5px;">
                    {{ roseData.description_text }}
                </div>
                <div style="text-align: right; margin-top: 10px; color: #BF360C; font-weight: bold; font-size: 16px;">
                    توزيع الأجزاء للتلاوة
                </div>
            </div>

            <!-- Grid -->
            <div style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                <!-- Column 1 (Right) -->
                <div style="width: 50%; padding: 0 10px; box-sizing: border-box;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr v-for="(group, idx) in rightColumnParts" :key="idx">
                            <td
                                style="text-align: right; padding: 5px; font-size: 16px; font-weight: bold; color: #3E2723;">
                                {{ group.userName }}
                            </td>
                            <td
                                style="text-align: left; padding: 5px; font-size: 16px; font-weight: bold; color: #3E2723;">
                                <div
                                    style="display: flex; flex-wrap: wrap; gap: 4px; justify-content: flex-start; direction: ltr;">
                                    <span v-for="(partNo, pIdx) in group.parts" :key="pIdx">
                                        {{ partNo }}<span v-if="pIdx < group.parts.length - 1">، </span>
                                    </span>
                                    <span v-if="group.reads.every(r => r)" style="margin-left: 2px;">🌹</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Column 2 (Left) -->
                <div style="width: 50%; padding: 0 10px; box-sizing: border-box; border-right: 1px dashed #E64A19;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr v-for="(group, idx) in leftColumnParts" :key="idx">
                            <td
                                style="text-align: right; padding: 5px; font-size: 16px; font-weight: bold; color: #3E2723;">
                                {{ group.userName }}
                            </td>
                            <td
                                style="text-align: left; padding: 5px; font-size: 16px; font-weight: bold; color: #3E2723;">
                                <div
                                    style="display: flex; flex-wrap: wrap; gap: 4px; justify-content: flex-start; direction: ltr;">
                                    <span v-for="(partNo, pIdx) in group.parts" :key="pIdx">
                                        {{ partNo }}<span v-if="pIdx < group.parts.length - 1">، </span>
                                    </span>
                                    <span v-if="group.reads.every(r => r)" style="margin-left: 2px;">🌹</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale" persistent maximized>
            <q-card class="shadow-24 overflow-hidden">
                <!-- Dialog Header -->
                <q-card-section class="bg-primary text-white row items-center q-py-md q-px-lg">
                    <q-avatar icon="menu_book" color="primary-8" text-color="white" size="md" class="q-mr-md" />
                    <div class="text-h6 text-weight-bold">{{ isEditing ? 'تعديل الختمة' : 'ختمة جديدة' }}</div>
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
                                <q-select filled dense v-model="form.group_reading_id" :options="readingGroupOptions"
                                    option-value="id" :option-label="opt => opt ? `${opt.group_no} - ${opt.names}` : ''"
                                    emit-value map-options label="مجموعة القراءة" color="primary" bg-color="grey-1"
                                    class="rounded-borders">
                                    <template v-slot:prepend>
                                        <q-icon name="format_list_numbered" color="primary" />
                                    </template>
                                </q-select>
                            </div>

                            <div class="col-12 col-md-6">
                                <q-input filled dense v-model.number="form.juz_count" label="عدد الأجزاء" type="number"
                                    color="primary" bg-color="grey-1" class="rounded-borders"
                                    :rules="[val => val > 0 || 'يجب أن يكون أكبر من 0']" />
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
                            <label class="text-weight-bold text-grey-8 q-mb-sm block">وصف الختمة وتفاصيلها</label>
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

                        <div class="row justify-end q-mt-xl">
                            <q-btn label="إلغاء" color="grey-7" flat v-close-popup class="q-mr-sm" />
                            <q-btn :label="isEditing ? 'حفظ التغييرات' : 'إنشاء الختمة'" type="submit" color="primary"
                                icon="save" unelevated class="q-px-lg shadow-2" style="border-radius: 10px" />
                        </div>
                    </q-form>
                </q-card-section>
            </q-card>
        </q-dialog>

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

    </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useQuasar, Loading } from 'quasar'
import html2canvas from 'html2canvas'

const $q = useQuasar()
const khatmas = ref([])
// ... existing refs ...

// ... (other functions) ...

const shareWhatsapp = async (khatma) => {
    try {
        Loading.show({ message: 'جاري تحضير الرسالة...' })

        // 1. Fetch Assignments
        const res = await axios.get('/api/tilawa-khatma-assignments', { params: { khatma_id: khatma.id } })
        const assignments = res.data

        // 2. Group by User and check if finished
        const userMap = new Map()

        // Use assignments directly instead of looping through part counts to be more accurate to fetched data
        assignments.forEach(assign => {
            const userName = assign.user?.name || 'غير محدد'
            const isRead = !!assign.read

            if (!userMap.has(userName)) {
                userMap.set(userName, { userName, finished: true })
            }
            const g = userMap.get(userName)
            if (!isRead) g.finished = false
        });

        const finishers = Array.from(userMap.values())
            .filter(u => u.finished && u.userName !== 'غير محدد')
            .map(u => u.userName)

        // 3. Construct Message
        const khatmaNo = khatma.khatma_no || '---'
        const groupNo = khatma.group_reading?.group_no || '---'
        const groupNames = khatma.group_reading?.names || ''

        let message = `الختمة رقم (${khatmaNo})\n`
        message += `مجموعة رقم (${groupNo})\n`
        if (groupNames) message += `${groupNames}\n`
        message += `السبّاقون جداً جداً و المسارعون للخيرات\n`
        message += `${finishers.join('؛ ')}\n\n`
        message += `نسأل الله تعالي أن يجزيهم خير الجزاء وأن يجعل ثواب تلاوتهم رحمةً ونوراً`

        // 4. Action: Try sharing, fallback to Copy
        try {
            if (navigator.share) {
                await navigator.share({
                    text: message,
                    title: 'مشاركة التلاوة'
                })
            } else {
                throw new Error('Web Share API not supported')
            }
        } catch (shareErr) {
            // Fallback to Clipboard - Handle non-secure context (HTTP)
            let copySuccess = false
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(message)
                    copySuccess = true
                } catch (e) {
                    console.error('Clipboard API failed', e)
                }
            }

            if (!copySuccess) {
                // Secondary fallback: Temporary textarea
                const textArea = document.createElement("textarea")
                textArea.value = message
                textArea.style.position = "fixed"
                textArea.style.left = "-9999px"
                textArea.style.top = "0"
                document.body.appendChild(textArea)
                textArea.focus()
                textArea.select()
                try {
                    copySuccess = document.execCommand('copy')
                } catch (err) {
                    console.error('execCommand copy failed', err)
                }
                document.body.removeChild(textArea)
            }

            if (copySuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'تم النسخ',
                    text: 'تم نسخ نص المشاركة للحافظة (للمشاركة في واتساب)',
                    timer: 3000,
                    showConfirmButton: false
                })
            } else {
                throw new Error('فشل النسخ إلى الحافظة')
            }
        }

        Loading.hide()
    } catch (error) {
        Loading.hide()
        console.error('Share failed', error)
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: error.message || 'فشل مشاركة الرسالة. يرجى التأكد من اختيار المجموعة والبيانات.'
        })
    }
}

const downloadImage = (canvas, filename = 'khatma-share.png') => {
    const link = document.createElement('a')
    link.download = filename
    link.href = canvas.toDataURL()
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    Swal.fire({ icon: 'success', title: 'تم النسخ', text: 'تم تحميل الصورة للمشاركة', timer: 2000, showConfirmButton: false })
}

const roseData = ref({ khatma_no: '', group_name: '', people_group_no: '', description_text: '', parts: [] })

const rightColumnParts = computed(() => {
    // Return first half (e.g., 1-15)
    return roseData.value.parts.slice(0, Math.ceil(roseData.value.parts.length / 2))
})

const leftColumnParts = computed(() => {
    // Return second half (e.g., 16-30)
    return roseData.value.parts.slice(Math.ceil(roseData.value.parts.length / 2))
})


const generateRoseImage = async (khatma) => {
    try {
        Loading.show({ message: 'جاري تحضير التقرير...' })

        // 1. Fetch Assignments
        const res = await axios.get('/api/tilawa-khatma-assignments', { params: { khatma_id: khatma.id } })
        const assignments = res.data

        // 2. Group by User
        const userMap = new Map()
        const totalParts = khatma.juz_count || 30

        for (let i = 1; i <= totalParts; i++) {
            const assignment = assignments.find(a => a.parts && a.parts.includes(i))
            const userName = assignment ? assignment.user.name : 'غير محدد'
            const isRead = assignment ? !!assignment.read : false

            if (!userMap.has(userName)) {
                userMap.set(userName, { userName, parts: [], reads: [] })
            }
            const g = userMap.get(userName)
            g.parts.push(i)
            g.reads.push(isRead)
        }

        const groupedUsers = Array.from(userMap.values())

        // 3. Prepare for 2-column layout (each row has 2 pairs of Name/Parts)
        const rows = []
        for (let i = 0; i < groupedUsers.length; i += 2) {
            rows.push({
                right: groupedUsers[i],
                left: groupedUsers[i + 1] || null
            })
        }

        // 4. Build Table UI
        const captureDiv = document.getElementById('capture-container')
        if (!captureDiv) return

        captureDiv.innerHTML = `
            <div dir="rtl" style="padding: 20px; background: white; font-family: 'Segoe UI', 'Tahoma', sans-serif;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; border: 0;">
                    <thead>
                        <tr>
                            <th colspan="4" style="padding: 10px; font-size: 18px; border: 0; background: #fff;">بسم الله الرحمن الرحيم</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="padding: 8px; font-size: 16px; border: 0; background: #fff;">ختمة رقم: ${khatma.khatma_no || '---'}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="padding: 8px; font-size: 16px; border: 0; background: #fff;">المجموعة رقم (${khatma.group_reading?.group_no || '---'})</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="padding: 8px; font-size: 14px; border: 0; color: #555; background: #fff;">${khatma.group_reading?.names || 'أسم مجموعة القراءة'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map(row => `
                            <tr>
                                <!-- Right Pair -->
                                <td style="padding: 8px; text-align: right; font-size: 15px; font-weight: bold; width: 30%; border: 0;">${row.right.userName}</td>
                                <td style="padding: 8px; text-align: right; font-size: 14px; width: 20%; border: 0;">
                                    <div style="direction: rtl;">
                                        ${row.right.parts.join('، ')}${row.right.reads.every(r => r) ? '🌹' : ''}
                                    </div>
                                </td>

                                <!-- Left Pair -->
                                ${row.left ? `
                                    <td style="padding: 8px; text-align: right; font-size: 15px; font-weight: bold; width: 30%; border: 0;">${row.left.userName}</td>
                                    <td style="padding: 8px; text-align: right; font-size: 14px; width: 20%; border: 0;">
                                        <div style="direction: rtl;">
                                            ${row.left.parts.join('، ')}${row.left.reads.every(r => r) ? '🌹' : ''}
                                        </div>
                                    </td>
                                ` : '<td style="border: 0;"></td><td style="border: 0;"></td>'}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <div style="margin-top: 20px; text-align: center; color: #888; font-size: 12px;">
                    تم الإنشاء بواسطة تطبيق ختمة
                </div>
            </div>
        `

        // Wait for rendering
        await new Promise(resolve => setTimeout(resolve, 300))

        const canvas = await html2canvas(captureDiv, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        })

        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'))
        Loading.hide()

        if (!blob) throw new Error('فشل إنشاء الصورة')

        const file = new File([blob], 'khatma-tilawa-share.png', { type: 'image/png' })

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: 'توزيع أجزاء الختمة',
                text: 'شاهد توزيع أجزاء الختمة في الصورة المرفقة'
            })
        } else {
            downloadImage(canvas, 'khatma-tilawa-share.png')
        }

    } catch (err) {
        Loading.hide()
        console.error('Share failed', err)
        Swal.fire({ icon: 'error', title: 'خطأ', text: err.message || 'فشل مشاركة التقرير' })
    }
}

const groupOptions = ref([])
const readingGroupOptions = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, group_id: null, group_reading_id: null, khatma_no: '', description: '', hijri_date: '', juz_count: 30 })

const columns = [
    { name: 'id', label: '#', field: 'id', sortable: true, align: 'left', style: 'width: 50px' },
    { name: 'group_id', label: 'المجموعة', field: row => row.group?.name || '', sortable: true, align: 'center' },
    { name: 'hijri_date', label: 'التاريخ الهجري', field: 'hijri_date', sortable: true, align: 'center' },
    { name: 'khatma_no', label: 'رقم الختمة', field: 'khatma_no', sortable: true, align: 'left' },
    { name: 'group_reading_id', label: 'مجموعة القراءة', field: row => row.group_reading ? `${row.group_reading.group_no} - ${row.group_reading.names}` : '', sortable: true, align: 'center' },
    { name: 'progress', label: 'الإنجاز', field: 'progress', align: 'center' },
    { name: 'actions', label: 'الإجراءات', field: 'actions' }
]

const fetchKhatmas = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/khatmas', { params: { type: 'tilawa' } })
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
        const response = await axios.get('/api/groups', { params: { type: 'tilawa' } })
        groupOptions.value = response.data
    } catch (error) {
        console.error('Error fetching groups')
    }
}

const fetchReadingGroups = async () => {
    try {
        const response = await axios.get('/api/group-readings')
        readingGroupOptions.value = response.data
    } catch (error) {
        console.error('Error fetching reading groups')
    }
}

const openCreateDialog = () => {
    isEditing.value = false
    form.value = { id: null, group_id: null, group_reading_id: null, khatma_no: '', description: '', hijri_date: '', juz_count: 30 }
    parseHijriDate('')
    fetchGroups()
    fetchReadingGroups()
    dialogVisible.value = true
}

const editKhatma = (khatma) => {
    isEditing.value = true
    form.value = { ...khatma }
    parseHijriDate(khatma.hijri_date)
    fetchGroups()
    fetchReadingGroups()
    dialogVisible.value = true
}

const saveKhatma = async () => {
    try {
        if (isEditing.value) {
            await axios.put(`/api/khatmas/${form.value.id}`, form.value)
            Swal.fire({ icon: 'success', title: 'تم التعديل', text: 'تم تعديل الختمة بنجاح', timer: 1500, showConfirmButton: false })
        } else {
            await axios.post('/api/khatmas', form.value)
            Swal.fire({ icon: 'success', title: 'تم الإنشاء', text: 'تم إنشاء الختمة بنجاح', timer: 1500, showConfirmButton: false })
        }
        dialogVisible.value = false
        fetchKhatmas()
    } catch (error) {
        console.error('Error saving khatma:', error)
        if (error.response && error.response.status === 422) {
            const message = error.response.data.message || 'بيانات غير صالحة'
            Swal.fire({ icon: 'error', title: 'خطأ', text: message })
        } else {
            Swal.fire({ icon: 'error', title: 'خطأ', text: 'حدث خطأ أثناء الحفظ' })
        }
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
    daysShort: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
    months: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'],
    monthsShort: ['محرم', 'صفر', 'ربيع 1', 'ربيع 2', 'جمادى 1', 'جمادى 2', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة']
}

const hijriDay = ref('')
const hijriMonth = ref('')
const hijriYear = ref('')
const daysList = Array.from({ length: 30 }, (_, i) => i + 1)
const yearsList = Array.from({ length: 10 }, (_, i) => 1445 + i) // 1445 to 1454

const updateHijriDate = () => {
    if (hijriDay.value && hijriMonth.value && hijriYear.value) {
        form.value.hijri_date = `${hijriDay.value} ${hijriMonth.value} ${hijriYear.value}`
    }
}

// Helper to parse string back to dropdowns
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
        text: `هل أنت متأكد من حذف الختمة والبيانات المرتبطة بها؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذفها',
        cancelButtonText: 'إلغاء'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/khatmas/${khatma.id}`)
                Swal.fire('تم الحذف!', 'تم حذف الختمة بنجاح.', 'success')
                fetchKhatmas()
            } catch (error) {
                console.error('Error deleting khatma:', error)
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الحذف' })
            }
        }
    })
}

// Helper to strip HTML and format for plain text sharing
const formatDescriptionForSharing = (html) => {
    if (!html) return ''

    // Create a temporary element to manipulate the DOM
    const tempDiv = document.createElement('div')
    tempDiv.innerHTML = html

    // Replace formatting with text markers
    // 1. Line breaks
    tempDiv.querySelectorAll('br').forEach(br => br.replaceWith('\n'))
    tempDiv.querySelectorAll('p, div, h1, h2, h3, h4, h5, h6, tr').forEach(block => {
        block.append('\n')
    })

    // 2. Lists
    tempDiv.querySelectorAll('li').forEach(li => {
        li.prepend('• ')
        li.append('\n')
    })

    // 3. Tables
    tempDiv.querySelectorAll('td, th').forEach(cell => {
        cell.append(' | ')
    })
    tempDiv.querySelectorAll('th').forEach(th => {
        th.prepend('*')
        th.append('*')
    })

    // 4. Bold / Headers
    tempDiv.querySelectorAll('b, strong, h1, h2, h3, h4, h5, h6').forEach(el => {
        el.prepend('*')
        el.append('*')
    })

    // 5. Italic
    tempDiv.querySelectorAll('i, em').forEach(el => {
        el.prepend('_')
        el.append('_')
    })

    // Get text content (removes all tags)
    let text = tempDiv.textContent || tempDiv.innerText || ''

    // Clean up excessive whitespace
    return text
        .replace(/\n\s+\n/g, '\n\n')
        .replace(/\n{3,}/g, '\n\n')
        .replace(/\|\s*\|/g, '|') // clean up double pipes if any
        .trim()
}

const copyDescription = async (khatma) => {
    const htmlContent = khatma.description
    if (!htmlContent) {
        Swal.fire({ icon: 'info', title: 'تنبيه', text: 'لا يوجد وصف للنسخ', timer: 1500, showConfirmButton: false })
        return
    }

    const plainText = formatDescriptionForSharing(htmlContent)

    // 1. Try modern Clipboard API (Best results)
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
        console.warn('Modern Clipboard API failed, trying fallback...', err)
    }

    // 2. Reliable Fallback: Selection-based Rich Text Copy
    // This copies the actual "rendered content" to the clipboard (with formatting)
    const hiddenDiv = document.createElement("div")
    hiddenDiv.innerHTML = htmlContent
    hiddenDiv.style.position = "fixed"
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
        console.error('Final copy fallback failed', err)
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل النسخ' })
    } finally {
        document.body.removeChild(hiddenDiv)
    }
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
