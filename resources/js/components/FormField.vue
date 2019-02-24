<template>
    <default-field :field="field" :errors="errors" :full-width-content="true">
        <template slot="field">
            
            <div v-if="value.length > 0">
                <div class="relative bg-white pl-8 mb-4" v-for="group in value">
                    <div class="w-full">
                        <div class="border-t border-r border-60 rounded-tr-lg">
                            <div class="border-b border-40 leading-normal py-2 px-8">
                                <p class="text-80">{{ group.title }}</p>
                            </div>
                        </div>
                        <div class="border-b border-r border-l border-60 rounded-b-lg p-8">
                            <input type="hidden"
                                :name="group.id + '_layout'"
                                :value="group.layout" 
                            />
                        </div>
                    </div>
                    <div class="absolute z-10 bg-white border-t border-l border-b border-60 rounded-l pin-l pin-t w-8">
                        <button class="btn border-r border-40 w-8 h-8 block" title="Move up"></button>
                        <button class="btn border-t border-r border-40 w-8 h-8 block" title="Move down"></button>
                        <button class="btn border-t border-r border-40 w-8 h-8 block" title="Delete"></button>
                    </div>
                </div>
            </div>

            <div class="z-20 relative" v-if="layouts">
                <div class="relative" v-if="layouts.length > 1">
                    <div v-if="isLayoutsDropdownOpen"
                        class="overflow-hidden absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40"
                    >
                        <div>
                            <ul class="list-reset">
                                <li v-for="layout in layouts" class="border-b border-40">
                                    <a  @click="addLayout(layout)"
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
                    <span>{{ button }}</span>
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
        button() {
            return this.field.button || '+'
        },
        layouts() {
            return this.field.layouts || false
        }
    },


    data() {
        return {
            isLayoutsDropdownOpen: false,
        };
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || []
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || [])
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },

        /**
         * Display or hide the layouts choice dropdown if there are multiple layouts
         * or directly add the only available layout.
         */
        toggleLayoutsDropdownOrAddDefault(event) {
            if(this.layouts.length === 1) {
                return this.addLayout(this.layouts[0]);
            }
            this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
        },

        /**
         * Append the given layout to flexible content's list
         */
        addLayout(layout) {
            this.value.push({
                layout: layout.name,
                title: layout.title,
                id: 'test_' + layout.name
            });
            this.isLayoutsDropdownOpen = false;
        },
    },
}
</script>
