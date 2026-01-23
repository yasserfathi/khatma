<template>
  <q-page class="window-height window-width row justify-center items-center"
    :style="`background: linear-gradient(rgba(255,255,255,0.5), rgba(255,255,255,0.5)), url(${bgImage}); background-size: 150px auto; background-repeat: repeat; background-position: center;`">


    <transition appear enter-active-class="animated fadeInUp" leave-active-class="animated fadeOutDown">
      <q-card square class="q-pa-lg shadow-24 relative-position glass-card" style="width: 380px; border-radius: 16px;">
        <q-card-section class="column items-center q-pb-none">
          <q-avatar size="100px" class="shadow-10 q-mb-md bg-white">
            <img src="/logo.png" style="object-fit: contain; padding: 10px;">
          </q-avatar>
          <div class="text-h5 text-center text-teal-9 text-weight-bold q-mb-xs">تسجيل الدخول</div>
          <div class="text-subtitle1 text-center text-grey-8">تطبيق ختمة</div>
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md">
            <q-input dense filled v-model="identity" label="اسم المستخدم أو البريد الإلكتروني" color="teal"
              label-color="grey-7" class="bg-white">
              <template v-slot:prepend>
                <q-icon name="account_circle" color="teal" />
              </template>
            </q-input>

            <q-input dense filled v-model="password" type="password" label="كلمة المرور" color="teal"
              label-color="grey-7" class="bg-white">
              <template v-slot:prepend>
                <q-icon name="lock" color="teal" />
              </template>
            </q-input>
          </q-form>
        </q-card-section>

        <q-card-actions class="q-px-md q-pt-none">
          <q-btn unelevated color="teal-9" text-color="white" size="lg" class="full-width text-weight-bold shadow-3"
            label="دخول" @click="handleLogin" style="border-radius: 8px;" />
        </q-card-actions>
      </q-card>
    </transition>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import bgImage from 'assets/islamic_bg_v4.png'

const identity = ref('')
const password = ref('')

import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const router = useRouter()
const $q = useQuasar()

const handleLogin = async () => {
  try {
    const response = await axios.post('/api/login', {
      email: identity.value,
      password: password.value
    })

    // Store token
    const token = response.data.access_token
    localStorage.setItem('auth_token', token)

    // Configure axios defaults for subsequent requests
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    $q.notify({ color: 'primary', icon: 'check_circle', message: 'تم تسجيل الدخول بنجاح' })
    router.push('/app')

  } catch (error) {
    console.error('Login error:', error)
    $q.notify({ type: 'negative', message: 'بيانات الدخول غير صحيحة' })
  }
}
</script>

<style scoped>
.opacity-fade {
  background: rgba(0, 77, 64, 0.4);
  /* Teal overlay */
  backdrop-filter: blur(2px);
}

.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.5);
}
</style>
