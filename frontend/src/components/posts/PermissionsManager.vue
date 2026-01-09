<template>
  <div class="space-y-4">
    <h4 class="text-lg font-medium text-text-dark-primary dark:text-text-light-primary flex items-center gap-2">
      Lock Media
      <button class="text-text-light-tertiary dark:text-text-dark-tertiary hover:text-text-light-secondary dark:hover:text-text-dark-secondary">
        <i class="ri-question-line text-xl"></i>
      </button>
    </h4>
    <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
      The following permissions decide who can see your media. You can create one or multiple sets of permissions. Click 
      <button class="text-primary-light dark:text-primary-dark hover:underline">here</button>
      to learn more.
    </p>
    <div class="flex gap-3">
      <!-- Removed Save Preset and Easy Mode buttons -->
    </div>
  
    <!-- Permission Sets -->
    <div v-if="permissionSets.length > 0">
      <div v-for="(permissionSet, index) in permissionSets" :key="index" class="bg-surface-light dark:bg-surface-dark rounded-lg p-4 space-y-2">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="text-text-dark-primary dark:text-text-light-primary">Permission Set {{ index + 1 }}</span>
            <button @click="removeAllPermissionSets" class="text-text-light-tertiary dark:text-text-dark-tertiary hover:text-red-500 dark:hover:text-red-400">
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>
        <div v-for="(permission, pIndex) in permissionSet" :key="pIndex" class="flex items-center gap-2">
          <select v-model="permission.type" @change="updatePermission(index, pIndex)" class="bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary rounded px-2 py-1 border border-border-light dark:border-border-dark">
            <option value="add_price">Add Price</option>
            <option value="subscribed_all_tiers">Subscribed (All Tiers)</option>
            <!-- <option value="following">Following</option> -->
            <option value="limited_time">Limited Time</option>
          </select>
          <button v-if="permission.type === 'add_price'" @click="openPriceModal(index, pIndex)" class="text-primary-light dark:text-primary-dark hover:underline">
            {{ permission.value ? `$${permission.value}` : 'Set Price' }}
          </button>
          <button v-if="permission.type === 'limited_time'" @click="openTimeModal(index, pIndex)" class="text-primary-light dark:text-primary-dark hover:underline">
            {{ permission.value ? formatTimeRange(permission.value) : 'Set Time Range' }}
          </button>
          <button @click="removePermission(index, pIndex)" class="text-text-light-tertiary dark:text-text-dark-tertiary hover:text-text-light-secondary dark:hover:text-text-dark-secondary">
            <i class="ri-close-line"></i>
          </button>
        </div>
        <button @click="addPermission(index)" class="w-full py-2 text-primary-light dark:text-primary-dark hover:text-primary-light-hover dark:hover:text-primary-dark-hover text-left">
          <i class="ri-add-line mr-1"></i>
          Add Permission
        </button>
      </div>
      <div class="text-center text-text-light-secondary dark:text-text-dark-secondary">
        OR
      </div>
      <button @click="addPermissionSet" class="w-full py-2 text-primary-light dark:text-primary-dark hover:text-primary-light-hover dark:hover:text-primary-dark-hover text-center">
        <i class="ri-add-line mr-1"></i>
        Add New Permission Set
      </button>
    </div>
    <div v-else class="p-4 text-center text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900 rounded">
      <i class="ri-lock-unlock-line text-2xl mb-2"></i>
      <div class="font-semibold">This post will be <span class="text-green-700 dark:text-green-300">free</span> and visible to everyone.</div>
      <div class="text-sm mt-1">Add a permission set to restrict access.</div>
      <button @click="addPermissionSet" class="mt-4 px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover">
        <i class="ri-add-line mr-1"></i>
        Add Permission Set
      </button>
    </div>
  
    <!-- Price Modal -->
    <Modal v-if="showPriceModal" @close="closePriceModal">
      <h3 class="text-lg font-medium mb-4 text-text-dark-primary dark:text-text-light-primary">Set Price</h3>
      <input v-model="tempPrice" type="number" min="0" step="0.01" class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary rounded border border-border-light dark:border-border-dark" placeholder="Enter price">
      <div class="flex justify-end mt-4">
        <button @click="closePriceModal" class="px-4 py-2 text-text-light-secondary dark:text-text-dark-secondary mr-2">Cancel</button>
        <button @click="confirmPrice" class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover">Confirm</button>
      </div>
    </Modal>
  
    <!-- Time Range Modal -->
    <Modal v-if="showTimeModal" @close="closeTimeModal">
      <h3 class="text-lg font-medium mb-4 text-text-dark-primary dark:text-text-light-primary">Set Time Range</h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Start Date</label>
          <input
            v-model="tempTimeRange.start"
            type="datetime-local"
            class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary rounded border border-border-light dark:border-border-dark"
            @change="handleDateChange('start')"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">End Date</label>
          <input
            v-model="tempTimeRange.end"
            type="datetime-local"
            class="w-full px-3 py-2 bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary rounded border border-border-light dark:border-border-dark"
            @change="handleDateChange('end')"
          />
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button @click="closeTimeModal" class="px-4 py-2 text-text-light-secondary dark:text-text-dark-secondary mr-2">Cancel</button>
        <button @click="confirmTimeRange" class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover">Confirm</button>
      </div>
    </Modal>
  </div>
  </template>
  
  <script setup>
  import { ref, computed, toRefs } from 'vue';
  import Modal from '@/components/common/Modal.vue';
  import { useUploadStore } from '@/stores/uploadStore';
  
  const props = defineProps({
    modelValue: Array,
    editMode: {
      type: Boolean,
      default: false
    }
  });
  const emit = defineEmits(['update:modelValue']);
  
  const uploadStore = useUploadStore();
  
  const isEasyMode = ref(false);
  const permissionSets = computed({
    get() {
      return props.editMode ? uploadStore.post.permissions : props.modelValue;
    },
    set(val) {
      if (!props.editMode) emit('update:modelValue', val);
    }
  });
  
  const showPriceModal = ref(false);
  const showTimeModal = ref(false);
  const tempPrice = ref('');
  const tempTimeRange = ref({ start: '', end: '' });
  const currentEditingPermission = ref({ setIndex: -1, permissionIndex: -1 });
  
  const addPermissionSet = () => {
    if (props.editMode) {
      uploadStore.addPermissionSet();
    } else {
      const newSets = [...permissionSets.value, [{ type: 'subscribed_all_tiers', value: null }]];
      permissionSets.value = newSets;
    }
    console.log('Permission set added in component:', permissionSets.value);
  };
  
  const removeAllPermissionSets = () => {
    if (props.editMode) {
      uploadStore.setPermissions([]);
    } else {
      permissionSets.value = [];
    }
    emit('update:modelValue', []);
    console.log('All permission sets removed, permissions now:', permissionSets.value);
  };
  
  const addPermission = (setIndex) => {
    if (props.editMode) {
      uploadStore.addPermission(setIndex);
    } else {
      const newSets = permissionSets.value.slice();
      newSets[setIndex] = [...newSets[setIndex], { type: 'subscribed_all_tiers', value: null }];
      permissionSets.value = newSets;
    }
    console.log('Permission added in component:', permissionSets.value);
  };
  
  const removePermission = (setIndex, permissionIndex) => {
    if (props.editMode) {
      uploadStore.removePermission(setIndex, permissionIndex);
    } else {
      const newSets = permissionSets.value.slice();
      newSets[setIndex] = newSets[setIndex].slice();
      newSets[setIndex].splice(permissionIndex, 1);
      permissionSets.value = newSets;
    }
    console.log('Permission removed in component:', permissionSets.value);
  };
  
  const updatePermission = (setIndex, permissionIndex) => {
    if (props.editMode) {
      const permission = permissionSets.value[setIndex][permissionIndex];
      if (permission.type === 'add_price' || permission.type === 'limited_time') {
        permission.value = null;
      }
      uploadStore.updatePermission(setIndex, permissionIndex, permission);
    } else {
      const newSets = permissionSets.value.slice();
      const permission = { ...newSets[setIndex][permissionIndex] };
      if (permission.type === 'add_price' || permission.type === 'limited_time') {
        permission.value = null;
      }
      newSets[setIndex] = newSets[setIndex].slice();
      newSets[setIndex][permissionIndex] = permission;
      permissionSets.value = newSets;
    }
    console.log('Permission updated in component:', permissionSets.value);
  };
  
  const openPriceModal = (setIndex, permissionIndex) => {
    currentEditingPermission.value = { setIndex, permissionIndex };
    tempPrice.value = permissionSets.value[setIndex][permissionIndex].value || '';
    showPriceModal.value = true;
  };
  
  const closePriceModal = () => {
    showPriceModal.value = false;
    tempPrice.value = '';
  };
  
  const confirmPrice = () => {
    const { setIndex, permissionIndex } = currentEditingPermission.value;
    uploadStore.updatePermissionValue(setIndex, permissionIndex, parseFloat(tempPrice.value));
    closePriceModal();
    console.log('Price confirmed in component:', permissionSets.value);
  };
  
  const openTimeModal = (setIndex, permissionIndex) => {
    currentEditingPermission.value = { setIndex, permissionIndex };
    const currentValue = permissionSets.value[setIndex][permissionIndex].value;
    tempTimeRange.value = currentValue ? { ...currentValue } : { start: '', end: '' };
    showTimeModal.value = true;
  };
  
  const closeTimeModal = () => {
    showTimeModal.value = false;
    tempTimeRange.value = { start: '', end: '' };
  };
  
  const confirmTimeRange = () => {
    const { setIndex, permissionIndex } = currentEditingPermission.value;
    uploadStore.updatePermissionValue(setIndex, permissionIndex, { ...tempTimeRange.value });
    closeTimeModal();
    console.log('Time range confirmed in component:', permissionSets.value);
  };
  
  const formatTimeRange = (range) => {
    const start = new Date(range.start)
    const end = new Date(range.end)
    const options = { 
      month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' 
    }
    return `${start.toLocaleDateString(undefined, options)} - ${end.toLocaleDateString(undefined, options)}`;
  };
  
  const toggleEasyMode = () => {
    isEasyMode.value = !isEasyMode.value;
    console.log('Easy mode toggled in component:', isEasyMode.value);
  };
  
  const savePreset = () => {
    // Implement save preset functionality
    console.log('Saving preset:', permissionSets.value);
  };
  
  const handleDateChange = (type) => {
    if (tempTimeRange.start && tempTimeRange.end) {
      confirmTimeRange();
    }
  };
  </script>