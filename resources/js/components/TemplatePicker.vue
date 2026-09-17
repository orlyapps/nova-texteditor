<template>
    <toolbar-menu
        ref="menu"
        :icon="icons.DocumentTextIcon"
        label="Vorlagen"
        title="Vorlage laden"
        :show-label="!compact"
        placement="bottom-end"
        :popover-class="['texteditor-templates', { 'is-sheet': sheet }]"
        :keep-editor-focus="false"
        @open="onOpen"
        @opened="focusSearch"
        @close="query = ''"
    >
        <template #default="{ close }">
            <div class="texteditor-templates__header">
                <magnifying-glass-icon class="texteditor-templates__search-icon" />
                <input
                    ref="search"
                    v-model="query"
                    type="search"
                    class="texteditor-templates__search"
                    placeholder="Vorlage suchen…"
                    aria-label="Vorlage suchen"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="choose(flatResults[activeIndex], close)"
                />
                <button v-if="sheet" type="button" class="texteditor-templates__close" aria-label="Schließen" @click="close">
                    <x-mark-icon />
                </button>
            </div>

            <div class="texteditor-templates__list">
                <div v-if="loading && !templates.length" class="texteditor-menu-empty">Vorlagen werden geladen…</div>

                <template v-else>
                    <div v-if="!query && !matching.length" class="texteditor-templates__hint">
                        Keine Vorlagen für {{ categoryLabels }} vorhanden.
                        <template v-if="others.length">Du kannst eine Vorlage aus einer anderen Kategorie laden.</template>
                    </div>

                    <section v-for="section in sections" :key="section.key">
                        <div class="texteditor-menu-heading">{{ section.title }}</div>
                        <button
                            v-for="template in section.templates"
                            :key="template.id"
                            :ref="(el) => (itemRefs[template.id] = el)"
                            type="button"
                            class="texteditor-menu-item texteditor-templates__item"
                            :class="{ 'is-selected': flatResults[activeIndex]?.id === template.id }"
                            @click="choose(template, close)"
                            @mouseenter="activeIndex = flatResults.indexOf(template)"
                        >
                            <span class="texteditor-menu-item__text">
                                <span class="texteditor-menu-item__label">
                                    <span v-if="!template.matches" class="texteditor-templates__category">{{ template.category_label }} ›</span>
                                    {{ template.name }}
                                </span>
                                <span v-if="preview(template)" class="texteditor-menu-item__subtitle">{{ preview(template) }}</span>
                            </span>
                        </button>
                    </section>

                    <div v-if="query && !flatResults.length" class="texteditor-menu-empty">Keine Vorlage gefunden</div>
                </template>
            </div>

            <div v-if="canCreate" class="texteditor-templates__footer">
                <button type="button" class="texteditor-menu-item" @click="saveNew(close)">
                    <plus-icon class="texteditor-menu-item__icon" />
                    <span class="texteditor-menu-item__label">Aktuellen Text als neue Vorlage speichern…</span>
                </button>
            </div>
        </template>
    </toolbar-menu>
</template>

<script>
import Fuse from "fuse.js";
import { DocumentTextIcon, MagnifyingGlassIcon, PlusIcon, XMarkIcon } from "@heroicons/vue/20/solid";
import ToolbarMenu from "./toolbar/ToolbarMenu.vue";

const stripHtml = (html) => String(html ?? "").replace(/<[^>]*>/g, " ").replace(/&nbsp;/g, " ").replace(/\s+/g, " ").trim();

/**
 * Vorlagen-Auswahl mit Suche: passende Kategorien zuerst, alle übrigen darunter.
 * Unter 640px Viewport-Breite als Sheet von unten statt als Popover.
 */
export default {
    components: { ToolbarMenu, MagnifyingGlassIcon, PlusIcon, XMarkIcon },

    emits: ["select", "save-new", "open"],

    props: {
        templates: { type: Array, required: true },
        categoryLabels: { type: String, default: "diese Kategorie" },
        canCreate: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        compact: { type: Boolean, default: false },
    },

    data() {
        return {
            query: "",
            activeIndex: 0,
            sheet: false,
            itemRefs: {},
            icons: { DocumentTextIcon },
        };
    },

    computed: {
        fuse() {
            return new Fuse(this.templates, {
                threshold: 0.35,
                ignoreLocation: true,
                keys: [
                    { name: "name", weight: 3 },
                    { name: "subject", weight: 2 },
                    { name: "category_label", weight: 1 },
                    { name: "plainText", getFn: (template) => stripHtml(template.text), weight: 1 },
                ],
            });
        },

        results() {
            return this.query ? this.fuse.search(this.query).map((result) => result.item) : this.templates;
        },

        matching() {
            const matching = this.results.filter((template) => template.matches);

            // Ohne Suche alphabetisch; mit Suche bleibt die Relevanz-Reihenfolge von Fuse.
            return this.query ? matching : [...matching].sort((a, b) => a.name.localeCompare(b.name));
        },

        others() {
            const others = this.results.filter((template) => !template.matches);

            return this.query
                ? others
                : [...others].sort((a, b) => a.category_label.localeCompare(b.category_label) || a.name.localeCompare(b.name));
        },

        sections() {
            return [
                { key: "matching", title: this.categoryLabels, templates: this.matching },
                { key: "others", title: "Andere Kategorien", templates: this.others },
            ].filter((section) => section.templates.length);
        },

        flatResults() {
            return [...this.matching, ...this.others];
        },
    },

    watch: {
        query() {
            this.activeIndex = 0;
        },
    },

    methods: {
        onOpen() {
            this.sheet = window.matchMedia("(max-width: 639px)").matches;
            this.activeIndex = 0;
            this.$emit("open");
        },

        /**
         * Auf Touch-Geräten würde der Fokus sofort die Tastatur über die Liste schieben.
         */
        focusSearch() {
            if (!this.sheet) {
                this.$refs.search?.focus();
            }
        },

        move(step) {
            if (!this.flatResults.length) {
                return;
            }

            this.activeIndex = (this.activeIndex + step + this.flatResults.length) % this.flatResults.length;
            this.itemRefs[this.flatResults[this.activeIndex].id]?.scrollIntoView({ block: "nearest" });
        },

        choose(template, close) {
            if (!template) {
                return;
            }

            close();
            this.$emit("select", template);
        },

        saveNew(close) {
            close();
            this.$emit("save-new");
        },

        preview(template) {
            const text = template.subject ? `Betreff: ${template.subject}` : stripHtml(template.text);

            return text.length > 90 ? `${text.slice(0, 90)}…` : text;
        },
    },
};
</script>
