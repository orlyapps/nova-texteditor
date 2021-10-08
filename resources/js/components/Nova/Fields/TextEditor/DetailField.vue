<template>
    <div class="w-full detail-full">

        <div class="document">
            <div class="page">
                <div class="inner">
                    <editor-content :editor="editor" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Editor, EditorContent } from "tiptap";

import Highlight from "./Highlight";
import Paragraph from "./Paragraph";
import Signature from "./Signature";
import PageBreak from "./PageBreak";
import Salutation from "./Salutation";


import {
    Blockquote,
    BulletList,
    CodeBlock,
    HardBreak,
    Heading,
    ListItem,
    OrderedList,
    Bold,
    Italic,
    Link,
    Strike,
    Underline,

    HorizontalRule
} from "tiptap-extensions";
export default {
    props: ["resource", "resourceName", "resourceId", "field"],
    components: {
        EditorContent
    },
    data() {
        return {
            editor: null
        };
    },
    created() {
        this.editor = new Editor({
            editable: false,
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
                new Signature(),
                new HorizontalRule(),
                new PageBreak(),
                new Salutation(),
            ],
            content: this.field.value || ""
        });
    },
    beforeDestroy() {
        this.editor.destroy();
    }
};
</script>

<style scoped>
.detail-full {
    margin-left: -1.5rem;
    margin-right: -1.5rem;
    margin-bottom: -0.75rem;
    width: calc(100% + 3rem);
}
</style>
