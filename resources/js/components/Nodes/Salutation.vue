<template>
    <node-view-wrapper class="salutation">
        <div
            class="drag-handle flex items-center justify-between p-2 px-6 bg-gray-100 border-primary-500"
            contenteditable="false"
            draggable="true"
            data-drag-handle
        >
            <div class="font-bold">Anrede</div>
            <select
                id="location"
                class="block form-control form-select form-select-bordered"
                :value="node.attrs.type"
            >
                <option selected value="du">Hallo Erika</option>
                <option value="lastname">Sehr geehrte Frau Musterfrau</option>
                <option value="firstname">
                    Sehr geehrte Frau Erika Musterfrau
                </option>
                <option value="general">Sehr geehrte Damen und Herren</option>
            </select>
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
