<script setup lang="ts">
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';

import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview
);

interface Props {
    modelValue?: string | string[] | null;
    label?: string;
    acceptedFileTypes?: string[];
    allowMultiple?: boolean;
    imagePreviewHeight?: number;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',
    acceptedFileTypes: () => ['image/*'],
    allowMultiple: false,
    imagePreviewHeight: 170,
});

const emit = defineEmits(['update:modelValue']);

const serverOptions = {
    process: '/filepond/process',
    revert: '/filepond/revert',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
};

const handleProcessFile = (error: any, file: any) => {
    if (error) return;

    if (props.allowMultiple) {
        const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
        if (!current.includes(file.serverId)) {
            emit('update:modelValue', [...current, file.serverId]);
        }
    } else {
        emit('update:modelValue', file.serverId);
    }
};

const handleRemoveFile = (error: any, file: any) => {
    if (error) return;

    if (props.allowMultiple) {
        const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
        const newValue = current.filter((id) => id !== file.serverId);
        emit('update:modelValue', newValue);
    } else {
        emit('update:modelValue', null);
    }
};
</script>

<template>
    <div class="w-full">
        <FilePond
            name="filepond"
            ref="pond"
            :label-idle="label"
            :allow-multiple="allowMultiple"
            :accepted-file-types="acceptedFileTypes"
            :image-preview-height="imagePreviewHeight"
            :server="serverOptions"
            @processfile="handleProcessFile"
            @removefile="handleRemoveFile"
        />
    </div>
</template>

<style scoped>
/* Custom styling to match app theme if needed */
:deep(.filepond--panel-root) {
    background-color: transparent;
    border: 1px dashed var(--border);
    border-radius: calc(var(--radius) - 2px);
}
:deep(.filepond--drop-label) {
    color: var(--muted-foreground);
}
</style>
