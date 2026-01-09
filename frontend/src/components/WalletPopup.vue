<template>
  <div v-if="isOpen" class="relative">
    <div class="absolute right-0 top-2 w-64 rounded-lg bg-surface-light dark:bg-surface-dark shadow-lg border border-border-light dark:border-border-dark">
      <div class="p-3 space-y-2">
        <div v-for="amount in walletAmounts" :key="amount" class="flex items-center justify-between">
          <span class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ t('wallet_balance', { amount }) }}</span>
          <button class="px-3 py-1 text-sm rounded bg-surface-light dark:bg-surface-dark hover:bg-secondary-light dark:hover:bg-secondary-dark text-text-light-primary dark:text-text-dark-primary transition-colors">
            ${{ amount }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  isOpen: Boolean,
});

const emit = defineEmits(['close']);

const walletAmounts = [10, 25, 50, 100, 500];
</script>

