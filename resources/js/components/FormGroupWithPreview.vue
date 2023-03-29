<template>
    <div
    class="group"
        :id="group.key">
        <div class="w-full relative">
            <div :class="{'group-hover:block hidden': !selectedGroup} "  class="absolute bg-white box-content flex h-8 items-center leading-normal overflow-hidden right-0 rounded-bl-lg shadow top-0 z-20">
            
                <div class="flex" v-if="!readonly">
                    <button
                        dusk="drag-group"
                        type="button"
                        class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block nova-flexible-content-drag-button"
                        :title="__('Drag')"
                        >
                        <icon type="selector" class="align-top" width="16" height="16" />
                    </button>
                    <button
                        dusk="move-up-group"
                        type="button"
                        class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                        :title="__('Move up')"
                        @click.prevent="moveUp">
                        <icon type="arrow-up" class="align-top" width="16" height="16" />
                    </button>
                    <button
                        dusk="move-down-group"
                        type="button"
                        class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                        :title="__('Move down')"
                        @click.prevent="moveDown">
                        <icon type="arrow-down" class="align-top" width="16" height="16" />
                    </button>
                    <button
                        dusk="delete-group"
                        type="button"
                        class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                        :title="__('Delete')"
                        @click.prevent="confirmRemove">
                        <icon type="trash" width="16" height="16" />
                    </button>
                    <delete-flexible-content-group-modal
                        v-if="removeMessage"
                        @confirm="remove"
                        @close="removeMessage=false"
                        :message="field.confirmRemoveMessage"
                        :yes="field.confirmRemoveYes"
                        :no="field.confirmRemoveNo"
                    />
                </div>

            </div>
        </div>
           
        <div 
            v-if="!group.preview" 
            class="py-6 border-2"
            :class="{'border-primary-500 border-dashed' : selectedGroup, '  hover:border-gray-200 border-transparent' : !selectedGroup}">


            <div v-show="fullScreen && selectedGroup" class="absolute top-0 left-0 md:w-1/5 bottom-0 h-full  bg-gray-50 overflow-y-scroll self-stretch">           
                <div class="w-full py-5 overflow-hidden">
                    <div class="px-6 py-6 gap-2 flex flex-row items-center ">
                        <button :aria-label="`Close ${ group.title }`" @click.prevent="$emit('group-selected')">    
                            <icon type="arrow-left" class="align-top rounded-full hover:bg-gray-200 p-2" width="36" height="36" />
                        </button>
                        <h3 class="font-semibold">{{  group.title }}</h3>
                    </div>
                </div>
            </div>
            <component
                @click="($event) => {if(!selectedGroup && group.preview) {$emit('group-selected', $el)}}"
                v-for="(item, index) in group.fields"
                :key="index"
                :is="'form-' + item.component"
                :resource-name="resourceName"
                :resource-id="resourceId"
                :field="item"
                :errors="errors"
                :mode="mode"
                :show-help-text="item.helpText != null"
            />
        
        </div>
        <field-list-with-preview
            v-else
            :fullScreen="fullScreen"
            :fields="group.fields"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :layoutName="group.name"
            :fieldName="field.attribute"
            :errors="errors"
            :stylesheet="field.enablePreview"
            :flexible_key="group.key"
            :selectedGroup="selectedGroup"
            :title="group.title"
            @group-selected="$emit('group-selected', $el)"
        />
    </div>
</template>

<script>
import BehavesAsPanel from 'nova-mixins/BehavesAsPanel';
import FieldListWithPreview from "./FieldListWithPreview";

export default {
    mixins: [BehavesAsPanel],

    components: {  FieldListWithPreview },

    props: {
        errors: {},
        group: {},
        index: {},
        field: {},
        selectedGroup: false,
        fullScreen: false,
    },

    emits: ['move-up', 'move-down', 'remove'],

    data() {
        return {
            removeMessage: false,
            readonly: this.group.readonly,
            storedScrollTop: 0
        };
    },

    computed: {

        containerStyle() {
            let classes = ['grow', 'border-b', 'border-r', 'border-l', 'border-gray-200', 'dark:border-gray-700', 'rounded-b-lg'];

            if (! this.group.title) {
                classes.push('border-t');
                classes.push('rounded-tr-lg');
            }

            return classes;
        }
    },

    methods: {
        /**
         * Move this group up
         */
        moveUp() {
            this.$emit('move-up');
        },

        /**
         * Move this group down
         */
        moveDown() {
            this.$emit('move-down');
        },

        /**
         * Remove this group
         */
        remove() {
            this.$emit('remove');
        },

        /**
         * Confirm remove message
         */
        confirmRemove() {
            if (this.field.confirmRemove){
                this.removeMessage = true;
            } else {
                this.remove()
            }
        },

       
    },
}
</script>

<style>
    .group-control:focus {
        outline: none;
    }
    .group-control:hover {
        color: rgb(var(--colors-primary-400));
    }
    .confirm-message{
        position: absolute;
        overflow: visible;
        right: 38px;
        bottom: 0;
        width: auto;
        border-radius: 4px;
        padding: 6px 7px;
        border: 1px solid #B7CAD6;
        background-color: var(--20);
        white-space: nowrap;
    }
    [dir=rtl] .confirm-message{
        right: auto;
        left: 35px;
    }

    .confirm-message .text-danger {
        color: #ee3f22;
    }

    .rounded-l {
        border-top-left-radius: 0.25rem; /* 4px */
        border-bottom-left-radius: 0.25rem; /* 4px */
    }

    .rounded-t-lg {
        border-top-right-radius: 0.5rem; /* 8px */
        border-top-left-radius: 0.5rem; /* 8px */
    }

    .rounded-b-lg {
        border-bottom-left-radius: 0.5rem; /* 8px */
        border-bottom-right-radius: 0.5rem; /* 8px */
    }

    .box-content {
        box-sizing: content-box;
    }

    .grow {
        flex-grow: 1;
    }

    .grow-0 {
        flex-grow: 0;
    }

    .shrink {
        flex-shrink: 1;
    }

    .shrink-0 {
        flex-shrink: 0;
    }

    .right-\[3px\] {
        right: 3px;
    }

    .group:hover .group-hover\:block {
        display: block;
    }
</style>
