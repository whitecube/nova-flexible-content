<template>
    <div class="z-20 relative" v-if="layouts">
        <div class="relative" v-if="layouts.length > 1">
            <div v-if="isLayoutsDropdownOpen" 
                 ref="dropdown"
                 class="absolute rounded-lg shadow-lg max-w-full max-h-search overflow-y-auto border border-40"
                 v-bind:class="dropdownClasses"
            >
                <div>
                    <ul class="list-reset">
                        <li v-for="layout in layouts" v-if="limitPerLayoutCounter[layout.name] === null || limitPerLayoutCounter[layout.name] > 0" class="border-b border-40">
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
            ref="dropdownButton"
            class="btn btn-default btn-primary inline-flex items-center relative"
            @click="toggleLayoutsDropdownOrAddDefault"
            v-if="this.limitCounter > 0 || this.limitCounter === null"
        >
            <span>{{ field.button }}</span>
        </button>
    </div>
</template>

<script>

    export default {
        props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'limitPerLayoutCounter'],

        data() {
            return {
                isLayoutsDropdownOpen: false,
                dropdownOrientation: "top",
            };
        },

        computed: {
            dropdownClasses() {
                return {
                    "pin-b": this.dropdownOrientation === "top",
                    "mb-3": this.dropdownOrientation === "top",
                    "mt-3": this.dropdownOrientation === "bottom",
                };
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
                
                this.$nextTick(() => {
                    if (this.isLayoutsDropdownOpen) {
                        const { top: dropdownTop } = this.$refs.dropdown.getBoundingClientRect();
                        const { height: buttonHeight } = this.$refs.dropdownButton.getBoundingClientRect();
                        // If the dropdown is popping out of the screen at the top,
                        // move it to the bottom.
                        if (dropdownTop < 0) {
                            this.dropdownOrientation = "bottom";
                            this.$refs.dropdown.style.top = `${buttonHeight}px`;
                        }
                    } else {
                        // Reset the orientation.
                        this.dropdownOrientation = "top";
                    }
                });
            },

            /**
             * Append the given layout to flexible content's list
             */
            addGroup(layout) {
                if (!layout) return;

                this.$emit('addGroup', layout);
                Nova.$emit('nova-flexible-content-add-group', layout);

                this.isLayoutsDropdownOpen = false;
                // Reset the orientation.
                this.dropdownOrientation = "top";
            },
        }
    }
</script>
