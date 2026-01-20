<template>
    <q-page class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="primary" icon="arrow_forward" to="/app" />
                <div>
                    <div class="text-h4 text-primary text-weight-bold text-islamic">ختمة</div>
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

                <template v-slot:body-cell-actions="props">
                    <q-td :props="props" auto-width>
                        <div class="row justify-center q-gutter-x-sm">
                            <q-btn flat round color="teal-7" icon="content_copy" size="sm" class="bg-teal-1 action-btn"
                                @click="copyDescription(props.row)">
                                <q-tooltip>نسخ الوصف</q-tooltip>
                            </q-btn>
                            <q-btn flat round color="green-7" icon="fa-brands fa-whatsapp" size="sm"
                                class="bg-green-1 action-btn" @click="shareWhatsapp(props.row)">
                                <q-tooltip>مشاركة عبر واتساب</q-tooltip>
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
                        لا توجد ختمات حالياً
                    </div>
                </template>
            </q-table>
        </q-card>

        <!-- Hidden Capture Container -->
        <div id="capture-container"
            style="position: fixed; left: -9999px; top: 0; width: 600px; background: white; z-index: -1;"></div>

        <!-- Create/Edit Dialog -->
        <q-dialog v-model="dialogVisible" transition-show="scale" transition-hide="scale" persistent maximized>
            <q-card class="bg-white shadow-24 overflow-hidden">
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
                                <q-input filled dense v-model="form.people_group_no" label="رقم الجماعة" color="primary"
                                    bg-color="grey-1" class="rounded-borders" />
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
                                ['bold', 'italic', 'strike', 'underline', 'subscript', 'superscript'],
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useQuasar, Loading } from 'quasar'
import html2canvas from 'html2canvas'

const $q = useQuasar()
const khatmas = ref([])
// ... existing refs ...

// ... (other functions) ...

const shareWhatsapp = async (khatma) => {
    // 1. Prepare Content
    const captureDiv = document.getElementById('capture-container')
    if (!captureDiv) return

    // Inject content with specific styling for image
    captureDiv.innerHTML = `
        <div style="padding: 30px; direction: rtl; font-family: 'Roboto', 'Arial', sans-serif; background: #fff; border: 1px solid #eee;">
             <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="color: #00897B; margin: 0 0 5px 0; font-size: 24px;">الختمات</h2>
                <div style="color: #666; font-size: 14px;">${khatma.khatma_no || 'تفاصيل الختمة'}</div>
            </div>
            <div class="description-content" style="font-size: 16px; line-height: 1.6; color: #333;">
                ${khatma.description}
            </div>
            <div style="margin-top: 30px; padding-top: 15px; border-top: 1px dashed #ccc; text-align: center; color: #888; font-size: 12px;">
                تم الإنشاء بواسطة تطبيق ختمة
            </div>
        </div>
    `

    // Wait a tick for DOM update
    await new Promise(resolve => setTimeout(resolve, 100))

    try {
        Loading.show({ message: 'جاري إنشاء الصورة...' })

        // 2. Capture
        const canvas = await html2canvas(captureDiv, {
            scale: 2, // High quality
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        })

        // 3. Convert to Blob
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'))

        Loading.hide()

        if (!blob) throw new Error('Blob creation failed')

        const file = new File([blob], 'khatma-share.png', { type: 'image/png' })

        // 4. Share or Download
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            try {
                await navigator.share({
                    files: [file],
                    title: 'تفاصيل الختمة',
                    text: 'شاهد تفاصيل الختمة في الصورة المرفقة'
                })
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Share failed', err)
                    // Fallback to download
                    downloadImage(canvas)
                }
            }
        } else {
            // Fallback for Desktop / unsupported browsers
            downloadImage(canvas)
        }

    } catch (err) {
        Loading.hide()
        console.error('Capture failed', err)
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل إنشاء الصورة' })
    }
}

const downloadImage = (canvas) => {
    const link = document.createElement('a')
    link.download = 'khatma-share.png'
    link.href = canvas.toDataURL()
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    Swal.fire({ icon: 'success', title: 'تم التحميل', text: 'تم تحميل الصورة للمشاركة', timer: 2000, showConfirmButton: false })
}
const groupOptions = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const isEditing = ref(false)
const form = ref({ id: null, group_id: null, khatma_no: '', people_group_no: '', description: '' })

const columns = [
    { name: 'id', label: '#', field: 'id', sortable: true, align: 'left', style: 'width: 50px' },
    { name: 'group_id', label: 'المجموعة', field: row => row.group?.name || '', sortable: true, align: 'center' },
    { name: 'khatma_no', label: 'رقم الختمة', field: 'khatma_no', sortable: true, align: 'left' },
    { name: 'people_group_no', label: 'رقم الجماعة', field: 'people_group_no', sortable: true, align: 'center' },
    { name: 'actions', label: 'الإجراءات', field: 'actions', align: 'center', style: 'width: 200px' }
]

const fetchKhatmas = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/khatmas')
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
        const response = await axios.get('/api/groups')
        groupOptions.value = response.data
    } catch (error) {
        console.error('Error fetching groups')
    }
}

const openCreateDialog = () => {
    isEditing.value = false
    form.value = { id: null, group_id: null, khatma_no: '', people_group_no: '', description: '' }
    fetchGroups()
    dialogVisible.value = true
}

const editKhatma = (khatma) => {
    isEditing.value = true
    form.value = { ...khatma }
    fetchGroups()
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

const copyDescription = (khatma) => {
    const text = formatDescriptionForSharing(khatma.description)
    if (!text) {
        Swal.fire({ icon: 'info', title: 'تنبيه', text: 'لا يوجد وصف للنسخ', timer: 1500, showConfirmButton: false })
        return
    }

    // Fallback for non-secure contexts (e.g., HTTP IP address)
    if (!navigator.clipboard) {
        const textArea = document.createElement("textarea")
        textArea.value = text

        // Ensure it's not visible but part of the DOM
        textArea.style.position = "fixed"
        textArea.style.left = "-9999px"
        textArea.style.top = "0"
        document.body.appendChild(textArea)

        textArea.focus()
        textArea.select()

        try {
            const successful = document.execCommand('copy')
            if (successful) {
                Swal.fire({ icon: 'success', title: 'تم النسخ', text: 'تم نسخ الوصف', timer: 1500, showConfirmButton: false })
            } else {
                throw new Error('Fallback copy failed')
            }
        } catch (err) {
            console.error('Fallback verify failed', err)
            Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل النسخ' })
        }

        document.body.removeChild(textArea)
        return
    }

    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({ icon: 'success', title: 'تم النسخ', text: 'تم نسخ الوصف', timer: 1500, showConfirmButton: false })
    }).catch(() => {
        Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل النسخ' })
    })
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
