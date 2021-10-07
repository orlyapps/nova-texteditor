import { Mark } from "tiptap";
import { toggleMark } from "tiptap-commands";

export default class Highlight extends Mark {
    get name() {
        return "highlight";
    }
    get defaultOptions() {
        return {
            color: ["red"]
        };
    }

    get schema() {
        return {
            attrs: {
                color: {
                    default: "#e64116"
                }
            },
            parseDOM: this.options.color.map(color => ({
                tag: `span[style="color:${color}"]`,
                attrs: { color }
            })),
            toDOM: node => {
                return [
                    "span",
                    {
                        style: `color:${node.attrs.color};font-weight:bold;`
                    },
                    0
                ];
            }
        };
    }

    commands({ type }) {
        return attrs => toggleMark(type, attrs);
    }
}
