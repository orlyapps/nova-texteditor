<template>
    <bubble-menu
        :editor="editor"
        :plugin-key="pluginKey"
        :should-show="shouldShow"
        :options="{ placement: 'top', offset: 8, flip: true, shift: { padding: 8 } }"
        class="texteditor-bubble"
    >
        <form v-if="editingLink" class="texteditor-bubble__link-form" @submit.prevent="applyLink">
            <input
                ref="linkInput"
                v-model="href"
                type="text"
                inputmode="url"
                class="texteditor-bubble__input"
                placeholder="https://… oder name@beispiel.de"
                aria-label="Link-Adresse"
                @keydown.esc.prevent="cancelLink"
            />
            <toolbar-button :icon="icons.CheckIcon" label="Übernehmen" @click="applyLink" />
            <toolbar-button v-if="editor.isActive('link')" :icon="icons.LinkSlashIcon" label="Link entfernen" @click="removeLink" />
            <toolbar-button :icon="icons.XMarkIcon" label="Abbrechen" @click="cancelLink" />
        </form>

        <div v-else-if="editor.isActive('link') && editor.state.selection.empty" class="texteditor-bubble__row">
            <a :href="currentHref" target="_blank" rel="noopener noreferrer" class="texteditor-bubble__href" @mousedown.prevent>
                {{ currentHref }}
            </a>
            <toolbar-button :icon="icons.PencilIcon" label="Link bearbeiten" @click="startLinkEditing" />
            <toolbar-button :icon="icons.LinkSlashIcon" label="Link entfernen" @click="removeLink" />
        </div>

        <div v-else class="texteditor-bubble__row">
            <toolbar-button :icon="icons.BoldIcon" label="Fett" :active="editor.isActive('bold')" @click="editor.chain().focus().toggleBold().run()" />
            <toolbar-button :icon="icons.ItalicIcon" label="Kursiv" :active="editor.isActive('italic')" @click="editor.chain().focus().toggleItalic().run()" />
            <toolbar-button :icon="icons.UnderlineIcon" label="Unterstrichen" :active="editor.isActive('underline')" @click="editor.chain().focus().toggleUnderline().run()" />
            <toolbar-button :icon="icons.StrikethroughIcon" label="Durchgestrichen" :active="editor.isActive('strike')" @click="editor.chain().focus().toggleStrike().run()" />
            <span class="texteditor-toolbar__divider" aria-hidden="true"></span>
            <toolbar-button :icon="icons.LinkIcon" label="Link" :active="editor.isActive('link')" @click="startLinkEditing" />
        </div>
    </bubble-menu>
</template>

<script>
import { BubbleMenu } from "@tiptap/vue-3/menus";
import { NodeSelection } from "@tiptap/pm/state";
import {
    BoldIcon,
    CheckIcon,
    ItalicIcon,
    LinkIcon,
    LinkSlashIcon,
    PencilIcon,
    StrikethroughIcon,
    UnderlineIcon,
    XMarkIcon,
} from "@heroicons/vue/20/solid";
import ToolbarButton from "./toolbar/ToolbarButton.vue";

const PLUGIN_KEY = "texteditorBubbleMenu";

/**
 * Schwebendes Menü bei Textauswahl (Formatierung + Link) und auf Links (Öffnen/Bearbeiten/Entfernen).
 */
export default {
    components: { BubbleMenu, ToolbarButton },

    props: {
        editor: { type: Object, required: true },
    },

    data() {
        return {
            pluginKey: PLUGIN_KEY,
            editingLink: false,
            href: "",
            icons: { BoldIcon, CheckIcon, ItalicIcon, LinkIcon, LinkSlashIcon, PencilIcon, StrikethroughIcon, UnderlineIcon, XMarkIcon },
        };
    },

    computed: {
        currentHref() {
            return this.editor.getAttributes("link").href ?? "";
        },
    },

    created() {
        this.editor.on("selectionUpdate", this.onSelectionUpdate);
    },

    beforeUnmount() {
        this.editor.off("selectionUpdate", this.onSelectionUpdate);
    },

    methods: {
        shouldShow({ editor, state, from, to }) {
            if (this.editingLink) {
                return true;
            }

            if (!editor.isEditable || state.selection instanceof NodeSelection) {
                return false;
            }

            return from !== to || editor.isActive("link");
        },

        /**
         * Öffnet das Link-Eingabefeld — auch ohne Auswahl (dann wird die URL selbst als Linktext eingefügt).
         */
        startLinkEditing() {
            this.href = this.currentHref;
            this.editingLink = true;
            this.editor.view.dispatch(this.editor.state.tr.setMeta(PLUGIN_KEY, "show"));
            this.$nextTick(() => this.$refs.linkInput?.focus());
        },

        applyLink() {
            const href = this.normalizeHref(this.href);
            const chain = this.editor.chain().focus();

            if (!href) {
                chain.extendMarkRange("link").unsetLink().run();
            } else if (this.editor.state.selection.empty && !this.editor.isActive("link")) {
                chain.insertContent({ type: "text", text: this.href.trim(), marks: [{ type: "link", attrs: { href } }] }).run();
            } else {
                chain.extendMarkRange("link").setLink({ href }).run();
            }

            this.editingLink = false;
        },

        removeLink() {
            this.editor.chain().focus().extendMarkRange("link").unsetLink().run();
            this.editingLink = false;
        },

        cancelLink() {
            this.editingLink = false;
            this.editor.commands.focus();
        },

        onSelectionUpdate() {
            if (this.editingLink && !this.$el?.contains?.(document.activeElement)) {
                this.editingLink = false;
            }
        },

        normalizeHref(value) {
            const href = value.trim();

            if (!href) {
                return "";
            }

            if (/^[a-z][a-z0-9+.-]*:/i.test(href)) {
                return href;
            }

            return href.includes("@") && !href.includes("/") ? `mailto:${href}` : `https://${href}`;
        },
    },
};
</script>
