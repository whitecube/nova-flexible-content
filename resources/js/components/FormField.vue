<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            
            <div class="relative mb-4" v-if="value"></div>

            <div class="z-20 relative" v-if="layouts">
                <div class="relative">
                    <div 
                        v-if="isLayoutsDropdownOpen"
                        class="overflow-hidden absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40"
                    >
                        <div>
                            <ul class="list-reset">
                                <li v-for="layout in layouts" class="border-b border-40">
                                    <a class="cursor-pointer flex items-center hover:bg-30 block py-2 px-3 no-underline font-normal bg-20">
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
                    @click="toggleLayoutsDropdown"
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
            this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },

        /**
         * Display or hide the layouts choice dropdown
         */
        toggleLayoutsDropdown(event) {
            event.preventDefault();
            this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
        },
    },
}
</script>
