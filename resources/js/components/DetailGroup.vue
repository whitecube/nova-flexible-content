<template>
    <div :class="classes">
        <div class="pb-4 border-b border-40">
            <span class="block float-left border-r border-40 pr-4 mr-4"><!--
             --><span class="text-60 text-xs">#</span><!--
             --><span class="text-80">{{index+1}}</span>
            </span>
            <span class="font-bold">{{group.title}}</span>
        </div>
        <div :class="last ? 'pt-6' : 'py-6'">
            <component
                v-for="(item, index) in group.fields"
                :key="index"
                :is="'detail-' + item.component"
                :resource-name="resourceName"
                :resource-id="resourceId"
                :resource="resource"
                :field="item"
                :errors="validationErrors"
                :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: ['group', 'index', 'last'],

    computed: {
        classes() {
            return this.last ? '' : 'border-b border-40 mb-4'
        },
    }
}
</script>