<template>
  <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/50 text-blue-600 dark:text-blue-400 p-4 rounded-lg">
    <p>
      {{ $t('email_not_verified') }}
      <a href="#" class="underline font-medium" @click.prevent="$emit('open-verification')">{{ $t('enter_code') }}</a>.
      <span class="ml-2">{{ $t('no_code_received') }} <a href="#" class="underline font-medium" @click.prevent="$emit('resend-code')">{{ $t('resend') }}</a>.</span>
    </p>
  </div>
</template>

<script setup>
// No script needed, just emits events
</script>
