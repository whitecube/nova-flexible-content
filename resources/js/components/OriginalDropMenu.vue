<template>
    <div
        class="z-20 relative"
        :class="{
            'add-at-position-button-container': addAtPosition
        }"
        v-if="layouts"
    >
        <div class="relative" v-if="layouts.length > 1">
            <div v-if="isLayoutsDropdownOpen"
                class="absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40"
            >
                <div>
                    <ul class="list-reset">
                        <li v-for="layout in layouts" :key="layout.name" class="border-b border-40">
                            <a
                                :dusk="'add-' + layout.name"
                                @click="addGroup(layout)"
                                class="cursor-pointer flex items-center hover:bg-30 block py-2 px-3 no-underline font-normal bg-20">
                                <div><p class="text-90">{{ layout.title }}</p></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <button
            dusk="toggle-layouts-dropdown-or-add-default"
            type="button"
            tabindex="0"
            class="btn btn-default btn-primary inline-flex items-center relative"
            :class="{
                'btn-xs': addAtPosition
            }"
            @click="toggleLayoutsDropdownOrAddDefault"
            v-if="this.limitCounter > 0"
        >
            <span>{{ field.button }}</span>
        </button>
    </div>
</template>

<script>
    import { eventBus } from '../eventbus';

    export default {
        props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'addAtPosition', 'index'],

        data() {
            return {
                isLayoutsDropdownOpen: false
            };
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
             * Add the given layout to flexible content's list at button's position
             */
            addGroup(layout) {
                if (!layout) return;

                eventBus.$emit('add-group-'+this.field.attribute, layout, this.index);

                this.isLayoutsDropdownOpen = false;
            },
        }
    }
</script>

<style scoped>
.add-at-position-button-container {
    padding-bottom: 5px;
    padding-top: 5px;
    margin-top: -16px;
    opacity: 0;
    overflow: visible;
    transition: opacity 0.6s;
}
.add-at-position-button-container:hover {
    opacity: 1;
}
.add-at-position-button-container button {
    margin: 0 auto;
}
</style>
