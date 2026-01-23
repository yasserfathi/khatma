<template>
  <q-layout view="hHh lpR fFf" class="bg-grey-1">
    <q-header elevated class="text-grey-8 q-py-xs glass-header" height-hint="58">
      <q-toolbar>
        <q-btn flat dense round @click="toggleLeftDrawer" aria-label="Menu" icon="menu" />

        <q-btn flat no-caps no-wrap class="q-ml-xs" v-if="$q.screen.gt.xs">
          <img src="/logo.png" style="height: 28px; width: auto;" class="q-mr-sm">
          <q-toolbar-title shrink class="text-weight-bold text-primary">
            Khatma
          </q-toolbar-title>
        </q-btn>

        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">
          <q-btn flat round :icon="$q.dark.isActive ? 'dark_mode' : 'light_mode'" @click="toggleDarkMode">
            <q-tooltip>الوضع الليلي</q-tooltip>
          </q-btn>

          <div v-if="userName" class="text-subtitle1 text-grey-8 q-mx-sm">
            مرحباً {{ userName }}
          </div>

          <q-btn outline color="primary" label="تسجيل الخروج" icon="logout" @click="logout" class="q-ml-sm" />
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="glass-drawer" :width="240">
      <q-scroll-area class="fit">
        <q-list padding>
          <q-item v-for="link in links1" :key="link.text" clickable v-ripple :to="link.to">
            <q-item-section avatar>
              <q-icon color="grey-8" :name="link.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="sidebar-label">{{ link.text }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator class="q-my-md" />

          <q-item clickable v-ripple to="/change-password" active-class="text-primary bg-primary-1">
            <q-item-section avatar>
              <q-icon color="grey-8" name="lock" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="sidebar-label">تغيير كلمة المرور</q-item-label>
            </q-item-section>
          </q-item>

        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const router = useRouter()
const $q = useQuasar()
const leftDrawerOpen = ref(false)
const search = ref('')
const userName = ref('')

const toggleDarkMode = () => {
  $q.dark.toggle()
}

const fetchUser = async () => {
  try {
    const response = await axios.get('/api/user')
    userName.value = response.data.name
  } catch (error) {
    console.error('Error fetching user', error)
  }
}

const logout = async () => {
  try {
    await axios.post('/api/logout')
  } catch (error) {
    console.error('Logout error', error)
  } finally {
    // Always cleanup on logout
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    delete axios.defaults.headers.common['Authorization']
    $q.notify({ color: 'primary', icon: 'check_circle', message: 'تم تسجيل الخروج بنجاح' })
    router.push('/')
  }
}

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

onMounted(() => {
  fetchUser()
})

const links1 = [
  { icon: 'home', text: 'الرئيسية', to: '/app' },
  { icon: 'group', text: 'مجموعات الواتساب', to: '/group' },
  { icon: 'group', text: 'مجموعات القراءة', to: '/group-reading' },
  { icon: 'person', text: 'المشاركين', to: '/user' },
  { icon: 'person', text: 'ترقيم المشتركين', to: '/zikr-participants' },
  { icon: 'menu_book', text: 'ختمات التلاوة', to: '/khatma-tilawa' },
  { icon: 'menu_book', text: 'ختمات الذكر', to: '/khatma-zikr' },
]

</script>
