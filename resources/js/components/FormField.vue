<template>
    <component
        :dusk="currentField.attribute"
        :is="currentField.fullWidth ? 'FullWidthField' : 'default-field'"
        :field="currentField"
        :errors="errors"
        :show-help-text="showHelpText"
        full-width-content
        class="relative"    
        @keyup.escape="selectGroup(null)">
        <template #field>

            <div :class="{
                '-mx-8 -mt-6' : currentField.enablePreview && !fullScreen,
                'fixed inset-0 bg-gray-50 z-50 flex flex-col' : fullScreen
    
                }">
                <div v-if="fullScreen" class="px-4 py-2 z-20 bg-white border-b text-center">
                    <button class="shadow relative bg-primary-500 hover:bg-primary-400 text-white dark:text-gray-900 cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3" @click="selectGroup(null)">Exit full screen mode</button>
                </div>
                <div ref="flexibleFieldContainer"                
                :class="{
                    'mb-4' : currentField.enablePreview && !fullScreen,
                    'flex-grow overflow-y-auto ml-sidebar border-l' : currentField.enablePreview && fullScreen
                }"
                >
                    <component
                        v-for="(group, index) in orderedGroups"
                        :is="currentField.enablePreview ? 'form-nova-flexible-content-group-with-preview' : 'form-nova-flexible-content-group'"
                        :dusk="currentField.attribute + '-' + index"
                        :key="group.key"
                        :field="currentField"
                        :group="group"
                        :index="index"
                        :resource-name="resourceName"
                        :resource-id="resourceId"
                        :errors="errors"
                        :mode="mode"
                        :selectedGroup="selectedGroupKey == group.key"
                        :full-screen="fullScreen"
                        
                        @move-up="moveUp(group.key)"
                        @move-down="moveDown(group.key)"
                        @remove="remove(group.key)"
                        @group-selected="selectGroup(group.key, $event)"
                    />            
                </div>

                <component
                    :layouts="layouts"
                    :is="currentField.menu.component"
                    :field="currentField"
                    :limit-counter="limitCounter"
                    :limit-per-layout-counter="limitPerLayoutCounter"
                    :errors="errors"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    @addGroup="addGroup($event)"
                    :class="{'px-8' : currentField.enablePreview , 'fixed z-50 border-t bg-100 ml-sidebar p-2 border-l' : currentField.enablePreview && fullScreen}"
                />
            </div>
        </template>
    </component>
</template>

<script>

import FullWidthField from './FullWidthField';
import Sortable from 'sortablejs'
import { DependentFormField, HandlesValidationErrors, mapProps } from 'laravel-nova';
import Group from '../group';
import { ElementTypes } from '@vue/compiler-core';

export default {
    mixins: [HandlesValidationErrors, DependentFormField],

    props: {
        ...mapProps(['mode']),
    },

    components: { FullWidthField },

    computed: {
        layouts() {
            return this.currentField.layouts || false
        },
        
        orderedGroups() {
            return this.order.reduce((groups, key) => {
                groups.push(this.groups[key]);
                return groups;
            }, []);
        },

        limitCounter() {
            if (this.currentField.limit === null || typeof(this.currentField.limit) == "undefined") {
                return null;
            }

            return this.currentField.limit - Object.keys(this.groups).length;
        },

        limitPerLayoutCounter() {
            return this.layouts.reduce((layoutCounts, layout) => {
                if (layout.limit === null) {
                    layoutCounts[layout.name] = null;

                    return layoutCounts;
                }

                let count = Object.values(this.groups).filter(group => group.name === layout.name).length;

                layoutCounts[layout.name] = layout.limit - count;

                return layoutCounts;
            }, {});
        },
    },

    data() {
        return {
            order: [],
            groups: {},
            files: {},
            sortableInstance: null,
            selectedGroupKey: null,
            fullScreen: false,
        };
    },

    beforeUnmount() {
        document.documentElement.classList.remove('overflow-hidden');
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }
    },

    methods: {

        /**
         * Select the current group
         */
        selectGroup(groupKey, element) {
            if(this.selectedGroupKey == groupKey || groupKey == null) {
                this.selectedGroupKey = null;
                this.fullScreen = false;
                document.documentElement.classList.remove('overflow-hidden');
                let scrollY = this.$refs.flexibleFieldContainer.scrollTop;
                this.$nextTick(() => {
                    document.documentElement.scrollTop = scrollY + this.$refs.flexibleFieldContainer.getBoundingClientRect().top;
                });
            }
            else {
                document.documentElement.classList.add('overflow-hidden');
                let elementY = element.offsetTop - element.clientHeight/2;
                this.fullScreen = true;
                this.selectedGroupKey = groupKey;
                this.$nextTick(() => {
                    element.parentElement.scrollTop = elementY;
                });
            }
        },

        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.currentField.value || [];
            this.files = {};

            this.populateGroups();
            this.$nextTick(this.initSortable.bind(this));
            
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

            this.appendFieldAttribute(formData, this.currentField.attribute);
            formData.append(this.currentField.attribute, this.value.length ? JSON.stringify(this.value) : '');

            // Append file uploads
            for (let file in this.files) {
                formData.append(file, this.files[file]);
            }

            this.$nextTick(this.initSortable.bind(this));
        },

        /**
         * Register given field attribute into the parsable flexible fields register
         */
        appendFieldAttribute(formData, attribute) {
            let registered = [];

            if (formData.has('___nova_flexible_content_fields')) {
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
            this.groups = {};

            for (var i = 0; i < this.value.length; i++) {
                this.addGroup(
                    this.getLayout(this.value[i].layout),
                    this.value[i].attributes,
                    this.value[i].key,
                    this.currentField.collapsed
                );
            }
            if(!this.resourceId) {
                this.currentField.defaultLayouts.forEach((layoutName) => {
                    this.addGroup(this.getLayout(layoutName));
                });
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
                group = new Group(layout.name, layout.title, fields, this.currentField, key, layout.preview, collapsed);

            this.groups[group.key] = group;
            this.order.push(group.key);
            
            if(this.fullScreen) {
                this.selectedGroupKey = group.key;
                this.$nextTick(() => {
                    this.$refs.flexibleFieldContainer.lastElementChild.scrollIntoView({behavior: "smooth", block: "end", inline: "center"});
                });
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
        },


        initSortable() {
            const containerRef = this.$refs['flexibleFieldContainer']

            if (! containerRef || this.sortableInstance) {
                return;
            }

            this.sortableInstance = Sortable.create(containerRef, {
                ghostClass: 'nova-flexible-content-sortable-ghost',
                dragClass: 'nova-flexible-content-sortable-drag',
                chosenClass: 'nova-flexible-content-sortable-chosen',
                direction: 'vertical',
                handle: '.nova-flexible-content-drag-button',
                scrollSpeed: 5,
                animation: 500,
                onEnd: (evt) => {
                    const item = evt.item;
                    const key = item.id;
                    const oldIndex = evt.oldIndex;
                    const newIndex = evt.newIndex;

                    if (newIndex < oldIndex) {
                        this.moveUp(key);
                    } else if (newIndex > oldIndex) {
                        this.moveDown(key);
                    }
                 }
            });
        },
    }
}
</script>

<style>
.ml-sidebar {
    margin-left: 20%;
}

.-mt-5 {
    margin-top: -1.25rem;
}

.-mt-6 {
    margin-top: -1.5rem;
}

.-mx-8 {
    margin-left: -2rem;
    margin-right: -2rem;
}
</style>