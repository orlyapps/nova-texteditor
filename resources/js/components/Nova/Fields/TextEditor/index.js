Nova.booting((Vue, router, store) => {
    store.registerModule("text-editor", {
        namespaced: true,
        state: {
            templates: [],
            textBlocks: []
        },

        actions: {
            async getTemplates(context, params) {
                if (params && params.category) {
                    let response = await axios.get("/api/templates?category=" + params.category);
                    context.commit("updateTemplates", response.data.data);
                } else {
                    context.commit("updateTemplates", []);
                }
            },
            async getTextBlocks(context) {
                let response = await axios.get("/api/text_templates");
                context.commit("updateTextBlocks", response.data.data);
            }
        },
        mutations: {
            updateTemplates(state, payload) {
                state.templates = payload;
            },
            updateTextBlocks(state, payload) {
                state.textBlocks = payload;
            }
        }
    });

    Vue.component("form-text-editor", require("./FormField").default);
    Vue.component("detail-text-editor", require("./DetailField").default);
    Vue.component("index-text-editor", require("./IndexField").default);
});
