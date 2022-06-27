<template>
    <node-view-wrapper class="signature">
        <div
            class="drag-handle flex items-center justify-between p-2 px-6 bg-gray-100 border-primary-500 mt-3"
            contenteditable="false"
            draggable="true"
            data-drag-handle
        >
            <div class="font-bold">Signatur</div>
        </div>
    </node-view-wrapper>
</template>

<script>
import { nodeViewProps, NodeViewWrapper, NodeViewContent } from "@tiptap/vue-3";

export default {
    components: {
        NodeViewWrapper,
        NodeViewContent,
    },
    props: nodeViewProps,
};
</script>
