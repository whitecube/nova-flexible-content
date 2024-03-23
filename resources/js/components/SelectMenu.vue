<template>
    <div class="w-full" v-if="layouts">
        <div v-if="this.limitCounter > 0 || this.limitCounter === null">
            <div v-if="layouts.length > 1">
                <div class="w-full">
                    <div class="grid grid-cols-3 gap-4">
                        <select class="border  p-2 rounded col-span-2" v-model="selectedLayout">
                            <option :value="null" disabled>Add a section</option>
                            <option v-for="layout in availableLayouts" :value="layout" :key="layout.key">{{ renderLayoutName(layout) }}</option>
                        </select>
                        <button :disabled="!selectedLayout" class="border rounded p-2 disabled:bg-white disabled:text-neutral-800 text-white bg-green-500 duration-150" type="button" @click="selectLayout(selectedLayout)">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


export default {

    props: ['layouts', 'field', 'errors', 'limitCounter', 'limitPerLayoutCounter'],

    data() {
        return {
            selectedLayout: null,
            isLayoutsDropdownOpen: false
        };
    },

    computed: {
        attributes() {
            return {
                selectLabel: this.field.menu.data.selectLabel || __('Press enter to select'),
                label: this.field.menu.data.label || 'title',
                openDirection: this.field.menu.data.openDirection || 'bottom',
            }
        },
        availableLayouts() {
            return this.layouts.filter(layout => (this.limitPerLayoutCounter[layout.name] > 0) || this.limitPerLayoutCounter[layout.name] === undefined);
        },
    },

    methods: {
        selectLayout(value) {
            this.addGroup(value);
        },
        renderLayoutName(layout) {
            return layout.title;
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

<style scoped>
@tailwind base;
@tailwind components;
@tailwind utilities;
</style>
