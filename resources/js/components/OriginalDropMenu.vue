<template>
    <div class="relative" v-if="layouts">
        <div class="z-20" v-if="layouts.length > 1">
            <div v-if="isLayoutsDropdownOpen"
                 class="absolute rounded-lg shadow-lg max-w-full top-full mt-3 pin-b max-h-search overflow-y-auto border border-gray-100 dark:border-gray-700 "
            >
                <div>
                    <ul class="list-reset">
                        <li v-for="layout in filteredLayouts" class="border-b border-gray-100 dark:border-gray-700" :key="'add-'+layout.name">
                            <a
                                :dusk="'add-' + layout.name"
                                @click="addGroup(layout)"
                                class="cursor-pointer flex items-center hover:bg-gray-50 dark:hover:bg-gray-900 block py-2 px-3 no-underline font-normal bg-white dark:bg-gray-800">
                                <div><p class="text-90">{{ layout.title }}</p></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <default-button
            dusk="toggle-layouts-dropdown-or-add-default"
            type="button"
            tabindex="0"
            @click="toggleLayoutsDropdownOrAddDefault"
            v-if="isBelowLayoutLimits"
        >
            <span>{{ field.button }}</span>
        </default-button>
    </div>
</template>

<script>

    export default {
        props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'limitPerLayoutCounter'],

        emits: ['addGroup'],

        data() {
            return {
                isLayoutsDropdownOpen: false
            };
        },

        computed: {
            filteredLayouts() {
                return this.layouts.filter(layout => {
                    return this.limitPerLayoutCounter[layout.name] === null || this.limitPerLayoutCounter[layout.name] > 0
                });
            },

            isBelowLayoutLimits() {
                return (this.limitCounter > 0 || this.limitCounter === null) && this.filteredLayouts.length > 0;
            }
        },

        methods: {
            /**
             * Display or hide the layouts choice dropdown if there are multiple layouts
             * or directly add the only available layout.
             */
            toggleLayoutsDropdownOrAddDefault(event) {
                if (this.layouts.length === 1) {
                    return this.addGroup(this.layouts[0]);
                }

                this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
            },

            /**
             * Append the given layout to flexible content's list
             */
            addGroup(layout) {
                if (!layout) return;

                this.$emit('addGroup', layout);
                Nova.$emit('nova-flexible-content-add-group', layout);

                this.isLayoutsDropdownOpen = false;
            },
        }
    }
</script>


<style>
    .top-full {
        top: 100%
    }
</style>
