import { mergeAttributes, Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import View from "./InvoicePositions.vue";

export default Node.create({
    name: "orlyapps-invoice-positions",
    group: "block",
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            ":invoice": {
                default: "$invoice",
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: "x-orlyapps-invoice-positions",
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ["x-orlyapps-invoice-positions", mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return VueNodeViewRenderer(View);
    },
});
