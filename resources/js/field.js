import DetailField from "./components/DetailField";
import FormField from "./components/FormField";
import { tiptap } from "./tiptap";

window.NovaTexteditor = tiptap;

Nova.booting((app, store) => {
    app.component("detail-text-editor", DetailField);
    app.component("form-text-editor", FormField);
    app.component("texteditor-block", tiptap.BlockView);
});
