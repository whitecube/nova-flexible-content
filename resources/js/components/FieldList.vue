<template>
    <div :class="containerStyle">
        <component
            v-for="(item, index) in fields"
            :key="index"
            :is="'form-' + item.component"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :field="item"
            :errors="errors"
            :mode="mode"
            :show-help-text="item.helpText != null"
            :class="{
                'remove-bottom-border': index == fields.length - 1,
            }"
        />
    </div>
</template>

<script>
import { mapProps } from 'laravel-nova';

export default {
    props: {
        containerStyle: [],
        fields: {},
        errors: {},
        ...mapProps(["mode"]),
    },
    computed: {
        containerStyle() {
            let classes = ['grow', 'border-b', 'border-r', 'border-l', 'border-gray-200', 'dark:border-gray-700', 'rounded-b-lg'];

            // if (! this.group.title) {
            //     classes.push('border-t');
            //     classes.push('rounded-tr-lg');
            // }

            // if (this.collapsed) {
            //     classes.push('hidden');
            // }

            return classes;
        }
    },
};
</script>
