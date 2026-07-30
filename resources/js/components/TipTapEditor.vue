<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import { watch, onBeforeUnmount } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: string | null | undefined;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = useEditor({
    extensions: [
        StarterKit,
        Link.configure({
            openOnClick: false,
        }),
        Image,
    ],
    content: props.modelValue || '',
    onUpdate: () => {
        if (editor.value) {
            emit('update:modelValue', editor.value.getHTML());
        }
    },
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose-base focus:outline-none min-h-[150px] p-4 bg-background max-w-none',
        },
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const isSame = editor.value?.getHTML() === value;
        if (isSame) {
            return;
        }
        editor.value?.commands.setContent(value || '', { emitUpdate: false });
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const setLink = () => {
    const previousUrl = editor.value?.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value
        ?.chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run();
};
</script>

<template>
    <div
        v-if="editor"
        class="flex flex-col overflow-hidden rounded-md border border-input"
    >
        <div class="flex flex-wrap items-center gap-1 border-b bg-muted/50 p-2">
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{ 'bg-muted': editor.isActive('bold') }"
                @click="editor.chain().focus().toggleBold().run()"
                >B</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs italic"
                :class="{ 'bg-muted': editor.isActive('italic') }"
                @click="editor.chain().focus().toggleItalic().run()"
                >I</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs line-through"
                :class="{ 'bg-muted': editor.isActive('strike') }"
                @click="editor.chain().focus().toggleStrike().run()"
                >S</Button
            >
            <div class="mx-1 h-4 w-px bg-border"></div>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{
                    'bg-muted': editor.isActive('heading', { level: 2 }),
                }"
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
                >H2</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{
                    'bg-muted': editor.isActive('heading', { level: 3 }),
                }"
                @click="
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                "
                >H3</Button
            >
            <div class="mx-1 h-4 w-px bg-border"></div>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{ 'bg-muted': editor.isActive('bulletList') }"
                @click="editor.chain().focus().toggleBulletList().run()"
                >• Liste</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{ 'bg-muted': editor.isActive('orderedList') }"
                @click="editor.chain().focus().toggleOrderedList().run()"
                >1. Liste</Button
            >
            <div class="mx-1 h-4 w-px bg-border"></div>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                :class="{ 'bg-muted': editor.isActive('link') }"
                @click="setLink"
                >🔗 Link</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                @click="editor.chain().focus().unsetLink().run()"
                :disabled="!editor.isActive('link')"
                >🔗 Kaldır</Button
            >
        </div>
        <editor-content :editor="editor" class="flex-1" />
    </div>
</template>
