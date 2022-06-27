import { mergeAttributes, Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import View from "./Signature.vue";

export default Node.create({
    name: "orlyapps-signature",
    group: "block",
    draggable: true,
    selectable: true,

    addAttributes() {
        return {
            ":user": {
                default: "$user",
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: "x-orlyapps-signature",
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ["x-orlyapps-signature", mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return VueNodeViewRenderer(View);
    },
});
