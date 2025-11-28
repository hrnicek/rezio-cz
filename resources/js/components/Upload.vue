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
    url: '/filepond',
    process: '/process',
    revert: '/revert',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    },
};

const handleProcessFile = (error: any, file: any) => {
    if (!error) {
        emit('update:modelValue', file.serverId);
    }
};

const handleRemoveFile = () => {
    emit('update:modelValue', null);
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
    border: 1px dashed #e5e7eb;
}
:deep(.filepond--drop-label) {
    color: #4b5563;
}
</style>
