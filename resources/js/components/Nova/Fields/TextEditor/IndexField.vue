<template>
  <span></span>
</template>

<script>
export default {
    props: ["resourceName", "field"],
    created() {
        this.value = this.field.value || "";
    }
};
</script>
