<template>
  <div class="mt-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">{{ t('made_previews') }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="item in content"
        :key="item.id"
        class="aspect-[9/16] relative rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 group cursor-pointer"
      >
        <video
          class="w-full h-full object-cover"
          :src="item.videoUrl"
          loop
          muted
          ref="video"
          @mouseover="playVideo"
          @mouseout="pauseVideo"
        ></video>
        <div
          class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent group-hover:opacity-0 transition-opacity"
        ></div>
        <div class="absolute bottom-0 left-0 right-0 p-2">
          <p class="text-white text-sm font-medium truncate">{{ item.title }}</p>
          <p class="text-gray-300 text-xs truncate">{{ item.creator }}</p>
        </div>
        <div
          class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <div
            class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center"
          >
            <i class="ri-play-fill text-white text-2xl"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const content = ref([
  {
    id: 1,
    title: "Summer Dance Vibes",
    creator: "@dancequeen",
    videoUrl: "https://assets.mixkit.co/videos/preview/mixkit-girl-in-neon-sign-1232-large.mp4"
  },
  {
    id: 2,
    title: "Sunset Yoga Flow",
    creator: "@zenmaster",
    videoUrl: "https://assets.mixkit.co/videos/preview/mixkit-woman-doing-yoga-at-sunset-4796-large.mp4"
  },
  {
    id: 3,
    title: "Urban Skateboarding",
    creator: "@skateguy",
    videoUrl: "https://assets.mixkit.co/videos/preview/mixkit-man-skating-backwards-on-a-street-34552-large.mp4"
  },
  {
    id: 4,
    title: "Neon Night Lights",
    creator: "@citylover",
    videoUrl: "https://assets.mixkit.co/videos/preview/mixkit-lights-of-the-city-at-night-4739-large.mp4"
  },
  {
    id: 5,
    title: "Beach Waves Relaxation",
    creator: "@oceanvibes",
    videoUrl: "https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-beach-with-blue-water-4071-large.mp4"
  }
]);

const playVideo = (event) => {
  event.target.play();
};

const pauseVideo = (event) => {
  event.target.pause();
  event.target.currentTime = 0;
};
</script>
