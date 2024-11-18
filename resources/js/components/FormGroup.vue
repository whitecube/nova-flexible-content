<template>
    <div class="relative mb-4 pb-1" :id="group.key">
        <div class="w-full shrink">
            <div :class="titleStyle" v-if="group.title">
                <div class="h-8 leading-normal  flex items-center box-content"
                    :class="{'border-b border-gray-200 dark:border-gray-700 ': !collapsed}">
                  <button
                            dusk="drag-group"
                            type="button"
                            class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block nova-flexible-content-drag-button"
                            :title="__('Drag')"
                            >
                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 mx-2">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
</svg>

                        </button>

                    <button @click.prevent="collapsed? expand() : collapse()" class="text-left text-80 grow">
                      <span class="mr-3 font-semibold">#{{ index + 1 }}</span>
                      {{ group.title }}<span v-if="collapsedPreview">: <span class="mr-3 font-semibold">{{ truncate(collapsedPreview, 30) }}</span></span>
                    </button>

                    <div class="flex" v-if="!readonly">
                       
                     
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

             <transition v-if="field.drawer"
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-show="!collapsed"
                        @click="collapse"
                        class="fixed z-20 inset-0 bg-gray-600/75 dark:bg-gray-900/75"
                        ></div>

                        </transition>

                        <div 
                        :class="{ 
                            'translate-x-full opacity-0': collapsed && field.drawer,
                            'fixed flex flex-col  z-20 top-0  transition duration-200 ease-in right-0 h-screen divide-y divide-gray-200 dark:divide-gray-700 shadow bg-white dark:bg-gray-800 w-screen max-w-4xl ml-auto border-b border-gray-200 dark:border-gray-700 overflow-x-hidden overflow-y-scroll' :  field.drawer,
                            'grow border-b border-r border-l border-gray-200 dark:border-gray-700 rounded-b-lg' : !field.drawer,
                            'hidden': collapsed && !field.drawer }"
                        
                        >          
                            <div v-if="field.drawer" class="-mb-px border-b border-gray-200 z-20 bg-primary-50 sticky top-0 py-5 px-6 md:px-8 flex justify-between">Edit {{ group.title }} <button class="ml-auto inline-block" @click.prevent="collapse">CLOSE</button></div>
                            <component
                                v-for="(item, index) in group.fields"
                                :key="index"
                                :is="'form-' + item.component"
                                :resource-name="resourceName"
                                :resource-id="resourceId"
                                :field="item"
                                :errors="errors"
                                :mode="mode"
                                :show-help-text="item.helpText != null"
                                @change="handleFieldChanged($event, item)"
                                :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
                            />   
                        </div>


            <!-- </transition> -->
</div>
    </div>
</template>

<script>
import BehavesAsPanel from 'nova-mixins/BehavesAsPanel';
import { mapProps } from 'laravel-nova';

export default {
    mixins: [BehavesAsPanel],

    props: {
        errors: {},
        group: {},
        index: {},
        field: {},
        ...mapProps(['mode'])
    },

    emits: ['move-up', 'move-down', 'remove'],

    data() {
        return {
            removeMessage: false,
            collapsed: this.group.collapsed && (window.location.hash.indexOf(`${this.group.name}_${this.group.key}`) == -1),
            readonly: this.group.readonly,
            collapsedPreview: null,
        };
    },

    computed: {
        collapsedPreviewAttribute() {
            return this.field.layouts.find(layout => layout.name === this.group.name)?.collapsedPreviewAttribute;
        },
        titleStyle() {
            let classes = ['border-t', 'border-r', 'border-l', 'border-gray-200', 'dark:border-gray-700', 'rounded-t-lg'];

            if (this.collapsed) {
                classes.push('border-b rounded-b-lg');
            }

            return classes;
        },
        containerStyle() {
            let classes = ['grow', 'border-b', 'border-r', 'border-l', 'border-gray-200', 'dark:border-gray-700', 'rounded-b-lg'];

            if (! this.group.title) {
                classes.push('border-t');
                classes.push('rounded-tr-lg');
            }

            if (this.collapsed) {
                classes.push('hidden');
            }

            return classes;
        }
    },

    mounted() {
        // Object.values(this.group.fields).forEach(field => {
        //   if (this.collapsedPreviewAttribute && this.getFieldAttribute(field) === this.collapsedPreviewAttribute) {
        //     this.collapsedPreview = field.value;
        //   }
        // });

        console.log('collapsed',this.group.collapsed && (window.location.hash.indexOf(`${this.group.name}_${this.group.key}`) == -1)       );


        this.collapsedPreview = Object.values(this.group.fields).find(field => {
          return this.getFieldAttribute(field) === this.collapsedPreviewAttribute;
        })?.value;   

    },

    methods: {
        /**
         * Move this group up
         */
        moveUp() {
            this.$emit('move-up');
        },

        getFieldAttribute(item) {
            return item.attribute.split('__').pop();
        },

         truncate(value, length) {
            if (value.length > length) {
                return value.substring(0, length) + "...";
            } else {
                return value;
            }
        },

         handleFieldChanged(event, item) {
            if (this.getFieldAttribute(item) === this.collapsedPreviewAttribute) {
                this.collapsedPreview = event.target.value;
            }
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

        /**
         * Expand fields
         */
        expand() {
            this.collapsed = false;
        },

        /**
         * Collapse fields
         */
        collapse() {
            this.collapsed = true;
        }
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
</style>