import { Node } from "tiptap";
import { Suggestions } from "tiptap-extensions";

export default class Template extends Node {
    get name() {
        return "template";
    }

    get schema() {
        return {
            attrs: {
                value: {}
            },
            group: "inline",
            inline: true
        };
    }

    replace(range = null, attrs = {}) {
        return (state, dispatch) => {
            const { $from, $to } = state.selection;
            const from = range ? range.from : $from.pos;
            const to = range ? range.to : $to.pos;

            if (dispatch) {
                dispatch(state.tr.replaceWith(from, to, this.editor.schema.text(attrs.value)));
            }

            return true;
        };
    }

    get plugins() {
        return [
            Suggestions({
                command: ({ range, attrs, schema }) => this.replace(range, attrs),
                appendText: " ",
                matcher: this.options.matcher,
                items: this.options.items,
                onEnter: this.options.onEnter,
                onChange: this.options.onChange,
                onExit: this.options.onExit,
                onKeyDown: this.options.onKeyDown,
                onFilter: this.options.onFilter,
                suggestionClass: ""
            })
        ];
    }
}
