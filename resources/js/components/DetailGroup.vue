<template>
    <div :class="componentStyle">
        <div :class="titleStyle" v-if="group.title">
            <span class="block float-left border-r border-gray-100 dark:border-gray-700  pr-4 mr-4"><!--
             --><span class="text-60 text-xs">#</span><!--
             --><span class="text-80">{{index+1}}</span>
            </span>
            <span class="font-bold">{{group.title}}</span>
        </div>
        <component
            v-for="(item, index) in group.fields"
            :key="index"
            :is="'detail-' + item.component"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :resource="resource"
            :field="item"
            :validation-errors="null"
            :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
        />
    </div>
</template>

<script>
export default {
    props: ['group', 'index', 'last', 'resource', 'resourceName', 'resourceId'],

    computed: {
        componentStyle() {
            return this.last ? [] : ['border-b border-50 pb-4 mb-4'];
        },
        titleStyle() {
            return ['pb-4', 'border-b', 'border-gray-100', 'dark:border-gray-700'];
        }
    }
}
</script>
