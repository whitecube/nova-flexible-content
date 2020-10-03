<template>
    <div class="relative flex bg-white mb-4 pb-1" :id="group.key">
        <div class="z-10 bg-white border-t border-l border-b border-60 h-auto pin-l pin-t rounded-l self-start w-8">
            <button
                dusk="expand-group"
                type="button"
                class="group-control btn border-r border-40 w-8 h-8 block"
                :title="__('Expand')"
                @click.prevent="expand"
                v-if="collapsed">
                <icon class="align-top" type="plus-square" width="16" height="16" view-box="0 0 24 24" />
            </button>
            <div v-if="!collapsed">
                <button
                    dusk="collapse-group"
                    type="button"
                    class="group-control btn border-r border-40 w-8 h-8 block"
                    :title="__('Collapse')"
                    @click.prevent="collapse">
                    <icon class="align-top" type="minus-square" width="16" height="16" view-box="0 0 24 24" />
                </button>
                <div v-if="!readonly">
                    <button
                        dusk="move-up-group"
                        type="button"
                        class="group-control btn border-t border-r border-40 w-8 h-8 block"
                        :title="__('Move up')"
                        @click.prevent="moveUp">
                        <icon type="arrow-up" view-box="0 0 8 4.8" width="10" height="10" />
                    </button>
                    <button
                        dusk="move-down-group"
                        type="button"
                        class="group-control btn border-t border-r border-40 w-8 h-8 block"
                        :title="__('Move down')"
                        @click.prevent="moveDown">
                        <icon type="arrow-down" view-box="0 0 8 4.8" width="10" height="10" />
                    </button>
                    <button
                        dusk="delete-group"
                        type="button"
                        class="group-control btn border-t border-r border-40 w-8 h-8 block"
                        :title="__('Delete')"
                        @click.prevent="confirmRemove">
                        <icon type="delete" view-box="0 0 20 20" width="16" height="16" />
                    </button>
                    <portal to="modals">
                        <delete-flexible-content-group-modal
                            v-if="removeMessage"
                            @confirm="remove"
                            @close="removeMessage=false"
                            :message="field.confirmRemoveMessage"
                            :yes="field.confirmRemoveYes"
                            :no="field.confirmRemoveNo"
                        />
                    </portal>
                </div>
            </div>
        </div>
        <div class="-mb-1 flex flex-col min-h-full w-full">
            <div :class="titleStyle" v-if="group.title">
                <div class="leading-normal py-1 px-8"
                    :class="{'border-b border-40': !collapsed}">
                    <p class="text-80">
                      <span class="mr-4 font-semibold">#{{ index + 1 }}</span>
                      {{ group.title }}
                    </p>
                </div>
            </div>
            <div :class="containerStyle">
                <component
                    v-for="(item, index) in group.fields"
                    :key="index"
                    :is="'form-' + item.component"
                    :resource-name="resourceName"
                    :resource-id="resourceId"
                    :resource="resource"
                    :field="item"
                    :errors="errors"
                    :show-help-text="item.helpText != null"
                    :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { BehavesAsPanel } from 'laravel-nova';

export default {
    mixins: [BehavesAsPanel],

    props: ['errors', 'group', 'index', 'field'],

    data() {
        return {
            removeMessage: false,
            collapsed: this.group.collapsed,
            readonly: this.group.readonly,
        };
    },

    computed: {
        titleStyle() {
            let classes = ['border-t', 'border-r', 'border-60', 'rounded-tr-lg'];
            if (this.collapsed) {
                classes.push('border-b rounded-br-lg');
            }
            return classes;
        },
        containerStyle() {
            let classes = ['flex-grow', 'border-b', 'border-r', 'border-l', 'border-60', 'rounded-b-lg'];
            if(!this.group.title) {
                classes.push('border-t');
                classes.push('rounded-tr-lg');
            }
            if (this.collapsed) {
                classes.push('hidden');
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
            if(this.field.confirmRemove){
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
    .group-control path {
        fill: #B7CAD6;
        transition: fill 200ms ease-out;
    }
    .group-control:hover path {
        fill: var(--primary);
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

    .closebtn {
        /*color: #B7CAD6;*/
    }
</style>
