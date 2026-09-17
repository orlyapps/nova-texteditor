<template>
    <div class="texteditor-popover texteditor-slash" role="listbox" @mousedown.prevent>
        <template v-if="items.length">
            <template v-for="(item, index) in items" :key="item.id">
                <div v-if="index === 0 || items[index - 1].group !== item.group" class="texteditor-menu-heading">{{ item.group }}</div>
                <button
                    :ref="(el) => (buttons[index] = el)"
                    type="button"
                    role="option"
                    class="texteditor-menu-item"
                    :class="{ 'is-selected': index === selectedIndex }"
                    :aria-selected="index === selectedIndex"
                    @click="selectItem(index)"
                    @mouseenter="selectedIndex = index"
                >
                    <component :is="item.icon" v-if="item.icon && typeof item.icon !== 'string'" class="texteditor-menu-item__icon" />
                    <span v-else class="texteditor-menu-item__glyph">{{ item.icon ?? "•" }}</span>
                    <span class="texteditor-menu-item__text">
                        <span class="texteditor-menu-item__label">{{ item.title }}</span>
                        <span v-if="item.subtitle" class="texteditor-menu-item__subtitle">{{ plain(item.subtitle) }}</span>
                    </span>
                </button>
            </template>
        </template>
        <div v-else class="texteditor-menu-empty">Keine Treffer</div>
    </div>
</template>

<script>
export default {
    props: {
        items: { type: Array, required: true },
        command: { type: Function, required: true },
    },

    data() {
        return {
            selectedIndex: 0,
            buttons: [],
        };
    },

    watch: {
        items() {
            this.selectedIndex = 0;
            this.buttons = [];
        },
    },

    methods: {
        onKeyDown({ event }) {
            if (!this.items.length) {
                return false;
            }

            if (event.key === "ArrowUp") {
                this.select((this.selectedIndex + this.items.length - 1) % this.items.length);

                return true;
            }

            if (event.key === "ArrowDown") {
                this.select((this.selectedIndex + 1) % this.items.length);

                return true;
            }

            if (event.key === "Enter" || event.key === "Tab") {
                this.selectItem(this.selectedIndex);

                return true;
            }

            return false;
        },

        select(index) {
            this.selectedIndex = index;
            this.buttons[index]?.scrollIntoView({ block: "nearest" });
        },

        selectItem(index) {
            const item = this.items[index];

            if (item) {
                this.command(item);
            }
        },

        plain(html) {
            const text = String(html).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();

            return text.length > 70 ? `${text.slice(0, 70)}…` : text;
        },
    },
};
</script>
