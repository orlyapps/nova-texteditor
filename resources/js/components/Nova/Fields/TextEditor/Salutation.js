import { Node } from "tiptap";

export default class Salutation extends Node {
    get name() {
        return "salutation";
    }
    get schema() {
        return {
            attrs: {
                type: {
                    default: "lastname"
                }
            },
            group: "block",
            selectable: true,
            draggable: true,
            parseDOM: [
                {
                    tag: "salutation"
                }
            ],
            toDOM: node => [
                "salutation",
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
                <div class=""><span class="font-bold">Anrede</span></div>
                <select id="location" :disabled="!view.editable" class="form-select block  pl-3 pr-10 py-2 text-base leading-6 border-gray-300" :value="node.attrs.type"  @change="onChange($event)">
                    <option selected value="du">Hallo Erika</option>
                    <option  value="lastname">Sehr geehrte Frau Musterfrau</option>
                    <option value="firstname">Sehr geehrte Frau Erika Musterfrau</option>
                    <option value="general">Sehr geehrte Damen und Herren</option>
                </select>
            </div>
            `
        };
    }
}
