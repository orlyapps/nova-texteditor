<template>
    <node-view-wrapper class="texteditor-block" :class="{ 'is-selected': selected }">
        <div class="texteditor-block__card" contenteditable="false">
            <div class="texteditor-block__header">
                <button
                    type="button"
                    class="texteditor-block__handle"
                    draggable="true"
                    data-drag-handle
                    title="Verschieben"
                    aria-label="Block verschieben"
                >
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <circle cx="7" cy="5" r="1.5" />
                        <circle cx="13" cy="5" r="1.5" />
                        <circle cx="7" cy="10" r="1.5" />
                        <circle cx="13" cy="10" r="1.5" />
                        <circle cx="7" cy="15" r="1.5" />
                        <circle cx="13" cy="15" r="1.5" />
                    </svg>
                </button>

                <div class="texteditor-block__title">{{ title }}</div>

                <div v-if="$slots.default" class="texteditor-block__options">
                    <slot />
                </div>

                <div class="texteditor-block__actions">
                    <button
                        type="button"
                        class="texteditor-block__action texteditor-block__action--move"
                        :disabled="!canMoveUp"
                        title="Nach oben"
                        aria-label="Block nach oben verschieben"
                        @mousedown.prevent
                        @click="move(-1)"
                    >
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="texteditor-block__action texteditor-block__action--move"
                        :disabled="!canMoveDown"
                        title="Nach unten"
                        aria-label="Block nach unten verschieben"
                        @mousedown.prevent
                        @click="move(1)"
                    >
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="texteditor-block__action texteditor-block__action--danger"
                        title="Entfernen"
                        aria-label="Block entfernen"
                        @mousedown.prevent
                        @click="remove"
                    >
                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </node-view-wrapper>
</template>

<script>
import { NodeViewWrapper } from "@tiptap/vue-3";
import { NodeSelection } from "@tiptap/pm/state";

/**
 * Gemeinsame Hülle für variable Blöcke (Anrede, Signatur, …): Drag-Griff, Titel, optionale
 * Einstellungen im Slot sowie Hoch/Runter/Entfernen — die Pfeile sind der Touch-Ersatz für Drag & Drop.
 */
export default {
    components: {
        NodeViewWrapper,
    },

    props: {
        title: { type: String, required: true },
        editor: { type: Object, required: true },
        node: { type: Object, required: true },
        getPos: { type: Function, required: true },
        deleteNode: { type: Function, required: true },
        selected: { type: Boolean, default: false },
    },

    data() {
        return {
            canMoveUp: false,
            canMoveDown: false,
        };
    },

    mounted() {
        this.updateMoveState();
        this.editor.on("update", this.updateMoveState);
    },

    beforeUnmount() {
        this.editor.off("update", this.updateMoveState);
    },

    methods: {
        siblingInfo() {
            const pos = this.getPos();

            if (typeof pos !== "number") {
                return null;
            }

            const $pos = this.editor.state.doc.resolve(pos);

            return { pos, parent: $pos.parent, index: $pos.index() };
        },

        updateMoveState() {
            const info = this.siblingInfo();

            this.canMoveUp = !!info && info.index > 0;
            this.canMoveDown = !!info && info.index < info.parent.childCount - 1;
        },

        move(direction) {
            const info = this.siblingInfo();

            if (!info) {
                return;
            }

            const sibling = info.parent.maybeChild(info.index + direction);

            if (!sibling) {
                return;
            }

            const { state, view } = this.editor;
            const node = state.doc.nodeAt(info.pos);
            const target = direction < 0 ? info.pos - sibling.nodeSize : info.pos + sibling.nodeSize;
            const tr = state.tr.delete(info.pos, info.pos + node.nodeSize).insert(target, node);

            tr.setSelection(NodeSelection.create(tr.doc, target)).scrollIntoView();
            view.dispatch(tr);
        },

        remove() {
            this.deleteNode();
            this.editor.commands.focus();
        },
    },
};
</script>

<style lang="scss">
.texteditor-block {
    margin: 0.75rem 0;

    &__card {
        border: 1px solid rgb(var(--colors-gray-200));
        border-left: 3px solid rgb(var(--colors-primary-500));
        border-radius: 0.375rem;
        background: rgb(var(--colors-gray-50));
        user-select: none;
    }

    &.is-selected &__card {
        box-shadow: 0 0 0 2px rgb(var(--colors-primary-300));
    }

    &__header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.5rem;
    }

    &__handle {
        display: inline-flex;
        flex: none;
        width: 1.75rem;
        height: 1.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
        color: rgb(var(--colors-gray-400));
        cursor: grab;

        &:hover {
            color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-200));
        }

        &:active {
            cursor: grabbing;
        }

        svg {
            width: 1rem;
            height: 1rem;
        }
    }

    &__title {
        flex: none;
        font-weight: 600;
        font-size: 0.875rem;
        color: rgb(var(--colors-gray-700));
    }

    &__options {
        flex: 1 1 auto;
        min-width: 0;

        select {
            max-width: 100%;
        }
    }

    &__actions {
        display: flex;
        flex: none;
        gap: 0.125rem;
        margin-left: auto;
    }

    &__action {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
        color: rgb(var(--colors-gray-500));

        &:hover:not(:disabled) {
            color: rgb(var(--colors-gray-800));
            background: rgb(var(--colors-gray-200));
        }

        &:disabled {
            opacity: 0.35;
            cursor: default;
        }

        &--danger:hover:not(:disabled) {
            color: rgb(var(--colors-red-600));
            background: rgb(var(--colors-red-50));
        }

        svg {
            width: 1rem;
            height: 1rem;
        }
    }

    /* Maus-Geräte: Pfeile erst beim Hover/Fokus zeigen — Entfernen bleibt immer sichtbar. */
    @media (hover: hover) and (pointer: fine) {
        &__action--move {
            visibility: hidden;
        }

        &__card:hover &__action--move,
        &__card:focus-within &__action--move,
        &.is-selected &__action--move {
            visibility: visible;
        }
    }

    /* Schmale Screens: Einstellungen in eine eigene Zeile unter den Kopf. */
    @media (max-width: 639px) {
        /* Titel darf schrumpfen, damit Griff, Titel und Aktionen in einer Zeile bleiben. */
        &__title {
            flex: 1 1 0;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        &__options {
            order: 3;
            flex-basis: 100%;

            select {
                width: 100%;
            }
        }
    }
}

.dark .texteditor-block {
    &__card {
        border-color: rgb(var(--colors-gray-700));
        border-left-color: rgb(var(--colors-primary-500));
        background: rgb(var(--colors-gray-800));
    }

    &__title {
        color: rgb(var(--colors-gray-100));
    }

    &__handle:hover,
    &__action:hover:not(:disabled) {
        color: rgb(var(--colors-gray-100));
        background: rgb(var(--colors-gray-700));
    }

    &__action--danger:hover:not(:disabled) {
        color: rgb(var(--colors-red-400));
        background: rgb(var(--colors-gray-700));
    }
}
</style>
