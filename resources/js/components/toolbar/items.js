import {
    ArrowUturnLeftIcon,
    ArrowUturnRightIcon,
    Bars3BottomLeftIcon,
    Bars3BottomRightIcon,
    Bars3Icon,
    Bars4Icon,
    BoldIcon,
    ChatBubbleBottomCenterTextIcon,
    CodeBracketIcon,
    CodeBracketSquareIcon,
    ItalicIcon,
    LinkIcon,
    ListBulletIcon,
    MinusIcon,
    NumberedListIcon,
    PaintBrushIcon,
    StrikethroughIcon,
    UnderlineIcon,
} from "@heroicons/vue/20/solid";

/**
 * Standard-Toolbar, wenn das Feld keine eigenen `buttons()` setzt.
 */
export const DEFAULT_BUTTONS = [
    "bold",
    "italic",
    "underline",
    "strike",
    "highlight",
    "|",
    "heading",
    "textStyle",
    "|",
    "bulletList",
    "orderedList",
    "sinkListItem",
    "liftListItem",
    "|",
    "link",
    "blockquote",
    "hardBreak",
    "textAlign",
    "|",
    "history",
];

const toggle = (command) => (editor) => editor.chain().focus()[command]().run();

const alignment = (value, label, icon) => ({
    name: `align-${value}`,
    label,
    icon,
    run: (editor) => editor.chain().focus().setTextAlign(value).run(),
    active: (editor) => editor.isActive({ textAlign: value }),
});

const heading = (level) => ({
    name: `heading-${level}`,
    label: `Überschrift ${level}`,
    icon: `H${level}`,
    labelClass: `is-heading-${level}`,
    run: (editor) => editor.chain().focus().toggleHeading({ level }).run(),
    active: (editor) => editor.isActive("heading", { level }),
});

/**
 * Button-Registry der Toolbar. `primary` bleibt in der kompakten (mobilen) Toolbar sichtbar, alles andere
 * wandert ins „Mehr“-Menü. Einträge mit `options` werden als Menü dargestellt.
 *
 * `inline` fügt mehrere Buttons unter einem Namen ein (Verlauf = Rückgängig + Wiederholen).
 * `active`/`disabled`/`run` bekommen den Editor erst beim Aufruf, damit Vue die Zustände reaktiv neu liest.
 */
export function createToolbarItems({ onEditLink }) {
    const items = {
        bold: { label: "Fett", icon: BoldIcon, shortcut: "ModB", primary: true, run: toggle("toggleBold"), active: (e) => e.isActive("bold") },
        italic: { label: "Kursiv", icon: ItalicIcon, shortcut: "ModI", primary: true, run: toggle("toggleItalic"), active: (e) => e.isActive("italic") },
        underline: { label: "Unterstrichen", icon: UnderlineIcon, shortcut: "ModU", primary: true, run: toggle("toggleUnderline"), active: (e) => e.isActive("underline") },
        strike: { label: "Durchgestrichen", icon: StrikethroughIcon, shortcut: "ModShiftS", run: toggle("toggleStrike"), active: (e) => e.isActive("strike") },
        highlight: { label: "Markieren", icon: PaintBrushIcon, run: toggle("toggleHighlight"), active: (e) => e.isActive("highlight") },
        textStyle: {
            label: "Kleine Schrift",
            icon: "A",
            run: (e) =>
                e.isActive("textStyle", { fontSize: "10px" })
                    ? e.chain().focus().unsetFontSize().run()
                    : e.chain().focus().setFontSize("10px").run(),
            active: (e) => e.isActive("textStyle", { fontSize: "10px" }),
        },
        heading: {
            label: "Absatzformat",
            icon: "¶",
            options: [
                {
                    name: "paragraph",
                    label: "Normaler Text",
                    icon: "¶",
                    run: (e) => e.chain().focus().setParagraph().run(),
                    active: (e) => e.isActive("paragraph"),
                },
                heading(2),
                heading(3),
                heading(4),
            ],
        },
        bulletList: { label: "Aufzählung", icon: ListBulletIcon, primary: true, run: toggle("toggleBulletList"), active: (e) => e.isActive("bulletList") },
        orderedList: { label: "Nummerierte Liste", icon: NumberedListIcon, run: toggle("toggleOrderedList"), active: (e) => e.isActive("orderedList") },
        sinkListItem: {
            label: "Einrücken",
            icon: "⇥",
            run: (e) => e.chain().focus().sinkListItem("listItem").run(),
            disabled: (e) => !e.can().sinkListItem("listItem"),
        },
        liftListItem: {
            label: "Ausrücken",
            icon: "⇤",
            run: (e) => e.chain().focus().liftListItem("listItem").run(),
            disabled: (e) => !e.can().liftListItem("listItem"),
        },
        link: { label: "Link", icon: LinkIcon, primary: true, run: () => onEditLink(), active: (e) => e.isActive("link") },
        blockquote: { label: "Zitat", icon: ChatBubbleBottomCenterTextIcon, run: toggle("toggleBlockquote"), active: (e) => e.isActive("blockquote") },
        hardBreak: { label: "Zeilenumbruch", icon: "↵", run: (e) => e.chain().focus().setHardBreak().run() },
        horizontalRule: { label: "Trennlinie", icon: MinusIcon, run: (e) => e.chain().focus().setHorizontalRule().run() },
        code: { label: "Code", icon: CodeBracketIcon, run: toggle("toggleCode"), active: (e) => e.isActive("code") },
        codeBlock: { label: "Codeblock", icon: CodeBracketSquareIcon, run: toggle("toggleCodeBlock"), active: (e) => e.isActive("codeBlock") },
        subscript: { label: "Tiefgestellt", icon: "x₂", run: toggle("toggleSubscript"), active: (e) => e.isActive("subscript") },
        superscript: { label: "Hochgestellt", icon: "x²", run: toggle("toggleSuperscript"), active: (e) => e.isActive("superscript") },
        paragraph: { label: "Normaler Text", icon: "¶", run: (e) => e.chain().focus().setParagraph().run(), active: (e) => e.isActive("paragraph") },
        textAlign: {
            label: "Ausrichtung",
            icon: Bars3BottomLeftIcon,
            options: [
                alignment("left", "Linksbündig", Bars3BottomLeftIcon),
                alignment("center", "Zentriert", Bars3Icon),
                alignment("right", "Rechtsbündig", Bars3BottomRightIcon),
                alignment("justify", "Blocksatz", Bars4Icon),
            ],
        },
        history: {
            inline: [
                { name: "undo", label: "Rückgängig", icon: ArrowUturnLeftIcon, shortcut: "ModZ", run: (e) => e.chain().focus().undo().run(), disabled: (e) => !e.can().undo() },
                { name: "redo", label: "Wiederholen", icon: ArrowUturnRightIcon, shortcut: "ModShiftZ", run: (e) => e.chain().focus().redo().run(), disabled: (e) => !e.can().redo() },
            ],
        },
    };

    return items;
}
