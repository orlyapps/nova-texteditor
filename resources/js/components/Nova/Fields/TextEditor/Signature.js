import { Node } from 'tiptap';

export default class Signature extends Node {
    get name() {
        return "signature";
    }
    get schema() {
        return {
            attrs: {
                type: {
                    default: "user"
                }
            },
            group: "block",
            selectable: true,
            draggable: true,
            parseDOM: [
                {
                    tag: "signature"
                }
            ],
            toDOM: node => [
                "signature",
                {
                    "data-type": node.attrs.type
                }
            ]
        };
    }

    get view() {
        return {
            props: ["node", "updateAttrs", "view"],
            methods: {
                onChange(event) {
                    this.updateAttrs({
                        type: event.target.value
                    });
                }
            },
            template: `
            <div class="text signature bg-30 mt-3 mb-3 p-3 flex items-center justify-between" :data-type="node.attrs.type">
                <div class=""><span class="font-bold">Freundliche Grüße</span> und</div>
                <select id="location" :disabled="!view.editable" class="form-select block  pl-3 pr-10 py-2 text-base leading-6 border-gray-300" :value="node.attrs.type"  @change="onChange($event)">
                    <option selected value="user">Signatur Benutzer</option>
                    <option selected value="team">Signatur Team</option>
                </select>
            </div>
            `
        };
    }
}
