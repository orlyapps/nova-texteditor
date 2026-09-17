<template>
    <div class="texteditor-preview">
        <div class="texteditor-preview__bar">
            <div v-if="!category" class="texteditor-preview__notice">Wähle zuerst eine Kategorie, um die Vorlage mit echten Daten zu sehen.</div>

            <template v-else>
                <label v-if="sources.length > 1" class="texteditor-preview__field">
                    <span class="texteditor-preview__label">Bereich</span>
                    <select v-model="source" class="texteditor-preview__select" @change="onSourceChange">
                        <option v-for="option in sources" :key="option.key" :value="option.key">{{ option.label }}</option>
                    </select>
                </label>

                <div v-if="source" ref="combobox" class="texteditor-preview__field texteditor-preview__field--grow">
                    <span class="texteditor-preview__label">{{ sourceLabel }}</span>
                    <div class="texteditor-preview__combobox">
                        <input
                            v-model="search"
                            type="search"
                            class="texteditor-preview__select"
                            :placeholder="selectedRecord ? selectedRecord.label : `${sourceLabel} suchen…`"
                            :aria-label="`${sourceLabel} für die Vorschau suchen`"
                            @focus="listOpen = true"
                            @input="onSearch"
                            @keydown.down.prevent="moveActive(1)"
                            @keydown.up.prevent="moveActive(-1)"
                            @keydown.enter.prevent="selectRecord(records[activeIndex])"
                            @keydown.esc="listOpen = false"
                        />
                        <div v-if="listOpen" class="texteditor-popover texteditor-preview__list" @mousedown.prevent>
                            <div v-if="loadingRecords" class="texteditor-menu-empty">Wird geladen…</div>
                            <div v-else-if="!records.length" class="texteditor-menu-empty">Keine Treffer</div>
                            <button
                                v-for="(record, index) in records"
                                v-else
                                :key="record.id"
                                type="button"
                                class="texteditor-menu-item"
                                :class="{ 'is-selected': index === activeIndex, 'is-active': record.id === recordId }"
                                @click="selectRecord(record)"
                                @mouseenter="activeIndex = index"
                            >
                                <span class="texteditor-menu-item__label">{{ record.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <toolbar-button :icon="icons.ArrowPathIcon" label="Vorschau aktualisieren" :disabled="rendering" @click="render()" />
            </template>
        </div>

        <div class="texteditor-preview__body nova-tiptap-editor form-input form-control-bordered w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
            <div v-if="rendering" class="texteditor-preview__skeleton" aria-live="polite">
                <span></span><span></span><span></span>
            </div>
            <div v-else-if="error" class="texteditor-preview__notice texteditor-preview__notice--error">{{ error }}</div>
            <template v-else-if="result">
                <p v-if="category && !sources.length && !loadingRecords" class="texteditor-preview__notice">
                    Für diese Kategorie gibt es keine Datensätze – Platzhalter bleiben in der Vorschau unverändert.
                </p>
                <p v-else-if="source && !recordId && !loadingRecords" class="texteditor-preview__notice">
                    Noch kein Datensatz „{{ sourceLabel }}“ vorhanden – Platzhalter bleiben deshalb unverändert.
                </p>
                <dl class="texteditor-preview__meta">
                    <template v-if="result.subject">
                        <dt>Betreff</dt>
                        <dd>{{ result.subject }}</dd>
                    </template>
                    <template v-if="result.recipient">
                        <dt>Empfänger:in</dt>
                        <dd>{{ result.recipient }}</dd>
                    </template>
                </dl>
                <div class="prose dark:prose-invert max-w-none" v-html="result.html"></div>
            </template>
        </div>

        <p class="texteditor__hint">Nur Ansicht – es wird nichts gespeichert oder versendet.</p>
    </div>
</template>

<script>
import { ArrowPathIcon } from "@heroicons/vue/20/solid";
import ToolbarButton from "./toolbar/ToolbarButton.vue";

/**
 * Vorschau einer Vorlage mit einem echten Datensatz (siehe PreviewsTemplates im Package, TemplatePreviewer in der App).
 * Gerendert wird der ungespeicherte Editor-Inhalt samt Betreff.
 */
export default {
    components: { ToolbarButton },

    props: {
        category: { type: String, default: null },
        getContent: { type: Function, required: true },
    },

    data() {
        return {
            sources: [],
            source: null,
            records: [],
            recordId: null,
            lastSelectedRecord: null,
            search: "",
            listOpen: false,
            activeIndex: 0,
            loadingRecords: false,
            rendering: false,
            result: null,
            error: null,
            icons: { ArrowPathIcon },
        };
    },

    computed: {
        sourceLabel() {
            return this.sources.find((option) => option.key === this.source)?.label ?? "Datensatz";
        },

        selectedRecord() {
            return this.records.find((record) => record.id === this.recordId) ?? this.lastSelectedRecord ?? null;
        },
    },

    watch: {
        category: {
            immediate: true,
            handler() {
                this.source = null;
                this.recordId = null;
                this.lastSelectedRecord = null;
                this.loadRecords(true);
            },
        },
    },

    mounted() {
        document.addEventListener("mousedown", this.onDocumentMousedown);
    },

    beforeUnmount() {
        document.removeEventListener("mousedown", this.onDocumentMousedown);
        clearTimeout(this.searchTimer);
    },

    methods: {
        async loadRecords(selectFirst = false) {
            if (!this.category) {
                this.sources = [];
                this.records = [];
                this.result = null;

                return;
            }

            this.loadingRecords = true;
            const requestId = (this.recordsRequestId = (this.recordsRequestId ?? 0) + 1);

            try {
                const { data } = await Nova.request().get("/api/templates/preview/records", {
                    params: { category: this.category, source: this.source, search: this.search || undefined },
                });

                if (requestId !== this.recordsRequestId) {
                    return;
                }

                this.sources = data.sources;
                this.source = data.source;
                this.records = data.records;
                this.activeIndex = 0;

                if (selectFirst || !this.recordId) {
                    this.recordId = data.records[0]?.id ?? null;
                    this.lastSelectedRecord = data.records[0] ?? null;
                    this.render();
                }
            } catch (error) {
                this.error = "Die Datensätze für die Vorschau konnten nicht geladen werden.";
            } finally {
                this.loadingRecords = false;
            }
        },

        onSourceChange() {
            this.recordId = null;
            this.lastSelectedRecord = null;
            this.search = "";
            this.loadRecords(true);
        },

        onSearch() {
            this.listOpen = true;
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.loadRecords(), 250);
        },

        moveActive(step) {
            if (this.records.length) {
                this.activeIndex = (this.activeIndex + step + this.records.length) % this.records.length;
            }
        },

        selectRecord(record) {
            if (!record) {
                return;
            }

            this.recordId = record.id;
            this.lastSelectedRecord = record;
            this.search = "";
            this.listOpen = false;
            this.render();
        },

        async render() {
            this.rendering = true;
            this.error = null;
            const requestId = (this.renderRequestId = (this.renderRequestId ?? 0) + 1);
            const { text, subject } = this.getContent();

            try {
                const { data } = await Nova.request().post("/api/templates/preview", {
                    category: this.category,
                    source: this.recordId ? this.source : null,
                    record: this.recordId,
                    subject,
                    text,
                });

                if (requestId === this.renderRequestId) {
                    this.result = data;
                }
            } catch (error) {
                if (requestId === this.renderRequestId) {
                    this.error = error.response?.data?.message ?? "Die Vorschau konnte nicht erzeugt werden.";
                }
            } finally {
                if (requestId === this.renderRequestId) {
                    this.rendering = false;
                }
            }
        },

        onDocumentMousedown(event) {
            if (this.listOpen && !this.$refs.combobox?.contains(event.target)) {
                this.listOpen = false;
            }
        },
    },
};
</script>
