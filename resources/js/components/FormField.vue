<template>
    <default-field
        :field="field"
        :errors="errors"
        :full-width-content="true"
        :show-help-text="showHelpText"
    >
        <template #field>
            <div style="position: relative; top: 0; left: 0">
                <div
                    class="w-full overflow-break z-10 bg-gray-100 rounded"
                    style="position: sticky; top: 0; left: 0"
                >
                    <div class="p-1 flex flex-wrap items-center">
                        <div
                            v-for="button in buttons"
                            :key="'button-' + button"
                            :class="{
                                'inline-block': button != 'br',
                            }"
                        >
                            <template v-if="button == '|'">
                                <div class="w-3">&nbsp;&nbsp;</div>
                            </template>

                            <template v-else-if="button == 'br'"> </template>
                            <template v-else-if="button == 'textAlign'">
                                <text-align-buttons
                                    :editor="editor"
                                    :mode="mode"
                                    :alignments="alignments"
                                    :alignElements="alignElements"
                                    :defaultAlignment="defaultAlignment"
                                >
                                </text-align-buttons>
                            </template>
                            <template v-else-if="button == 'heading'">
                                <heading-buttons
                                    :headingLevels="headingLevels"
                                    :mode="mode"
                                    :editor="editor"
                                >
                                </heading-buttons>
                            </template>

                            <template v-else-if="button == 'history'">
                                <history-buttons :editor="editor" :mode="mode">
                                </history-buttons>
                            </template>

                            <template v-else>
                                <normal-button
                                    :editor="editor"
                                    :button="button"
                                    :mode="mode"
                                >
                                </normal-button>
                            </template>
                        </div>
                        <div>
                            <div class="flex">
                                <Dropdown
                                    class="bg-30 hover:bg-40 mr-3 rounded"
                                    v-if="
                                        templates.length &&
                                        field.templateCategory
                                    "
                                >
                                    <DropdownTrigger class="px-3">
                                        <h3 class="flex items-center">
                                            Vorlage auswählen
                                        </h3>
                                    </DropdownTrigger>
                                    <template #menu>
                                        <DropdownMenu width="240">
                                            <ScrollWrap
                                                :height="350"
                                                class="divide-y divide-gray-100 dark:divide-gray-800 divide-solid"
                                            >
                                                <div>
                                                    <DropdownMenuItem
                                                        @click.stop="
                                                            saveTemplate()
                                                        "
                                                    >
                                                        Als Vorlage speichern
                                                    </DropdownMenuItem>
                                                </div>
                                                <div class="py-1">
                                                    <DropdownMenuItem
                                                        @click.stop="
                                                            selectTemplate(
                                                                template,
                                                                true
                                                            )
                                                        "
                                                        v-for="template in templates"
                                                        :key="template.id"
                                                    >
                                                        {{ template.name }}
                                                    </DropdownMenuItem>
                                                </div>
                                            </ScrollWrap>
                                        </DropdownMenu>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="nova-tiptap-editor mt-4 form-input form-input-bordered w-full pt-2 pb-2"
                    :style="cssProps"
                    v-show="mode == 'editor'"
                >
                    <editor-content
                        :editor="editor"
                        class="prose"
                        style="max-width: none"
                    />
                </div>
                <div
                    class="bg-primary-100 px-6 py-3 text-sm mt-3 rounded-lg"
                    v-if="field.blocks"
                >
                    <h3 class="text-gray-700 mb-1 font-bold">
                        Variable Blöcke
                    </h3>
                    <p class="mb-2">
                        Diese Blöcke können im Text platziert werden und werden
                        automatisch durch den Inhalt ersetzt. Die Blöcke können
                        mit der Backspace-Taste (Rücktaste) gelöscht werden.
                    </p>
                    <button
                        v-for="(name, key) in field.blocks"
                        v-bind:key="key"
                        @click.prevent="addElement(key)"
                        type="button"
                        class="mr-1 inline-flex items-center px-3 py-2 border border-gray-300 text-xs leading-4 font-medium rounded text-gray-700 bg-white hover:bg-primary-100 hover:text-primary-500 hover:border-primary-500"
                    >
                        {{ name }}
                    </button>
                </div>
                <div
                    v-if="field.showHelp"
                    class="bg-primary-100 px-6 py-4 text-sm"
                >
                    <p v-if="field.variables">
                        Folgende Platzhalter stehen zur Verfügung:
                        <strong
                            class="cursor-pointer hover:text-primary-500 bg-white px-2 py-1 rounded-lg"
                            @click.prevent="addVariable(name)"
                            v-for="(value, name) in field.variables"
                            v-html="'{ ' + name + ' }&nbsp;'"
                            :key="name"
                        ></strong>
                    </p>
                </div>
            </div>
        </template>
    </default-field>
</template>

<script>
import { Editor, EditorContent } from "@tiptap/vue-3";

import Text from "@tiptap/extension-text";

import Blockquote from "@tiptap/extension-blockquote";
import Bold from "@tiptap/extension-bold";
import BulletList from "@tiptap/extension-bullet-list";
import Highlight from "@tiptap/extension-highlight";
import HorizontalRule from "@tiptap/extension-horizontal-rule";
import Italic from "@tiptap/extension-italic";
import ListItem from "@tiptap/extension-list-item";
import OrderedList from "@tiptap/extension-ordered-list";
import Strike from "@tiptap/extension-strike";
import Subscript from "@tiptap/extension-subscript";
import Superscript from "@tiptap/extension-superscript";
import TextStyle from "@tiptap/extension-text-style";
import Underline from "@tiptap/extension-underline";
import TextAlign from "@tiptap/extension-text-align";
import History from "@tiptap/extension-history";
import Document from "@tiptap/extension-document";
import Heading from "@tiptap/extension-heading";
import Link from "@tiptap/extension-link";
import Paragraph from "@tiptap/extension-paragraph";
import HardBreak from "@tiptap/extension-hard-break";
import NormalButton from "./buttons/NormalButton";
import HeadingButtons from "./buttons/HeadingButtons";
import TextAlignButtons from "./buttons/TextAlignButtons";
import HistoryButtons from "./buttons/HistoryButtons";
import BaseButton from "./buttons/BaseButton.vue";
import Dropcursor from "@tiptap/extension-dropcursor";

import { FormField, HandlesValidationErrors } from "laravel-nova";

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ["resourceName", "resourceId", "field"],

    components: {
        EditorContent,
        NormalButton,
        HeadingButtons,
        TextAlignButtons,
        HistoryButtons,
        BaseButton,
    },

    data() {
        return {
            editor: null,
            mode: "editor",
            placeholder: "",
            templates: [],
        };
    },

    computed: {
        contentWithTrailingParagraph() {
            if (
                _.isString(this.value) &&
                _.endsWith(_.trim(this.value), "content-block>")
            ) {
                return this.value + "<p></p>";
            }

            return this.value;
        },
        headingLevels() {
            return [2, 3, 4];
        },
        buttons() {
            let tmpButtons = this.field.buttons
                ? this.field.buttons
                : [
                      "bold",
                      "italic",
                      "strike",
                      "|",
                      "underline",
                      "highlight",
                      "|",
                      "heading",
                      "|",
                      "bulletList",
                      "orderedList",
                      "sinkListItem",
                      "liftListItem",
                      "|",
                      "blockquote",
                      "hardBreak",
                      "textAlign",
                      "history",
                  ];

            return tmpButtons;
        },

        alignments() {
            return ["left", "center", "end", "justify"];
        },

        alignElements() {
            return ["heading", "paragraph"];
        },

        defaultAlignment() {
            return "left";
        },

        cssProps() {
            return {
                "--text-align": this.defaultAlignment,
            };
        },
        saveAsJson() {
            return this.field.saveAsJson ? this.field.saveAsJson : false;
        },
    },

    methods: {
        updateValue(value) {
            this.value = value;
        },
        selectTemplate(template, overwrite = false) {
            let element = document.querySelector("[id^='subject']");

            if (element && overwrite) {
                element.value = template.subject;
                element.dispatchEvent(new Event("input", { bubbles: true }));
            }

            this.editor.commands.setContent(template.text, true);
        },
        async saveTemplate() {
            let name = window.prompt("Name der Vorlage");

            await Nova.request().post("/api/templates", {
                category: this.field.templateCategory,
                text: this.editor.getHTML(),
                subject: document.querySelector("[id^='subject']").value,
                name,
            });

            this.fetchTemplates();
        },
        async fetchTemplates() {
            this.templates = (
                await Nova.request().get(
                    "/api/templates?category=" + this.field.templateCategory
                )
            ).data.data;
        },
        addElement(type) {
            this.editor.commands.insertContent([
                {
                    type,
                },
                {
                    type: "paragraph",
                },
            ]);
            this.editor.view.dom.focus();
        },
        addVariable(variable) {
            this.editor
                .chain()
                .insertContent("{ " + variable + " }")
                .run();
        },
    },

    async mounted() {
        await this.fetchTemplates();
        this.placeholder = this.field.placeholder
            ? this.field.placeholder
            : this.field.extraAttributes
            ? this.field.extraAttributes.placeholder
            : "";

        let extensions = [
            Dropcursor,
            Document,
            Bold,
            Italic,
            Highlight,
            Strike,
            TextStyle,
            Underline,
            Subscript,
            Superscript,
            Heading.configure({
                levels: [2, 3, 4],
            }),
            Link.configure(),
            Blockquote.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        dir: String,
                    };
                },
            }),
            BulletList.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        dir: String,
                    };
                },
            }),
            HorizontalRule,
            ListItem,
            OrderedList.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        dir: String,
                    };
                },
            }),
            HardBreak,
            Paragraph.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        dir: String,
                        data: String,
                    };
                },
            }),
            TextAlign.configure({
                types: ["heading", "paragraph"],
            }),
            History,
            Text,
            ...window.TextEditorNotes,
        ];

        const context = this;

        this.editor = new Editor({
            extensions: extensions,
            content: this.contentWithTrailingParagraph,
            onCreate() {
                try {
                    let content = JSON.parse(context.value);
                    this.commands.setContent(content);
                } catch {}
            },
            onUpdate() {
                if (context.saveAsJson) {
                    let jsonContent = this.getJSON();
                    context.updateValue(JSON.stringify(jsonContent.content));
                } else {
                    context.updateValue(this.getHTML());
                }
            },
        });

        if (this.field.selectFirstTemplate) {
            if (!this.value.length) {
                this.selectTemplate(
                    this.templates[0],
                    document.querySelector("[id^='subject']").value.length == 0
                );
            }
        }
    },

    beforeDestroy() {
        this.editor.destroy();
    },
};
</script>

<style lang="scss">
.nova-tiptap-editor {
    padding-bottom: 20px;
    padding-top: 20px;

    .ProseMirror-focused {
        outline: none;
    }

    img.ProseMirror-selectednode {
        outline: 3px solid var(--primary-30);
    }

    .ProseMirror {
        p.is-editor-empty:first-child::before {
            content: attr(data-placeholder);
            float: left;
            color: #ced4da;
            pointer-events: none;
            height: 0;
        }

        a {
            color: #0ea5e9;
            text-decoration: underline;
        }

        pre {
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 12px;
            padding-right: 12px;
            background-color: #3c4b5f;
            color: white;
            border-radius: 0.125rem;
        }

        a {
            pointer-events: none;
        }

        hr {
            border-top: 1px solid #dddddd;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .tableWrapper {
            margin-top: 15px;
            overflow-x: auto;
        }

        .resize-cursor {
            cursor: ew-resize;
            cursor: col-resize;
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
            overflow: hidden;

            td,
            th {
                min-width: 1em;
                border: 2px solid #dddddd;
                padding: 3px 5px;
                vertical-align: top;
                box-sizing: border-box;
                position: relative;

                > * {
                    margin-bottom: 0;
                }
            }

            th {
                font-weight: bold;
                text-align: left;
                background-color: #fafafa;
            }

            .selectedCell:after {
                z-index: 2;
                position: absolute;
                content: "";
                left: 0;
                right: 0;
                top: 0;
                bottom: 0;
                background: rgba(200, 200, 255, 0.4);
                pointer-events: none;
            }

            .column-resize-handle {
                position: absolute;
                right: -2px;
                top: 0;
                bottom: -2px;
                width: 4px;
                background-color: #adf;
                pointer-events: none;
            }
        }
    }
}
</style>
