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
                    :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
                    :key="index"
                    v-for="(item, index) in group.fields"
                    :is="'form-' + item.component"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                    :field="item"
                    :errors="validationErrors"
                    :ref="'subfield-' + index"
                />
            </div>
        </div>
        <div class="absolute z-10 bg-white border-t border-l border-b border-60 rounded-l pin-l pin-t w-8">
            <button class="group-control btn border-r border-40 w-8 h-8 block" title="Move up" @click.prevent="moveUp">
                <icon type="arrow-up" view-box="0 0 8 4.8" width="10" height="10" />
            </button>
            <button class="group-control btn border-t border-r border-40 w-8 h-8 block" title="Move down" @click.prevent="moveDown">
                <icon type="arrow-down" view-box="0 0 8 4.8" width="10" height="10" />
            </button>
            <button class="group-control btn border-t border-r border-40 w-8 h-8 block" title="Delete" @click.prevent="remove">
                <icon type="delete" view-box="0 0 20 20" width="16" height="16" />
            </button>
        </div>
    </div>
</template>

<script>
import { BehavesAsPanel } from 'laravel-nova'
export default {
    props: ['validationErrors', 'group', 'field'],
    mixins: [BehavesAsPanel],

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
        Nova.$on('flexible-field-reorder', () => this.updateValues());
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

        /**
         * Move this group up
         */
        moveUp() {
            this.$emit('move-up');
        },

        /**
         * Move this group down
         */
        moveDown() {
            this.$emit('move-down');
        },

        /**
         * Remove this group
         */
        remove() {
            this.$emit('remove');
        },

        /**
         * Update this group's fields with their latest value
         */
        updateValues() {
            this.$nextTick(() => {
                for(let ref of Object.keys(this.$refs)) {
                    if(!this.$refs[ref][0]) return;
                    this.updateValue(this.$refs[ref][0]);
                }
            });
        },

        /**
         * Update a field with its latest value
         */
        updateValue(field) {
            // Since field data is copied from a prop at creation, we need to update it manually
            field.setInitialValue();

            // Fix for markdown field
            if(field.codemirror) {
                field.doc.setValue(field.value);
            }
        }
    },
}
</script>

<style>
    .group-control:focus {
        outline: none;
    }
    .group-control path {
        fill: #B7CAD6;
        transition: fill 200ms ease-out;
    }
    .group-control:hover path {
        fill: var(--primary);
    }
</style>