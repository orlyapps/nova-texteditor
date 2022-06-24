<template>
  <button
        type="button"
        class="
            group relative
            p-2
            leading-none text-xs
            rounded shadow
            tiptap-button
        "
        style="margin: 4px; min-width: 32px; height: 32px"
        :class="{
            'bg-primary-500 hover:bg-primary-400 text-white': isActive,
            'bg-white hover:bg-gray-200 text-black': !isActive,
            'opacity-50 pointer-events-none': isDisabled,
        }"

        @click="callClickMethod"
    >
        <template v-if="icon">
            <font-awesome-icon :icon="icon">
            </font-awesome-icon>
        </template>

        <template v-else>
            <span class="font-bold" v-html="innerHtml"></span>
        </template>
    </button>
</template>

<script>

import { library } from '@fortawesome/fontawesome-svg-core';

import {
    faAlignCenter,
    faAlignJustify,
    faAlignLeft,
    faAlignRight,
    faBold,
    faCode,
    faFileCode,
    faHighlighter,
    faImage,
    faItalic,
    faLink,
    faListOl,
    faListUl,
    faParagraph,
    faQuoteRight,
    faRedoAlt,
    faRotateLeft,
    faRotateRight,
    faStrikethrough,
    faSubscript,
    faSuperscript,
    faTable,
    faUnderline,
    faUndoAlt,
    faUnlink
} from '@fortawesome/free-solid-svg-icons';

library.add(
    faAlignCenter,
    faAlignJustify,
    faAlignLeft,
    faAlignRight,
    faBold,
    faCode,
    faFileCode,
    faHighlighter,
    faImage,
    faItalic,
    faLink,
    faListOl,
    faListUl,
    faParagraph,
    faQuoteRight,
    faRedoAlt,
    faStrikethrough,
    faSubscript,
    faSuperscript,
    faTable,
    faUnderline,
    faUndoAlt,
    faUnlink,
    faAlignLeft,
    faAlignRight,
    faAlignJustify,
    faRotateLeft,
    faRotateRight
);

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

export default {
    data() {
        return {
            hovered: false,
        }
    },
    props: [
        'clickMethod',
        'clickMethodParameters',
        'title',
        'isActive',
        'isDisabled',
        'icon',
        'innerHtml',
    ],

    components: {
        FontAwesomeIcon,
    },

    methods: {
        callClickMethod() {
            let tmpParams = this.clickMethodParameters
            if (tmpParams) {

                if (!typeof(tmpParams) != 'object') {
                    tmpParams = [tmpParams];
                }

                this.clickMethod(...tmpParams);
            } else {
                this.clickMethod();
            }
        }
    }
}
</script>
