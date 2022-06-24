import { mergeAttributes, Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import View from "./Salutation.vue";

export default Node.create({
    name: "salutation",
    group: "block",
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            type: {
                default: "du",
            },
            ":user": {
                default: "$user",
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: "x-salutation",
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ["x-salutation", mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return VueNodeViewRenderer(View);
    },
});
