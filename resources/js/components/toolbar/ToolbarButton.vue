<template>
    <button
        type="button"
        class="texteditor-tb-button"
        :class="{ 'is-active': active, 'has-label': showLabel }"
        :disabled="disabled"
        :title="title || label"
        :aria-label="label"
        :aria-pressed="active === undefined ? undefined : String(!!active)"
        @mousedown.prevent
        @click="$emit('click', $event)"
    >
        <component :is="icon" v-if="icon && typeof icon !== 'string'" class="texteditor-tb-button__icon" />
        <span v-else-if="icon" class="texteditor-tb-button__glyph">{{ icon }}</span>
        <span v-if="showLabel" class="texteditor-tb-button__label">{{ label }}</span>
        <slot />
    </button>
</template>

<script>
/**
 * Einheitlicher Toolbar-Button. `@mousedown.prevent` hält den Fokus (und damit die Auswahl) im Editor.
 */
export default {
    emits: ["click"],

    props: {
        icon: { type: [Object, Function, String], default: null },
        label: { type: String, required: true },
        title: { type: String, default: null },
        active: { type: Boolean, default: undefined },
        disabled: { type: Boolean, default: false },
        showLabel: { type: Boolean, default: false },
    },
};
</script>
