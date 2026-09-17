import { Extension } from "@tiptap/core";
import { Plugin, PluginKey } from "@tiptap/pm/state";
import { Decoration, DecorationSet } from "@tiptap/pm/view";

/**
 * Gleiche Schreibweisen wie TextEditor::evaluateVariables(): {x}, { x }, {{x}}, {{ x }}.
 */
const VARIABLE_PATTERN = /\{\{?\s*([^{}\s]+)\s*\}?\}/g;

const pluginKey = new PluginKey("variableHighlight");

function buildDecorations(doc, knownVariables) {
    const decorations = [];
    const known = new Set(knownVariables);

    doc.descendants((node, pos) => {
        if (!node.isText) {
            return;
        }

        for (const match of node.text.matchAll(VARIABLE_PATTERN)) {
            const isUnknown = known.size > 0 && !known.has(match[1]);

            decorations.push(
                Decoration.inline(pos + match.index, pos + match.index + match[0].length, {
                    class: isUnknown ? "texteditor-variable texteditor-variable--unknown" : "texteditor-variable",
                    title: isUnknown ? `Unbekannter Platzhalter „${match[1]}“ – wird nicht ersetzt` : `Platzhalter „${match[1]}“`,
                })
            );
        }
    });

    return DecorationSet.create(doc, decorations);
}

/**
 * Hebt Platzhalter rein optisch hervor (Decorations) — das gespeicherte HTML bleibt unverändert.
 * Unbekannte Platzhalter werden nur markiert, wenn das Feld überhaupt Variablen kennt.
 */
export default Extension.create({
    name: "variableHighlight",

    addOptions() {
        return {
            getVariables: () => [],
        };
    },

    addCommands() {
        return {
            refreshVariableHighlight:
                () =>
                ({ tr, dispatch }) => {
                    dispatch?.(tr.setMeta(pluginKey, "refresh"));

                    return true;
                },
        };
    },

    addProseMirrorPlugins() {
        const getVariables = () => this.options.getVariables();

        return [
            new Plugin({
                key: pluginKey,
                state: {
                    init: (_, { doc }) => buildDecorations(doc, getVariables()),
                    apply: (tr, decorations) =>
                        tr.docChanged || tr.getMeta(pluginKey) ? buildDecorations(tr.doc, getVariables()) : decorations,
                },
                props: {
                    decorations: (state) => pluginKey.getState(state),
                },
            }),
        ];
    },
});
