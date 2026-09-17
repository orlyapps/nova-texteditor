import { VueRenderer } from "@tiptap/vue-3";
import { computePosition, flip, shift, offset } from "@floating-ui/dom";
import MentionList from "./MentionList.vue";

function updatePosition(clientRect, element) {
    if (!clientRect) {
        return;
    }

    const virtualElement = { getBoundingClientRect: clientRect };

    computePosition(virtualElement, element, {
        placement: "bottom-start",
        strategy: "fixed",
        middleware: [offset(6), flip(), shift({ padding: 8 })],
    }).then(({ x, y, strategy }) => {
        Object.assign(element.style, {
            position: strategy,
            left: `${x}px`,
            top: `${y}px`,
            zIndex: 60,
        });
    });
}

export default {
    command: ({ editor, range, props }) => {
        editor.chain().focus().insertContentAt(range, [{ type: "text", text: props.id.item.text }]).run();
    },
    render: () => {
        let component;

        return {
            onStart: (props) => {
                component = new VueRenderer(MentionList, {
                    props,
                    editor: props.editor,
                });

                document.body.appendChild(component.element);
                updatePosition(props.clientRect, component.element);
            },

            onUpdate(props) {
                component.updateProps(props);
                updatePosition(props.clientRect, component.element);
            },

            onKeyDown(props) {
                if (props.event.key === "Escape") {
                    component.element.remove();

                    return true;
                }

                return component.ref?.onKeyDown(props);
            },

            onExit() {
                component.element.remove();
                component.destroy();
            },
        };
    },
};
