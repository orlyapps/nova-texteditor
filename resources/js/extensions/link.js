import {
    combineTransactionSteps,
    findChildrenInRange,
    getChangedRanges,
    getMarksBetween,
} from "@tiptap/core";
import { isAllowedUri, Link } from "@tiptap/extension-link";
import { Plugin, PluginKey } from "@tiptap/pm/state";
import { tokenize } from "linkifyjs";

/**
 * Zeichen, die beim Tippen direkt vor oder hinter einer URL stehen, aber nie Teil des Links sein sollen
 * (typografische Anführungszeichen, Satzzeichen, Klammern).
 */
const LEADING_PUNCTUATION = /^[„“”‚‘’"'«»‹›(\[<]+/u;
const TRAILING_PUNCTUATION = /[„“”‚‘’"'«»‹›)\]>.,;:!?]+$/u;
const WHITESPACE = /[\s\u00A0\u2000-\u200B\u2028\u2029\u202F\u205F\u3000]/u;
const WHITESPACE_AT_END = new RegExp(`${WHITESPACE.source}$`, "u");

/**
 * Sucht im letzten getippten Wort genau einen Link und schneidet umgebende Satzzeichen ab.
 *
 * Upstream verlinkt `„www.x.de“` gar nicht und zieht bei `https://x.de/pfad“` das Anführungszeichen
 * mit in den Link. Deshalb wird hier erst gekürzt und dann der Rest als Ganzes gegen linkify geprüft.
 *
 * @returns {{ start: number, end: number, href: string, value: string } | null}
 */
export function findLinkInWord(word, defaultProtocol = "https") {
    const leading = word.match(LEADING_PUNCTUATION)?.[0] ?? "";
    const withoutLeading = word.slice(leading.length);
    const trailing = withoutLeading.match(TRAILING_PUNCTUATION)?.[0] ?? "";
    const value = withoutLeading.slice(0, withoutLeading.length - trailing.length);

    if (!value) {
        return null;
    }

    const tokens = tokenize(value).map((token) => token.toObject(defaultProtocol));

    if (tokens.length !== 1 || !tokens[0].isLink) {
        return null;
    }

    return {
        start: leading.length,
        end: leading.length + value.length,
        href: tokens[0].href,
        value,
    };
}

function autolink(options) {
    return new Plugin({
        key: new PluginKey("autolink"),
        appendTransaction: (transactions, oldState, newState) => {
            const docChanged = transactions.some((transaction) => transaction.docChanged) && !oldState.doc.eq(newState.doc);
            const preventAutolink = transactions.some((transaction) => transaction.getMeta("preventAutolink"));

            if (!docChanged || preventAutolink) {
                return;
            }

            const { tr } = newState;
            const transform = combineTransactionSteps(oldState.doc, [...transactions]);

            getChangedRanges(transform).forEach(({ newRange }) => {
                const textBlocks = findChildrenInRange(newState.doc, newRange, (node) => node.isTextblock);

                if (textBlocks.length !== 1) {
                    return;
                }

                const endText = newState.doc.textBetween(newRange.from, newRange.to, " ", " ");

                if (!WHITESPACE_AT_END.test(endText)) {
                    return;
                }

                const textBlock = textBlocks[0];
                const textBeforeWhitespace = newState.doc.textBetween(textBlock.pos, newRange.to, undefined, " ");
                const words = textBeforeWhitespace.split(WHITESPACE).filter(Boolean);
                const lastWord = words[words.length - 1];

                if (!lastWord) {
                    return;
                }

                const link = findLinkInWord(lastWord, options.defaultProtocol);

                if (!link || !options.validate(link.value) || !options.shouldAutoLink(link.value)) {
                    return;
                }

                const wordOffset = textBlock.pos + textBeforeWhitespace.lastIndexOf(lastWord) + 1;
                const from = wordOffset + link.start;
                const to = wordOffset + link.end;

                if (newState.schema.marks.code && newState.doc.rangeHasMark(from, to, newState.schema.marks.code)) {
                    return;
                }

                if (getMarksBetween(from, to, newState.doc).some((item) => item.mark.type === options.type)) {
                    return;
                }

                tr.addMark(from, to, options.type.create({ href: link.href }));
            });

            return tr.steps.length ? tr : undefined;
        },
    });
}

/**
 * Link-Mark mit robuster Autolink-Erkennung (siehe findLinkInWord).
 */
export default Link.extend({
    addProseMirrorPlugins() {
        const plugins = this.parent?.() ?? [];

        if (!this.options.autolink) {
            return plugins;
        }

        return [
            ...plugins.filter((plugin) => !plugin.key.startsWith("autolink$")),
            autolink({
                type: this.type,
                defaultProtocol: this.options.defaultProtocol,
                validate: (url) => this.options.isAllowedUri(url, {
                    defaultValidate: (href) => !!isAllowedUri(href, this.options.protocols),
                    protocols: this.options.protocols,
                    defaultProtocol: this.options.defaultProtocol,
                }),
                shouldAutoLink: this.options.shouldAutoLink,
            }),
        ];
    },
}).configure({
    autolink: true,
    linkOnPaste: true,
    openOnClick: false,
    defaultProtocol: "https",
    protocols: ["mailto", "tel"],
});
