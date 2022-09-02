<template>
    <PanelItem :index="index" :field="field">
        <template #value>
           <div class="hundeschule">
             <div class="prose max-w-none" v-html="field.displayedAs"></div>
           </div>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ["index", "resource", "resourceName", "resourceId", "field"],
};
</script>
