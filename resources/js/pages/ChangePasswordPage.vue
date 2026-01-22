<template>
    <q-page class="flex flex-center bg-grey-1">
        <q-card class="q-pa-md shadow-2 my_card" style="width: 100%; max-width: 400px;">
            <q-card-section class="text-center">
                <div class="text-h6 text-primary">تغيير كلمة المرور</div>
                <div class="text-subtitle2 text-grey">أدخل كلمة المرور الحالية والجديدة</div>
            </q-card-section>

            <q-card-section>
                <q-form @submit="onSubmit" class="q-gutter-md">
                    <q-input filled v-model="form.current_password" label="كلمة المرور الحالية" type="password"
                        :rules="[val => !!val || 'مطلوب']" />

                    <q-input filled v-model="form.new_password" label="كلمة المرور الجديدة" type="password" :rules="[
                        val => !!val || 'مطلوب',
                        val => val.length >= 8 || 'يجب أن لا تقل عن 8 خانات'
                    ]" />

                    <q-input filled v-model="form.new_password_confirmation" label="تأكيد كلمة المرور الجديدة"
                        type="password" :rules="[
                            val => !!val || 'مطلوب',
                            val => val === form.new_password || 'كلمة المرور غير متطابقة'
                        ]" />

                    <div>
                        <q-btn label="تغيير كلمة المرور" type="submit" color="primary" class="full-width"
                            :loading="loading" />
                    </div>
                </q-form>
            </q-card-section>
        </q-card>
    </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()
const router = useRouter()
const loading = ref(false)

const form = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
})

const onSubmit = async () => {
    loading.value = true
    try {
        await axios.post('/api/change-password', form.value)

        $q.notify({
            color: 'primary',
            textColor: 'white',
            icon: 'check_circle',
            message: 'تم تغيير كلمة المرور بنجاح'
        })

        form.value = { current_password: '', new_password: '', new_password_confirmation: '' }
        router.push('/') // Or wherever

    } catch (error) {
        const msg = error.response?.data?.message || 'فشل تغيير كلمة المرور'
        $q.notify({
            color: 'red-5',
            textColor: 'white',
            icon: 'warning',
            message: msg
        })
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.my_card {
    border-radius: 12px;
}
</style>
