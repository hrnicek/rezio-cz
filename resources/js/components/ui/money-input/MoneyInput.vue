<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { cn } from '@/lib/utils';
import { Input } from '@/components/ui/input';

const props = defineProps<{
  modelValue: number | null;
  currency?: string;
  class?: string;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | null): void;
}>();

const isFocused = ref(false);
const displayValue = ref('');

// Format: 1 200
const formatNumber = (val: number | null) => {
  if (val === null || val === undefined) return '';
  return new Intl.NumberFormat('cs-CZ', {
    maximumFractionDigits: 2,
    useGrouping: true,
  }).format(val);
};

// Parse: 1 200 -> 1200
const parseNumber = (val: string) => {
  // Remove spaces and non-numeric chars except comma/dot
  const cleaned = val.replace(/[^\d.,-]/g, '').replace(',', '.');
  const parsed = parseFloat(cleaned);
  return isNaN(parsed) ? null : parsed;
};

watch(() => props.modelValue, (newVal) => {
  if (!isFocused.value) {
    displayValue.value = formatNumber(newVal);
  }
}, { immediate: true });

const handleInput = (e: string | number) => {
  const val = e.toString();
  displayValue.value = val;
  const parsed = parseNumber(val);
  emit('update:modelValue', parsed);
};

const handleFocus = () => {
  isFocused.value = true;
  // Show raw number for editing, but if it's null/0 maybe empty? 
  // Usually user wants to edit the number. 
  // If 1 200 -> 1200
  if (props.modelValue !== null) {
      displayValue.value = props.modelValue.toString();
  } else {
      displayValue.value = '';
  }
};

const handleBlur = () => {
  isFocused.value = false;
  displayValue.value = formatNumber(props.modelValue);
};
</script>

<template>
  <div class="relative">
    <Input
      type="text"
      :class="cn('pr-12 text-right tabular-nums', props.class)"
      :model-value="displayValue"
      :placeholder="placeholder"
      @update:model-value="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
    />
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
      <span class="text-muted-foreground text-sm font-medium">{{ currency || 'Kč' }}</span>
    </div>
  </div>
</template>
