<template>
    <div class="w-full" v-if="layouts">

        <div class="flex">
            <div class="w-2/5">
                <button
                    type="button"
                    tabindex="0"
                    class="btn btn-default btn-primary inline-flex items-center relative float-left"
                    @click="toggleLayoutsDropdownOrAddDefault"
                    v-if="this.limitCounter != 0"
                >
                    <span>{{ field.button }}</span>
                </button>
            </div>
            <div class="w-3/5">
                <div v-if="layouts.length > 1" style="min-width: 300px;">
                    <div v-if="isLayoutsDropdownOpen">
                        <div>
                            <multiselect v-model="selectedLayout" :options="layouts"
                                         :custom-label="renderLayoutName"
                                         placeholder="Select one"
                                         label="title"
                                         openDirection="bottom"
                                         @input="selectLayout"
                                         track-by="name">

                            </multiselect>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>

    import Multiselect from 'vue-multiselect'

    export default {
        components: {Multiselect},

        props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter'],

        data() {
            return {
                selectedLayout: null,
                isLayoutsDropdownOpen: false
            };
        },

        methods: {
            selectLayout(value){
                this.addGroup(value);
            },
            renderLayoutName(layout){
                return layout.title;
            },
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

                this.isLayoutsDropdownOpen = false;
                this.selectedLayout = null;
            },
        }
    }
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
