import { Extension, Mark, mergeAttributes, Node } from "@tiptap/core";
import { NodeViewContent, NodeViewWrapper, nodeViewProps, VueNodeViewRenderer } from "@tiptap/vue-3";
import BlockView from "./components/BlockView.vue";

/**
 * Die tiptap-Instanz des Editors für eigene Erweiterungen der App.
 *
 * Eigene Nodes (z. B. variable Blöcke) dürfen tiptap nicht selbst bündeln — zwei Core-Kopien in
 * einem Editor brechen unter tiptap 3. Stattdessen registriert die App Factories in
 * `window.TextEditorNotes`, die diese Instanz übergeben bekommen:
 *
 *     window.TextEditorNotes = [({ Node, VueNodeViewRenderer }) => Node.create({ … })]
 */
export const tiptap = {
    Extension,
    Mark,
    Node,
    mergeAttributes,
    NodeViewContent,
    NodeViewWrapper,
    nodeViewProps,
    VueNodeViewRenderer,
    BlockView,
};

/**
 * Löst die von der App registrierten Erweiterungen auf. Factories werden mit der tiptap-Instanz
 * aufgerufen; direkt übergebene Erweiterungen bleiben aus Kompatibilitätsgründen erlaubt.
 */
export function resolveCustomExtensions() {
    return (window.TextEditorNotes ?? []).map((extension) =>
        typeof extension === "function" ? extension(tiptap) : extension
    );
}
