import { Extension } from "@tiptap/core";
import { PluginKey } from "@tiptap/pm/state";
import { Suggestion } from "@tiptap/suggestion";
import { VueRenderer } from "@tiptap/vue-3";
import { autoUpdate, computePosition, flip, offset, shift, size } from "@floating-ui/dom";
import Fuse from "fuse.js";
import SlashMenuList from "../components/SlashMenuList.vue";

/**
 * Popup für Suggestions, positioniert per floating-ui am Cursor.
 */
function renderPopup() {
    let component;
    let cleanup;

    const position = (clientRect) => {
        cleanup?.();

        if (!clientRect || !component) {
            return;
        }

        const reference = { getBoundingClientRect: () => clientRect() ?? new DOMRect() };
        const element = component.element;

        cleanup = autoUpdate(reference, element, () =>
            computePosition(reference, element, {
                strategy: "fixed",
                placement: "bottom-start",
                middleware: [
                    offset(6),
                    flip({ padding: 8 }),
                    shift({ padding: 8 }),
                    size({
                        padding: 8,
                        apply({ availableHeight, elements }) {
                            elements.floating.style.maxHeight = `${Math.min(360, Math.max(160, availableHeight))}px`;
                        },
                    }),
                ],
            }).then(({ x, y }) => {
                Object.assign(element.style, { position: "fixed", left: `${x}px`, top: `${y}px` });
            })
        );
    };

    const destroy = () => {
        cleanup?.();
        cleanup = null;
        component?.element.remove();
        component?.destroy();
        component = null;
    };

    return {
        onStart(props) {
            component = new VueRenderer(SlashMenuList, { props, editor: props.editor });
            document.body.appendChild(component.element);
            position(props.clientRect);
        },

        onUpdate(props) {
            component?.updateProps(props);
            position(props.clientRect);
        },

        onKeyDown(props) {
            if (props.event.key === "Escape") {
                destroy();

                return true;
            }

            return component?.ref?.onKeyDown(props) ?? false;
        },

        onExit: destroy,
    };
}

const stripHtml = (html) => String(html ?? "").replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();

/**
 * „/“-Menü: Absatzformate, variable Blöcke, Platzhalter und Textbausteine an einer Stelle.
 *
 * `getItems()` liefert die Einträge frisch pro Aufruf, damit nachgeladene Platzhalter/Textbausteine
 * ohne Neuaufbau des Editors erscheinen. Ein Eintrag: { id, group, title, subtitle?, icon?, keywords?, run(chain) }.
 */
export default Extension.create({
    name: "slashCommand",

    addOptions() {
        return {
            getItems: () => [],
        };
    },

    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                pluginKey: new PluginKey("slashCommand"),
                char: "/",
                allowSpaces: false,
                allowedPrefixes: [" ", " "],
                items: ({ query }) => {
                    const items = this.options.getItems();

                    if (!query) {
                        return items;
                    }

                    return new Fuse(items, {
                        threshold: 0.35,
                        ignoreLocation: true,
                        keys: [
                            { name: "title", weight: 3 },
                            { name: "keywords", weight: 2 },
                            { name: "searchText", getFn: (item) => stripHtml(item.subtitle), weight: 1 },
                        ],
                    })
                        .search(query)
                        .map((result) => result.item);
                },
                command: ({ editor, range, props }) => {
                    props.run(editor.chain().focus().deleteRange(range));
                },
                render: renderPopup,
            }),
        ];
    },
});
