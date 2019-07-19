<template>
    <default-field :field="field" :errors="errors" :full-width-content="true">
        <template slot="field">

            <div v-if="order.length > 0">
                <form-nova-flexible-content-group
                    v-for="group in orderedGroups"
                    :key="group.key"
                    :group="group"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                    @move-up="moveUp(group.key)"
                    @move-down="moveDown(group.key)"
                    @remove="remove(group.key)"
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
                    v-if="this.limitCounter != 0"
                >
                    <span>{{ field.button }}</span>
                </button>
            </div>

        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import Group from '../group';

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'resource', 'field'],

    computed: {
        layouts() {
            return this.field.layouts || false
        },
        orderedGroups() {
            return this.order.reduce((groups, key) => {
                groups.push(this.groups[key]);
                return groups;
            }, []);
        }
    },

    data() {
        return {
            isLayoutsDropdownOpen: false,
            order: [],
            groups: {},
            files: {},
            limitCounter: this.field.limit
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
            let key, group;

            this.value = [];
            this.files = {};

            for (var i = 0; i < this.order.length; i++) {
                key = this.order[i];
                group = this.groups[key].serialize();

                // Only serialize the group's non-file attributes
                this.value.push({
                    layout: group.layout,
                    key: group.key,
                    attributes: group.attributes
                });

                // Attach the files for formData appending
                this.files = {...this.files, ...group.files};
            }

            formData.append(this.field.attribute, JSON.stringify(this.value));

            // Append file uploads
            for(let file in this.files) {
                formData.append(file, this.files[file]);
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
            this.order.splice(0, this.order.length);
            this.groups = {};

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
            return this.layouts.find(layout => layout.name == name);
        },

        /**
         * Append the given layout to flexible content's list
         */
        addGroup(layout, attributes, key) {
            if(!layout) return;

            let fields = attributes || JSON.parse(JSON.stringify(layout.fields)),
                group = new Group(layout.name, layout.title, fields, this.field, key);

            this.groups[group.key] = group;
            this.order.push(group.key);

            this.isLayoutsDropdownOpen = false;

            if (this.limitCounter > 0) {
                this.limitCounter--;
            }
        },

        /**
         * Move a group up
         */
        moveUp(key) {
            let index = this.order.indexOf(key);

            if(index <= 0) return;

            this.order.splice(index - 1, 0, this.order.splice(index, 1)[0]);
        },

        /**
         * Move a group down
         */
        moveDown(key) {
            let index = this.order.indexOf(key);

            if(index < 0 || index >= this.order.length - 1) return;

            this.order.splice(index + 1, 0, this.order.splice(index, 1)[0]);
        },

        /**
         * Remove a group
         */
        remove(key) {
            let index = this.order.indexOf(key);

            if(index < 0) return;

            this.order.splice(index, 1);
            delete this.groups[key];

            if (this.limitCounter >= 0) {
                this.limitCounter++;
            }
        }
    }
}
</script>
