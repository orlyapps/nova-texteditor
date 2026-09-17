<template>
    <default-field :field="field" :errors="errors" :full-width-content="true" :show-help-text="showHelpText">
        <template #field>
            <a name="nova-form-text-editor"></a>
            <div class="texteditor">
                <toolbar
                    v-if="editor"
                    class="texteditor__toolbar"
                    :editor="editor"
                    :buttons="field.buttons"
                    :disabled="previewing"
                    @edit-link="$refs.bubble?.startLinkEditing()"
                >
                    <template #end="{ compact }">
                        <toolbar-button
                            v-if="field.templatePreview"
                            :icon="previewing ? icons.PencilSquareIcon : icons.EyeIcon"
                            :label="previewing ? 'Bearbeiten' : 'Vorschau'"
                            :tooltip="previewing ? 'Zurück zum Bearbeiten' : 'Vorlage mit echten Daten ansehen'"
                            :active="previewing"
                            :show-label="!compact"
                            @click="previewing = !previewing"
                        />

                        <template v-if="!previewing">
                        <toolbar-menu
                            v-if="blockOptions.length"
                            :icon="icons.Squares2X2Icon"
                            label="Block"
                            title="Variablen Block einfügen"
                            :show-label="!compact"
                        >
                            <template #default="{ close }">
                                <div class="texteditor-menu-hint">Blöcke werden beim Versand automatisch durch den Inhalt ersetzt.</div>
                                <button v-for="block in blockOptions" :key="block.type" type="button" class="texteditor-menu-item" @click="addElement(block.type); close()">
                                    <squares-2-x-2-icon class="texteditor-menu-item__icon" />
                                    <span class="texteditor-menu-item__label">{{ block.label }}</span>
                                </button>
                            </template>
                        </toolbar-menu>

                        <toolbar-menu
                            v-if="variableNames.length || field.variableCatalog"
                            :icon="icons.VariableIcon"
                            label="Platzhalter"
                            title="Platzhalter einfügen"
                            :show-label="!compact"
                            popover-class="texteditor-variables"
                        >
                            <template #default="{ close }">
                                <div class="texteditor-menu-hint">{{ variableMenuHint }}</div>
                                <template v-for="group in variableGroups" :key="group.label ?? 'variables'">
                                    <div v-if="group.label" class="texteditor-menu-heading">{{ group.label }}</div>
                                    <button
                                        v-for="name in group.variables"
                                        :key="`${group.label}-${name}`"
                                        type="button"
                                        class="texteditor-menu-item"
                                        @click="addVariable(name); close()"
                                    >
                                        <span class="texteditor-menu-item__text">
                                            <span class="texteditor-menu-item__label"><code>{ {{ name }} }</code></span>
                                            <span v-if="variablePreview(name)" class="texteditor-menu-item__subtitle">{{ variablePreview(name) }}</span>
                                        </span>
                                    </button>
                                </template>
                            </template>
                        </toolbar-menu>

                        <template v-if="field.templateCategory">
                            <template-picker
                                :templates="templates"
                                :category-labels="categoryLabels"
                                :can-create="canCreateTemplates"
                                :loading="loadingTemplates"
                                :compact="compact"
                                @open="fetchTemplates"
                                @select="selectTemplate($event, field.syncTemplateSubject !== false)"
                                @save-new="openDialog('save')"
                            />

                            <div v-if="loadedTemplate" class="texteditor-loaded" :class="{ 'is-dirty': templateDirty }">
                                <span
                                    class="texteditor-loaded__name"
                                    v-tooltip="templateDirty ? `„${loadedTemplate.name}“ – Text wurde seit dem Laden geändert` : `Geladene Vorlage: ${loadedTemplate.name}`"
                                >
                                    <span v-if="templateDirty" class="texteditor-loaded__dot" aria-hidden="true"></span>
                                    {{ loadedTemplate.name }}
                                </span>

                                <button
                                    v-if="loadedTemplate.can_update"
                                    type="button"
                                    class="texteditor-loaded__action"
                                    v-tooltip="`Aktuellen Text${syncsSubject ? ' und Betreff' : ''} in „${loadedTemplate.name}“ übernehmen`"
                                    @mousedown.prevent
                                    @click="openDialog('overwrite')"
                                >
                                    <arrow-down-on-square-icon class="texteditor-loaded__action-icon" />
                                    <span>Überschreiben</span>
                                </button>
                                <button
                                    v-else-if="canCreateTemplates"
                                    type="button"
                                    class="texteditor-loaded__action"
                                    v-tooltip="'Aktuellen Text als neue Vorlage speichern'"
                                    @mousedown.prevent
                                    @click="openDialog('save')"
                                >
                                    <plus-icon class="texteditor-loaded__action-icon" />
                                    <span>Als neue speichern</span>
                                </button>

                                <toolbar-menu
                                    v-if="loadedTemplate.can_update && canCreateTemplates"
                                    :icon="icons.ChevronDownIcon"
                                    label="Weitere Speicheroptionen"
                                    placement="bottom-end"
                                >
                                    <template #default="{ close }">
                                        <button type="button" class="texteditor-menu-item" @click="openDialog('save'); close()">
                                            <plus-icon class="texteditor-menu-item__icon" />
                                            <span class="texteditor-menu-item__label">Als neue Vorlage speichern…</span>
                                        </button>
                                    </template>
                                </toolbar-menu>

                                <toolbar-button
                                    :icon="icons.XMarkIcon"
                                    label="Vorlage schließen"
                                    tooltip="Verknüpfung zur Vorlage lösen – dein Text bleibt erhalten"
                                    @click="loadedTemplate = null"
                                />
                            </div>
                        </template>

                        </template>

                        <toolbar-button
                            v-if="resourceId && field.previewUrl"
                            :icon="icons.EyeIcon"
                            label="Vorschau"
                            :show-label="!compact"
                            @click="onPreview($event)"
                        />
                    </template>
                </toolbar>

                <template-preview v-if="previewing" :category="previewCategory" :get-content="previewContent" />

                <div v-show="!previewing" class="nova-tiptap-editor texteditor__content form-input form-control-bordered w-full dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700" :style="cssProps">
                    <editor-content :editor="editor" class="prose" style="max-width: none" />
                </div>

                <p v-if="editor && slashHint && !previewing" class="texteditor__hint">
                    Tipp: Tippe <kbd>/</kbd> für {{ slashHint }}.
                </p>

                <editor-bubble-menu v-if="editor" ref="bubble" :editor="editor" />
            </div>

            <Modal :show="dialog === 'replace'" size="md" role="alertdialog" @close-via-escape="closeDialog">
                <form class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" @submit.prevent="confirmReplace">
                    <ModalHeader>Aktuellen Text ersetzen?</ModalHeader>
                    <ModalContent>
                        <p class="leading-normal">
                            Der Text im Editor wurde bearbeitet. Wenn Du „{{ pendingTemplate?.template.name }}“ lädst, werden diese Änderungen ersetzt.
                        </p>
                    </ModalContent>
                    <ModalFooter>
                        <div class="ml-auto flex items-center gap-3">
                            <Button variant="link" state="mellow" label="Abbrechen" @click.prevent="closeDialog" />
                            <Button type="submit" label="Vorlage laden" />
                        </div>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal :show="dialog === 'save'" size="md" @close-via-escape="closeDialog">
                <form class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" @submit.prevent="saveNewTemplate">
                    <ModalHeader>Als neue Vorlage speichern</ModalHeader>
                    <ModalContent>
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-1 text-sm font-semibold" for="texteditor-template-name">Name der Vorlage</label>
                                <input
                                    id="texteditor-template-name"
                                    ref="templateName"
                                    v-model="templateForm.name"
                                    type="text"
                                    maxlength="255"
                                    required
                                    class="w-full form-control form-input form-control-bordered"
                                    placeholder="z. B. Kursbestätigung Welpen"
                                />
                            </div>
                            <div v-if="categoryList.length > 1">
                                <label class="block mb-1 text-sm font-semibold" for="texteditor-template-category">Kategorie</label>
                                <select id="texteditor-template-category" v-model="templateForm.category" class="w-full form-control form-select form-control-bordered">
                                    <option v-for="category in categoryList" :key="category" :value="category">{{ categoryLabel(category) }}</option>
                                </select>
                            </div>
                            <p v-else class="text-sm text-gray-500">Kategorie: {{ categoryLabel(categoryList[0]) }}</p>
                            <p class="text-sm text-gray-500">
                                Gespeichert wird der aktuelle Text{{ syncsSubject ? " inklusive Betreff" : "" }}.
                            </p>
                            <p v-if="dialogError" class="text-sm text-red-500">{{ dialogError }}</p>
                        </div>
                    </ModalContent>
                    <ModalFooter>
                        <div class="ml-auto flex items-center gap-3">
                            <Button variant="link" state="mellow" label="Abbrechen" @click.prevent="closeDialog" />
                            <Button type="submit" label="Vorlage speichern" :loading="savingTemplate" :disabled="!templateForm.name.trim()" />
                        </div>
                    </ModalFooter>
                </form>
            </Modal>

            <Modal :show="dialog === 'overwrite'" size="md" role="alertdialog" @close-via-escape="closeDialog">
                <form class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" @submit.prevent="overwriteTemplate">
                    <ModalHeader>Vorlage überschreiben?</ModalHeader>
                    <ModalContent>
                        <p class="leading-normal">
                            „{{ loadedTemplate?.name }}“ ({{ loadedTemplate?.category_label }}) wird mit dem aktuellen Text{{ syncsSubject ? " und Betreff" : "" }} ersetzt.
                            Das betrifft alle, die diese Vorlage künftig verwenden.
                        </p>
                        <p v-if="dialogError" class="mt-3 text-sm text-red-500">{{ dialogError }}</p>
                    </ModalContent>
                    <ModalFooter>
                        <div class="ml-auto flex items-center gap-3">
                            <Button variant="link" state="mellow" label="Abbrechen" @click.prevent="closeDialog" />
                            <Button type="submit" state="danger" label="Überschreiben" :loading="savingTemplate" />
                        </div>
                    </ModalFooter>
                </form>
            </Modal>
        </template>
    </default-field>
</template>

<script>
    import { Editor, EditorContent } from "@tiptap/vue-3";
    import { Text } from "@tiptap/extension-text";
    import { Blockquote } from "@tiptap/extension-blockquote";
    import { Bold } from "@tiptap/extension-bold";
    import { BulletList, ListItem, OrderedList } from "@tiptap/extension-list";
    import { Highlight } from "@tiptap/extension-highlight";
    import { HorizontalRule } from "@tiptap/extension-horizontal-rule";
    import { Italic } from "@tiptap/extension-italic";
    import { Strike } from "@tiptap/extension-strike";
    import { Subscript } from "@tiptap/extension-subscript";
    import { Superscript } from "@tiptap/extension-superscript";
    import { FontSize, TextStyle } from "@tiptap/extension-text-style";
    import { Underline } from "@tiptap/extension-underline";
    import { TextAlign } from "@tiptap/extension-text-align";
    import { Document } from "@tiptap/extension-document";
    import { Heading } from "@tiptap/extension-heading";
    import { Paragraph } from "@tiptap/extension-paragraph";
    import { CodeBlock } from "@tiptap/extension-code-block";
    import { HardBreak } from "@tiptap/extension-hard-break";
    import { Dropcursor, Gapcursor, UndoRedo } from "@tiptap/extensions";
    import {
        ArrowDownOnSquareIcon,
        ChevronDownIcon,
        ChatBubbleBottomCenterTextIcon,
        EyeIcon,
        ListBulletIcon,
        PencilSquareIcon,
        MinusIcon,
        NumberedListIcon,
        PlusIcon,
        Squares2X2Icon,
        VariableIcon,
        XMarkIcon,
    } from "@heroicons/vue/20/solid";
    import { Button } from "laravel-nova-ui";
    import { FormField, HandlesValidationErrors } from "laravel-nova";
    import Link from "../extensions/link";
    import SlashCommand from "../extensions/slash-command";
    import VariableHighlight from "../extensions/variable-highlight";
    import { resolveCustomExtensions } from "../tiptap";
    import EditorBubbleMenu from "./EditorBubbleMenu.vue";
    import TemplatePicker from "./TemplatePicker.vue";
    import TemplatePreview from "./TemplatePreview.vue";
    import Toolbar from "./toolbar/Toolbar.vue";
    import ToolbarButton from "./toolbar/ToolbarButton.vue";
    import ToolbarMenu from "./toolbar/ToolbarMenu.vue";

    const withDirAttribute = (extension) =>
        extension.extend({
            addAttributes() {
                return {
                    ...this.parent?.(),
                    dir: String,
                };
            },
        });

    export default {
        mixins: [FormField, HandlesValidationErrors],
        props: ["resourceName", "resourceId", "field"],

        components: {
            ArrowDownOnSquareIcon,
            Button,
            EditorBubbleMenu,
            EditorContent,
            PlusIcon,
            Squares2X2Icon,
            TemplatePicker,
            TemplatePreview,
            Toolbar,
            ToolbarButton,
            ToolbarMenu,
        },

        data() {
            return {
                editor: null,
                templates: [],
                templateCategories: {},
                canCreateTemplates: false,
                loadingTemplates: false,
                loadedTemplate: null,
                pendingTemplate: null,
                lastAppliedHtml: "",
                textTemplates: [],
                dialog: null,
                dialogError: null,
                savingTemplate: false,
                templateForm: { name: "", category: null },
                icons: { ChevronDownIcon, EyeIcon, PencilSquareIcon, Squares2X2Icon, VariableIcon, XMarkIcon },
                previewing: false,
                templateDirty: false,
                catalogValue: this.field.variableCatalog?.current ?? null,
            };
        },

        computed: {
            contentWithTrailingParagraph() {
                if (_.isString(this.value) && _.endsWith(_.trim(this.value), "content-block>")) {
                    return this.value + "<p></p>";
                }

                return this.value;
            },

            cssProps() {
                return {
                    "--text-align": "left",
                };
            },

            saveAsJson() {
                return this.field.saveAsJson ? this.field.saveAsJson : false;
            },

            syncsSubject() {
                return this.field.syncTemplateSubject !== false && !!this.subjectInput();
            },

            categoryList() {
                return String(this.field.templateCategory ?? "")
                    .split(",")
                    .map((category) => category.trim())
                    .filter(Boolean);
            },

            /**
             * Bezeichnungen der Kategorien des Feldes; `null`, wenn keine übersetzt ist (nie den rohen Schlüssel zeigen).
             */
            categoryLabels() {
                return this.categoryList.map((category) => this.templateCategories[category]).filter(Boolean).join(" / ") || null;
            },

            matchingTemplates() {
                return this.templates.filter((template) => template.matches);
            },

            /**
             * Nur Blöcke, deren Node die App auch registriert hat — sonst würde insertContent still scheitern.
             */
            blockOptions() {
                if (!this.editor || !this.field.blocks) {
                    return [];
                }

                return Object.entries(this.field.blocks)
                    .filter(([type]) => this.editor.schema.nodes[type])
                    .map(([type, label]) => ({ type, label }));
            },

            /**
             * Platzhalter-Gruppen: aus dem Katalog passend zum abhängigen Feld (z. B. Vorlagen-Kategorie),
             * sonst die konkreten Variablen des Datensatzes als eine Gruppe.
             *
             * @returns {Array<{label: ?string, variables: string[], conditional?: boolean}>}
             */
            variableGroups() {
                const catalog = this.field.variableCatalog;

                if (catalog) {
                    return catalog.groups?.[this.catalogValue] ?? [];
                }

                const names = Object.keys(this.field.variables ?? {});

                return names.length ? [{ label: null, variables: names }] : [];
            },

            /**
             * Kategorie der Vorschau: die im Formular gewählte (Vorlagen-Verwaltung) oder die erste des Feldes.
             */
            previewCategory() {
                return this.field.variableCatalog ? this.catalogValue : this.categoryList[0] ?? null;
            },

            variableNames() {
                return [...new Set(this.variableGroups.flatMap((group) => group.variables))];
            },

            variableMenuHint() {
                if (this.field.variableCatalog && !this.catalogValue) {
                    return "Wähle zuerst eine Kategorie, um die passenden Platzhalter zu sehen.";
                }

                if (!this.variableGroups.length) {
                    return "Für diese Kategorie gibt es keine Platzhalter.";
                }

                if (this.variableGroups.some((group) => group.conditional)) {
                    return "Ersetzt werden nur die Platzhalter des Bereichs, mit dem der Text verknüpft ist – z. B. Rechnungs-Platzhalter nur bei einer Rechnung.";
                }

                return "Platzhalter werden beim Speichern bzw. Versand ersetzt.";
            },

            slashHint() {
                const parts = ["Formate"];

                if (this.blockOptions.length) {
                    parts.push("Blöcke");
                }

                if (this.variableNames.length) {
                    parts.push("Platzhalter");
                }

                if (this.field.withTextTemplates) {
                    parts.push("Textbausteine");
                }

                return parts.length > 1 ? parts.join(", ").replace(/, ([^,]*)$/, " und $1") : null;
            },
        },

        watch: {
            variableNames() {
                this.editor?.commands.refreshVariableHighlight();
            },
        },

        methods: {
            updateValue(value) {
                this.value = value;
            },

            setInitialValue() {
                this.value = !(this.field.value === undefined || this.field.value === null) ? this.field.value : this.fieldDefaultValue();
            },

            fieldDefaultValue() {
                return this.field.defaultValue ?? "";
            },

            fill(formData) {
                if (this.saveAsJson) {
                    formData.append(this.fieldAttribute, JSON.stringify(this.editor.getJSON()));
                } else {
                    formData.append(this.fieldAttribute, String(this.value));
                }
            },

            previewContent() {
                return { text: this.editor.getHTML(), subject: this.subjectInput()?.value ?? null };
            },

            subjectInput() {
                return document.querySelector("[id^='subject']");
            },

            categoryLabel(category) {
                return this.templateCategories[category] ?? "Diese Stelle";
            },

            /**
             * Lädt eine Vorlage — fragt vorher nach, wenn der aktuelle Text seit dem letzten Laden bearbeitet wurde.
             */
            selectTemplate(template, overwriteSubject = false) {
                if (this.isEditedSinceLastApply()) {
                    this.pendingTemplate = { template, overwriteSubject };
                    this.openDialog("replace");

                    return;
                }

                this.applyTemplate(template, overwriteSubject);
            },

            confirmReplace() {
                const { template, overwriteSubject } = this.pendingTemplate;

                this.closeDialog();
                this.applyTemplate(template, overwriteSubject);
            },

            applyTemplate(template, overwriteSubject = false) {
                const element = this.subjectInput();

                if (element && overwriteSubject) {
                    element.value = template.subject ?? "";
                    element.dispatchEvent(new Event("input", { bubbles: true }));
                }

                this.editor.commands.setContent(template.text, { emitUpdate: true });
                this.loadedTemplate = template;
                this.lastAppliedHtml = this.editor.getHTML();
                this.templateDirty = false;
            },

            isEditedSinceLastApply() {
                return !!this.editor && !this.editor.isEmpty && this.editor.getHTML() !== this.lastAppliedHtml;
            },

            openDialog(dialog) {
                this.dialog = dialog;
                this.dialogError = null;

                if (dialog === "save") {
                    this.templateForm = { name: "", category: this.loadedTemplate?.matches ? this.loadedTemplate.category : this.categoryList[0] };
                    this.$nextTick(() => this.$refs.templateName?.focus());
                }
            },

            closeDialog() {
                this.dialog = null;
                this.dialogError = null;
                this.pendingTemplate = null;
            },

            async saveNewTemplate() {
                await this.persistTemplate(async () => {
                    const { data } = await Nova.request().post("/api/templates", {
                        category: this.templateForm.category,
                        name: this.templateForm.name.trim(),
                        text: this.editor.getHTML(),
                        subject: this.syncsSubject ? this.subjectInput()?.value : null,
                    });

                    return data.data;
                }, "gespeichert");
            },

            async overwriteTemplate() {
                await this.persistTemplate(async () => {
                    const payload = { text: this.editor.getHTML() };

                    if (this.syncsSubject) {
                        payload.subject = this.subjectInput()?.value ?? null;
                    }

                    const { data } = await Nova.request().put(`/api/templates/${this.loadedTemplate.id}`, payload);

                    return data.data;
                }, "überschrieben");
            },

            async persistTemplate(request, verb) {
                this.savingTemplate = true;
                this.dialogError = null;

                try {
                    const saved = await request();

                    await this.fetchTemplates();
                    this.loadedTemplate = this.templates.find((template) => template.id === saved.id) ?? saved;
                    this.lastAppliedHtml = this.editor.getHTML();
                    this.templateDirty = false;
                    this.closeDialog();
                    Nova.success(`Vorlage „${saved.name}“ ${verb}.`);
                } catch (error) {
                    const status = error.response?.status;

                    this.dialogError =
                        status === 403
                            ? "Dir fehlt die Berechtigung, Vorlagen zu bearbeiten (Einstellungen › Vorlagen)."
                            : status === 422
                              ? Object.values(error.response.data.errors ?? {}).flat()[0] ?? "Bitte prüfe Deine Eingaben."
                              : "Die Vorlage konnte nicht gespeichert werden. Bitte versuche es erneut.";
                } finally {
                    this.savingTemplate = false;
                }
            },

            async onPreview(e) {
                const updateForm = this.$parent.$parent.$parent.$parent;
                await updateForm.submitViaUpdateResourceAndContinueEditing(e);
                let win = window.open(this.field.previewUrl + this.resourceId + "?watermark=1", "preview");
                win.focus();
                setTimeout(() => {
                    document.querySelector("a[name='nova-form-text-editor']")?.scrollIntoView();
                }, 2000);
            },

            async fetchTemplates() {
                if (!this.field.templateCategory) {
                    return;
                }

                this.loadingTemplates = true;

                try {
                    const { data } = await Nova.request().get("/api/templates", {
                        params: { all: 1, category: this.field.templateCategory },
                    });

                    this.templates = data.data;
                    this.canCreateTemplates = !!data.meta?.can_create;
                    this.templateCategories = data.meta?.categories ?? {};
                } finally {
                    this.loadingTemplates = false;
                }
            },

            async fetchTextTemplates() {
                this.textTemplates = (await Nova.request().get("/api/text_templates")).data.data;
            },

            addElement(type) {
                this.editor.chain().focus().insertContent([{ type }, { type: "paragraph" }]).run();
            },

            addVariable(variable) {
                this.editor.chain().focus().insertContent("{ " + variable + " }").run();
            },

            variablePreview(name) {
                const value = this.field.variables?.[name];

                if (value === null || value === undefined || value === "") {
                    return null;
                }

                const text = String(value).replace(/<[^>]*>/g, " ").trim();

                return text.length > 60 ? `${text.slice(0, 60)}…` : text;
            },

            /**
             * Einträge des „/“-Menüs; wird pro Tastendruck neu gebaut, damit nachgeladene Daten erscheinen.
             */
            slashItems() {
                const block = (id, title, icon, keywords, run) => ({ id, group: "Formatierung", title, icon, keywords, run });

                const items = [
                    block("paragraph", "Normaler Text", "¶", "absatz text", (chain) => chain.setParagraph().run()),
                    block("heading-2", "Überschrift 2", "H2", "titel h2", (chain) => chain.setHeading({ level: 2 }).run()),
                    block("heading-3", "Überschrift 3", "H3", "titel h3", (chain) => chain.setHeading({ level: 3 }).run()),
                    block("heading-4", "Überschrift 4", "H4", "titel h4", (chain) => chain.setHeading({ level: 4 }).run()),
                    block("bullet-list", "Aufzählung", ListBulletIcon, "liste punkte", (chain) => chain.toggleBulletList().run()),
                    block("ordered-list", "Nummerierte Liste", NumberedListIcon, "liste nummern zahlen", (chain) => chain.toggleOrderedList().run()),
                    block("blockquote", "Zitat", ChatBubbleBottomCenterTextIcon, "zitat quote", (chain) => chain.toggleBlockquote().run()),
                    block("horizontal-rule", "Trennlinie", MinusIcon, "linie trenner", (chain) => chain.setHorizontalRule().run()),
                ];

                this.blockOptions.forEach(({ type, label }) =>
                    items.push({
                        id: `block-${type}`,
                        group: "Variable Blöcke",
                        title: label,
                        icon: Squares2X2Icon,
                        keywords: "block baustein",
                        run: (chain) => chain.insertContent([{ type }, { type: "paragraph" }]).run(),
                    })
                );

                this.variableGroups.forEach((variableGroup) =>
                    variableGroup.variables.forEach((name) =>
                        items.push({
                            id: `variable-${variableGroup.label}-${name}`,
                            group: variableGroup.label ? `Platzhalter · ${variableGroup.label}` : "Platzhalter",
                            title: `{ ${name} }`,
                            subtitle: this.variablePreview(name),
                            icon: VariableIcon,
                            keywords: `${name} platzhalter variable ${variableGroup.label ?? ""}`,
                            run: (chain) => chain.insertContent(`{ ${name} }`).run(),
                        })
                    )
                );

                this.textTemplates.forEach((textTemplate) =>
                    items.push({
                        id: `text-template-${textTemplate.id}`,
                        group: "Textbausteine",
                        title: textTemplate.name || textTemplate.shortcode,
                        subtitle: textTemplate.text,
                        icon: "¶",
                        keywords: textTemplate.shortcode ?? "",
                        run: (chain) => chain.insertContent({ type: "text", text: textTemplate.text }).run(),
                    })
                );

                return items;
            },

            async applyFirstTemplate() {
                await this.fetchTemplates();

                const firstTemplate = this.matchingTemplates[0];

                if (!this.field.selectFirstTemplate || !firstTemplate || String(this.value ?? "").length) {
                    return;
                }

                const subject = this.subjectInput();
                const isSubjectEmpty = !subject || subject.value.length === 0;

                this.applyTemplate(firstTemplate, this.field.syncTemplateSubject !== false && isSubjectEmpty);
            },
        },

        mounted() {
            const context = this;

            this.editor = new Editor({
                extensions: [
                    Dropcursor,
                    Gapcursor,
                    CodeBlock,
                    Document,
                    Bold,
                    Italic,
                    Highlight,
                    Strike,
                    TextStyle,
                    FontSize,
                    Underline,
                    Subscript,
                    Superscript,
                    Heading.configure({ levels: [2, 3, 4] }),
                    Link,
                    withDirAttribute(Blockquote),
                    withDirAttribute(BulletList),
                    HorizontalRule,
                    ListItem,
                    withDirAttribute(OrderedList),
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
                    TextAlign.configure({ types: ["heading", "paragraph"] }),
                    UndoRedo,
                    Text,
                    SlashCommand.configure({ getItems: () => context.slashItems() }),
                    VariableHighlight.configure({
                        getVariables: () => (context.variableNames.length ? [...context.variableNames, "heute"] : []),
                    }),
                    ...resolveCustomExtensions(),
                ],
                content: this.contentWithTrailingParagraph,
                onCreate({ editor }) {
                    try {
                        editor.commands.setContent(JSON.parse(context.value));
                    } catch {}

                    context.lastAppliedHtml = editor.getHTML();
                },
                onUpdate({ editor }) {
                    const html = editor.getHTML();

                    context.updateValue(context.saveAsJson ? editor.getJSON() : html);
                    context.templateDirty = html !== context.lastAppliedHtml;
                },
            });

            this.applyFirstTemplate();

            if (this.field.variableCatalog) {
                // Nova sendet `<attribute>-change` immer auch ohne Formular-Präfix; getFieldAttributeChangeEventName()
                // fehlt im hier gebündelten laravel-nova-Mixin (npm 1.x).
                this.catalogChangeEvent = `${this.field.variableCatalog.attribute}-change`;
                this.onCatalogValueChange = (value) => (this.catalogValue = value || null);
                Nova.$on(this.catalogChangeEvent, this.onCatalogValueChange);
            }

            if (this.field.withTextTemplates) {
                this.fetchTextTemplates();
            }
        },

        beforeUnmount() {
            if (this.catalogChangeEvent) {
                Nova.$off(this.catalogChangeEvent, this.onCatalogValueChange);
            }

            this.editor?.destroy();
        },
    };
</script>

<style lang="scss">
    .nova-tiptap-editor {
        padding-bottom: 20px;
        padding-top: 20px;

        .prose {
            & li>p {
                margin: 0 !important;
            }
        }

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

                    >* {
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

    // Dark mode styles
    .dark {
        .nova-tiptap-editor {
            .ProseMirror {
                color: #e5e7eb;

                p.is-editor-empty:first-child::before {
                    color: #6b7280;
                }

                hr {
                    border-top-color: #4b5563;
                }

                table {
                    td,
                    th {
                        border-color: #4b5563;
                    }

                    th {
                        background-color: #374151;
                    }
                }

                blockquote {
                    border-left-color: #4b5563;
                    color: #9ca3af;
                }
            }

            .prose {
                color: #e5e7eb;

                h1, h2, h3, h4, h5, h6 {
                    color: #f3f4f6;
                }

                strong {
                    color: #f3f4f6;
                }
            }
        }

        // Content block styles for dark mode
        .content-block {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;

            .content-block-title {
                background-color: #4b5563;
                color: #f3f4f6;
            }
        }
    }

    /*
     * Editor-Chrome (Toolbar, Menüs, Bubble, Vorlagen). Eigenes Author-CSS statt Tailwind-Utilities:
     * Das Package bringt kein eigenes Tailwind mit, und Teleport-Inhalte liegen außerhalb von .orlyapp.
     */
    .texteditor {
        position: relative;

        &__toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        &__content {
            margin-top: 0.5rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            min-height: 8rem;
        }

        &__hint {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: rgb(var(--colors-gray-500));

            kbd {
                padding: 0 0.3rem;
                border: 1px solid rgb(var(--colors-gray-300));
                border-bottom-width: 2px;
                border-radius: 0.25rem;
                font-family: inherit;
                font-size: 0.7rem;
                background: white;
            }

            @media (pointer: coarse) {
                display: none;
            }
        }
    }

    .texteditor-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.25rem 0.5rem;
        padding: 0.25rem;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.5rem;
        background: rgb(var(--colors-gray-50));

        &__format,
        &__end {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.125rem;
        }

        &__end {
            margin-left: auto;
        }

        &__divider {
            width: 1px;
            height: 1.25rem;
            margin: 0 0.25rem;
            background: rgb(var(--colors-gray-200));
        }
    }

    .texteditor-tb-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.375rem;
        border-radius: 0.375rem;
        color: rgb(var(--colors-gray-600));
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        transition: background-color 0.1s, color 0.1s;

        &:hover:not(:disabled) {
            color: rgb(var(--colors-gray-900));
            background: rgb(var(--colors-gray-200));
        }

        &:focus-visible {
            outline: 2px solid rgb(var(--colors-primary-500));
            outline-offset: 1px;
        }

        &.is-active {
            color: rgb(var(--colors-primary-600));
            background: rgb(var(--colors-primary-100));
        }

        &:disabled {
            opacity: 0.4;
            cursor: default;
        }

        &.has-label {
            padding: 0 0.5rem;
        }

        &__icon,
        &__chevron {
            width: 1.125rem;
            height: 1.125rem;
            flex: none;
        }

        &__chevron {
            width: 0.875rem;
            height: 0.875rem;
            margin-left: -0.125rem;
            opacity: 0.6;
        }

        &__glyph {
            min-width: 1.125rem;
            font-size: 0.875rem;
            text-align: center;
        }

        /* Touch: größere Trefferflächen */
        @media (pointer: coarse) {
            min-width: 2.5rem;
            height: 2.5rem;
        }
    }

    .texteditor-tb-menu {
        display: inline-flex;
    }

    .texteditor-popover {
        z-index: 70;
        min-width: 13rem;
        max-width: min(24rem, calc(100vw - 16px));
        overflow-y: auto;
        padding: 0.25rem;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.5rem;
        background: white;
        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.15), 0 4px 10px -6px rgb(0 0 0 / 0.1);
        color: rgb(var(--colors-gray-700));
        font-size: 0.875rem;
    }

    .texteditor-menu-heading {
        padding: 0.5rem 0.625rem 0.25rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgb(var(--colors-gray-400));
    }

    .texteditor-menu-hint {
        padding: 0.375rem 0.625rem 0.5rem;
        font-size: 0.75rem;
        color: rgb(var(--colors-gray-500));
    }

    .texteditor-menu-empty {
        padding: 0.75rem 0.625rem;
        color: rgb(var(--colors-gray-500));
    }

    .texteditor-menu-item {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.625rem;
        padding: 0.4375rem 0.625rem;
        border-radius: 0.375rem;
        text-align: left;

        &:hover:not(:disabled),
        &.is-selected {
            background: rgb(var(--colors-gray-100));
        }

        &.is-active {
            color: rgb(var(--colors-primary-600));
            font-weight: 600;
        }

        &:disabled {
            opacity: 0.4;
            cursor: default;
        }

        &__icon {
            width: 1.125rem;
            height: 1.125rem;
            flex: none;
            color: rgb(var(--colors-gray-400));
        }

        &__glyph {
            width: 1.125rem;
            flex: none;
            font-size: 0.8125rem;
            font-weight: 700;
            text-align: center;
            color: rgb(var(--colors-gray-400));
        }

        &__text {
            display: flex;
            min-width: 0;
            flex-direction: column;
        }

        &__label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            &.is-heading-2 { font-size: 1.125rem; font-weight: 700; }
            &.is-heading-3 { font-size: 1rem; font-weight: 700; }
            &.is-heading-4 { font-size: 0.9375rem; font-weight: 600; }

            code {
                font-size: 0.8125rem;
                color: rgb(var(--colors-primary-600));
            }
        }

        &__subtitle {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.75rem;
            color: rgb(var(--colors-gray-500));
        }

        @media (pointer: coarse) {
            padding-top: 0.625rem;
            padding-bottom: 0.625rem;
        }
    }

    .texteditor-slash {
        width: 20rem;
    }

    .texteditor-variables {
        width: 20rem;
    }

    .texteditor-bubble {
        display: flex;
        align-items: center;
        padding: 0.25rem;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.5rem;
        background: white;
        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.15);
        z-index: 40;

        &__row,
        &__link-form {
            display: flex;
            align-items: center;
            gap: 0.125rem;
        }

        &__input {
            width: min(18rem, 60vw);
            height: 2rem;
            padding: 0 0.5rem;
            border: 1px solid rgb(var(--colors-gray-300));
            border-radius: 0.375rem;
            font-size: 0.875rem;
            background: white;

            &:focus {
                outline: 2px solid rgb(var(--colors-primary-500));
                outline-offset: -1px;
            }
        }

        &__href {
            max-width: 16rem;
            overflow: hidden;
            padding: 0 0.5rem;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.8125rem;
            color: rgb(var(--colors-primary-600));
            text-decoration: underline;
        }
    }

    .texteditor-loaded {
        display: inline-flex;
        max-width: 100%;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.125rem 0.125rem 0.625rem;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.5rem;
        background: white;

        &__name {
            display: inline-flex;
            min-width: 0;
            max-width: 12rem;
            align-items: center;
            gap: 0.375rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgb(var(--colors-gray-600));
        }

        &__dot {
            width: 0.5rem;
            height: 0.5rem;
            flex: none;
            border-radius: 9999px;
            background: rgb(var(--colors-yellow-500, var(--colors-primary-500)));
        }

        &__action {
            display: inline-flex;
            height: 1.75rem;
            align-items: center;
            gap: 0.25rem;
            padding: 0 0.5rem;
            border: 1px solid rgb(var(--colors-gray-300));
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            color: rgb(var(--colors-gray-700));
            background: white;

            &:hover {
                border-color: rgb(var(--colors-primary-500));
                color: rgb(var(--colors-primary-600));
            }
        }

        &__action-icon {
            width: 1rem;
            height: 1rem;
        }

        /* Text seit dem Laden geändert: Überschreiben wird zur Hauptaktion. */
        &.is-dirty &__action {
            border-color: rgb(var(--colors-primary-500));
            color: white;
            background: rgb(var(--colors-primary-500));

            &:hover {
                background: rgb(var(--colors-primary-600));
                color: white;
            }
        }

        .texteditor-tb-button {
            min-width: 1.75rem;
            height: 1.75rem;
        }

        @media (max-width: 639px) {
            &__name {
                max-width: 7rem;
            }
        }
    }

    .texteditor-templates {
        display: flex;
        width: 26rem;
        flex-direction: column;
        padding: 0;

        &__header {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-bottom: 1px solid rgb(var(--colors-gray-100));
        }

        &__search-icon {
            position: absolute;
            left: 1.125rem;
            width: 1rem;
            height: 1rem;
            color: rgb(var(--colors-gray-400));
            pointer-events: none;
        }

        &__search {
            width: 100%;
            height: 2.25rem;
            padding: 0 0.625rem 0 2rem;
            border: 1px solid rgb(var(--colors-gray-300));
            border-radius: 0.375rem;
            font-size: 0.875rem;
            background: white;

            &:focus {
                outline: 2px solid rgb(var(--colors-primary-500));
                outline-offset: -1px;
            }
        }

        &__close {
            display: inline-flex;
            width: 2.25rem;
            height: 2.25rem;
            flex: none;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            color: rgb(var(--colors-gray-500));

            svg {
                width: 1.25rem;
                height: 1.25rem;
            }
        }

        &__list {
            min-height: 0;
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 0.25rem;
        }

        &__hint {
            margin: 0.25rem;
            padding: 0.5rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-50));
        }

        &__category {
            font-weight: 400;
            color: rgb(var(--colors-gray-500));
        }

        &__footer {
            padding: 0.25rem;
            border-top: 1px solid rgb(var(--colors-gray-100));
        }

        /* Mobil: Sheet von unten, feste Position statt floating-ui */
        &.is-sheet {
            left: 0 !important;
            right: 0 !important;
            top: auto !important;
            bottom: 0 !important;
            width: 100%;
            max-width: none;
            max-height: 85vh !important;
            height: 85vh;
            border-radius: 1rem 1rem 0 0;
            box-shadow: 0 -10px 40px rgb(0 0 0 / 0.25);
        }
    }

    .texteditor-preview {
        margin-top: 0.5rem;

        &__bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        &__field {
            display: flex;
            min-width: 10rem;
            flex-direction: column;
            gap: 0.25rem;

            &--grow {
                flex: 1 1 14rem;
            }
        }

        &__label {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgb(var(--colors-gray-500));
        }

        &__select {
            width: 100%;
            height: 2.25rem;
            padding: 0 0.625rem;
            border: 1px solid rgb(var(--colors-gray-300));
            border-radius: 0.375rem;
            font-size: 0.875rem;
            background: white;

            &:focus {
                outline: 2px solid rgb(var(--colors-primary-500));
                outline-offset: -1px;
            }
        }

        &__combobox {
            position: relative;
        }

        &__list {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-width: none;
            max-height: 18rem;
        }

        &__body {
            min-height: 8rem;
            padding: 1rem 1.25rem;
        }

        &__meta {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 0.25rem 0.75rem;
            margin: 0 0 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgb(var(--colors-gray-200));
            font-size: 0.875rem;

            &:empty {
                display: none;
            }

            dt {
                font-weight: 600;
                color: rgb(var(--colors-gray-500));
            }

            dd {
                margin: 0;
            }
        }

        &__notice {
            margin: 0 0 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-100));

            &--error {
                color: rgb(var(--colors-red-700, var(--colors-red-600)));
                background: rgb(var(--colors-red-50));
            }
        }

        &__bar &__notice {
            margin: 0;
        }

        &__skeleton {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;

            span {
                height: 0.875rem;
                border-radius: 0.25rem;
                background: rgb(var(--colors-gray-200));
                animation: texteditor-pulse 1.2s ease-in-out infinite;

                &:nth-child(1) { width: 40%; }
                &:nth-child(2) { width: 90%; }
                &:nth-child(3) { width: 75%; }
            }
        }
    }

    @keyframes texteditor-pulse {
        50% {
            opacity: 0.45;
        }
    }

    .dark .texteditor-preview {
        &__select {
            border-color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-900));
            color: rgb(var(--colors-gray-100));
        }

        &__meta {
            border-color: rgb(var(--colors-gray-700));
        }

        &__notice {
            color: rgb(var(--colors-gray-300));
            background: rgb(var(--colors-gray-800));
        }

        &__skeleton span {
            background: rgb(var(--colors-gray-700));
        }
    }

    .nova-tiptap-editor .ProseMirror {
        .texteditor-variable {
            padding: 0.05rem 0.2rem;
            border-radius: 0.25rem;
            background: rgb(var(--colors-primary-100));
            color: rgb(var(--colors-primary-700, var(--colors-primary-600)));
            box-decoration-break: clone;
        }

        .texteditor-variable--unknown {
            background: transparent;
            color: inherit;
            text-decoration: underline wavy rgb(var(--colors-red-500));
            text-underline-offset: 3px;
        }

        .suggestion {
            color: rgb(var(--colors-primary-600));
        }
    }

    .dark {
        .texteditor-toolbar,
        .texteditor-popover,
        .texteditor-bubble {
            border-color: rgb(var(--colors-gray-700));
            background: rgb(var(--colors-gray-800));
            color: rgb(var(--colors-gray-200));
        }

        .texteditor-toolbar__divider {
            background: rgb(var(--colors-gray-700));
        }

        .texteditor-tb-button {
            color: rgb(var(--colors-gray-300));

            &:hover:not(:disabled) {
                color: white;
                background: rgb(var(--colors-gray-700));
            }

            &.is-active {
                color: rgb(var(--colors-primary-300));
                background: rgb(var(--colors-gray-700));
            }
        }

        .texteditor-menu-item {
            &:hover:not(:disabled),
            &.is-selected {
                background: rgb(var(--colors-gray-700));
            }

            &.is-active {
                color: rgb(var(--colors-primary-300));
            }
        }

        .texteditor-menu-item__label code {
            color: rgb(var(--colors-primary-300));
        }

        .texteditor-templates__header,
        .texteditor-templates__footer {
            border-color: rgb(var(--colors-gray-700));
        }

        .texteditor-templates__hint {
            color: rgb(var(--colors-gray-300));
            background: rgb(var(--colors-gray-900));
        }

        .texteditor-templates__search,
        .texteditor-bubble__input {
            border-color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-900));
            color: rgb(var(--colors-gray-100));
        }

        .texteditor-loaded {
            border-color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-800));

            &__name {
                color: rgb(var(--colors-gray-200));
            }

            &__action {
                border-color: rgb(var(--colors-gray-600));
                color: rgb(var(--colors-gray-200));
                background: rgb(var(--colors-gray-700));
            }

            &.is-dirty .texteditor-loaded__action {
                border-color: rgb(var(--colors-primary-500));
                color: white;
                background: rgb(var(--colors-primary-600));
            }
        }

        .texteditor__hint kbd {
            border-color: rgb(var(--colors-gray-600));
            background: rgb(var(--colors-gray-800));
        }

        .nova-tiptap-editor .ProseMirror .texteditor-variable {
            background: rgb(var(--colors-gray-700));
            color: rgb(var(--colors-primary-300));
        }
    }
</style>
