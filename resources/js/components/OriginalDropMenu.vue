<template>
    <div class="z-20 relative" v-if="layouts">
        <div class="relative" v-if="layouts.length > 1">
            <div v-if="isLayoutsDropdownOpen" class="absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40">
                <div>
                    <ul class="list-reset">
                        <li v-for="layout in layouts" class="border-b border-40">
                            <a @click="addGroup(layout)" class="cursor-pointer flex items-center hover:bg-30 block py-2 px-3 no-underline font-normal bg-20">
                                <div>
                                    <p class="text-90" :class="{'text-danger':layout.limit}">
                                        {{ layout.title }}
                                        <span v-if="layout.limit" :title="layout.name + ' can be used only ' + layout.limit + ' times'" class="text-60 bg-danger text-white px-2 ml-2">{{ layout.limit }}</span v-if="layout.limit">
                                    </p>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <button type="button" tabindex="0" class="btn btn-default btn-primary inline-flex items-center relative" @click="toggleLayoutsDropdownOrAddDefault" v-if="this.limitCounter != 0">
            <span>{{ field.button }}</span>
        </button>
    </div>
</template>
<script>
export default {
    props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'layoutsCounter'],

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
         * Append the given layout to flexible content's list
         */
        addGroup(layout) {
            if (!layout) return;

            if (!this.checkLayoutLimit(layout.name, layout.limit)) {
                Nova.error(this.__('The layout :customLayout is limited to :layoutLimit', {
                    customLayout: layout.name,
                    layoutLimit: layout.limit,
                }));
                return;
            }

            this.$emit('addGroup', layout);

            this.isLayoutsDropdownOpen = false;
        },

        checkLayoutLimit(key, limit) {

            if (limit && this.layoutsCounter.hasOwnProperty(key)) {

                if (this.layoutsCounter[key] >= limit) {
                    return false;
                }
            }

            return true;
        }
    }
}
</script>