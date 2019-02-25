<template>
    <default-field :field="field" :errors="errors" :full-width-content="true">
        <template slot="field">
            
            <div v-if="groups.length > 0">
                <form-nova-flexible-content-group 
                    v-for="(group, index) in groups"
                    :key="index"
                    :field="field"
                    :group="group"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                />
            </div>

            <div class="z-20 relative" v-if="layouts">
                <div class="relative" v-if="layouts.length > 1">
                    <div v-if="isLayoutsDropdownOpen"
                        class="overflow-hidden absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40"
                    >
                        <div>
                            <ul class="list-reset">
                                <li v-for="layout in layouts" class="border-b border-40">
                                    <a  @click="addGroup(layout)"
                                        class="cursor-pointer flex items-center hover:bg-30 block py-2 px-3 no-underline font-normal bg-20">
                                        <div><p class="text-90">{{ layout.title }}</p></div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    tabindex="0"
                    class="btn btn-default btn-primary inline-flex items-center relative"
                    @click="toggleLayoutsDropdownOrAddDefault"
                >
                    <span>{{ field.button }}</span>
                </button>
            </div>

        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    computed: {
        layouts() {
            return this.field.layouts || false
        }
    },

    data() {
        return {
            isLayoutsDropdownOpen: false,
            groups: [],
            files: {},
        };
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || [];
            this.files = {};

            this.populateGroups();
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            let group;

            this.value = [];
            this.files = {};

            for (var i = 0; i < this.groups.length; i++) {
                group = this.groups[i].serialize();

                // Only serialize the group's non-file attributes
                this.value.push({
                    layout: group.layout,
                    key: group.key,
                    attributes: group.attributes
                });

                // Attach the files for formData appending
                this.fields = {...this.fields, ...group.files};
            }

            formData.append(this.field.attribute, JSON.stringify(this.value));

            // Append file uploads
            for(var key in this.fields) {
                formData.append(key, this.fields[key]);
            }
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value || [];
            this.files = {};

            this.populateGroups();
        },

        /**
         * Display or hide the layouts choice dropdown if there are multiple layouts
         * or directly add the only available layout.
         */
        toggleLayoutsDropdownOrAddDefault(event) {
            if(this.layouts.length === 1) {
                return this.addGroup(this.layouts[0]);
            }

            this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
        },

        /**
         * Set the displayed layouts from the field's current value
         */
        populateGroups() {
            this.groups = [];

            for (var i = 0; i < this.value.length; i++) {
                this.addGroup(
                    this.getLayout(this.value[i].layout),
                    this.value[i].attributes,
                    this.value[i].key
                );
            }
        },

        /**
         * Retrieve layout definition from its name
         */
        getLayout(name) {
            if(!this.layouts) return;

            for (var i = this.layouts.length - 1; i >= 0; i--) {
                if(this.layouts[i].name !== name) continue;
                return this.layouts[i];
            }
        },

        /**
         * Append the given layout to flexible content's list
         */
        addGroup(layout, attributes, key) {
            if(!layout) return;

            this.groups.push({
                name: layout.name,
                key: key || null,
                title: layout.title,
                fields: attributes || layout.fields
            });

            this.isLayoutsDropdownOpen = false;
        },
    },
}
</script>
