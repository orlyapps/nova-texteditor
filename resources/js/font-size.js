import { Extension } from "@tiptap/core";
import "@tiptap/extension-text-style";

export default Extension.create({
    name: "fontSize",

    addOptions() {
        return {
            types: ["textStyle"],
        };
    },

    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    fontSize: {
                        default: null,
                        parseHTML(element) {
                            return element.style.fontSize.replace(/['"]+/g, "");
                        },
                        renderHTML(attributes) {
                            if (!attributes.fontSize) {
                                return {};
                            }

                            return {
                                style: `font-size: ${attributes.fontSize}`,
                            };
                        },
                    },
                },
            },
        ];
    },

    addCommands() {
        return {
            setFontSize(fontSize) {

                return ({ chain }) => {
                    return chain().setMark("textStyle", { fontSize }).run();
                };
            },
            unsetFontSize() {
                return ({ chain }) => {
                    return chain()
                        .setMark("textStyle", { fontSize: null })
                        .removeEmptyTextStyle()
                        .run();
                };
            },
        };
    },
});
