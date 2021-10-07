import { Node, Plugin } from "tiptap";

export default class PageBreak extends Node {
    get name() {
        return "page-break";
    }

    get schema() {
        return {
            group: "block",
            selectable: true,
            draggable: true,
            parseDOM: [
                {
                    tag: "page-break"
                }
            ],
            toDOM: node => ["page-break", {}]
        };
    }

    commands({ type }) {
        return () => (state, dispatch) => dispatch(state.tr.replaceSelectionWith(type.create()));
    }

    get view() {
        return {
            props: ["node", "updateAttrs", "view"],
            template: `<div class='page-break'>Seitenumbruch</div>`
        };
    }
}
