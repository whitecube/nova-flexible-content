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
    props: ['resourceName', 'resourceId', 'group', 'field'],

    data() {
        return {
            key: null,
        };
    },

    /**
     * Mount the component.
     */
    mounted() {
        this.initializeComponent()
    },

    methods: {
        initializeComponent() {
            // Get a unique identifier for this FormGroup
            this.key = this.group.key || this.getTemporaryUniqueKey();
            // Rename all fields with this key
            for (var i = 0; i < this.group.fields.length; i++) {
                this.group.fields[i].attribute = this.key + '__' + this.group.fields[i].attribute;
            }
            // Link this component's serialize function to the parent object
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
         * Generate a unique string for current form
         */
        getTemporaryUniqueKey() {
            return this.field.attribute
                + '_' + Math.random().toString(36).substr(2, 9)
                + '_' + this.group.name;
        },
        
        /**
         * Retrieve the layout's filled object
         */
        serialize() {
            let data = {
                layout: this.group.name,
                key: this.key,
                attributes: {},
                files: {}
            };

            for(var item of this.values()) {
                if(!(item[1] instanceof File || item[1] instanceof Blob)) {
                    // Simple input value, no need to attach files
                    data.attributes[item[0]] = item[1];
                    continue;
                }

                // File object, attach its file for upload
                data.attributes[item[0]] = '___upload-' + item[0];
                data.files['___upload-' + item[0]] = item[1];
            }

            return data;
        },
    },
}
</script>
