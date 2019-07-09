<template>
    <div class="relative bg-white pl-8 mb-4" :id="group.key">
        <div class="w-full">
            <div :class="titleStyle" v-if="group.title">
                <div class="border-b border-40 leading-normal py-2 px-8">
                    <p class="text-80">{{ group.title }}</p>
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
                    :errors="validationErrors"
                    :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
                />
            </div>
        </div>
        <div class="absolute z-10 bg-white border-t border-l border-b border-60 rounded-l pin-l pin-t w-8" >
            <button class="group-control btn border-r border-40 w-8 h-8 block" title="Move up" @click.prevent="moveUp">
                <icon type="arrow-up" view-box="0 0 8 4.8" width="10" height="10" />
            </button>
            <button class="group-control btn border-t border-r border-40 w-8 h-8 block" title="Move down" @click.prevent="moveDown">
                <icon type="arrow-down" view-box="0 0 8 4.8" width="10" height="10" />
            </button>
            <button class="group-control btn border-t border-r border-40 w-8 h-8 block" title="Delete" @click.prevent="confirmRemove">
                <icon type="delete" view-box="0 0 20 20" width="16" height="16" />
            </button>
            <div v-if="removeMessage" class="confirm-message">
                <span v-if="field.confirmRemoveMessage">{{ field.confirmRemoveMessage }}</span>
                <button @click.prevent="remove" class="text-danger btn mx-1 focus:outline-none">{{ field.confirmRemoveYes }}</button>
                <button @click.prevent="removeMessage=false" class="text-80 btn closebtn focus:outline-none">{{ field.confirmRemoveNo }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import { BehavesAsPanel } from 'laravel-nova'

export default {
    mixins: [BehavesAsPanel],

    props: ['validationErrors', 'group', 'field'],

    data() {
        return {
            removeMessage: false,
        };
    },

    computed: {
        titleStyle() {
            return ['border-t', 'border-r', 'border-60', 'rounded-tr-lg'];
        },
        containerStyle() {
            let classes = ['border-b', 'border-r', 'border-l', 'border-60', 'rounded-b-lg'];
            if(!this.group.title) {
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
            if(this.field.confirmRemove){
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
        fill: var(--20);
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
        color: #B7CAD6;
    }
</style>
