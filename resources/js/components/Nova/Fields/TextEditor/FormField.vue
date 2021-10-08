<template>
    <field-wrapper>
        <a name="nova-form-text-editor"></a>
        <div class="w-full">
            <div
                class="py-6 px-8 w-full border-b border-40 flex justify-between items-center"
            >
                <h2 class="text-90 font-normal text-xl">{{ field.name }}</h2>
                <div>
                    <div class="flex">
                        <dropdown
                            class="bg-30 hover:bg-40 mr-3 rounded"
                            v-if="templates.length && field.templateCategory"
                        >
                            <dropdown-trigger class="px-3">
                                <h3
                                    slot="default"
                                    class="flex items-center font-normal text-base text-90 h-9"
                                >
                                    Vorlage auswählen
                                </h3>
                            </dropdown-trigger>

                            <dropdown-menu
                                slot="menu"
                                width="240"
                                direction="rtl"
                            >
                                <button
                                    class="w-full px-3 py-2 dim block text-base text-90 text-left no-underline leading-normal nova-router-link rounded hover:bg-50"
                                    v-for="template in templates"
                                    :key="template.id"
                                    @click.stop="selectTemplate(template)"
                                >
                                    {{ template.name }}
                                </button>
                            </dropdown-menu>
                        </dropdown>
                        <a
                            v-if="resourceId && field.previewUrl"
                            @click="onPreview()"
                            class="cursor-pointer text-80 hover:text-primary mr-3 inline-flex items-center has-tooltip nova-router-link ml-2"
                            data-testid="projects-items-0-view-button"
                            dusk="404-view-button"
                            data-original-title="null"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="22"
                                height="18"
                                viewBox="0 0 22 16"
                                aria-labelledby="view"
                                role="presentation"
                                class="fill-current nova-icon"
                            >
                                <path
                                    d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"
                                />
                            </svg>
                            <span class="inline-block ml-1">Vorschau</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="editor">
                <div
                    class="suggestion-list"
                    v-show="suggestionRange"
                    ref="suggestions"
                >
                    <template v-if="hasResults">
                        <div
                            v-for="(template, index) in filteredTemplates.slice(
                                0,
                                5
                            )"
                            :key="template.id"
                            class="suggestion-list__item"
                            :class="{
                                'is-selected': navigatedTemplateIndex === index
                            }"
                            @click="replaceTemplate(template)"
                        >
                            {{ template.item.text }}
                        </div>
                    </template>
                    <div
                        v-else-if="!query"
                        class="suggestion-list__item is-empty"
                    >
                        Bitte geben Sie ein Suchwort ein
                    </div>
                    <div v-else class="suggestion-list__item is-empty">
                        Keine Textbausteine gefunden
                    </div>
                </div>

                <div class="document">
                    <editor-menu-bar
                        :editor="editor"
                        v-slot="{ commands, isActive, focused }"
                    >
                        <div class="flex editor-menu-bar">
                            <button
                                :class="{
                                    'btn-primary': isActive.bold(),
                                    'btn-white': !isActive.bold()
                                }"
                                @click.prevent.stop="commands.bold"
                            >
                                <i class="far fa-bold"></i>
                            </button>

                            <button
                                :class="{
                                    'btn-primary': isActive.italic(),
                                    'btn-white': !isActive.italic()
                                }"
                                @click.prevent.stop="commands.italic"
                            >
                                <i class="far fa-italic"></i>
                            </button>

                            <button
                                :class="{
                                    'btn-primary': isActive.strike(),
                                    'btn-white': !isActive.strike()
                                }"
                                @click.prevent.stop="commands.strike"
                            >
                                <i class="far fa-strikethrough"></i>
                            </button>

                            <button
                                :class="{
                                    'btn-primary': isActive.underline(),
                                    'btn-white': !isActive.underline()
                                }"
                                @click.prevent.stop="commands.underline"
                            >
                                <i class="far fa-underline"></i>
                            </button>
                            <button
                                :class="{
                                    'btn-primary': isActive.highlight(),
                                    'btn-white': !isActive.highlight()
                                }"
                                @click.prevent.stop="commands.highlight"
                            >
                                <i class="far fa-highlighter"></i>
                            </button>

                            <div class="ml-3 border-l border-60">
                                <button
                                    :class="{
                                        'btn-primary': isActive.paragraph(),
                                        'btn-white': !isActive.paragraph()
                                    }"
                                    @click.prevent.stop="
                                        commands.paragraph({
                                            textAlign: 'left'
                                        })
                                    "
                                >
                                    <i class="far fa-paragraph"></i>
                                </button>

                                <button
                                    :class="{
                                        'btn-primary': isActive.heading({
                                            level: 2
                                        }),
                                        'btn-white': !isActive.heading({
                                            level: 2
                                        })
                                    }"
                                    @click.prevent.stop="
                                        commands.heading({ level: 2 })
                                    "
                                >
                                    <i class="far fa-h2"></i>
                                </button>

                                <button
                                    :class="{
                                        'btn-primary': isActive.heading({
                                            level: 3
                                        }),
                                        'btn-white': !isActive.heading({
                                            level: 3
                                        })
                                    }"
                                    @click.prevent.stop="
                                        commands.heading({ level: 3 })
                                    "
                                >
                                    <i class="far fa-h3"></i>
                                </button>
                            </div>

                            <button
                                :class="{
                                    'btn-primary': isActive.bullet_list(),
                                    'btn-white': !isActive.bullet_list()
                                }"
                                @click.prevent.stop="commands.bullet_list"
                            >
                                <i class="far list-ul"></i>
                            </button>

                            <button
                                :class="{
                                    'btn-primary': isActive.ordered_list(),
                                    'btn-white': !isActive.ordered_list()
                                }"
                                @click.prevent.stop="commands.ordered_list"
                            >
                                <i class="far list-ol"></i>
                            </button>
                        </div>
                    </editor-menu-bar>
                    <div class="page">
                        <div class="inner">
                            <editor-content
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                class="tiptap-content h-auto w-full"
                                :editor="editor"
                            />
                        </div>
                        <div
                            class="bg-info-light px-6 py-4 text-sm border-t border-info"
                            v-if="field.blocks"
                        >
                            <h3 class="text-info-dark mb-3">Variable Blöcke</h3>
                            <p class="mb-2">
                                Diese Blöcke können im Text platziert werden und
                                werden beim drucken automatisch durch den Inhalt
                                ersetzt.
                            </p>
                            <button
                                v-for="(name, key) in field.blocks"
                                v-bind:key="key"
                                @click="addElement(key)"
                                type="button"
                                class="mr-1 inline-flex items-center px-3 py-2 border border-dashed border-80 text-xs leading-4 font-medium rounded text-90 bg-white hover:text-primary hover:border-primary"
                            >
                                {{ name }}
                            </button>
                        </div>
                        <div
                            v-if="field.showHelp"
                            class="bg-info-light px-6 py-4 text-sm border-t border-info"
                        >
                            <h3 class="text-info-dark mb-3">Hilfe</h3>
                            <p class="mb-2">
                                Textbausteine können per
                                <strong>#Kurzwort</strong>
                                <i>(Beispiel #21)</i> eingesetzt werden oder mit
                                <strong>@Suchwort</strong>
                                gesucht werden.
                            </p>
                            <p class="mb-2">
                                Änderungen können per Tastenkürze
                                <i>Strg+Z</i>
                                <strong>Rückgängig</strong> gemacht werden.
                            </p>
                            <p class>
                                Folgende Platzhalter stehen zur Verfügung:
                                <strong
                                    v-for="variable in field.variables"
                                    v-html="'{{ ' + variable + ' }}&nbsp;'"
                                    :key="variable"
                                ></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </field-wrapper>
</template>

<script>
import { FormField, HandlesValidationErrors } from "laravel-nova";
import { Editor, EditorContent, EditorMenuBar } from "tiptap";
import {
    Blockquote,
    BulletList,
    CodeBlock,
    HardBreak,
    Heading,
    ListItem,
    OrderedList,
    TodoItem,
    TodoList,
    Bold,
    Code,
    Italic,
    Link,
    Strike,
    Underline,
    History,
    Mention,
    Placeholder,
    HorizontalRule,
    TrailingNode,
    Focus
} from "tiptap-extensions";
import "tippy.js/dist/svg-arrow.css";
import tippy, { roundArrow, sticky } from "tippy.js";
import Fuse from "fuse.js";
import { mapState } from "vuex";
import Template from "./Template";
import Signature from "./Signature";
import Highlight from "./Highlight";
import Paragraph from "./Paragraph";
import PageBreak from "./PageBreak";
import Salutation from "./Salutation";

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ["resourceName", "resourceId", "field"],
    components: {
        EditorContent,
        EditorMenuBar
    },
    data() {
        return {
            editor: null,
            query: null,
            suggestionRange: null,
            filteredTemplates: [],
            navigatedTemplateIndex: 0,
            insertMention: () => {},
            observer: null
        };
    },
    mounted() {
        this.$store.dispatch("text-editor/getTemplates", {
            category: this.field.templateCategory
        });
        this.$store.dispatch("text-editor/getTextBlocks");
    },
    beforeDestroy() {
        this.editor.destroy();
    },
    computed: {
        ...mapState("text-editor", ["templates", "textBlocks"]),

        hasResults() {
            return this.filteredTemplates.length;
        },
        showSuggestions() {
            return this.query || this.hasResults;
        }
    },
    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.editor = new Editor({
                disableInputRules: true,
                disablePasteRules: true,
                excludedExtensions: false,
                extensions: [
                    new Paragraph(),
                    new Highlight(),
                    new Blockquote(),
                    new BulletList(),
                    new CodeBlock(),
                    new HardBreak(),
                    new Heading({ levels: [2, 3] }),
                    new ListItem(),
                    new OrderedList(),
                    new Link(),
                    new Bold(),
                    new Italic(),
                    new Strike(),
                    new Underline(),
                    new History(),
                    new Placeholder({
                        emptyNodeClass: "is-empty",
                        emptyNodeText: this.field.name + " bearbeiten",
                        showOnlyWhenEditable: false,
                        showOnlyCurrent: true
                    }),
                    new TrailingNode({
                        node: "paragraph",
                        notAfter: ["paragraph"]
                    }),
                    new Focus({
                        className: "has-focus",
                        nested: false
                    }),
                    new Signature(),
                    new Salutation(),
                    new HorizontalRule(),
                    new PageBreak(),
                    new Template({
                        matcher: {
                            char: "#",
                            allowSpaces: false,
                            startOfLine: false
                        },
                        onExit: event => {
                            const template = this.textBlocks.find(
                                item => item.shortcode === event.query
                            );

                            if (!template) {
                                return;
                            }
                            event.command({
                                range: event.range,
                                attrs: {
                                    value: template.text
                                }
                            });
                        }
                    }),
                    new Template({
                        items: () => this.textBlocks,
                        matcher: {
                            char: "@",
                            allowSpaces: true,
                            startOfLine: false
                        },
                        onEnter: ({
                            items,
                            query,
                            range,
                            command,
                            virtualNode
                        }) => {
                            this.query = query;
                            this.filteredTemplates = items;
                            this.suggestionRange = range;
                            this.renderPopup(virtualNode);
                            this.insertMention = command;
                        },
                        onChange: ({ items, query, range, virtualNode }) => {
                            this.query = query;
                            this.filteredTemplates = items;
                            this.suggestionRange = range;
                            this.navigatedTemplateIndex = 0;
                            this.renderPopup(virtualNode);
                        },
                        onExit: () => {
                            this.query = null;
                            this.filteredUsers = [];
                            this.suggestionRange = null;
                            this.navigatedTemplateIndex = 0;
                            this.destroyPopup();
                        },
                        onFilter: (items, query) => {
                            const fuse = new Fuse(items, {
                                shouldSort: true,
                                tokenize: true,
                                threshold: 0.3,
                                location: 0,
                                distance: 100,
                                maxPatternLength: 32,
                                minMatchCharLength: 1,
                                keys: ["text", "name"]
                            });
                            return fuse.search(query);
                        },
                        onKeyDown: ({ event }) => {
                            if (event.keyCode === 38) {
                                this.upHandler();
                                return true;
                            }
                            if (event.keyCode === 40) {
                                this.downHandler();
                                return true;
                            }
                            if (event.keyCode === 13) {
                                this.enterHandler();
                                return true;
                            }
                            return false;
                        }
                    })
                ]
            });
            this.editor.setContent(
                this.field.value || this.field.defaultValue || "<p></p>",
                true
            );
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            // fill empty paragraphs
            let json = this.editor.getJSON();
            formData.append(this.field.attribute, JSON.stringify(json));
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value;
        },

        selectTemplate(template) {
            this.editor.setContent(template.text);
        },
        addElement(type) {
            const doc = this.editor.getJSON();
            doc.content.push({
                type
            });
            this.editor.setContent(doc);
        },

        upHandler() {
            this.navigatedTemplateIndex =
                (this.navigatedTemplateIndex +
                    this.filteredTemplates.length -
                    1) %
                this.filteredTemplates.length;
        },
        downHandler() {
            this.navigatedTemplateIndex =
                (this.navigatedTemplateIndex + 1) %
                this.filteredTemplates.length;
        },
        enterHandler() {
            const template = this.filteredTemplates[
                this.navigatedTemplateIndex
            ];
            if (template) {
                this.replaceTemplate(template);
            }
        },
        replaceTemplate(template) {
            this.insertMention({
                range: this.suggestionRange,
                attrs: {
                    value: template.item.text
                }
            });
            this.editor.focus();
        },
        renderPopup(node) {
            if (this.popup) {
                return;
            }
            // ref: https://atomiks.github.io/tippyjs/v6/all-props/
            this.popup = tippy(".page", {
                getReferenceClientRect: node.getBoundingClientRect,
                appendTo: () => document.body,
                interactive: true,
                arrow: roundArrow,
                sticky: true, // make sure position of tippy is updated when content changes
                plugins: [sticky],
                content: this.$refs.suggestions,
                trigger: "mouseenter", // manual
                showOnCreate: true,
                theme: "dark",
                placement: "top-start",
                inertia: true,
                duration: [400, 200]
            });
        },
        destroyPopup() {
            if (this.popup) {
                this.popup[0].destroy();
                this.popup = null;
            }
        },
        async onPreview() {
            const updateForm = this.$parent.$parent.$parent.$parent;
            await updateForm.submitViaUpdateResourceAndContinueEditing();
            let win = window.open(
                this.field.previewUrl + this.resourceId,
                "preview"
            );
            win.focus();
            setTimeout(() => {
                document
                    .querySelector("a[name='nova-form-text-editor']")
                    ?.scrollIntoView();
            }, 500);
        }
    }
};
</script>

<style lang="scss">
.page-break {
    display: flex;
    align-items: center;
    text-align: center;
    font-weight: 800;
    font-size: 0.75rem;
    color: var(--primary);
    text-transform: uppercase;
    padding: 5px 0px;
}
.page-break,
.salutation,
.signature {
    &.has-focus {
        border-radius: 3px;
        box-shadow: 0 0 0 3px #3ea4ffe6;
    }
}

.page-break::before,
.page-break::after {
    content: "";
    flex: 1;
    border-bottom: 2px dashed var(--60);
}
.page-break::before {
    margin-right: 1em;
}
.page-break::after {
    margin-left: 1em;
}

.editor-menu-bar {
    margin: 0px auto;
    background: #fff;
    padding-left: 1rem;
    padding-right: 1rem;
    button {
        padding: 0.75rem 1rem;
    }
}
p.is-empty:first-child::before {
    content: attr(data-empty-text);
    float: left;
    color: #aaa;
    pointer-events: none;
    height: 0;
    font-style: italic;
}

.page {
    background: white;
    display: block;
    display: flex;
    flex-direction: column;

    page-break-after: always;
}

.btn-default {
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2);
}
.page .inner {
    outline: none !important;
    box-shadow: none !important;
    padding-left: 2rem;
    padding-right: 2rem;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.ProseMirror {
    white-space: pre-wrap;
    &:focus {
        outline: none;
    }
}
.ProseMirror .ProseMirror ins {
    text-decoration: none;
    background-color: highlight;
}
.ProseMirror p,
.ProseMirror ul,
.ProseMirror ol,
.ProseMirror blockquote,
.ProseMirror pre {
    margin-bottom: 1.25rem;
}

.ProseMirror ul,
.ProseMirror ol {
    list-style-position: outside;
    padding: 0px 0px 0px 20px;
    margin-bottom: 0.75rem;
}

.ProseMirror pre {
    white-space: pre-line;
}
.ProseMirror hr {
    border-top: 3px dashed #ccc;
}

.ProseMirror p:last-child {
    margin-bottom: 0;
}

.header {
    background-color: var(--30);
    font-weight: 800;
    font-size: 0.75rem;
    color: var(--80);
    text-transform: uppercase;
    border-bottom-width: 2px;
    border-color: var(--50);
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    letter-spacing: 0.05em;
    vertical-align: middle;
}
.template {
    font-weight: 300;
    color: var(--90);
    border-top-width: 1px;
    border-bottom-width: 1px;
    border-color: var(--50);
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    vertical-align: middle;
}
.template:hover {
    background-color: var(--30);
    cursor: pointer;
}

.suggestion-list {
    padding: 0.2rem;
    border: 2px solid rgba(#000, 0.1);
    font-size: 0.8rem;
    font-weight: bold;
    &__no-results {
        padding: 0.2rem 0.5rem;
    }
    &__item {
        border-radius: 5px;
        padding: 0.2rem 0.5rem;
        margin-bottom: 0.2rem;
        cursor: pointer;
        &:last-child {
            margin-bottom: 0;
        }
        &.is-selected,
        &:hover {
            background-color: rgba(#fff, 0.2);
        }
        &.is-empty {
            opacity: 0.5;
        }
    }
}
.tippy-tooltip.dark-theme {
    background-color: #000;
    padding: 0;
    font-size: 1rem;
    text-align: inherit;
    color: #fff;
    border-radius: 5px;
    .tippy-backdrop {
        display: none;
    }
    .tippy-roundarrow {
        fill: #000;
    }
    .tippy-popper[x-placement^="top"] & .tippy-arrow {
        border-top-color: #000;
    }
    .tippy-popper[x-placement^="bottom"] & .tippy-arrow {
        border-bottom-color: #000;
    }
    .tippy-popper[x-placement^="left"] & .tippy-arrow {
        border-left-color: #000;
    }
    .tippy-popper[x-placement^="right"] & .tippy-arrow {
        border-right-color: #000;
    }
}
</style>
