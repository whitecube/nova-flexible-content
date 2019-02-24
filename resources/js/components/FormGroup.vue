<template>
    <div class="relative bg-white pl-8 mb-4">
        <div class="w-full">
            <div class="border-t border-r border-60 rounded-tr-lg">
                <div class="border-b border-40 leading-normal py-2 px-8">
                    <p class="text-80">{{ group.title }}</p>
                </div>
            </div>
            <div class="border-b border-r border-l border-60 rounded-b-lg">
                <component
                    v-for="(field, index) in group.fields"
                    :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
                    :is="'form-' + field.component"
                    :key="index"
                    :errors="validationErrors"
                    :resource-id="resourceId"
                    :resource-name="resourceName"
                    :field="field"
                />
            </div>
        </div>
        <div class="absolute z-10 bg-white border-t border-l border-b border-60 rounded-l pin-l pin-t w-8">
            <button class="btn border-r border-40 w-8 h-8 block" title="Move up"></button>
            <button class="btn border-t border-r border-40 w-8 h-8 block" title="Move down"></button>
            <button class="btn border-t border-r border-40 w-8 h-8 block" title="Delete"></button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'resourceId', 'group'],

    /**
     * Mount the component.
     */
    mounted() {
        this.initializeComponent()
    },

    methods: {
        initializeComponent() {
            this.group.serialize = this.serialize;
        },

        /**
         * Retrieve the layout's filled FormData
         */
        values() {
            let formData = new FormData();

            for (var i = 0; i < this.group.fields.length; i++) {
                this.group.fields[i].fill(formData);
            }

            return formData;
        },
        
        /**
         * Retrieve the layout's filled object
         */
        serialize() {
            let data = { layout: this.group.name, attributes: {} };

            this.values().forEach(function(value, key) {
                data.attributes[key] = value;
            });

            return data;
        },
    },
}
</script>
