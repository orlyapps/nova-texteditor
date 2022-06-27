import { mergeAttributes, Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import View from "./Salutation.vue";

export default Node.create({
    name: "orlyapps-salutation",
    group: "block",
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            type: {
                default: "du",
            },
            ":contact": {
                default: "$contact",
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: "x-orlyapps-salutation",
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ["x-orlyapps-salutation", mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return VueNodeViewRenderer(View);
    },
});
