<template>
    <component
        :dusk="field.attribute"
        :is="field.fullWidth ? 'full-width-field' : 'default-field'"
        :field="field"
        :errors="errors"
        full-width-content
        :show-help-text="showHelpText">
        <template slot="field">

            <div
                v-if="order.length > 0">
                <form-nova-flexible-content-group
                    v-for="(group, index) in orderedGroups"
                    :dusk="field.attribute + '-' + index"
                    :key="group.key"
                    :field="field"
                    :group="group"
                    :index="index"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                    :errors="errors"
                    @move-up="moveUp(group.key)"
                    @move-down="moveDown(group.key)"
                    @remove="remove(group.key)"
                />
            </div>

            <component
                :layouts="layouts"
                :is="field.menu.component"
                :field="field"
                :limit-counter="limitCounter"
                :errors="errors"
                :resource-name="resourceName"
                :resource-id="resourceId"
                :resource="resource"
                @addGroup="addGroup($event)"
            />

        </template>
    </component>
</template>

<script>

import FullWidthField from './FullWidthField';
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import Group from '../group';

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'resource', 'field'],

    components: { FullWidthField },

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

            this.appendFieldAttribute(formData, this.field.attribute);
            formData.append(this.field.attribute, this.value.length ? JSON.stringify(this.value) : '');

            // Append file uploads
            for(let file in this.files) {
                formData.append(file, this.files[file]);
            }
        },

        /**
         * Register given field attribute into the parsable flexible fields register
         */
        appendFieldAttribute(formData, attribute) {
            let registered = [];

            if(formData.has('___nova_flexible_content_fields')) {
                registered = JSON.parse(formData.get('___nova_flexible_content_fields'));
            }

            registered.push(attribute);

            formData.set('___nova_flexible_content_fields', JSON.stringify(registered));
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
         * Set the displayed layouts from the field's current value
         */
        populateGroups() {
            this.order.splice(0, this.order.length);
            this.groups = {};

            for (var i = 0; i < this.value.length; i++) {
                this.addGroup(
                    this.getLayout(this.value[i].layout),
                    this.value[i].attributes,
                    this.value[i].key,
                    this.field.collapsed
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
        addGroup(layout, attributes, key, collapsed) {
            if(!layout) return;

            collapsed = collapsed || false;

            let fields = attributes || JSON.parse(JSON.stringify(layout.fields)),
                group = new Group(layout.name, layout.title, fields, this.field, key, collapsed);

            this.groups[group.key] = group;
            this.order.push(group.key);

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
