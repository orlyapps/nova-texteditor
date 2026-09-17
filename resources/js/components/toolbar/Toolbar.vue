<template>
    <div ref="root" class="texteditor-toolbar" :class="{ 'is-compact': compact }">
        <div class="texteditor-toolbar__format">
            <template v-for="(group, groupIndex) in visibleGroups" :key="'group-' + groupIndex">
                <span v-if="groupIndex > 0" class="texteditor-toolbar__divider" aria-hidden="true"></span>

                <template v-for="item in group" :key="item.name">
                    <toolbar-menu
                        v-if="item.options"
                        :icon="item.icon"
                        :label="item.label"
                        :active="item.options.some((option) => option.active?.(editor))"
                    >
                        <template #default="{ close }">
                            <button
                                v-for="option in item.options"
                                :key="option.name"
                                type="button"
                                class="texteditor-menu-item"
                                :class="{ 'is-active': option.active?.(editor) }"
                                @click="run(option, close)"
                            >
                                <component :is="option.icon" v-if="option.icon && typeof option.icon !== 'string'" class="texteditor-menu-item__icon" />
                                <span v-else-if="option.icon" class="texteditor-menu-item__glyph">{{ option.icon }}</span>
                                <span class="texteditor-menu-item__label" :class="option.labelClass">{{ option.label }}</span>
                            </button>
                        </template>
                    </toolbar-menu>

                    <toolbar-button
                        v-else
                        :icon="item.icon"
                        :label="item.label"
                        :active="item.active ? item.active(editor) : undefined"
                        :disabled="item.disabled ? item.disabled(editor) : false"
                        @click="run(item)"
                    />
                </template>
            </template>

            <toolbar-menu v-if="overflowItems.length" :icon="moreIcon" label="Weitere Formatierungen" title="Mehr">
                <template #default="{ close }">
                    <template v-for="item in overflowItems" :key="'more-' + item.name">
                        <div v-if="item.options" class="texteditor-menu-heading">{{ item.label }}</div>
                        <button
                            v-for="option in item.options || [item]"
                            :key="'more-option-' + option.name"
                            type="button"
                            class="texteditor-menu-item"
                            :class="{ 'is-active': option.active?.(editor) }"
                            :disabled="option.disabled ? option.disabled(editor) : false"
                            @click="run(option, close)"
                        >
                            <component :is="option.icon" v-if="option.icon && typeof option.icon !== 'string'" class="texteditor-menu-item__icon" />
                            <span v-else-if="option.icon" class="texteditor-menu-item__glyph">{{ option.icon }}</span>
                            <span class="texteditor-menu-item__label">{{ option.label }}</span>
                        </button>
                    </template>
                </template>
            </toolbar-menu>
        </div>

        <div class="texteditor-toolbar__end">
            <slot name="end" :compact="compact" />
        </div>
    </div>
</template>

<script>
import { EllipsisHorizontalIcon } from "@heroicons/vue/20/solid";
import ToolbarButton from "./ToolbarButton.vue";
import ToolbarMenu from "./ToolbarMenu.vue";
import { createToolbarItems, DEFAULT_BUTTONS } from "./items";

/**
 * Unterhalb dieser Toolbar-Breite wandern sekundäre Buttons ins „Mehr“-Menü statt umzubrechen.
 */
const COMPACT_WIDTH = 640;

export default {
    components: { ToolbarButton, ToolbarMenu },

    emits: ["edit-link"],

    props: {
        editor: { type: Object, required: true },
        buttons: { type: Array, default: null },
    },

    data() {
        return {
            compact: false,
            moreIcon: EllipsisHorizontalIcon,
        };
    },

    computed: {
        items() {
            return createToolbarItems({ onEditLink: () => this.$emit("edit-link") });
        },

        /**
         * Buttons aus `TextEditor::buttons()` (bzw. Default) als Gruppen; `|` trennt Gruppen, `br` wird ignoriert.
         */
        groups() {
            const groups = [[]];

            (this.buttons ?? DEFAULT_BUTTONS).forEach((name) => {
                if (name === "|") {
                    groups.push([]);
                } else if (this.items[name]?.inline) {
                    groups[groups.length - 1].push(...this.items[name].inline);
                } else if (this.items[name]) {
                    groups[groups.length - 1].push({ name, ...this.items[name] });
                }
            });

            return groups.filter((group) => group.length);
        },

        visibleGroups() {
            if (!this.compact) {
                return this.groups;
            }

            return this.groups.map((group) => group.filter((item) => item.primary)).filter((group) => group.length);
        },

        overflowItems() {
            return this.compact ? this.groups.flat().filter((item) => !item.primary) : [];
        },
    },

    mounted() {
        // Umschalten erst im nächsten Frame: Das Layout ändert sich dadurch selbst, synchron gäbe das
        // „ResizeObserver loop completed with undelivered notifications“ als Window-Error.
        this.resizeObserver = new ResizeObserver(([entry]) => {
            const compact = entry.contentRect.width < COMPACT_WIDTH;

            if (compact !== this.compact) {
                cancelAnimationFrame(this.resizeFrame);
                this.resizeFrame = requestAnimationFrame(() => (this.compact = compact));
            }
        });
        this.resizeObserver.observe(this.$refs.root);
    },

    beforeUnmount() {
        this.resizeObserver?.disconnect();
        cancelAnimationFrame(this.resizeFrame);
    },

    methods: {
        run(item, close) {
            item.run(this.editor);
            close?.();
        },
    },
};
</script>
