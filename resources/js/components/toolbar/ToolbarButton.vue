<template>
    <button
        type="button"
        class="texteditor-tb-button"
        :class="{ 'is-active': active, 'has-label': showLabel }"
        :disabled="disabled"
        v-tooltip="tooltipOptions"
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
const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

/**
 * Einheitlicher Toolbar-Button. `@mousedown.prevent` hält den Fokus (und damit die Auswahl) im Editor.
 * Tooltips kommen über Novas floating-vue (`v-tooltip`); `shortcut` nutzt „Mod“ als ⌘ bzw. Strg.
 */
export default {
    emits: ["click"],

    props: {
        icon: { type: [Object, Function, String], default: null },
        label: { type: String, required: true },
        title: { type: String, default: null },
        tooltip: { type: String, default: null },
        shortcut: { type: String, default: null },
        active: { type: Boolean, default: undefined },
        disabled: { type: Boolean, default: false },
        showLabel: { type: Boolean, default: false },
    },

    computed: {
        tooltipOptions() {
            const text = this.tooltip ?? this.title ?? (this.showLabel ? null : this.label);

            if (!text) {
                return null;
            }

            const shortcut = this.shortcut?.replace("Mod", isMac ? "⌘" : "Strg+").replace("Shift", isMac ? "⇧" : "Umschalt+");

            return { content: shortcut ? `${text} (${shortcut})` : text, triggers: ["hover", "focus"], delay: { show: 300, hide: 0 } };
        },
    },
};
</script>
