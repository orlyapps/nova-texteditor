<template>
    <div ref="reference" class="texteditor-tb-menu">
        <toolbar-button :icon="icon" :label="label" :title="title" :active="open || active" :show-label="showLabel" @click="toggle">
            <chevron-down-icon v-if="showLabel" class="texteditor-tb-button__chevron" />
        </toolbar-button>

        <teleport to="body">
            <div
                v-if="open"
                ref="floating"
                class="texteditor-popover"
                :class="popoverClass"
                :style="floatingStyle"
                role="menu"
                @mousedown="onPopoverMousedown"
                @keydown.esc.stop="close"
            >
                <slot :close="close" />
            </div>
        </teleport>
    </div>
</template>

<script>
import { autoUpdate, computePosition, flip, offset, shift, size } from "@floating-ui/dom";
import { ChevronDownIcon } from "@heroicons/vue/20/solid";
import ToolbarButton from "./ToolbarButton.vue";

/**
 * Toolbar-Button mit schwebendem Menü (floating-ui). Wird in den Body teleportiert, weil Novas Layout
 * einen transformierten Vorfahren hat, der `position: fixed` sonst einsperrt.
 */
export default {
    components: { ToolbarButton, ChevronDownIcon },

    emits: ["open", "opened", "close"],

    props: {
        icon: { type: [Object, Function, String], default: null },
        label: { type: String, required: true },
        title: { type: String, default: null },
        active: { type: Boolean, default: false },
        showLabel: { type: Boolean, default: false },
        placement: { type: String, default: "bottom-start" },
        popoverClass: { type: [String, Object, Array], default: null },
        keepEditorFocus: { type: Boolean, default: true },
    },

    data() {
        return {
            open: false,
            floatingStyle: { position: "fixed", left: "0px", top: "0px", visibility: "hidden" },
        };
    },

    beforeUnmount() {
        this.teardown();
    },

    methods: {
        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            this.open = true;
            this.$emit("open");

            this.$nextTick(() => {
                this.cleanupAutoUpdate = autoUpdate(this.$refs.reference, this.$refs.floating, this.updatePosition);
                document.addEventListener("mousedown", this.onDocumentMousedown, true);
                document.addEventListener("keydown", this.onDocumentKeydown);
            });
        },

        close() {
            if (!this.open) {
                return;
            }

            this.open = false;
            this.floatingStyle = { ...this.floatingStyle, visibility: "hidden" };
            this.teardown();
            this.$emit("close");
        },

        teardown() {
            this.cleanupAutoUpdate?.();
            this.cleanupAutoUpdate = null;
            document.removeEventListener("mousedown", this.onDocumentMousedown, true);
            document.removeEventListener("keydown", this.onDocumentKeydown);
        },

        updatePosition() {
            if (!this.$refs.reference || !this.$refs.floating) {
                return;
            }

            computePosition(this.$refs.reference, this.$refs.floating, {
                strategy: "fixed",
                placement: this.placement,
                middleware: [
                    offset(6),
                    flip({ padding: 8 }),
                    shift({ padding: 8 }),
                    size({
                        padding: 8,
                        apply({ availableHeight, elements }) {
                            elements.floating.style.maxHeight = `${Math.max(160, availableHeight)}px`;
                        },
                    }),
                ],
            }).then(({ x, y }) => {
                const firstPosition = this.floatingStyle.visibility === "hidden";

                this.floatingStyle = { position: "fixed", left: `${x}px`, top: `${y}px`, visibility: "visible" };

                if (firstPosition) {
                    // Erst jetzt ist das Popover sichtbar und damit fokussierbar.
                    this.$nextTick(() => this.$emit("opened"));
                }
            });
        },

        onPopoverMousedown(event) {
            // Klicks auf Menüpunkte sollen die Editor-Auswahl nicht aufheben — Eingabefelder brauchen aber Fokus.
            if (this.keepEditorFocus && !event.target.closest("input, textarea, select")) {
                event.preventDefault();
            }
        },

        onDocumentMousedown(event) {
            if (this.$refs.floating?.contains(event.target) || this.$refs.reference?.contains(event.target)) {
                return;
            }

            this.close();
        },

        onDocumentKeydown(event) {
            if (event.key === "Escape") {
                this.close();
            }
        },
    },
};
</script>
