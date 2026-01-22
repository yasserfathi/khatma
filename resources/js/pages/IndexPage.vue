<template>
  <q-page class="q-pa-md">
    <!-- Hero Section -->
    <div class="row justify-center q-mb-lg">
      <div class="col-12 col-md-8 text-center text-primary">
        <div class="text-h4 text-weight-bold q-mb-sm">السلام عليكم ورحمة الله</div>
        <div class="text-subtitle1 opacity-80">{{ currentDate }}</div>
      </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="row q-col-gutter-md justify-center">

      <!-- Quran Progress -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card full-height column">
          <q-card-section>
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="text-h6 text-primary">ورد اليوم</div>
                <div class="text-caption text-grey-8">سورة البقرة - صفحة 42</div>
              </div>
              <q-avatar color="teal-1" text-color="teal" icon="menu_book" font-size="24px" />
            </div>
            <q-linear-progress value="0.75" color="primary" class="q-mt-md rounded-borders" size="8px" />
            <div class="text-right text-caption text-grey-8 q-mt-xs">75% مكتمل</div>
          </q-card-section>
          <q-card-actions align="right" class="q-pt-none mt-auto">
            <q-btn flat color="primary" label="متابعة القراءة" icon-right="arrow_forward" />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Prayer Times -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card full-height">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="text-h6 text-primary">مواقيت الصلاة</div>
              <div class="text-caption text-grey-8"><q-icon name="place" /> الشارقة - القصباء</div>
            </div>

            <div v-if="loadingPrayers" class="row justify-center q-pa-md">
              <q-spinner color="primary" size="2em" />
            </div>
            <div v-else class="row text-center justify-between no-wrap q-gutter-x-xs">
              <div class="column items-center">
                <div class="text-weight-bold text-primary text-caption">الفجر</div>
                <div class="text-caption">{{ prayerTimes?.Fajr || '-' }}</div>
                <q-icon name="wb_twilight" color="blue-grey" size="xs" class="q-mt-xs" />
              </div>
              <div class="column items-center">
                <div class="text-weight-bold text-primary text-caption">الظهر</div>
                <div class="text-caption">{{ prayerTimes?.Dhuhr || '-' }}</div>
                <q-icon name="wb_sunny" color="yellow-9" size="xs" class="q-mt-xs" />
              </div>
              <div class="column items-center">
                <div class="text-weight-bold text-primary text-caption">العصر</div>
                <div class="text-caption">{{ prayerTimes?.Asr || '-' }}</div>
                <q-icon name="wb_sunny" color="orange" size="xs" class="q-mt-xs" />
              </div>
              <div class="column items-center bg-teal-1 q-pa-xs rounded-borders">
                <div class="text-weight-bold text-teal text-caption">المغرب</div>
                <div class="text-caption text-teal-9 text-weight-bold">{{ prayerTimes?.Maghrib || '-' }}</div>
                <q-badge floating color="red" rounded />
              </div>
              <div class="column items-center">
                <div class="text-weight-bold text-primary text-caption">العشاء</div>
                <div class="text-caption">{{ prayerTimes?.Isha || '-' }}</div>
                <q-icon name="nights_stay" color="indigo-3" size="xs" class="q-mt-xs" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Daily Verse -->
      <div class="col-12 col-md-4">
        <q-card class="glass-card full-height bg-primary text-white relative-position overflow-hidden">
          <div class="absolute-right opacity-10" style="font-size: 15rem; line-height: 0; top: -2rem; right: -2rem;">
            <q-icon name="format_quote" />
          </div>
          <q-card-section>
            <div v-if="loadingVerse" class="row justify-center q-pa-md">
              <q-spinner color="white" size="2em" />
            </div>
            <div v-else>
              <div class="text-h6 text-weight-regular" style="font-family: 'Amiri', serif; line-height: 1.8;">
                {{ dailyVerse?.text }}
              </div>
              <div class="text-caption text-teal-2 q-mt-md text-right">سورة {{ dailyVerse?.surah }} - {{
                dailyVerse?.numberInSurah }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const currentDate = ref('')
const prayerTimes = ref(null)
const prayerCity = ref('جاري التحديد...')
const loadingPrayers = ref(true)
const dailyVerse = ref(null)
const loadingVerse = ref(true)

const updateDate = () => {
  const today = new Date()

  // Hijri Date
  const hijriFormatter = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
  const hijri = hijriFormatter.format(today)

  // Gregorian Date
  const gregorianFormatter = new Intl.DateTimeFormat('ar-EG', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
  const gregorian = gregorianFormatter.format(today) + ' م'

  currentDate.value = `${hijri} | ${gregorian}`
}

const fetchPrayerTimes = async () => {
  try {
    // Sharjah, Al Qasba Coordinates
    const lat = 25.3228
    const lng = 55.3768

    const response = await axios.get(`https://api.aladhan.com/v1/timings/${Math.floor(Date.now() / 1000)}`, {
      params: {
        latitude: lat,
        longitude: lng,
        method: 4 // Umm Al-Qura
      }
    })

    prayerTimes.value = response.data.data.timings
    prayerCity.value = 'الشارقة - القصباء'
  } catch (error) {
    console.error('Error fetching prayer times', error)
    prayerCity.value = 'تعذر التحديث'
  } finally {
    loadingPrayers.value = false
  }
}

const fetchDailyVerse = async () => {
  try {
    // Random verse between 1 and 6236
    const randomVerse = Math.floor(Math.random() * 6236) + 1
    const response = await axios.get(`https://api.alquran.cloud/v1/ayah/${randomVerse}/ar.alafasy`)

    dailyVerse.value = {
      text: response.data.data.text,
      surah: response.data.data.surah.name,
      numberInSurah: response.data.data.numberInSurah
    }
  } catch (error) {
    console.error('Error fetching verse', error)
  } finally {
    loadingVerse.value = false
  }
}

onMounted(() => {
  updateDate()
  fetchPrayerTimes()
  fetchDailyVerse()
})
</script>

<style scoped lang="scss">
.glass-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 16px;
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s;
}

// Dark Mode Overrides


.glass-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 77, 64, 0.1);
}

.opacity-80 {
  opacity: 0.8;
}

.opacity-10 {
  opacity: 0.1;
}
</style>
