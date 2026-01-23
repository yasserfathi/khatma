<template>
  <q-page class="q-pa-md">
    <!-- Hero Section -->
    <div class="row justify-center">
      <div class="col-12 col-md-8 text-center text-primary">
        <div class="text-h5 text-weight-bold q-mb-md islamic-font">السلام عليكم ورحمة الله وبركاته</div>

        <!-- Digital Clock -->
        <div class="clock-display q-mb-lg">
          <div class="text-h3 text-weight-bolder text-primary tabular-nums tracking-wide shadow-text">
            {{ currentTime }}
          </div>
          <div class="text-subtitle1 text-grey-7 q-mt-sm">{{ currentDate }}</div>
        </div>
      </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="row q-col-gutter-xl justify-center">

      <!-- Quran Progress -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card full-height column border-primary-2 shadow-10 hover-lift">
          <q-card-section class="q-pb-none">
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="text-subtitle1 text-primary text-weight-bold">تفاصيل ختمة التلاوة</div>
                <div class="text-caption text-grey-8" v-if="latestKhatma">
                  {{ latestKhatma.khatma_no }} | {{ latestKhatma.group?.name }}
                </div>
                <div class="text-caption text-grey-6" v-else>لا توجد ختمات نشطة حالياً</div>
              </div>
              <q-avatar color="primary-1" text-color="primary" icon="menu_book" size="48px" class="shadow-2" />
            </div>

            <div class="q-mt-xl">
              <q-linear-progress :value="latestKhatma?.progress ? latestKhatma.progress.percentage / 100 : 0"
                color="primary" track-color="primary-1" rounded size="12px" class="shadow-inner" />
              <div class="row justify-between text-subtitle2 text-weight-bold q-mt-md">
                <span class="text-primary">{{ latestKhatma?.progress ? latestKhatma.progress.percentage : 0 }}%
                  مكتمل</span>
                <span v-if="latestKhatma?.progress" class="text-grey-7">
                  ({{ latestKhatma.progress.finished }} من {{ latestKhatma.progress.total }} قراء استكملوا)
                </span>
              </div>
            </div>
          </q-card-section>
          <q-card-actions align="center" class="q-pa-md mt-auto">
            <q-btn unelevated rounded color="primary" label="عرض تفاصيل الختمة" icon-right="chevron_left"
              to="/khatma-tilawa" class="full-width" />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Prayer Times -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card full-height shadow-15 border-gold overflow-hidden hover-lift">
          <q-card-section>
            <div class="row items-center justify-between q-mb-lg">
              <div class="column">
                <div class="text-subtitle1 text-primary text-weight-bold">مواقيت صلاة مكة</div>
                <div class="text-caption text-grey-6">{{ currentDate }}</div>
              </div>
              <q-avatar color="amber-1" text-color="amber-9" icon="mosque" size="40px" />
            </div>

            <div v-if="loadingPrayers" class="row justify-center q-pa-xl">
              <q-spinner-cube color="primary" size="3em" />
            </div>
            <div v-else class="prayer-grid">
              <div v-for="(time, name) in prayerList" :key="name"
                class="column items-center justify-center q-pa-md rounded-borders prayer-card transition-all"
                :class="isNextPrayer(time.key) ? 'bg-gradient-gold text-white shadow-15 scale-105 active-prayer' : 'bg-white-50 text-grey-9'">
                <q-icon :name="time.icon" size="xs" :color="isNextPrayer(time.key) ? 'white' : 'primary'"
                  class="q-mb-xs" />
                <div class="text-caption text-weight-bold opacity-80" style="font-size: 0.75rem;">{{ name }}</div>
                <div class="text-h6 text-weight-bolder tabular-nums q-my-xs">
                  {{ prayerTimes?.[time.key] || '--:--' }}
                </div>
                <div v-if="isNextPrayer(time.key)" class="next-label text-weight-bold">القادم</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Daily Verse -->
      <div class="col-12 col-md-4">
        <q-card
          class="glass-card full-height bg-gradient-islamic text-white relative-position overflow-hidden shadow-20 border-primary-2 hover-lift">
          <div class="absolute-right opacity-05" style="font-size: 20rem; line-height: 0; top: -4rem; right: -4rem;">
            <q-icon name="menu_book" />
          </div>

          <q-card-section class="column full-height justify-between q-pa-lg">
            <div class="text-subtitle2 text-weight-thin q-mb-md opacity-70">آية من الذكر الحكيم</div>

            <div v-if="loadingVerse" class="row justify-center q-pa-md">
              <q-spinner-oval color="white" size="3em" />
            </div>
            <div v-else class="column items-center full-width">
              <div class="ayah-text text-h5 text-center q-mb-lg" dir="rtl">
                " {{ dailyVerse?.text }} "
              </div>

              <div class="row items-center q-gutter-x-md">
                <q-btn round flat color="white" :icon="isPlaying ? 'stop' : 'play_arrow'" @click="toggleAudio" size="lg"
                  class="bg-white-20 hover-scale shadow-5">
                  <q-tooltip>{{ isPlaying ? 'إيقاف' : 'استماع' }}</q-tooltip>
                </q-btn>

                <div class="surah-info bg-white-20 q-px-lg q-py-xs rounded-borders-20 text-subtitle2 text-weight-bold">
                  {{ dailyVerse?.surah }} | آية {{ dailyVerse?.numberInSurah }}
                </div>
              </div>
            </div>

            <div class="text-caption text-right opacity-50 q-mt-md">تم التحديث تلقائياً</div>
          </q-card-section>
        </q-card>
      </div>

    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

const currentTime = ref('')
const currentDate = ref('')
const prayerTimes = ref(null)
const loadingPrayers = ref(true)
const dailyVerse = ref(null)
const loadingVerse = ref(true)
const latestKhatma = ref(null)
const isPlaying = ref(false)
const audioPlayer = ref(null)

const prayerList = {
  'الفجر': { key: 'Fajr', icon: 'brightness_5' },
  'الشروق': { key: 'Sunrise', icon: 'wb_sunny' },
  'الظهر': { key: 'Dhuhr', icon: 'wb_sunny' },
  'العصر': { key: 'Asr', icon: 'wb_cloudy' },
  'المغرب': { key: 'Maghrib', icon: 'nights_stay' },
  'العشاء': { key: 'Isha', icon: 'bedtime' }
}

const isNextPrayer = (key) => {
  if (!prayerTimes.value) return false
  const now = new Date()
  const currentMinutes = now.getHours() * 60 + now.getMinutes()

  const times = Object.entries(prayerTimes.value)
    .filter(([k]) => ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'].includes(k))
    .map(([k, v]) => {
      const [h, m] = v.split(':').map(Number)
      return { key: k, minutes: h * 60 + m }
    })
    .sort((a, b) => a.minutes - b.minutes)

  let next = times.find(t => t.minutes > currentMinutes)
  if (!next) next = times[0] // If after Isha, next is Fajr tomorrow

  return next.key === key
}

let clockInterval = null

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('ar-EG', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  }).replace('ص', 'ص').replace('م', 'م')
}

const updateDate = () => {
  const today = new Date()
  const hijriFormatter = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
  const gregorianFormatter = new Intl.DateTimeFormat('ar-EG', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
  currentDate.value = `${hijriFormatter.format(today)} | ${gregorianFormatter.format(today)} م`
}

const fetchPrayerTimes = async () => {
  try {
    const res = await fetch('https://api.aladhan.com/v1/timingsByCity?city=Makkah&country=SA&method=4');
    const data = await res.json();
    prayerTimes.value = data.data.timings;
  } catch (err) {
    console.error('Prayers error', err)
  } finally {
    loadingPrayers.value = false
  }
}

const fetchDailyVerse = async () => {
  loadingVerse.value = true
  try {
    const res = await fetch('https://api.alquran.cloud/v1/ayah/random/ar.alafasy')
    const data = await res.json()
    dailyVerse.value = {
      text: data.data.text,
      surah: data.data.surah.name,
      numberInSurah: data.data.numberInSurah,
      audio: data.data.audio
    }
  } catch (err) {
    console.error('Verse error', err)
  } finally {
    loadingVerse.value = false
  }
}

const toggleAudio = () => {
  if (!dailyVerse.value?.audio) return

  if (!audioPlayer.value) {
    audioPlayer.value = new Audio(dailyVerse.value.audio)
    audioPlayer.value.onended = () => {
      isPlaying.value = false
    }
  }

  if (isPlaying.value) {
    audioPlayer.value.pause()
    audioPlayer.value.currentTime = 0
    isPlaying.value = false
  } else {
    audioPlayer.value.play()
    isPlaying.value = true
  }
}

const fetchLatestKhatma = async () => {
  try {
    const res = await axios.get('/api/khatmas', { params: { type: 'tilawa' } })
    if (res.data && res.data.length > 0) latestKhatma.value = res.data[0]
  } catch (err) {
    console.error('Khatma error', err)
  }
}

onMounted(() => {
  updateClock()
  updateDate()
  fetchPrayerTimes()
  fetchDailyVerse()
  fetchLatestKhatma()
  clockInterval = setInterval(updateClock, 1000)
})

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval)
  if (audioPlayer.value) {
    audioPlayer.value.pause()
    audioPlayer.value = null
  }
})
</script>

<style scoped lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap');

.islamic-font {
  font-family: 'Amiri', serif;
}

.shadow-text {
  text-shadow: 0 4px 10px rgba(0, 77, 64, 0.2);
}

.glass-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(15px);
  border-radius: 24px;
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.hover-lift:hover {
  transform: translateY(-12px);
  box-shadow: 0 20px 50px rgba(0, 77, 64, 0.15);
}

.border-primary-2 {
  border: 2px solid rgba(0, 77, 64, 0.3);
}

.border-gold {
  border: 2px solid rgba(255, 193, 7, 0.3);
}

.bg-gradient-islamic {
  background: linear-gradient(135deg, #004d40 0%, #00695c 100%);
}

.ayah-text {
  font-family: 'Amiri', serif;
  line-height: 1.8;
  letter-spacing: 0.5px;
}

.prayer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 12px;
}

.prayer-card {
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.3);
  overflow: hidden;
}

.active-prayer {
  z-index: 2;
}

.next-label {
  position: absolute;
  top: 4px;
  right: 4px;
  font-size: 8px;
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 6px;
  border-radius: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.bg-gradient-gold {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.transition-all {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.scale-105 {
  transform: scale(1.05);
}

.hover-scale:hover {
  transform: scale(1.1);
  background: rgba(255, 255, 255, 0.3) !important;
}

.rounded-borders-20 {
  border-radius: 20px;
}

.opacity-05 {
  opacity: 0.05;
}

.opacity-70 {
  opacity: 0.7;
}
</style>
