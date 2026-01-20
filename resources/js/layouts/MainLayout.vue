<template>
  <q-layout view="hHh lpR fFf" class="bg-grey-1">
    <q-header elevated class="text-grey-8 q-py-xs glass-header" height-hint="58">
      <q-toolbar>
        <q-btn flat dense round @click="toggleLeftDrawer" aria-label="Menu" icon="menu" />

        <q-btn flat no-caps no-wrap class="q-ml-xs" v-if="$q.screen.gt.xs">
          <q-icon name="menu_book" color="primary" size="28px" />
          <q-toolbar-title shrink class="text-weight-bold text-primary">
            Khatma
          </q-toolbar-title>
        </q-btn>

        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">
          <q-btn round flat>
            <q-avatar size="26px">
              <img src="https://cdn.quasar.dev/img/boy-avatar.png">
            </q-avatar>
            <q-tooltip>حسابي</q-tooltip>
          </q-btn>
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
              <q-item-label>{{ link.text }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator class="q-my-md" />

          <q-item v-for="link in links2" :key="link.text" clickable v-ripple>
            <q-item-section avatar>
              <q-icon color="grey-8" :name="link.icon" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ link.text }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator class="q-my-md" />

        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const router = useRouter()
const $q = useQuasar()
const leftDrawerOpen = ref(false)
const search = ref('')

const logout = async () => {
  try {
    await axios.post('/api/logout')
  } catch (error) {
    console.error('Logout error', error)
  } finally {
    // Always cleanup on logout
    localStorage.removeItem('auth_token')
    delete axios.defaults.headers.common['Authorization']
    $q.notify({ type: 'positive', message: 'تم تسجيل الخروج بنجاح' })
    router.push('/')
  }
}

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const links1 = [
  { icon: 'home', text: 'الرئيسية', to: '/app' },
  { icon: 'group', text: 'المجموعات', to: '/group' },
  { icon: 'person', text: 'المستخدمين', to: '/user' },
  { icon: 'menu_book', text: 'الختمات', to: '/khatma' },
]

const links2 = [
  { icon: 'folder', text: 'المكتبة' },
  { icon: 'restore', text: 'السجل' },
  { icon: 'watch_later', text: 'مشاهدة لاحقاً' },
  { icon: 'thumb_up_alt', text: 'أعجبتني' }
]

</script>
