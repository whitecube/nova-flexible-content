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
};
</script>
